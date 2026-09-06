<?php
declare(strict_types=1);
namespace TCM\Models;

use TCM\Core\Database;

/**
 * Course Notes Model (W3Schools style reading material)
 */
final class CourseNote extends Model
{
    protected static string $table = 'course_notes';

    /**
     * Get all notes for a course
     */
    public static function getByCourse(int $courseId): array
    {
        return Database::all(
            "SELECT * FROM course_notes 
             WHERE course_id = ? AND is_published = 1 
             ORDER BY order_index ASC, id ASC",
            [$courseId]
        );
    }

    /**
     * Get note by slug
     */
    public static function findBySlug(int $courseId, string $slug): ?array
    {
        return Database::first(
            "SELECT * FROM course_notes 
             WHERE course_id = ? AND slug = ? AND is_published = 1",
            [$courseId, $slug]
        );
    }

    /**
     * Get note with navigation (prev/next)
     */
    public static function getWithNavigation(int $courseId, string $slug): array
    {
        $current = self::findBySlug($courseId, $slug);
        if (!$current) {
            return ['current' => null, 'prev' => null, 'next' => null];
        }

        $prev = Database::first(
            "SELECT id, title, slug FROM course_notes 
             WHERE course_id = ? AND order_index < ? AND is_published = 1
             ORDER BY order_index DESC LIMIT 1",
            [$courseId, $current['order_index']]
        );

        $next = Database::first(
            "SELECT id, title, slug FROM course_notes 
             WHERE course_id = ? AND order_index > ? AND is_published = 1
             ORDER BY order_index ASC LIMIT 1",
            [$courseId, $current['order_index']]
        );

        return [
            'current' => $current,
            'prev' => $prev,
            'next' => $next,
        ];
    }

    /**
     * Mark note as read by student
     */
    public static function markAsRead(int $userId, int $noteId): void
    {
        Database::run(
            "INSERT INTO student_note_progress (user_id, note_id, read_at) 
             VALUES (?, ?, NOW())
             ON DUPLICATE KEY UPDATE read_at = NOW()",
            [$userId, $noteId]
        );
    }

    /**
     * Check if student has access to course notes
     */
    public static function hasAccess(int $userId, int $courseId): array
    {
        $enrollment = Database::first(
            "SELECT e.*, c.title as course_title, c.duration
             FROM enrollments e
             JOIN courses c ON c.id = e.course_id
             WHERE e.user_id = ? AND e.course_id = ? AND e.status = 'active'",
            [$userId, $courseId]
        );

        if (!$enrollment) {
            return ['has_access' => false, 'reason' => 'not_enrolled'];
        }

        // Check expiry
        if ($enrollment['expires_at']) {
            $expiryDate = new \DateTime($enrollment['expires_at']);
            $today = new \DateTime();
            
            if ($today > $expiryDate) {
                return [
                    'has_access' => false, 
                    'reason' => 'expired',
                    'expired_on' => $enrollment['expires_at']
                ];
            }
        }

        return [
            'has_access' => true,
            'enrollment' => $enrollment,
            'expires_at' => $enrollment['expires_at']
        ];
    }

    /**
     * Get reading progress for a student
     */
    public static function getProgress(int $userId, int $courseId): array
    {
        $total = (int) Database::scalar(
            "SELECT COUNT(*) FROM course_notes WHERE course_id = ? AND is_published = 1",
            [$courseId]
        );

        $read = (int) Database::scalar(
            "SELECT COUNT(DISTINCT snp.note_id) 
             FROM student_note_progress snp
             JOIN course_notes cn ON cn.id = snp.note_id
             WHERE snp.user_id = ? AND cn.course_id = ?",
            [$userId, $courseId]
        );

        return [
            'total' => $total,
            'read' => $read,
            'percentage' => $total > 0 ? round(($read / $total) * 100) : 0,
        ];
    }

    /**
     * Create table of contents (nested structure)
     */
    public static function getTableOfContents(int $courseId): array
    {
        $allNotes = self::getByCourse($courseId);
        return self::buildTree($allNotes);
    }

    private static function buildTree(array $notes, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($notes as $note) {
            if ((int)($note['parent_id'] ?? 0) === ($parentId ?? 0)) {
                $children = self::buildTree($notes, (int)$note['id']);
                if ($children) {
                    $note['children'] = $children;
                }
                $branch[] = $note;
            }
        }
        return $branch;
    }
}
