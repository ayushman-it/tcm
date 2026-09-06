<?php
declare(strict_types=1);
namespace TCM\Models;

use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Services\OpenRouterService;

/**
 * Daily Task Model
 * AI-generated personalized learning tasks for students
 */
final class DailyTask extends Model
{
    protected static string $table = 'daily_tasks';

    /**
     * Ensure daily_tasks table exists
     */
    public static function ensureTable(): void
    {
        $exists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables 
             WHERE table_schema = DATABASE() AND table_name = 'daily_tasks'"
        );

        if ($exists) return;

        Database::run("CREATE TABLE daily_tasks (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            title VARCHAR(200) NOT NULL,
            description TEXT NOT NULL,
            course_id BIGINT UNSIGNED DEFAULT NULL,
            course_title VARCHAR(200) DEFAULT NULL,
            difficulty ENUM('Easy','Medium','Hard') DEFAULT 'Medium',
            estimated_time INT DEFAULT 30 COMMENT 'minutes',
            tags JSON DEFAULT NULL,
            motivation VARCHAR(255) DEFAULT NULL,
            status ENUM('pending','in_progress','completed','skipped') DEFAULT 'pending',
            completed_at DATETIME DEFAULT NULL,
            week_start DATE NOT NULL COMMENT 'Friday of the week',
            generated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            notified_at DATETIME DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_user_date (user_id, week_start, status),
            KEY idx_course (course_id),
            CONSTRAINT fk_dt_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    }

    /**
     * Generate tasks for a student using AI
     */
    public static function generateForStudent(int $userId): array
    {
        self::ensureTable();

        // Get student data
        $student = Database::first("SELECT * FROM users WHERE id = ?", [$userId]);
        if (!$student) return [];

        // Get enrolled courses
        $courses = Database::all(
            "SELECT c.* FROM courses c
             JOIN enrollments e ON e.course_id = c.id
             WHERE e.user_id = ? AND e.status = 'active'",
            [$userId]
        );

        if (empty($courses)) {
            // No courses enrolled - create generic tasks
            return self::createGenericTasks($userId);
        }

        // Get learning progress
        $progress = self::getStudentProgress($userId);

        // Call AI service
        try {
            $aiService = new OpenRouterService();
            $tasks = $aiService->generateDailyTasks($student, $courses, $progress);
        } catch (\Exception $e) {
            error_log("AI Task Generation failed: " . $e->getMessage());
            return self::createFallbackTasks($userId, $courses);
        }

        // Save tasks to database
        $weekStart = self::getCurrentWeekStart();
        $savedTasks = [];

        foreach ($tasks as $task) {
            $taskId = Database::insert('daily_tasks', [
                'user_id' => $userId,
                'title' => $task['title'],
                'description' => $task['description'],
                'course_id' => $task['course_id'] ?: null,
                'course_title' => $task['course_title'],
                'difficulty' => $task['difficulty'],
                'estimated_time' => $task['estimated_time'],
                'tags' => json_encode($task['tags']),
                'motivation' => $task['motivation'],
                'week_start' => $weekStart,
                'status' => 'pending',
            ]);

            $savedTasks[] = array_merge($task, ['id' => $taskId]);
        }

        return $savedTasks;
    }

    /**
     * Get current week's tasks for a student
     */
    public static function getCurrentWeekTasks(int $userId): array
    {
        self::ensureTable();
        $weekStart = self::getCurrentWeekStart();

        return Database::all(
            "SELECT * FROM daily_tasks 
             WHERE user_id = ? AND week_start = ? 
             ORDER BY 
                FIELD(status, 'in_progress', 'pending', 'completed', 'skipped'),
                difficulty DESC,
                id ASC",
            [$userId, $weekStart]
        );
    }

    /**
     * Mark task as completed
     */
    public static function complete(int $taskId, int $userId): bool
    {
        $updated = Database::update('daily_tasks', [
            'status' => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
        ], [
            'id' => $taskId,
            'user_id' => $userId,
        ]);

        return $updated > 0;
    }

