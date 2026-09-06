<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;
use TCM\Core\Response;
use TCM\Core\Upload;
use TCM\Models\Notification;

/**
 * Student Help Requests + Group Chat
 *
 * Routes:
 *   GET  /student/chat                    — main chat page (groups + requests)
 *   POST /student/help/request/{id}       — send help request to student {id}
 *   POST /student/help/requests/{id}/accept  — accept a request
 *   POST /student/help/requests/{id}/decline — decline a request
 *   POST /student/groups/create           — create a group chat
 *   POST /student/groups/{id}/join        — join a group
 *   POST /student/groups/{id}/leave       — leave a group
 *   GET  /api/student/chat/groups         — list my groups (JSON)
 *   GET  /api/student/chat/groups/{id}/messages — fetch messages (JSON, poll)
 *   POST /api/student/chat/groups/{id}/messages — send message (JSON + file upload)
 *   GET  /api/student/help/requests       — list incoming requests (JSON)
 */
final class HelpController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    //  Main chat dashboard page
    // ─────────────────────────────────────────────────────────────

    public function chatPage(): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $myGroups = $this->myGroups((int) $me['id']);
        $incoming = $this->incomingRequests((int) $me['id']);
        $outgoing = $this->outgoingRequests((int) $me['id']);

        $this->view('student/chat/index', [
            'title'    => 'Chat & Help',
            'me'       => $me,
            'groups'   => $myGroups,
            'incoming' => $incoming,
            'outgoing' => $outgoing,
        ], 'student');
    }

    // ─────────────────────────────────────────────────────────────
    //  Help requests
    // ─────────────────────────────────────────────────────────────

    /** POST /student/help/request/{id}  — send help request to another student */
    public function sendRequest(array $params): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $targetId = (int) $params['id'];
        if ($targetId === (int) $me['id']) {
            if (Request::isJson()) Response::error('Cannot send a request to yourself.', 422);
            flash('error', 'Cannot send a request to yourself.');
            redirect('/student/community');
        }

        // Check target exists
        $target = Database::first('SELECT id, name FROM users WHERE id = ? AND role = "student" AND status = "active"', [$targetId]);
        if ($target === null) {
            if (Request::isJson()) Response::error('Student not found.', 404);
            flash('error', 'Student not found.');
            redirect('/student/community');
        }

        // Already pending/accepted?
        $existing = Database::scalar(
            'SELECT id FROM help_requests WHERE from_user_id = ? AND to_user_id = ? AND status IN ("pending","accepted")',
            [(int) $me['id'], $targetId]
        );
        if ($existing) {
            if (Request::isJson()) Response::error('Request already sent.', 409);
            flash('info', 'You already have a pending request to this student.');
            redirect('/student/community/' . $targetId);
        }

        $message = Request::string('message', 'Hi! I need some help. Can we connect?');
        $message = mb_substr(trim($message), 0, 500);

        Database::insert('help_requests', [
            'from_user_id' => (int) $me['id'],
            'to_user_id'   => $targetId,
            'message'      => $message,
            'status'       => 'pending',
        ]);

        // In-app notification
        Notification::ensureTable();
        Notification::forUser(
            $targetId,
            '🤝 Help Request from ' . $me['name'],
            $me['name'] . ' sent you a help request: "' . mb_substr($message, 0, 80) . '"',
            '🤝',
            base_url('/student/chat')
        );

        if (Request::isJson()) {
            Response::success(['sent' => true], 'Help request sent!');
        }
        flash('success', 'Help request sent to ' . $target['name'] . '!');
        redirect('/student/community/' . $targetId);
    }

    /** POST /student/help/requests/{id}/accept */
    public function acceptRequest(array $params): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $reqId = (int) $params['id'];
        $req = Database::first(
            'SELECT * FROM help_requests WHERE id = ? AND to_user_id = ? AND status = "pending"',
            [$reqId, (int) $me['id']]
        );

        if ($req === null) {
            if (Request::isJson()) Response::error('Request not found.', 404);
            flash('error', 'Request not found.');
            redirect('/student/chat');
        }

        Database::update('help_requests', ['status' => 'accepted'], ['id' => $reqId]);

        // Create a 1-on-1 group automatically
        $fromUser = Database::first('SELECT id, name FROM users WHERE id = ?', [(int) $req['from_user_id']]);
        $groupName = $me['name'] . ' & ' . ($fromUser['name'] ?? 'Student');

        $groupId = Database::insert('chat_groups', [
            'name'       => $groupName,
            'created_by' => (int) $me['id'],
            'type'       => 'help',
        ]);

        Database::insert('chat_group_members', ['group_id' => $groupId, 'user_id' => (int) $me['id']]);
        Database::insert('chat_group_members', ['group_id' => $groupId, 'user_id' => (int) $req['from_user_id']]);

        // Update request with group id
        Database::update('help_requests', ['group_id' => $groupId], ['id' => $reqId]);

        // Notify the requester
        Notification::ensureTable();
        Notification::forUser(
            (int) $req['from_user_id'],
            '✅ Help Request Accepted!',
            $me['name'] . ' accepted your help request. You can now chat!',
            '✅',
            base_url('/student/chat')
        );

        if (Request::isJson()) Response::success(['group_id' => $groupId], 'Request accepted. Chat started!');
        flash('success', 'Request accepted! You can now chat.');
        redirect('/student/chat');
    }

    /** POST /student/help/requests/{id}/decline */
    public function declineRequest(array $params): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $reqId = (int) $params['id'];
        $req = Database::first(
            'SELECT * FROM help_requests WHERE id = ? AND to_user_id = ? AND status = "pending"',
            [$reqId, (int) $me['id']]
        );

        if ($req !== null) {
            Database::update('help_requests', ['status' => 'declined'], ['id' => $reqId]);
        }

        if (Request::isJson()) Response::success(['done' => true], 'Request declined.');
        flash('info', 'Request declined.');
        redirect('/student/chat');
    }

    // ─────────────────────────────────────────────────────────────
    //  Group management
    // ─────────────────────────────────────────────────────────────

    /** POST /student/groups/create */
    public function createGroup(): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $name = mb_substr(trim(Request::string('name')), 0, 100);
        if ($name === '') {
            if (Request::isJson()) Response::error('Group name is required.', 422);
            flash('error', 'Group name is required.');
            redirect('/student/chat');
        }

        $groupId = Database::insert('chat_groups', [
            'name'       => $name,
            'created_by' => (int) $me['id'],
            'type'       => 'group',
        ]);

        Database::insert('chat_group_members', ['group_id' => $groupId, 'user_id' => (int) $me['id']]);

        // Add extra members if provided
        $memberIds = array_filter(array_map('intval', explode(',', Request::string('member_ids', ''))));
        foreach ($memberIds as $uid) {
            if ($uid === (int) $me['id']) continue;
            $exists = Database::scalar('SELECT id FROM users WHERE id = ? AND role = "student"', [$uid]);
            if ($exists) {
                Database::run(
                    'INSERT IGNORE INTO chat_group_members (group_id, user_id) VALUES (?,?)',
                    [$groupId, $uid]
                );
                Notification::ensureTable();
                Notification::forUser(
                    $uid,
                    '👥 Added to group: ' . $name,
                    $me['name'] . ' added you to the group "' . $name . '".',
                    '👥',
                    base_url('/student/chat')
                );
            }
        }

        if (Request::isJson()) Response::success(['group_id' => $groupId], 'Group created.');
        flash('success', 'Group "' . $name . '" created!');
        redirect('/student/chat');
    }

    /** POST /student/groups/{id}/leave */
    public function leaveGroup(array $params): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $groupId = (int) $params['id'];
        Database::run(
            'DELETE FROM chat_group_members WHERE group_id = ? AND user_id = ?',
            [$groupId, (int) $me['id']]
        );

        if (Request::isJson()) Response::success(['done' => true], 'Left group.');
        flash('info', 'You left the group.');
        redirect('/student/chat');
    }

    // ─────────────────────────────────────────────────────────────
    //  API: groups list
    // ─────────────────────────────────────────────────────────────

    /** GET /api/student/chat/groups */
    public function apiGroups(): void
    {
        $me = Auth::user();
        if ($me === null) Response::error('Unauthenticated.', 401);
        self::ensureTables();

        $groups = $this->myGroups((int) $me['id']);
        $incoming = $this->incomingRequests((int) $me['id']);

        Response::success([
            'groups'   => $groups,
            'incoming' => $incoming,
        ], 'OK');
    }

    // ─────────────────────────────────────────────────────────────
    //  API: messages (poll-based)
    // ─────────────────────────────────────────────────────────────

    /** GET /api/student/chat/groups/{id}/messages */
    public function apiMessages(array $params): void
    {
        $me = Auth::user();
        if ($me === null) Response::error('Unauthenticated.', 401);
        self::ensureTables();

        $groupId = (int) $params['id'];

        // Check membership
        $member = Database::scalar(
            'SELECT id FROM chat_group_members WHERE group_id = ? AND user_id = ?',
            [$groupId, (int) $me['id']]
        );
        if (!$member) Response::error('Not a member.', 403);

        $since = Request::int('since', 0); // message id to fetch after
        $sql = 'SELECT m.id, m.user_id, m.body, m.file_path, m.file_name,
                       m.file_type, m.created_at,
                       u.name AS sender_name, u.avatar AS sender_avatar
                FROM chat_messages m
                JOIN users u ON u.id = m.user_id
                WHERE m.group_id = ?';
        $params = [$groupId];

        if ($since > 0) {
            $sql .= ' AND m.id > ?';
            $params[] = $since;
        } else {
            $sql .= ' ORDER BY m.id DESC LIMIT 60';
            $msgs = Database::all($sql, $params);
            $msgs = array_reverse($msgs);
            Response::success(['messages' => $msgs, 'group_id' => $groupId], 'OK');
            return;
        }

        $sql .= ' ORDER BY m.id ASC LIMIT 50';
        $msgs = Database::all($sql, $params);
        Response::success(['messages' => $msgs, 'group_id' => $groupId], 'OK');
    }

    /** POST /api/student/chat/groups/{id}/messages */
    public function apiSendMessage(array $params): void
    {
        $me = Auth::user();
        if ($me === null) Response::error('Unauthenticated.', 401);
        self::ensureTables();

        $groupId = (int) $params['id'];

        // Check membership
        $member = Database::scalar(
            'SELECT id FROM chat_group_members WHERE group_id = ? AND user_id = ?',
            [$groupId, (int) $me['id']]
        );
        if (!$member) Response::error('Not a member.', 403);

        // Handle file upload
        $filePath = null;
        $fileName = null;
        $fileType = null;

        if (Upload::present($_FILES['file'] ?? null)) {
            $uploadDir = config('uploads.path') . '/chat';
            if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }

            try {
                $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','txt','zip','mp4','mp3'];
                $stored = Upload::store(
                    $_FILES['file'],
                    $uploadDir,
                    $allowed,
                    5 * 1024 * 1024
                );
                $filePath = $stored;
                $fileName = mb_substr(basename((string)($_FILES['file']['name'] ?? '')), 0, 200);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $fileType = in_array($ext, ['jpg','jpeg','png','gif','webp']) ? 'image' : 'file';
            } catch (\RuntimeException $e) {
                Response::error($e->getMessage(), 422);
            }
        }

        $body = mb_substr(trim(Request::string('body', '')), 0, 2000);

        if ($body === '' && $filePath === null) {
            Response::error('Message or file is required.', 422);
        }

        $msgId = Database::insert('chat_messages', [
            'group_id'  => $groupId,
            'user_id'   => (int) $me['id'],
            'body'      => $body !== '' ? $body : null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_type' => $fileType,
        ]);

        $msg = Database::first(
            'SELECT m.id, m.user_id, m.body, m.file_path, m.file_name, m.file_type,
                    m.created_at, u.name AS sender_name, u.avatar AS sender_avatar
             FROM chat_messages m
             JOIN users u ON u.id = m.user_id
             WHERE m.id = ?',
            [$msgId]
        );

        Response::success(['message' => $msg], 'Sent.');
    }

    /** POST /student/help/requests/{id}/cookie — give a cookie + review after resolved help */
    public function giveCookie(array $params): void
    {
        $me = Auth::require('student');
        self::ensureTables();

        $reqId  = (int) $params['id'];
        $review = mb_substr(trim(Request::string('review', '')), 0, 300);
        $cookies = max(1, min(5, Request::int('cookies', 1)));

        // Must be an accepted request where I was the one who asked
        $req = Database::first(
            "SELECT * FROM help_requests WHERE id = ? AND from_user_id = ? AND status = 'accepted'",
            [$reqId, (int) $me['id']]
        );
        if ($req === null) {
            if (Request::isJson()) Response::error('Request not found or not accepted.', 404);
            flash('error', 'Cannot give a cookie for this request.');
            redirect('/student/chat');
        }

        $recipientId = (int) $req['to_user_id'];

        // Only one cookie per request
        self::ensureCookieTable();
        $already = Database::scalar(
            'SELECT id FROM peer_cookies WHERE from_user_id = ? AND request_id = ?',
            [(int) $me['id'], $reqId]
        );
        if ($already) {
            if (Request::isJson()) Response::error('Already gave a cookie for this request.', 409);
            flash('info', 'You already gave a cookie for this request.');
            redirect('/student/chat');
        }

        Database::insert('peer_cookies', [
            'from_user_id' => (int) $me['id'],
            'to_user_id'   => $recipientId,
            'request_id'   => $reqId,
            'cookies'      => $cookies,
            'review'       => $review ?: null,
        ]);

        // Mark request resolved
        Database::update('help_requests', ['status' => 'resolved'], ['id' => $reqId]);

        // Notify recipient
        Notification::ensureTable();
        Notification::forUser(
            $recipientId,
            '🍪 ' . $me['name'] . ' gave you ' . $cookies . ' cookie' . ($cookies > 1 ? 's' : '') . '!',
            $review ?: 'Great help! Keep it up.',
            '🍪',
            base_url('/student/community/' . (int) $me['id'])
        );

        if (Request::isJson()) Response::success(['done' => true], 'Cookie given!');
        flash('success', 'Cookie given! 🍪 Your review has been posted.');
        redirect('/student/chat');
    }

    public static function ensureCookieTable(): void
    {
        static $cookieDone = false;
        if ($cookieDone) return;
        $exists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='peer_cookies'"
        );
        if (!$exists) {
            Database::run("CREATE TABLE IF NOT EXISTS peer_cookies (
                id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                from_user_id BIGINT UNSIGNED NOT NULL,
                to_user_id   BIGINT UNSIGNED NOT NULL,
                request_id   BIGINT UNSIGNED NOT NULL,
                cookies      TINYINT         NOT NULL DEFAULT 1,
                review       VARCHAR(300)    DEFAULT NULL,
                created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_cookie_req (from_user_id, request_id),
                KEY idx_cookie_to (to_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $cookieDone = true;
    }

    /** GET /api/student/help/requests */
    public function apiRequests(): void
    {
        $me = Auth::user();
        if ($me === null) Response::error('Unauthenticated.', 401);
        self::ensureTables();

        $incoming = $this->incomingRequests((int) $me['id']);
        $outgoing = $this->outgoingRequests((int) $me['id']);

        Response::success(['incoming' => $incoming, 'outgoing' => $outgoing], 'OK');
    }

    /** GET /api/student/search?q=name — search students for group member picker */
    public function apiSearchStudents(): void
    {
        $me = Auth::user();
        if ($me === null) Response::error('Unauthenticated.', 401);

        $q = trim(Request::string('q'));
        if (strlen($q) < 1) {
            Response::success(['students' => []], 'OK');
            return;
        }

        $like = '%' . $q . '%';
        $students = Database::all(
            "SELECT u.id, u.name, u.avatar, sp.headline, sp.experience_level
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.role = 'student' AND u.status = 'active'
               AND u.id != ? AND u.onboarded = 1
               AND (u.name LIKE ? OR sp.headline LIKE ?)
             ORDER BY u.name ASC LIMIT 15",
            [(int) $me['id'], $like, $like]
        );

        Response::success(['students' => $students], 'OK');
    }

    // ─────────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────────

    /** @return list<array<string,mixed>> */
    private function myGroups(int $userId): array
    {
        return Database::all(
            "SELECT g.id, g.name, g.type, g.created_at,
                    (SELECT COUNT(*) FROM chat_group_members cgm WHERE cgm.group_id = g.id) AS member_count,
                    (SELECT m.body FROM chat_messages m WHERE m.group_id = g.id ORDER BY m.id DESC LIMIT 1) AS last_message,
                    (SELECT m.created_at FROM chat_messages m WHERE m.group_id = g.id ORDER BY m.id DESC LIMIT 1) AS last_at,
                    (SELECT COUNT(*) FROM chat_messages m WHERE m.group_id = g.id) AS message_count
             FROM chat_groups g
             JOIN chat_group_members cgm ON cgm.group_id = g.id
             WHERE cgm.user_id = ?
             ORDER BY last_at DESC, g.created_at DESC",
            [$userId]
        );
    }

    /** @return list<array<string,mixed>> */
    private function incomingRequests(int $userId): array
    {
        return Database::all(
            "SELECT hr.id, hr.from_user_id, hr.message, hr.status, hr.created_at, hr.group_id,
                    u.name AS from_name, u.avatar AS from_avatar
             FROM help_requests hr
             JOIN users u ON u.id = hr.from_user_id
             WHERE hr.to_user_id = ? AND hr.status = 'pending'
             ORDER BY hr.created_at DESC",
            [$userId]
        );
    }

    /** @return list<array<string,mixed>> */
    private function outgoingRequests(int $userId): array
    {
        return Database::all(
            "SELECT hr.id, hr.to_user_id, hr.message, hr.status, hr.created_at,
                    u.name AS to_name, u.avatar AS to_avatar
             FROM help_requests hr
             JOIN users u ON u.id = hr.to_user_id
             WHERE hr.from_user_id = ? AND hr.status IN ('pending','accepted','resolved')
             ORDER BY hr.created_at DESC",
            [$userId]
        );
    }

    // ─────────────────────────────────────────────────────────────
    //  Table setup (auto-create, safe before migration)
    // ─────────────────────────────────────────────────────────────

    public static function ensureTables(): void
    {
        static $done = false;
        if ($done) return;

        $tables = Database::all(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name IN
             ('help_requests','chat_groups','chat_group_members','chat_messages')"
        );
        $existing = array_column($tables, 'table_name');

        if (!in_array('help_requests', $existing)) {
            Database::run("CREATE TABLE IF NOT EXISTS help_requests (
                id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                from_user_id BIGINT UNSIGNED NOT NULL,
                to_user_id  BIGINT UNSIGNED NOT NULL,
                message     VARCHAR(500)    DEFAULT NULL,
                status      ENUM('pending','accepted','declined','resolved') NOT NULL DEFAULT 'pending',
                group_id    BIGINT UNSIGNED DEFAULT NULL,
                created_at  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_hr_to   (to_user_id),
                KEY idx_hr_from (from_user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        if (!in_array('chat_groups', $existing)) {
            Database::run("CREATE TABLE IF NOT EXISTS chat_groups (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                name       VARCHAR(100)    NOT NULL,
                created_by BIGINT UNSIGNED NOT NULL,
                type       ENUM('help','group') NOT NULL DEFAULT 'group',
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        if (!in_array('chat_group_members', $existing)) {
            Database::run("CREATE TABLE IF NOT EXISTS chat_group_members (
                id       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                group_id BIGINT UNSIGNED NOT NULL,
                user_id  BIGINT UNSIGNED NOT NULL,
                joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                UNIQUE KEY uniq_gm (group_id, user_id),
                KEY idx_gm_user (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        if (!in_array('chat_messages', $existing)) {
            Database::run("CREATE TABLE IF NOT EXISTS chat_messages (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                group_id   BIGINT UNSIGNED NOT NULL,
                user_id    BIGINT UNSIGNED NOT NULL,
                body       TEXT            DEFAULT NULL,
                file_path  VARCHAR(255)    DEFAULT NULL,
                file_name  VARCHAR(200)    DEFAULT NULL,
                file_type  ENUM('image','file') DEFAULT NULL,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_cm_group (group_id),
                KEY idx_cm_user  (user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }

        $done = true;
        self::ensureCookieTable();
    }
}
