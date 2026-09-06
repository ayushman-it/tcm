<?php
/**
 * Production Server Debug Script
 * Safe for thecodemunk.in - No sensitive data exposure
 */

// Only show to logged-in admin
session_start();
require_once __DIR__ . '/config/config.php';

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['user'])) {
    $stmt = getDbConnection()->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $isAdmin = ($user && $user['role'] === 'admin');
}

if (!$isAdmin) {
    http_response_code(403);
    die('Access Denied');
}

// Safe error reporting (logs only, no display)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Production Debug - TCM</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .check { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        pre { background: #000; padding: 15px; border-radius: 5px; }
        h2 { color: #0ff; }
    </style>
</head>
<body>
    <h1>🔧 Production Debug - thecodemunk.in</h1>
    <pre><?php

echo "=== System Check ===\n\n";

// 1. PHP Version
echo "1. PHP Version: " . PHP_VERSION;
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    echo " <span class='check'>✓</span>\n";
} else {
    echo " <span class='error'>✗ Need PHP 8.0+</span>\n";
}

// 2. Database Connection
echo "2. Database Connection: ";
try {
    $db = getDbConnection();
    echo "<span class='check'>✓ Connected</span>\n";
} catch (Exception $e) {
    echo "<span class='error'>✗ Failed: " . $e->getMessage() . "</span>\n";
    exit;
}

// 3. Required Tables
echo "\n3. Database Tables:\n";
$tables = ['users', 'courses', 'course_modules', 'course_lessons', 'course_enrollments', 'lesson_content'];
foreach ($tables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() > 0) {
        echo "   $table: <span class='check'>✓</span>\n";
    } else {
        echo "   $table: <span class='error'>✗ Missing</span>\n";
    }
}

// 4. Controllers
echo "\n4. Controllers:\n";
$controllers = [
    'TCM\\Controllers\\Student\\CourseController',
    'TCM\\Controllers\\Student\\LessonConceptsController',
    'TCM\\Services\\AIContentGenerator'
];
foreach ($controllers as $class) {
    echo "   " . basename(str_replace('\\', '/', $class)) . ": ";
    if (class_exists($class)) {
        echo "<span class='check'>✓</span>\n";
    } else {
        echo "<span class='error'>✗ Not Found</span>\n";
    }
}

// 5. Required Files
echo "\n5. Required Files:\n";
$files = [
    'src/Controllers/Student/CourseController.php',
    'src/Controllers/Student/LessonConceptsController.php',
    'src/Services/AIContentGenerator.php',
    'views/student/dashboard.php'
];
foreach ($files as $file) {
    echo "   $file: ";
    if (file_exists(__DIR__ . '/' . $file)) {
        echo "<span class='check'>✓</span>\n";
    } else {
        echo "<span class='error'>✗ Missing</span>\n";
    }
}

// 6. Environment Variables
echo "\n6. Environment:\n";
echo "   GEMINI_API_KEY: ";
if (getenv('GEMINI_API_KEY')) {
    echo "<span class='check'>✓ Set</span>\n";
} else {
    echo "<span class='warning'>⚠ Not set</span>\n";
}
echo "   OPENAI_API_KEY: ";
if (getenv('OPENAI_API_KEY')) {
    echo "<span class='check'>✓ Set</span>\n";
} else {
    echo "<span class='warning'>⚠ Not set</span>\n";
}

// 7. Writable Directories
echo "\n7. Permissions:\n";
$dirs = ['storage/logs', 'public/uploads'];
foreach ($dirs as $dir) {
    echo "   $dir: ";
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path) && is_writable($path)) {
        echo "<span class='check'>✓ Writable</span>\n";
    } else {
        echo "<span class='error'>✗ Not writable</span>\n";
    }
}

// 8. Recent Errors
echo "\n8. Recent Errors:\n";
$errorLog = __DIR__ . '/storage/logs/error.log';
if (file_exists($errorLog)) {
    $lines = array_slice(file($errorLog), -10);
    if (empty($lines)) {
        echo "   <span class='check'>No recent errors</span>\n";
    } else {
        foreach ($lines as $line) {
            echo "   " . htmlspecialchars(trim($line)) . "\n";
        }
    }
} else {
    echo "   <span class='warning'>Log file not found</span>\n";
}

// 9. Test Database Query
echo "\n9. Test Query:\n";
try {
    $count = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    echo "   Students count: $count <span class='check'>✓</span>\n";
} catch (Exception $e) {
    echo "   <span class='error'>✗ Query failed: " . $e->getMessage() . "</span>\n";
}

// 10. lesson_content table structure
echo "\n10. lesson_content Table:\n";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'lesson_content'");
    if ($stmt->rowCount() > 0) {
        $count = $db->query("SELECT COUNT(*) FROM lesson_content")->fetchColumn();
        echo "   Table exists: <span class='check'>✓</span>\n";
        echo "   Records: $count\n";
        
        // Check structure
        $cols = $db->query("DESCRIBE lesson_content")->fetchAll(PDO::FETCH_COLUMN);
        $required = ['lesson_id', 'overview_hi', 'overview_en', 'key_concepts', 'code_examples'];
        foreach ($required as $col) {
            if (in_array($col, $cols)) {
                echo "   Column '$col': <span class='check'>✓</span>\n";
            } else {
                echo "   Column '$col': <span class='error'>✗ Missing</span>\n";
            }
        }
    } else {
        echo "   <span class='error'>✗ Table doesn't exist</span>\n";
        echo "\n   <span class='warning'>To create it, run:</span>\n";
        echo "   mysql -u root tcm_db < database/lesson_content.sql\n";
    }
} catch (Exception $e) {
    echo "   <span class='error'>✗ Error: " . $e->getMessage() . "</span>\n";
}

echo "\n=== End of Checks ===\n";

    ?></pre>
    
    <h2>📝 Next Steps:</h2>
    <ol>
        <li>If any checks are <span class="error">✗ red</span>, fix those first</li>
        <li>If <code>lesson_content</code> table is missing, create it</li>
        <li>Check file permissions on server</li>
        <li>Clear cache: <code>opcache_reset()</code></li>
    </ol>
    
    <p><a href="/student">← Back to Dashboard</a></p>
</body>
</html>