    /**
     * Update task status
     */
    public static function updateStatus(int $taskId, int $userId, string $status): bool
    {
        if (!in_array($status, ['pending', 'in_progress', 'completed', 'skipped'])) {
            return false;
        }

        $data = ['status' => $status];
        if ($status === 'completed') {
            $data['completed_at'] = date('Y-m-d H:i:s');
        }

        return Database::update('daily_tasks', $data, [
            'id' => $taskId,
            'user_id' => $userId,
        ]) > 0;
    }

    /**
     * Get Friday of current week (week starts on Friday)
     */
    private static function getCurrentWeekStart(): string
    {
        $today = new \DateTime();
        $dayOfWeek = (int)$today->format('N'); // 1=Mon, 7=Sun
        
        // If today is Friday (5) or after, this week's Friday
        // If before Friday, last Friday
        if ($dayOfWeek >= 5) {
            $daysToFriday = $dayOfWeek - 5;
            $friday = $today->modify("-{$daysToFriday} days");
        } else {
            $daysToLastFriday = $dayOfWeek + 2; // Mon=3, Thu=6
            $friday = $today->modify("-{$daysToLastFriday} days");
        }

        return $friday->format('Y-m-d');
    }

    /**
     * Get student learning progress based on completed lessons
     */
    private static function getStudentProgress(int $userId): array
    {
        // Get actual completed lessons from lesson_progress table
        $completedLessons = Database::all(
            "SELECT 
                lp.lesson_id,
                lp.completed,
                lp.completed_at,
                cl.title as lesson_title,
                cm.course_id,
                c.title as course_title
             FROM lesson_progress lp
             JOIN course_lessons cl ON cl.id = lp.lesson_id
             JOIN course_modules cm ON cm.id = cl.module_id
             JOIN courses c ON c.id = cm.course_id
             WHERE lp.user_id = ? AND lp.completed = 1
             ORDER BY lp.completed_at DESC",
            [$userId]
        );

        // Get all enrolled courses with lesson counts
        $courseStats = Database::all(
            "SELECT 
                c.id as course_id,
                c.title as course_title,
                COUNT(DISTINCT cl.id) as total_lessons,
                COUNT(DISTINCT CASE WHEN lp.completed = 1 THEN lp.lesson_id END) as completed_lessons,
                e.progress
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             LEFT JOIN course_modules cm ON cm.course_id = c.id
             LEFT JOIN course_lessons cl ON cl.module_id = cm.id
             LEFT JOIN lesson_progress lp ON lp.lesson_id = cl.id AND lp.user_id = ?
             WHERE e.user_id = ? AND e.status = 'active'
             GROUP BY c.id, c.title, e.progress",
            [$userId, $userId]
        );

        $byCourse = [];
        $totalCompleted = 0;
        $totalLessons = 0;

        foreach ($courseStats as $stat) {
            $courseId = (int)$stat['course_id'];
            $completed = (int)$stat['completed_lessons'];
            $total = (int)$stat['total_lessons'];
            
            $byCourse[$courseId] = [
                'course_title' => $stat['course_title'],
                'completed_lessons' => $completed,
                'total_lessons' => $total,
                'progress' => (int)$stat['progress'],
                'next_lessons' => self::getNextLessons($userId, $courseId, 3)
            ];

            $totalCompleted += $completed;
            $totalLessons += $total;
        }

        return [
            'completed_lessons' => $totalCompleted,
            'total_lessons' => $totalLessons,
            'by_course' => $byCourse,
            'recent_completions' => array_slice($completedLessons, 0, 5),
        ];
    }

    /**
     * Get next incomplete lessons for a course
     */
    private static function getNextLessons(int $userId, int $courseId, int $limit = 3): array
    {
        return Database::all(
            "SELECT 
                cl.id,
                cl.title,
                cl.type,
                cm.title as module_title
             FROM course_lessons cl
             JOIN course_modules cm ON cm.id = cl.module_id
             LEFT JOIN lesson_progress lp ON lp.lesson_id = cl.id AND lp.user_id = ?
             WHERE cm.course_id = ? 
             AND (lp.completed IS NULL OR lp.completed = 0)
             ORDER BY cm.order_index ASC, cl.order_index ASC
             LIMIT ?",
            [$userId, $courseId, $limit]
        );
    }

