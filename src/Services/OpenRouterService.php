<?php
declare(strict_types=1);
namespace TCM\Services;

/**
 * OpenRouter AI Service
 * Handles AI task generation using OpenRouter API
 */
final class OpenRouterService
{
    private string $apiKey;
    private string $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
    private string $model = 'openai/gpt-4o-mini'; // Cost-effective, good quality

    public function __construct()
    {
        $this->apiKey = config('openrouter.api_key', '');
        if (empty($this->apiKey)) {
            throw new \RuntimeException('OpenRouter API key not configured in .env');
        }
    }

    /**
     * Generate personalized daily tasks for a student
     * 
     * @param array $student Student data
     * @param array $enrolledCourses Courses student is enrolled in
     * @param array $progress Student's learning progress
     * @return array Generated tasks
     */
    public function generateDailyTasks(array $student, array $enrolledCourses, array $progress): array
    {
        $prompt = $this->buildTaskPrompt($student, $enrolledCourses, $progress);
        
        $response = $this->callAPI($prompt);
        
        return $this->parseTasks($response);
    }

    /**
     * Build prompt for AI based on student context
     */
    private function buildTaskPrompt(array $student, array $courses, array $progress): string
    {
        $studentName = $student['name'] ?? 'Student';
        $completedLessons = $progress['completed_lessons'] ?? 0;
        $totalLessons = $progress['total_lessons'] ?? 0;
        $progressPercent = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;

        // Build detailed course context with next lessons
        $courseContext = '';
        $byCourse = $progress['by_course'] ?? [];
        
        foreach ($courses as $course) {
            $courseId = (int)$course['id'];
            $courseData = $byCourse[$courseId] ?? null;
            
            if ($courseData) {
                $completed = $courseData['completed_lessons'] ?? 0;
                $total = $courseData['total_lessons'] ?? 0;
                $percent = $courseData['progress'] ?? 0;
                $nextLessons = $courseData['next_lessons'] ?? [];
                
                $courseContext .= "\n\n📚 {$course['title']}:";
                $courseContext .= "\n   Progress: {$percent}% ({$completed}/{$total} lessons completed)";
                
                if (!empty($nextLessons)) {
                    $courseContext .= "\n   Next lessons to complete:";
                    foreach (array_slice($nextLessons, 0, 3) as $lesson) {
                        $courseContext .= "\n   - {$lesson['title']} (Module: {$lesson['module_title']})";
                    }
                } else {
                    $courseContext .= "\n   ✅ All lessons completed! Ready for practice projects.";
                }
            } else {
                $courseContext .= "\n\n📚 {$course['title']}: Just started (0%)";
            }
        }

        // Add recent completions if any
        $recentContext = '';
        if (!empty($progress['recent_completions'])) {
            $recentContext = "\n\nRecently Completed Lessons:";
            foreach (array_slice($progress['recent_completions'], 0, 3) as $recent) {
                $recentContext .= "\n- ✅ {$recent['lesson_title']} ({$recent['course_title']})";
            }
        }

        return <<<PROMPT
You are a personalized learning assistant for The Code Munk (TCM) students. Generate 3-5 SPECIFIC, ACTIONABLE coding tasks for {$studentName} based on their ACTUAL progress.

Student Context:
- Name: {$studentName}
- Overall Progress: {$progressPercent}%
- Completed Lessons: {$completedLessons}/{$totalLessons}{$recentContext}

Enrolled Courses & Progress:{$courseContext}

🎯 TASK GENERATION RULES:

1. **SPECIFIC TITLES** - Use exact problem statements:
   ✅ "Fix the missing semicolon error in JavaScript code"
   ✅ "Create a 3-column responsive layout using CSS Grid"
   ✅ "Build a student registration form with validation"
   ❌ "Complete the next lesson"
   ❌ "Practice HTML"

2. **DETAILED DESCRIPTIONS** - Include:
   - WHAT to build (exact requirements)
   - WHICH concepts to use (specific techniques)
   - EXPECTED outcome (what should work)
   - SAMPLE data or structure to use

3. **REALISTIC CODING PROBLEMS**:
   - Beginner: "Create a table showing 5 products with name, price, and quantity"
   - Intermediate: "Build a todo list with add/delete/mark complete features"
   - Advanced: "Implement user authentication with login/logout functionality"

4. **BASE ON COMPLETED WORK**:
   - If they completed HTML lessons → Give HTML table/form building tasks
   - If they completed CSS lessons → Give styling/layout tasks
   - If they completed JS lessons → Give interactive functionality tasks

5. **DIFFICULTY MATCHING**:
   - 0-30% progress = Easy (basic syntax, simple HTML/CSS)
   - 31-70% progress = Medium (combining concepts, small projects)
   - 71-100% progress = Hard (full features, real-world problems)

6. **TIME**: 30-90 minutes per task

7. **EXPANDED DETAILS**: Description should be 3-5 sentences minimum with:
   - Problem statement
   - Technical requirements
   - Expected result
   - Optional: Sample data/structure

EXAMPLES OF PERFECT TASKS:

✅ BEGINNER (HTML/CSS):
{
  "title": "Create a student information table with 5 columns",
  "description": "Build an HTML table displaying student data with columns: ID, Name, Email, Course, and Grade. Use proper table headers (<thead>), body (<tbody>), and style it with CSS to have borders, alternating row colors, and centered text. Include at least 5 student records.",
  "difficulty": "Easy",
  "estimated_time": 30
}

✅ INTERMEDIATE (JavaScript):
{
  "title": "Build a simple calculator with 4 operations",
  "description": "Create a calculator that can add, subtract, multiply, and divide two numbers. Use HTML for the interface (input fields and buttons), CSS for styling, and JavaScript functions for each operation. Display the result on the page. Handle division by zero error.",
  "difficulty": "Medium",
  "estimated_time": 60
}

✅ ADVANCED (Full Project):
{
  "title": "Develop a todo list app with localStorage",
  "description": "Build a complete todo list application where users can add, delete, and mark tasks as complete. Use HTML for structure, CSS for styling (modern card design), JavaScript for functionality, and localStorage to save tasks even after page refresh. Include a counter showing pending vs completed tasks.",
  "difficulty": "Hard",
  "estimated_time": 90
}

Response Format (STRICT JSON ONLY, NO MARKDOWN):
[
  {
    "title": "Specific, actionable task title (50-80 chars)",
    "description": "3-5 sentences with exact requirements, technical details, and expected outcome (150-300 chars)",
    "course_id": 123,
    "course_title": "Course name",
    "difficulty": "Easy|Medium|Hard",
    "estimated_time": 30-90,
    "tags": ["specific-tech", "hands-on", "practice"],
    "motivation": "Short encouraging message"
  }
]

Generate {$studentName}'s practice tasks NOW with MAXIMUM DETAIL:
PROMPT;
    }

