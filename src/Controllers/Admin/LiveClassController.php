<?php
declare(strict_types=1);
namespace TCM\Controllers\Admin;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Core\Request;
use TCM\Models\Notification;

final class LiveClassController extends Controller
{
    public function index(): void
    {
        Auth::require('admin');
        self::ensureTable();
        $links    = Database::all('SELECT l.*, u.name AS sent_by_name FROM live_class_links l JOIN users u ON u.id = l.sent_by ORDER BY l.created_at DESC LIMIT 50');
        $courses  = Database::all("SELECT id, title FROM courses WHERE status='published' ORDER BY title");
        $programs = Database::all("SELECT id, title FROM programs WHERE status='published' ORDER BY title");

        // All enrolled students per course — for the live preview
        $enrolledByCourse = [];
        foreach ($courses as $c) {
            $enrolledByCourse[(int)$c['id']] = Database::all(
                "SELECT u.id, u.name, u.avatar FROM users u
                 JOIN enrollments e ON e.user_id = u.id
                 WHERE e.course_id = ? AND u.status = 'active'
                 ORDER BY u.name",
                [(int)$c['id']]
            );
        }

        // All enrolled students per program
        $enrolledByProgram = [];
        foreach ($programs as $p) {
            $enrolledByProgram[(int)$p['id']] = Database::all(
                "SELECT u.id, u.name, u.avatar FROM users u
                 JOIN program_enrollments pe ON pe.user_id = u.id
                 WHERE pe.program_id = ? AND u.status = 'active'
                 ORDER BY u.name",
                [(int)$p['id']]
            );
        }

        // All active students (for specific student picker)
        $allStudents = Database::all(
            "SELECT u.id, u.name, u.avatar, sp.headline
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.role = 'student' AND u.status = 'active' AND u.onboarded = 1
             ORDER BY u.name"
        );

        $this->view('admin/live-class/index', [
            'title'             => 'Live Class Links',
            'links'             => $links,
            'courses'           => $courses,
            'programs'          => $programs,
            'enrolledByCourse'  => $enrolledByCourse,
            'enrolledByProgram' => $enrolledByProgram,
            'allStudents'       => $allStudents,
        ], 'admin');
    }

    public function send(): void
    {
        $admin = Auth::require('admin');
        self::ensureTable();

        $title      = trim(Request::string('title'));
        $meetingUrl = trim(Request::string('meeting_url'));
        $desc       = trim(Request::string('description'));
        $target     = Request::string('target', 'all');
        $targetId   = Request::int('target_id') ?: null;
        $scheduledAt = Request::string('scheduled_at') ?: null;
        // Specific student IDs (comma-separated from hidden input)
        $specificIds = array_filter(array_map('intval',
            explode(',', Request::string('specific_student_ids', ''))
        ));

        if (empty($title) || empty($meetingUrl)) {
            flash('error', 'Title and meeting URL are required.');
            redirect('/admin/live-class');
        }

        if (!filter_var($meetingUrl, FILTER_VALIDATE_URL)) {
            flash('error', 'Please enter a valid meeting URL.');
            redirect('/admin/live-class');
        }

        $linkId = Database::insert('live_class_links', [
            'title'           => $title,
            'meeting_url'     => $meetingUrl,
            'description'     => $desc ?: null,
            'target'          => $target,
            'target_id'       => $targetId,
            'scheduled_at'    => $scheduledAt ?: null,
            'sent_by'         => (int) $admin['id'],
        ]);

        // Determine which students to notify
        $students = self::getTargetStudents($target, $targetId, $specificIds);

        // Save recipient count back
        Database::update('live_class_links', ['recipient_count' => count($students)], ['id' => $linkId]);

        // Save in-app notifications + push
        Notification::ensureTable();
        $notifTitle = '📡 Live Class: ' . $title;
        $notifBody  = $desc ?: 'Your live class link is ready. Click to join the session.';
        if ($scheduledAt) {
            $notifBody .= ' | ' . date('d M Y, h:i A', strtotime($scheduledAt));
        }
        $clickUrl = base_url('/student');

        foreach ($students as $s) {
            Notification::forUser(
                (int) $s['id'],
                $notifTitle,
                $notifBody,
                '📡',
                base_url('/student')
            );
            FirebaseNotification::notifyStudent(
                (int) $s['id'],
                $notifTitle,
                $notifBody,
                ['tag' => 'live-class', 'link_id' => (string)$linkId],
                base_url('/student')
            );
        }

        flash('success', 'Live class link sent to ' . count($students) . ' student(s)!');
        redirect('/admin/live-class');
    }

    public function delete(array $params): void
    {
        Auth::require('admin');
        self::ensureTable();
        Database::delete('live_class_links', ['id' => (int) $params['id']]);
        flash('success', 'Link removed.');
        redirect('/admin/live-class');
    }

    /** @return list<array<string,mixed>> */
    private static function getTargetStudents(string $target, ?int $targetId, array $specificIds = []): array
    {
        return match ($target) {
            'course' => $targetId ? Database::all(
                "SELECT DISTINCT u.id FROM users u
                 JOIN enrollments e ON e.user_id = u.id
                 WHERE e.course_id = ? AND u.status = 'active'",
                [$targetId]
            ) : [],
            'program' => $targetId ? Database::all(
                "SELECT DISTINCT u.id FROM users u
                 JOIN program_enrollments pe ON pe.user_id = u.id
                 WHERE pe.program_id = ? AND u.status = 'active'",
                [$targetId]
            ) : [],
            'specific' => !empty($specificIds) ? Database::all(
                "SELECT id FROM users WHERE id IN (" . implode(',', array_fill(0, count($specificIds), '?')) . ")
                 AND role = 'student' AND status = 'active'",
                $specificIds
            ) : [],
            default => Database::all(
                "SELECT id FROM users WHERE role = 'student' AND status = 'active'"
            ),
        };
    }

    public static function ensureTable(): void
    {
        static $done = false;
        if ($done) return;
        $exists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='live_class_links'"
        );
        if (!$exists) {
            Database::run("CREATE TABLE IF NOT EXISTS live_class_links (
                id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                title           VARCHAR(200)    NOT NULL,
                meeting_url     VARCHAR(500)    NOT NULL,
                description     VARCHAR(500)    DEFAULT NULL,
                target          ENUM('all','course','program','specific') NOT NULL DEFAULT 'all',
                target_id       BIGINT UNSIGNED DEFAULT NULL,
                scheduled_at    DATETIME        DEFAULT NULL,
                recipient_count INT             NOT NULL DEFAULT 0,
                sent_by         BIGINT UNSIGNED NOT NULL,
                created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_lcl_target (target),
                KEY idx_lcl_sent   (sent_by)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } else {
            // Silently add recipient_count if the table was created before this column existed
            $hasCol = (bool) Database::scalar(
                "SELECT COUNT(*) FROM information_schema.columns
                 WHERE table_schema = DATABASE() AND table_name = 'live_class_links'
                 AND column_name = 'recipient_count'"
            );
            if (!$hasCol) {
                Database::run(
                    "ALTER TABLE live_class_links ADD COLUMN recipient_count INT NOT NULL DEFAULT 0 AFTER scheduled_at"
                );
            }
        }
        $done = true;
    }
}
