<?php

declare(strict_types=1);

namespace TCM\Models;

use TCM\Core\Database;

/**
 * In-app notification model.
 *
 * table: notifications
 *   id, user_id (NULL = all admins), role (admin|student|all),
 *   title, body, icon, click_url, is_read, created_at
 */
final class Notification extends Model
{
    protected static string $table = 'notifications';

    /* ── Create helpers ─────────────────────────────────────── */

    /** Notify a specific user. */
    public static function forUser(int $userId, string $title, string $body, string $icon = '🔔', string $url = ''): void
    {
        if (!self::tableReady()) return;
        self::create([
            'user_id'   => $userId,
            'role'      => null,
            'title'     => $title,
            'body'      => $body,
            'icon'      => $icon,
            'click_url' => $url,
            'is_read'   => 0,
        ]);
    }

    /** Notify all admins (stores one row per admin). */
    public static function toAdmins(string $title, string $body, string $icon = '🔔', string $url = ''): void
    {
        if (!self::tableReady()) return;
        $admins = Database::all("SELECT id FROM users WHERE role = 'admin' AND status = 'active'");
        foreach ($admins as $a) {
            self::create([
                'user_id'   => (int) $a['id'],
                'role'      => 'admin',
                'title'     => $title,
                'body'      => $body,
                'icon'      => $icon,
                'click_url' => $url,
                'is_read'   => 0,
            ]);
        }
    }

    /** Notify all students (stores one row per student). */
    public static function toStudents(string $title, string $body, string $icon = '🔔', string $url = ''): void
    {
        if (!self::tableReady()) return;
        $students = Database::all("SELECT id FROM users WHERE role = 'student' AND status = 'active'");
        foreach ($students as $s) {
            self::create([
                'user_id'   => (int) $s['id'],
                'role'      => 'student',
                'title'     => $title,
                'body'      => $body,
                'icon'      => $icon,
                'click_url' => $url,
                'is_read'   => 0,
            ]);
        }
    }

    /* ── Query helpers ──────────────────────────────────────── */

    /** @return list<array<string,mixed>> */
    public static function forUserId(int $userId, int $limit = 30): array
    {
        if (!self::tableReady()) return [];
        return Database::all(
            'SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ' . $limit,
            [$userId]
        );
    }

    public static function unreadCount(int $userId): int
    {
        if (!self::tableReady()) return 0;
        return (int) Database::scalar(
            'SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    public static function markAllRead(int $userId): void
    {
        if (!self::tableReady()) return;
        Database::run(
            'UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0',
            [$userId]
        );
    }

    public static function markOneRead(int $id, int $userId): void
    {
        if (!self::tableReady()) return;
        Database::run(
            'UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?',
            [$id, $userId]
        );
    }

    /* ── Table guard ────────────────────────────────────────── */

    private static ?bool $ready = null;

    public static function tableReady(): bool
    {
        if (self::$ready !== null) return self::$ready;
        self::$ready = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'notifications'"
        );
        return self::$ready;
    }

    public static function ensureTable(): void
    {
        if (self::tableReady()) return;
        Database::run("
            CREATE TABLE IF NOT EXISTS notifications (
                id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                user_id    BIGINT UNSIGNED NOT NULL,
                role       VARCHAR(20)     DEFAULT NULL,
                title      VARCHAR(200)    NOT NULL,
                body       TEXT            DEFAULT NULL,
                icon       VARCHAR(20)     NOT NULL DEFAULT '🔔',
                click_url  VARCHAR(500)    DEFAULT NULL,
                is_read    TINYINT(1)      NOT NULL DEFAULT 0,
                created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_notif_user    (user_id),
                KEY idx_notif_unread  (user_id, is_read),
                CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        self::$ready = true;
    }
}
