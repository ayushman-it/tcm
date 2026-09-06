<?php
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
$db = TCM\Core\Database::connection();

function tblEx(PDO $db, string $t): bool {
    return (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='$t'")->fetchColumn();
}

$log = [];

// notifications table
if (!tblEx($db, 'notifications')) {
    $db->exec("CREATE TABLE notifications (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    BIGINT UNSIGNED NOT NULL,
        role       VARCHAR(20)     DEFAULT NULL,
        title      VARCHAR(200)    NOT NULL,
        body       TEXT            DEFAULT NULL,
        icon       VARCHAR(10)     NOT NULL DEFAULT '🔔',
        click_url  VARCHAR(500)    DEFAULT NULL,
        is_read    TINYINT(1)      NOT NULL DEFAULT 0,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_notif_user   (user_id),
        KEY idx_notif_unread (user_id, is_read),
        CONSTRAINT fk_notif_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created notifications table';
} else {
    $log[] = '⏭️  notifications table already exists';
}

// fcm_tokens table
if (!tblEx($db, 'fcm_tokens')) {
    $db->exec("CREATE TABLE fcm_tokens (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id    BIGINT UNSIGNED NOT NULL,
        token      VARCHAR(512)    NOT NULL,
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_fcm_token (token),
        KEY idx_fcm_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created fcm_tokens table';
} else {
    $log[] = '⏭️  fcm_tokens table already exists';
}

unlink(__FILE__);
$log[] = '🗑️  migrate_notifications.php deleted';
foreach ($log as $l) echo $l . PHP_EOL;
