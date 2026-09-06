<?php
/**
 * Fix notifications table — widen icon column to VARCHAR(20)
 * Run once: https://thecodemunk.in/migrate_notifications_fix.php
 */
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
$db  = TCM\Core\Database::connection();
$log = [];

// Widen icon column if needed
$colType = $db->query(
    "SELECT COLUMN_TYPE FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'notifications' AND column_name = 'icon'"
)->fetchColumn();

if ($colType && str_contains(strtolower((string)$colType), 'varchar(10)')) {
    $db->exec("ALTER TABLE notifications MODIFY COLUMN icon VARCHAR(20) NOT NULL DEFAULT '🔔'");
    $log[] = '✅ Widened notifications.icon from VARCHAR(10) to VARCHAR(20)';
} else {
    $log[] = '⏭️  notifications.icon already OK: ' . ($colType ?: 'column not found');
}

// Check notifications table exists at all
$exists = (bool) $db->query(
    "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='notifications'"
)->fetchColumn();

if (!$exists) {
    $db->exec("CREATE TABLE IF NOT EXISTS notifications (
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
        KEY idx_notif_user   (user_id),
        KEY idx_notif_unread (user_id, is_read),
        CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created notifications table';
}

unlink(__FILE__);
$log[] = '🗑️ migrate_notifications_fix.php deleted';
foreach ($log as $l) echo $l . PHP_EOL;
