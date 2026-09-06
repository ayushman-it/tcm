<?php

declare(strict_types=1);

namespace TCM\Controllers\Api;

use TCM\Core\Auth;
use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Core\Request;
use TCM\Core\Response;
use TCM\Models\Notification;

/**
 * Handles FCM token registration, notification dispatch,
 * and in-app notification fetch/mark-read.
 */
final class NotificationController
{
    /**
     * POST /api/notifications/token
     * Save the browser's FCM token for the current user.
     */
    public function saveToken(): void
    {
        $user = Auth::user();
        if ($user === null) {
            Response::error('Unauthenticated.', 401);
        }

        $body  = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        $token = trim((string) ($body['token'] ?? ''));

        if (empty($token)) {
            Response::error('Token is required.', 422);
        }

        // Ensure table exists (safe before migration)
        $tableExists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'fcm_tokens'"
        );

        if (!$tableExists) {
            Database::run("
                CREATE TABLE IF NOT EXISTS fcm_tokens (
                    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id    BIGINT UNSIGNED NOT NULL,
                    token      VARCHAR(512)    NOT NULL,
                    created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uniq_fcm_token (token),
                    KEY idx_fcm_user (user_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        Database::run(
            'DELETE FROM fcm_tokens WHERE token = ? AND user_id != ?',
            [$token, (int) $user['id']]
        );

        Database::run(
            'INSERT INTO fcm_tokens (user_id, token)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE user_id = VALUES(user_id), updated_at = NOW()',
            [(int) $user['id'], $token]
        );

        Response::success(['saved' => true], 'Token registered.');
    }

    /**
     * GET /api/notifications
     * Return the current user's in-app notifications.
     */
    public function index(): void
    {
        $user = Auth::user();
        if ($user === null) {
            Response::error('Unauthenticated.', 401);
        }

        Notification::ensureTable();
        $userId = (int) $user['id'];

        $items = Notification::forUserId($userId, 40);
        $unread = Notification::unreadCount($userId);

        Response::success([
            'notifications' => $items,
            'unread_count'  => $unread,
        ], 'OK');
    }

    /**
     * POST /api/notifications/read-all
     * Mark all notifications as read for current user.
     */
    public function readAll(): void
    {
        $user = Auth::user();
        if ($user === null) {
            Response::error('Unauthenticated.', 401);
        }
        Notification::ensureTable();
        Notification::markAllRead((int) $user['id']);
        Response::success(['done' => true], 'Marked all read.');
    }

    /**
     * POST /api/notifications/{id}/read
     * Mark a single notification as read.
     */
    public function readOne(array $params): void
    {
        $user = Auth::user();
        if ($user === null) {
            Response::error('Unauthenticated.', 401);
        }
        Notification::ensureTable();
        Notification::markOneRead((int) $params['id'], (int) $user['id']);
        Response::success(['done' => true], 'Marked read.');
    }

    /**
     * POST /api/notifications/send  (admin only)
     * Send a manual push notification to a group.
     */
    public function send(): void
    {
        $user = Auth::user();
        if ($user === null || $user['role'] !== 'admin') {
            Response::error('Forbidden.', 403);
        }

        $body     = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        $title    = trim((string) ($body['title']     ?? ''));
        $message  = trim((string) ($body['body']      ?? ''));
        $target   = trim((string) ($body['target']    ?? 'admins'));
        $clickUrl = trim((string) ($body['click_url'] ?? ''));

        if (empty($title) || empty($message)) {
            Response::error('Title and body are required.', 422);
        }

        // Save in-app notification
        Notification::ensureTable();
        match ($target) {
            'students' => Notification::toStudents($title, $message, '📢', $clickUrl),
            'all'      => (function() use ($title, $message, $clickUrl) {
                Notification::toAdmins($title, $message, '📢', $clickUrl);
                Notification::toStudents($title, $message, '📢', $clickUrl);
            })(),
            default    => Notification::toAdmins($title, $message, '📢', $clickUrl),
        };

        // Also send Firebase push
        $ok = match ($target) {
            'students' => FirebaseNotification::notifyAllStudents($title, $message, [], $clickUrl),
            'all'      => FirebaseNotification::broadcast($title, $message, [], $clickUrl),
            default    => FirebaseNotification::notifyAdmins($title, $message, [], $clickUrl),
        };

        Response::success(['sent' => $ok], $ok ? 'Notification sent.' : 'Saved in-app (check FCM config for push).');
    }
}
