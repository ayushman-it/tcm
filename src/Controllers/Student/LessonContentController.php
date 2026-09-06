<?php
namespace TCM\Controllers\Student;

use TCM\Core\Controller;
use TCM\Services\AIContentGenerator;
use Exception;

class LessonContentController extends Controller
{
    private AIContentGenerator $aiGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('student');
        $this->aiGenerator = new AIContentGenerator($this->db);
    }

    /**
     * Get lesson content with examples and exercises
     * GET /student/lesson-content/{lessonId}
     */
    public function getContent(int $lessonId): void
    {
        try {
            // Check if student is enrolled in this course
            if (!$this->isEnrolled($lessonId)) {
                $this->jsonError('You are not enrolled in this course', 403);
                return;
            }

            $language = $_GET['lang'] ?? 'hi';
            $content = $this->aiGenerator->getContent($lessonId, $language);

            if (!$content || $content['status'] !== 'published') {
                $this->jsonError('Content not available yet', 404);
                return;
            }

            // Format response based on language preference
            $response = $this->formatContent($content, $language);

            // Mark lesson as viewed
            $this->markViewed($lessonId);

            $this->jsonSuccess($response);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Submit exercise solution
     * POST /student/lesson-content/submit-exercise
     */
    public function submitExercise(): void
    {
        try {
            $data = $this->getJsonInput();
            
            $lessonId = $data['lesson_id'] ?? null;
            $exerciseIndex = $data['exercise_index'] ?? null;
            $code = $data['code'] ?? '';

            if (!$lessonId || $exerciseIndex === null) {
                $this->jsonError('Missing required fields', 400);
                return;
            }

            // Check enrollment
            if (!$this->isEnrolled($lessonId)) {
                $this->jsonError('You are not enrolled in this course', 403);
                return;
            }

            // Save submission
            $stmt = $this->db->prepare("
                INSERT INTO exercise_submissions (
                    user_id, lesson_id, exercise_index, code, attempts, submitted_at
                ) VALUES (?, ?, ?, ?, 1, NOW())
                ON DUPLICATE KEY UPDATE
                    code = VALUES(code),
                    attempts = attempts + 1,
                    submitted_at = NOW()
            ");
            $stmt->execute([
                $this->user['id'],
                $lessonId,
                $exerciseIndex,
                $code
            ]);

            // TODO: Add AI-based code evaluation here
            // For now, just mark as pending review

            $this->jsonSuccess([
                'message' => 'Exercise submitted successfully',
                'status' => 'pending'
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get student's exercise submissions
     * GET /student/lesson-content/my-submissions/{lessonId}
     */
    public function getMySubmissions(int $lessonId): void
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    exercise_index,
                    code,
                    status,
                    feedback,
                    attempts,
                    submitted_at
                FROM exercise_submissions
                WHERE user_id = ? AND lesson_id = ?
                ORDER BY exercise_index, submitted_at DESC
            ");
            $stmt->execute([$this->user['id'], $lessonId]);
            $submissions = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->jsonSuccess(['submissions' => $submissions]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Check if student is enrolled in the course containing this lesson
     */
    private function isEnrolled(int $lessonId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM enrollments e
            JOIN course_modules m ON m.course_id = e.course_id
            JOIN course_lessons l ON l.module_id = m.id
            WHERE e.user_id = ? AND l.id = ? AND e.status = 'active'
        ");
        $stmt->execute([$this->user['id'], $lessonId]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Mark lesson as viewed
     */
    private function markViewed(int $lessonId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO lesson_progress (user_id, lesson_id, completed, completed_at)
            VALUES (?, ?, 0, NULL)
        ");
        $stmt->execute([$this->user['id'], $lessonId]);
    }

    /**
     * Format content based on language preference
     */
    private function formatContent(array $content, string $language): array
    {
        $suffix = $language === 'hi' ? '_hi' : '_en';
        
        return [
            'lesson_id' => $content['lesson_id'],
            'overview' => $content['overview' . $suffix] ?? $content['overview_en'],
            'key_concepts' => array_map(function($concept) use ($suffix) {
                return [
                    'title' => $concept['title' . $suffix] ?? $concept['title_en'],
                    'explanation' => $concept['explanation' . $suffix] ?? $concept['explanation_en'],
                    'importance' => $concept['importance'] ?? ''
                ];
            }, $content['key_concepts']),
            'explanation' => $content['explanation' . $suffix] ?? $content['explanation_en'],
            'code_examples' => array_map(function($example) use ($suffix) {
                return [
                    'title' => $example['title'],
                    'description' => $example['description' . $suffix] ?? $example['description_en'],
                    'code' => $example['code'],
                    'output' => $example['output'] ?? '',
                    'explanation' => $example['explanation' . $suffix] ?? $example['explanation_en']
                ];
            }, $content['code_examples']),
            'exercises' => array_map(function($exercise, $index) use ($suffix) {
                return [
                    'index' => $index,
                    'title' => $exercise['title'],
                    'difficulty' => $exercise['difficulty'],
                    'description' => $exercise['description' . $suffix] ?? $exercise['description_en'],
                    'starter_code' => $exercise['starter_code'] ?? '',
                    'hints' => $exercise['hints'] ?? []
                ];
            }, $content['exercises'], array_keys($content['exercises'])),
            'resources' => array_map(function($resource) use ($suffix) {
                return [
                    'type' => $resource['type'],
                    'title' => $resource['title'],
                    'url' => $resource['url'] ?? null,
                    'description' => $resource['description' . $suffix] ?? $resource['description_en']
                ];
            }, $content['resources'] ?? []),
            'estimated_time' => $content['estimated_time'],
            'difficulty_level' => $content['difficulty_level']
        ];
    }
}
