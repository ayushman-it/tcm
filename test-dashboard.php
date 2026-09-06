<?php
/**
 * Test dashboard page for debugging 500 errors
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "<h2>Testing Dashboard Components...</h2>";
echo "<pre>";

// Test 1: Autoloader
echo "1. Testing Autoloader...\n";
require_once __DIR__ . '/config/config.php';
echo "   ✅ Autoloader loaded\n\n";

// Test 2: Database Connection
echo "2. Testing Database Connection...\n";
try {
    $db = getDbConnection();
    echo "   ✅ Database connected\n\n";
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n\n";
    exit;
}

// Test 3: Check required tables
echo "3. Checking Required Tables...\n";
$requiredTables = ['users', 'courses', 'course_enrollments', 'course_lessons', 'course_modules'];
foreach ($requiredTables as $table) {
    $stmt = $db->query("SHOW TABLES LIKE '$table'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Table '$table' exists\n";
    } else {
        echo "   ❌ Table '$table' MISSING\n";
    }
}
echo "\n";

// Test 4: Check lesson_content table
echo "4. Checking lesson_content Table...\n";
$stmt = $db->query("SHOW TABLES LIKE 'lesson_content'");
if ($stmt->rowCount() > 0) {
    echo "   ✅ lesson_content table exists\n";
} else {
    echo "   ⚠️ lesson_content table NOT FOUND (will be created on first use)\n";
}
echo "\n";

// Test 5: Check Controllers
echo "5. Testing Controllers...\n";
$controllers = [
    'TCM\Controllers\Student\CourseController',
    'TCM\Controllers\Student\LessonConceptsController'
];

foreach ($controllers as $class) {
    if (class_exists($class)) {
        echo "   ✅ $class found\n";
    } else {
        echo "   ❌ $class NOT FOUND\n";
    }
}
echo "\n";

// Test 6: Check Routes
echo "6. Testing Route Configuration...\n";
$routeFile = __DIR__ . '/app.php';
if (file_exists($routeFile)) {
    echo "   ✅ app.php exists\n";
    $content = file_get_contents($routeFile);
    if (strpos($content, '/student/lesson-concepts/') !== false) {
        echo "   ✅ lesson-concepts route found\n";
    } else {
        echo "   ❌ lesson-concepts route NOT FOUND\n";
    }
    if (strpos($content, '/student/learn/{id}/lessons') !== false) {
        echo "   ✅ lessons API route found\n";
    } else {
        echo "   ❌ lessons API route NOT FOUND\n";
    }
} else {
    echo "   ❌ app.php NOT FOUND\n";
}
echo "\n";

// Test 7: Check .env
echo "7. Checking Environment Variables...\n";
$envVars = ['GEMINI_API_KEY', 'OPENAI_API_KEY'];
foreach ($envVars as $var) {
    $value = getenv($var);
    if ($value) {
        echo "   ✅ $var is set (" . substr($value, 0, 10) . "...)\n";
    } else {
        echo "   ⚠️ $var not set\n";
    }
}
echo "\n";

// Test 8: Try to load dashboard view
echo "8. Testing Dashboard View...\n";
$dashboardFile = __DIR__ . '/views/student/dashboard.php';
if (file_exists($dashboardFile)) {
    echo "   ✅ dashboard.php exists\n";
    // Check for syntax by parsing
    $result = exec("c:\\xampp\\php\\php.exe -l \"$dashboardFile\" 2>&1", $output, $code);
    if ($code === 0) {
        echo "   ✅ No syntax errors\n";
    } else {
        echo "   ❌ Syntax errors found:\n";
        foreach ($output as $line) {
            echo "      $line\n";
        }
    }
} else {
    echo "   ❌ dashboard.php NOT FOUND\n";
}
echo "\n";

echo "</pre>";
echo "<h3>All Tests Complete!</h3>";
echo "<p><a href='/tcm/tcm-2.0/'>Go to Homepage</a> | <a href='/tcm/tcm-2.0/student'>Go to Student Dashboard</a></p>";