    /**
     * Call OpenRouter API
     */
    private function callAPI(string $prompt): string
    {
        $data = [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful learning assistant. Always respond with valid JSON only.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 1500,
        ];

        $ch = curl_init($this->apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . config('app.url'),
                'X-Title: TCM Learning Platform'
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException("OpenRouter API error: HTTP {$httpCode}");
        }

        $decoded = json_decode($response, true);
        
        if (!isset($decoded['choices'][0]['message']['content'])) {
            throw new \RuntimeException('Invalid API response format');
        }

        return $decoded['choices'][0]['message']['content'];
    }

    /**
     * Parse AI response into task array
     */
    private function parseTasks(string $response): array
    {
        // Extract JSON from response (in case AI adds extra text)
        preg_match('/\[.*\]/s', $response, $matches);
        $jsonString = $matches[0] ?? $response;

        $tasks = json_decode($jsonString, true);

        if (!is_array($tasks)) {
            throw new \RuntimeException('Failed to parse AI response');
        }

        // Validate and clean tasks
        return array_map(function($task) {
            return [
                'title' => substr($task['title'] ?? 'Complete learning activity', 0, 80),
                'description' => $task['description'] ?? '',
                'course_id' => (int)($task['course_id'] ?? 0),
                'course_title' => $task['course_title'] ?? 'General',
                'difficulty' => in_array($task['difficulty'] ?? '', ['Easy', 'Medium', 'Hard']) 
                    ? $task['difficulty'] 
                    : 'Medium',
                'estimated_time' => (int)($task['estimated_time'] ?? 30),
                'tags' => $task['tags'] ?? [],
                'motivation' => $task['motivation'] ?? '💪 You got this!',
            ];
        }, $tasks);
    }

    /**
     * Get progress percentage for a specific course
     */
    private function getCourseProgress(array $course, array $progress): int
    {
        // Calculate based on completed lessons in this course
        $courseId = $course['id'] ?? 0;
        $byCourse = $progress['by_course'] ?? [];
        
        if (isset($byCourse[$courseId])) {
            return (int)($byCourse[$courseId]['progress'] ?? 0);
        }
        
        return 0;
    }
}
