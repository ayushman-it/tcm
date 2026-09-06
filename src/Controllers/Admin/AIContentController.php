<?php
namespace TCM\Controllers\Admin;

use TCM\Core\Controller;
use TCM\Services\AIContentGenerator;
use Exception;

class AIContentController extends Controller
{
    private AIContentGenerator $aiGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->requireAuth('admin');
        $this->aiGenerator = new AIContentGenerator($this->db);
    }

    /**
     * Show the AI Content Generator UI
     * GET /admin/ai-content
     */
    public function index(): void
    {
        $this->render('admin/ai-content-generator', [
            'title' => 'AI Content Generator'
        ]);
    }

    /**
     * Generate content for a single lesson
     * POST /admin/ai-content/generate-lesson
     */
    public function generateLesson(): void
    {
        try {
            $data = $this->getJsonInput();
            
            $lessonId = $data['lesson_id'] ?? null;
            $language = $data['language'] ?? 'hi+en';

            if (!$lessonId) {
                $this->jsonError('Lesson ID is required', 400);
                return;
            }

            // Generate content
            $content = $this->aiGenerator->generateLessonContent($lessonId, $language);

            $this->jsonSuccess([
                'message' => 'Content generated successfully',
                'content' => $content
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Generate content for all lessons in a module
     * POST /admin/ai-content/generate-module
     */
    public function generateModule(): void
    {
        try {
            $data = $this->getJsonInput();
            
            $moduleId = $data['module_id'] ?? null;
            $language = $data['language'] ?? 'hi+en';

            if (!$moduleId) {
                $this->jsonError('Module ID is required', 400);
                return;
            }

            // Generate content for all lessons
            $results = $this->aiGenerator->generateForModule($moduleId, $language);

            $successCount = count(array_filter($results, fn($r) => $r['success']));
            $totalCount = count($results);

            $this->jsonSuccess([
                'message' => "Generated content for $successCount out of $totalCount lessons",
                'results' => $results
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get lesson content
     * GET /admin/ai-content/lesson/{id}
     */
    public function getLesson(int $id): void
    {
        try {
            $language = $_GET['lang'] ?? 'hi';
            $content = $this->aiGenerator->getContent($id, $language);

            if (!$content) {
                $this->jsonError('Content not found. Generate it first.', 404);
                return;
            }

            $this->jsonSuccess($content);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Regenerate content for a lesson
     * POST /admin/ai-content/regenerate-lesson
     */
    public function regenerateLesson(): void
    {
        try {
            $data = $this->getJsonInput();
            
            $lessonId = $data['lesson_id'] ?? null;
            $language = $data['language'] ?? 'hi+en';

            if (!$lessonId) {
                $this->jsonError('Lesson ID is required', 400);
                return;
            }

            // Regenerate content
            $content = $this->aiGenerator->regenerateContent($lessonId, $language);

            $this->jsonSuccess([
                'message' => 'Content regenerated successfully',
                'content' => $content
            ]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Approve/Publish lesson content
     * POST /admin/ai-content/publish
     */
    public function publishContent(): void
    {
        try {
            $data = $this->getJsonInput();
            $lessonId = $data['lesson_id'] ?? null;

            if (!$lessonId) {
                $this->jsonError('Lesson ID is required', 400);
                return;
            }

            $stmt = $this->db->prepare("
                UPDATE lesson_content 
                SET status = 'published', 
                    reviewed_by = ?,
                    updated_at = NOW()
                WHERE lesson_id = ?
            ");
            $stmt->execute([$this->user['id'], $lessonId]);

            $this->jsonSuccess(['message' => 'Content published successfully']);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Get all lessons with content status
     * GET /admin/ai-content/status
     */
    public function getContentStatus(): void
    {
        try {
            $courseId = $_GET['course_id'] ?? null;

            $sql = "
                SELECT 
                    l.id as lesson_id,
                    l.title as lesson_title,
                    l.type,
                    m.title as module_title,
                    c.title as course_title,
                    lc.id as content_id,
                    lc.status as content_status,
                    lc.ai_generated,
                    lc.generated_at,
                    lc.language
                FROM course_lessons l
                JOIN course_modules m ON l.module_id = m.id
                JOIN courses c ON m.course_id = c.id
                LEFT JOIN lesson_content lc ON l.id = lc.lesson_id
            ";

            $params = [];
            if ($courseId) {
                $sql .= " WHERE c.id = ?";
                $params[] = $courseId;
            }

            $sql .= " ORDER BY m.position, l.position";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $lessons = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Group by course and module
            $grouped = [];
            foreach ($lessons as $lesson) {
                $courseTitle = $lesson['course_title'];
                $moduleTitle = $lesson['module_title'];
                
                if (!isset($grouped[$courseTitle])) {
                    $grouped[$courseTitle] = [];
                }
                if (!isset($grouped[$courseTitle][$moduleTitle])) {
                    $grouped[$courseTitle][$moduleTitle] = [];
                }
                
                $grouped[$courseTitle][$moduleTitle][] = [
                    'lesson_id' => $lesson['lesson_id'],
                    'title' => $lesson['lesson_title'],
                    'type' => $lesson['type'],
                    'has_content' => !is_null($lesson['content_id']),
                    'status' => $lesson['content_status'],
                    'ai_generated' => (bool) $lesson['ai_generated'],
                    'generated_at' => $lesson['generated_at'],
                    'language' => $lesson['language']
                ];
            }

            $this->jsonSuccess(['lessons' => $grouped]);

        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
}
