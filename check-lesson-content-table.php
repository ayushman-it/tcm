<?php
/**
 * Quick script to check if lesson_content table exists
 */

require_once __DIR__ . '/config/config.php';

try {
    $db = getDbConnection();
    
    // Check if table exists
    $stmt = $db->query("SHOW TABLES LIKE 'lesson_content'");
    $exists = $stmt->rowCount() > 0;
    
    if ($exists) {
        echo "✅ SUCCESS: lesson_content table EXISTS in database!\n\n";
        
        // Show table structure
        echo "Table Structure:\n";
        echo "================\n";
        $columns = $db->query("DESCRIBE lesson_content")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            echo sprintf("%-20s %-20s %s\n", $col['Field'], $col['Type'], $col['Null'] === 'YES' ? 'NULL' : 'NOT NULL');
        }
        
        // Count records
        $count = $db->query("SELECT COUNT(*) FROM lesson_content")->fetchColumn();
        echo "\n📊 Total Records: $count\n";
        
        if ($count > 0) {
            echo "\n📝 Sample Records:\n";
            echo "==================\n";
            $samples = $db->query("SELECT lesson_id, status, ai_generated, language, generated_at FROM lesson_content LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samples as $s) {
                echo sprintf("Lesson ID: %-5d Status: %-10s AI: %-3s Lang: %-6s Generated: %s\n", 
                    $s['lesson_id'], 
                    $s['status'], 
                    $s['ai_generated'] ? 'Yes' : 'No',
                    $s['language'],
                    $s['generated_at'] ?? 'N/A'
                );
            }
        }
        
    } else {
        echo "❌ ERROR: lesson_content table does NOT exist!\n\n";
        echo "To create it, run:\n";
        echo "mysql -u root tcm_db < database/lesson_content.sql\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}