    /**
     * Create generic tasks for students with no enrollments
     */
    private static function createGenericTasks(int $userId): array
    {
        $weekStart = self::getCurrentWeekStart();
        $genericTasks = [
            [
                'title' => '🎯 Explore Available Courses',
                'description' => 'Browse our course catalog and enroll in a course that interests you.',
                'difficulty' => 'Easy',
                'estimated_time' => 15,
            ],
            [
                'title' => '💼 Build Your Portfolio',
                'description' => 'Add your projects, skills, and achievements to your TCM portfolio.',
                'difficulty' => 'Easy',
                'estimated_time' => 20,
            ],
            [
                'title' => '🤝 Join Community',
                'description' => 'Connect with fellow students and introduce yourself in the community.',
                'difficulty' => 'Easy',
                'estimated_time' => 10,
            ],
        ];

        $tasks = [];
        foreach ($genericTasks as $task) {
            $taskId = Database::insert('daily_tasks', [
                'user_id' => $userId,
                'title' => $task['title'],
                'description' => $task['description'],
                'difficulty' => $task['difficulty'],
                'estimated_time' => $task['estimated_time'],
                'week_start' => $weekStart,
                'status' => 'pending',
            ]);
            $tasks[] = array_merge($task, ['id' => $taskId]);
        }

        return $tasks;
    }

