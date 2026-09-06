<?php
/**
 * Verification Script - TCM 2.0 Fixes
 * 
 * This script verifies that all fixes are properly applied:
 * 1. Payment referral columns
 * 2. Daily tasks system
 * 3. Referral ID format
 * 
 * Usage: php verify_fixes.php
 */

require __DIR__ . '/src/bootstrap.php';

use TCM\Core\Database;

echo "🔍 TCM 2.0 - Fix Verification Script\n";
echo str_repeat("=", 60) . "\n\n";

$allGood = true;

// =====================================================================
// 1. Check Payment Submissions Table
// =====================================================================
echo "1️⃣  Checking Payment Submissions Table...\n";

$tableExists = (bool) Database::scalar(
    "SELECT COUNT(*) FROM information_schema.tables 
     WHERE table_schema = DATABASE() AND table_name = 'payment_submissions'"
);

if (!$tableExists) {
    echo "   ❌ payment_submissions table does not exist!\n";
    echo "      Run: database/payment_system.sql first\n\n";
    $allGood = false;
} else {
    echo "   ✅ payment_submissions table exists\n";
    
    // Check referral_code column
    $hasReferralCode = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name = 'referral_code'"
    );
    
    // Check referrer_id column
    $hasReferrerId = (bool) Database::scalar(
        "SELECT COUNT(*) FROM information_schema.columns 
         WHERE table_schema = DATABASE() 
         AND table_name = 'payment_submissions' 
         AND column_name = 'referrer_id'"
    );
    
    if (!$hasReferralCode || !$hasReferrerId) {
        echo "   ❌ Referral columns missing!\n";
        echo "      Run: php migrate_referral_columns.php\n\n";
        $allGood = false;
    } else {
        echo "   ✅ referral_code column exists\n";
        echo "   ✅ referrer_id column exists\n";
        
        // Check foreign key
        $hasForeignKey = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.key_column_usage 
             WHERE table_schema = DATABASE() 
             AND table_name = 'payment_submissions' 
             AND constraint_name = 'fk_ps_referrer'"
        );
        
        if ($hasForeignKey) {
            echo "   ✅ Foreign key constraint exists\n";
        } else {
            echo "   ⚠️  Foreign key constraint missing (optional)\n";
        }
    }
}

echo "\n";

// =====================================================================
// 2. Check Daily Tasks System
// =====================================================================
echo "2️⃣  Checking Daily Tasks System...\n";

$dailyTasksExists = (bool) Database::scalar(
    "SELECT COUNT(*) FROM information_schema.tables 
     WHERE table_schema = DATABASE() AND table_name = 'daily_tasks'"
);

if (!$dailyTasksExists) {
    echo "   ⚠️  daily_tasks table doesn't exist (will be auto-created)\n";
} else {
    echo "   ✅ daily_tasks table exists\n";
    
    $taskCount = (int) Database::scalar("SELECT COUNT(*) FROM daily_tasks");
    echo "   📊 Current tasks in DB: {$taskCount}\n";
}

// Check lesson progress table (needed for smart tasks)
$lessonProgressExists = (bool) Database::scalar(
    "SELECT COUNT(*) FROM information_schema.tables 
     WHERE table_schema = DATABASE() AND table_name = 'lesson_progress'"
);

if (!$lessonProgressExists) {
    echo "   ⚠️  lesson_progress table missing (tasks will use fallback)\n";
} else {
    echo "   ✅ lesson_progress table exists\n";
    
    $progressCount = (int) Database::scalar("SELECT COUNT(*) FROM lesson_progress WHERE completed = 1");
    echo "   📊 Completed lessons tracked: {$progressCount}\n";
}

// Check course lessons table
$courseLessonsExists = (bool) Database::scalar(
    "SELECT COUNT(*) FROM information_schema.tables 
     WHERE table_schema = DATABASE() AND table_name = 'course_lessons'"
);

if (!$courseLessonsExists) {
    echo "   ⚠️  course_lessons table missing (tasks will use fallback)\n";
} else {
    echo "   ✅ course_lessons table exists\n";
    
    $lessonCount = (int) Database::scalar("SELECT COUNT(*) FROM course_lessons");
    echo "   📊 Total lessons available: {$lessonCount}\n";
}

echo "\n";

// =====================================================================
// 3. Check Referral System
// =====================================================================
echo "3️⃣  Checking Referral System...\n";

$usersWithReferral = Database::all(
    "SELECT COUNT(*) as count FROM users WHERE referral_id IS NOT NULL"
);
$referralCount = (int)($usersWithReferral[0]['count'] ?? 0);

