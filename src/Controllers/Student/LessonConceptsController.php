<?php
namespace TCM\Controllers\Student;

use TCM\Core\Controller;
use TCM\Services\AIContentGenerator;
use Exception;

/**
 * On-Demand Lesson Concepts Generator for Students
 * Generates AI concepts automatically when student expands a lesson
 */
class LessonConceptsController extends Controller
{
    private AIContentGenerator $aiGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('student');
        $this->aiGenerator = new AIContentGenerator($this->db);
    }

    /**
     * Get or Generate concepts for a lesson
     * GET /student/lesson-concepts/{lessonId}
     */
    public function getConcepts(array $params): void
    {
        try {
            $lessonId = (int) ($params['id'] ?? 0);
            
            if (!$lessonId) {
                $this->jsonError('Invalid lesson ID', 400);
                return;
            }

            // Check if student is enrolled in this course
            $stmt = $this->db->prepare("
                SELECT l.id, l.title, l.type, l.duration_minutes,
                       m.title as module_title, m.course_id,
                       c.title as course_title
                FROM course_lessons l
                JOIN course_modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                JOIN course_enrollments e ON c.id = e.course_id
                WHERE l.id = ? AND e.user_id = ? AND e.status = 'active'
            ");
            $stmt->execute([$lessonId, $this->user['id']]);
            $lesson = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$lesson) {
                $this->jsonError('Lesson not found or you are not enrolled', 404);
                return;
            }

            // Check if concepts already exist
            $existing = $this->aiGenerator->getContent($lessonId, 'hi');

            if ($existing && $existing['status'] === 'published') {
                // Content already exists, return it
                $this->jsonSuccess([
                    'lesson' => $lesson,
                    'concepts' => $this->formatConcepts($existing),
                    'cached' => true
                ]);
                return;
            }

            // Generate new concepts on-the-fly
            $content = $this->aiGenerator->generateLessonContent($lessonId, 'hi+en');

            // Auto-publish for students (no admin review needed for auto-generated)
            $stmt = $this->db->prepare("
                UPDATE lesson_content 
                SET status = 'published' 
                WHERE lesson_id = ?
            ");
            $stmt->execute([$lessonId]);

            $this->jsonSuccess([
                'lesson' => $lesson,
                'concepts' => $this->formatConcepts($content),
                'cached' => false
            ]);

        } catch (Exception $e) {
            error_log("Lesson Concepts Error: " . $e->getMessage());
            $this->jsonError('Failed to generate concepts: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format concepts for frontend display
     */
    private function formatConcepts(array $content): array
    {
        return [
            'overview_hi' => $content['overview_hi'] ?? '',
            'overview_en' => $content['overview_en'] ?? '',
            'key_concepts' => $content['key_concepts'] ?? [],
            'code_examples' => array_slice($content['code_examples'] ?? [], 0, 3), // First 3 examples
            'estimated_time' => $content['estimated_time'] ?? 30
        ];
    }
}