    /**
     * Fallback tasks if AI fails - Generate based on completed lessons
     */
    private static function createFallbackTasks(int $userId, array $courses): array
    {
        $weekStart = self::getCurrentWeekStart();
        $progress = self::getStudentProgress($userId);
        $tasks = [];

        // Specific task templates based on course type and progress
        $taskTemplates = [
            'html' => [
                'easy' => [
                    ['title' => 'Create a student bio page with headings and paragraphs', 'desc' => 'Build an HTML page with your name, photo, education details, and hobbies. Use proper heading tags (h1, h2), paragraphs, and an image tag. Include at least 3 sections with different headings.', 'time' => 30],
                    ['title' => 'Build a product catalog table with 5 items', 'desc' => 'Create an HTML table showing 5 products. Include columns: Product Name, Price, Category, Stock Status, and Action. Use <thead> for headers and <tbody> for data. Style with basic CSS borders.', 'time' => 35],
                ],
                'medium' => [
                    ['title' => 'Design a registration form with 8 input fields', 'desc' => 'Create a complete registration form with fields: Name, Email, Password, Confirm Password, Phone, Gender (radio), Course (dropdown), and Terms checkbox. Use proper input types and required attributes.', 'time' => 45],
                    ['title' => 'Build a navigation menu with 5 pages', 'desc' => 'Create a horizontal navigation bar linking to Home, About, Courses, Contact, and Login pages. Use <nav>, <ul>, and <li> tags. Style with CSS to have hover effects and active state.', 'time' => 50],
                ],
            ],
            'css' => [
                'easy' => [
                    ['title' => 'Style a card component with hover effect', 'desc' => 'Create a card showing an image, title, description, and button. Apply CSS for rounded corners, shadow, padding, and a smooth hover animation that lifts the card and changes shadow.', 'time' => 40],
                    ['title' => 'Create a 3-column responsive layout using Flexbox', 'desc' => 'Build a page with 3 equal-width columns using CSS Flexbox. Each column should have a heading, image, and text. On mobile (below 768px), columns should stack vertically.', 'time' => 45],
                ],
                'medium' => [
                    ['title' => 'Build a responsive pricing table with 3 plans', 'desc' => 'Create a pricing comparison table with Basic, Pro, and Enterprise plans. Include plan name, price, features list, and CTA button. Use CSS Grid for layout and make it responsive for mobile devices.', 'time' => 60],
                    ['title' => 'Design a modern login page with animations', 'desc' => 'Create a centered login form with username, password fields, and submit button. Add gradient background, glassmorphism effect on form, smooth transitions, and input focus animations.', 'time' => 55],
                ],
            ],
            'javascript' => [
                'easy' => [
                    ['title' => 'Build a simple calculator with 4 operations', 'desc' => 'Create a calculator that adds, subtracts, multiplies, and divides two numbers. Use input fields for numbers, buttons for operations, and display result on page. Handle division by zero with an error message.', 'time' => 50],
                    ['title' => 'Create a color changer button for page background', 'desc' => 'Add a button that changes the page background color randomly when clicked. Use JavaScript to generate random RGB values and apply them to document.body.style.backgroundColor. Show current color code on page.', 'time' => 35],
                ],
                'medium' => [
                    ['title' => 'Develop a todo list with add, delete, and complete features', 'desc' => 'Build a todo list where users can add tasks, mark them complete (strikethrough), and delete them. Use DOM manipulation (createElement, appendChild, removeChild). Display total tasks and completed count.', 'time' => 70],
                    ['title' => 'Create a form validator with 5 validation rules', 'desc' => 'Build a registration form with validation: email format, password minimum 8 characters, confirm password match, phone number 10 digits, and required fields. Show error messages and prevent submission if invalid.', 'time' => 65],
                ],
            ],
        ];

        foreach ($courses as $course) {
            $courseId = (int)$course['id'];
            $courseProgress = $progress['by_course'][$courseId] ?? null;
            
            if (!$courseProgress) continue;

            $progressPercent = $courseProgress['progress'];
            $completedCount = $courseProgress['completed_lessons'];
            $nextLessons = $courseProgress['next_lessons'] ?? [];
            
            // Determine course type from title/slug
            $courseType = 'html'; // default
            $courseTitle = strtolower($course['title']);
            if (str_contains($courseTitle, 'css')) $courseType = 'css';
            elseif (str_contains($courseTitle, 'javascript') || str_contains($courseTitle, 'js')) $courseType = 'javascript';
            
            // Determine difficulty
            $difficulty = $progressPercent < 30 ? 'Easy' : ($progressPercent < 70 ? 'Medium' : 'Hard');
            $difficultyKey = strtolower($difficulty);
            
            // Get appropriate task template
            if (empty($nextLessons)) {
                // Course completed - create advanced project task
                $taskId = Database::insert('daily_tasks', [
                    'user_id' => $userId,
                    'title' => "Build a complete portfolio website using {$course['title']}",
                    'description' => "Create a full portfolio website applying all concepts from {$course['title']}. Include: homepage with hero section, about page, projects gallery, contact form, and responsive navigation. Use modern design principles and ensure mobile responsiveness.",
                    'course_id' => $courseId,
                    'course_title' => $course['title'],
                    'difficulty' => 'Hard',
                    'estimated_time' => 90,
                    'tags' => json_encode(['project', 'portfolio', 'complete']),
                    'motivation' => 'Time to build something impressive for your portfolio!',
                    'week_start' => $weekStart,
                    'status' => 'pending',
                ]);
                $tasks[] = ['id' => $taskId, 'title' => "Portfolio Project"];
            } else {
                // Get specific task from templates
                $templates = $taskTemplates[$courseType][$difficultyKey] ?? $taskTemplates['html']['easy'];
                $template = $templates[array_rand($templates)];
                
                $taskId = Database::insert('daily_tasks', [
                    'user_id' => $userId,
                    'title' => $template['title'],
                    'description' => $template['desc'],
                    'course_id' => $courseId,
                    'course_title' => $course['title'],
                    'difficulty' => $difficulty,
                    'estimated_time' => $template['time'],
                    'tags' => json_encode(['practice', $courseType, 'hands-on']),
                    'motivation' => "Perfect practice for what you've learned in {$course['title']}!",
                    'week_start' => $weekStart,
                    'status' => 'pending',
                ]);
                $tasks[] = [
                    'id' => $taskId,
                    'title' => $template['title'],
                    'course_id' => $courseId,
                ];
            }

            // Limit to 3-4 tasks
            if (count($tasks) >= 3) break;
        }

        return $tasks;
    }
}