if ($referralCount === 0) {
    echo "   ⚠️  No users have referral IDs yet\n";
    echo "      Referral IDs are generated on user registration\n";
} else {
    echo "   ✅ {$referralCount} users have referral IDs\n";
    
    // Show sample referral IDs
    $samples = Database::all(
        "SELECT name, referral_id FROM users 
         WHERE referral_id IS NOT NULL 
         LIMIT 3"
    );
    
    echo "   📝 Sample referral IDs:\n";
    foreach ($samples as $sample) {
        $name = substr($sample['name'], 0, 20);
        echo "      - {$name}: {$sample['referral_id']}\n";
    }
    
    // Verify format
    $invalidFormat = Database::all(
        "SELECT COUNT(*) as count FROM users 
         WHERE referral_id IS NOT NULL 
         AND referral_id NOT REGEXP '^REF-[A-Z0-9]{6}$'"
    );
    
    $invalidCount = (int)($invalidFormat[0]['count'] ?? 0);
    if ($invalidCount > 0) {
        echo "   ⚠️  {$invalidCount} referral IDs have invalid format\n";
        $allGood = false;
    } else {
        echo "   ✅ All referral IDs have correct format (REF-XXXXXX)\n";
    }
}

echo "\n";

// =====================================================================
// 4. Check OpenRouter Configuration
// =====================================================================
echo "4️⃣  Checking OpenRouter Configuration...\n";

$apiKey = config('openrouter.api_key', '');
if (empty($apiKey)) {
    echo "   ⚠️  OpenRouter API key not configured\n";
    echo "      Tasks will use fallback generation (still works!)\n";
    echo "      To enable AI: Add OPENROUTER_API_KEY in .env\n";
} else {
    echo "   ✅ OpenRouter API key configured\n";
    echo "      AI-powered task generation enabled\n";
}

echo "\n";

// =====================================================================
// 5. Check Cron Job Setup
// =====================================================================
echo "5️⃣  Checking Cron Job File...\n";

if (file_exists(__DIR__ . '/cron-daily-tasks.php')) {
    echo "   ✅ cron-daily-tasks.php exists\n";
    echo "   📝 To setup cron job:\n";
    echo "      crontab -e\n";
    echo "      Add: 0 12 * * 5 cd " . __DIR__ . " && php cron-daily-tasks.php\n";
} else {
    echo "   ❌ cron-daily-tasks.php missing!\n";
    $allGood = false;
}

echo "\n";

// =====================================================================
// Final Summary
// =====================================================================
echo str_repeat("=", 60) . "\n";
if ($allGood) {
    echo "🎉 All Critical Fixes Verified Successfully!\n\n";
    echo "Next Steps:\n";
    echo "1. Test payment submission: /student/payments/submit\n";
    echo "2. Test task generation: php cron-daily-tasks.php\n";
    echo "3. Check student dashboard: /student\n";
} else {
    echo "⚠️  Some Issues Found - Please Fix Them\n\n";
    echo "Common Fixes:\n";
    echo "1. Run migration: php migrate_referral_columns.php\n";
    echo "2. Run schema: database/schema.sql\n";
    echo "3. Setup .env: Copy .env.example and configure\n";
}
echo str_repeat("=", 60) . "\n";

// =====================================================================
// Optional: Test Task Generation
// =====================================================================
echo "\n🧪 Want to test task generation? (y/n): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) === 'y') {
    echo "\nTesting task generation...\n\n";
    
    // Get first active student
    $student = Database::first(
        "SELECT id, name FROM users WHERE role = 'student' AND status = 'active' LIMIT 1"
    );
    
    if (!$student) {
        echo "❌ No active students found in database\n";
        exit(1);
    }
    
    echo "📝 Generating tasks for: {$student['name']} (ID: {$student['id']})\n\n";
    
    try {
        require_once __DIR__ . '/src/Models/DailyTask.php';
        $tasks = TCM\Models\DailyTask::generateForStudent((int)$student['id']);
        
        if (empty($tasks)) {
            echo "⚠️  No tasks generated (student might have no enrollments)\n";
        } else {
            echo "✅ Generated " . count($tasks) . " tasks:\n\n";
            foreach ($tasks as $i => $task) {
                echo "   " . ($i + 1) . ". {$task['title']}\n";
                if (isset($task['course_title'])) {
                    echo "      Course: {$task['course_title']}\n";
                }
                if (isset($task['difficulty'])) {
                    echo "      Difficulty: {$task['difficulty']}\n";
                }
                echo "\n";
            }
        }
    } catch (Exception $e) {
        echo "❌ Task generation failed: {$e->getMessage()}\n";
    }
}

exit($allGood ? 0 : 1);
