<?php
/**
 * TCM Chat & Help System Migration
 * Run once: https://thecodemunk.in/migrate_chat.php
 * Self-deletes after running.
 */
declare(strict_types=1);
require __DIR__ . '/src/bootstrap.php';
$db = TCM\Core\Database::connection();
$log = [];

function chatTableExists(PDO $db, string $t): bool {
    return (bool) $db->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='$t'")->fetchColumn();
}

// help_requests
if (!chatTableExists($db, 'help_requests')) {
    $db->exec("CREATE TABLE help_requests (
        id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        from_user_id BIGINT UNSIGNED NOT NULL,
        to_user_id   BIGINT UNSIGNED NOT NULL,
        message      VARCHAR(500)    DEFAULT NULL,
        status       ENUM('pending','accepted','declined') NOT NULL DEFAULT 'pending',
        group_id     BIGINT UNSIGNED DEFAULT NULL,
        created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_hr_to   (to_user_id),
        KEY idx_hr_from (from_user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created help_requests table';
} else { $log[] = '⏭️  help_requests already exists'; }

// chat_groups
if (!chatTableExists($db, 'chat_groups')) {
    $db->exec("CREATE TABLE chat_groups (
        id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        name       VARCHAR(100)    NOT NULL,
        created_by BIGINT UNSIGNED NOT NULL,
        type       ENUM('help','group') NOT NULL DEFAULT 'group',
        created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created chat_groups table';
} else { $log[] = '⏭️  chat_groups already exists'; }

// chat_group_members
if (!chatTableExists($db, 'chat_group_members')) {
    $db->exec("CREATE TABLE chat_group_members (
        id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        group_id  BIGINT UNSIGNED NOT NULL,
        user_id   BIGINT UNSIGNED NOT NULL,
        joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_gm (group_id, user_id),
        KEY idx_gm_user (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created chat_group_members table';
} else { $log[] = '⏭️  chat_group_members already exists'; }

// chat_messages
if (!chatTableExists($db, 'chat_messages')) {
    $db->exec("CREATE TABLE chat_messages (
        id        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        group_id  BIGINT UNSIGNED NOT NULL,
        user_id   BIGINT UNSIGNED NOT NULL,
        body      TEXT            DEFAULT NULL,
        file_path VARCHAR(255)    DEFAULT NULL,
        file_name VARCHAR(200)    DEFAULT NULL,
        file_type ENUM('image','file') DEFAULT NULL,
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_cm_group (group_id),
        KEY idx_cm_user  (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✅ Created chat_messages table';
} else { $log[] = '⏭️  chat_messages already exists'; }

// uploads/chat directory
$dir = __DIR__ . '/uploads/chat';
if (!is_dir($dir)) { mkdir($dir, 0775, true); $log[] = '✅ Created uploads/chat/ directory'; }
else { $log[] = '⏭️  uploads/chat/ already exists'; }

unlink(__FILE__);
$log[] = '🗑️  migrate_chat.php deleted';
foreach ($log as $l) echo $l . PHP_EOL;
