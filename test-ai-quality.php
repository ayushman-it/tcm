<?php
/**
 * Test AI Content Quality
 * Run this to test if AI generates proper detailed content
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';

use TCM\Services\AIContentGenerator;

echo "<h1>🤖 AI Content Quality Test</h1>";
echo "<pre>";

// Get a test lesson
$db = getDbConnection();
$lesson = $db->query("
    SELECT l.*, m.title as module_title, c.title as course_title
    FROM course_lessons l
    JOIN course_modules m ON l.module_id = m.id
    JOIN courses c ON m.course_id = c.id
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    die("❌ No lessons found in database. Create a course with lessons first.");
}

echo "Testing with lesson:\n";
echo "  Title: {$lesson['title']}\n";
echo "  Module: {$lesson['module_title']}\n";
echo "  Course: {$lesson['course_title']}\n\n";

echo "Generating AI content...\n";
echo "(This will take 30-60 seconds)\n\n";

try {
    $generator = new AIContentGenerator($db);
    $content = $generator->generateLessonContent($lesson['id'], 'hi+en');
    
    echo "✅ Content generated successfully!\n\n";
    
    // Check quality
    echo "=== QUALITY CHECK ===\n\n";
    
    // 1. Overview
    echo "1. Overview (Hindi):\n";
    if (!empty($content['overview_hi']) && 
        strlen($content['overview_hi']) > 50 && 
        strpos($content['overview_hi'], 'course material') === false &&
        strpos($content['overview_hi'], 'follow') === false) {
        echo "   ✅ GOOD - Detailed and specific\n";
        echo "   Preview: " . substr($content['overview_hi'], 0, 100) . "...\n";
    } else {
        echo "   ❌ BAD - Generic or placeholder\n";
        echo "   Content: " . ($content['overview_hi'] ?? 'N/A') . "\n";
    }
    echo "\n";
    
    // 2. Key Concepts
    echo "2. Key Concepts:\n";
    if (!empty($content['key_concepts']) && count($content['key_concepts']) >= 3) {
        echo "   ✅ GOOD - " . count($content['key_concepts']) . " concepts found\n";
        foreach ($content['key_concepts'] as $i => $concept) {
            echo "   " . ($i+1) . ". " . ($concept['title_en'] ?? 'N/A') . "\n";
            if (strlen($concept['explanation_en'] ?? '') > 100) {
                echo "      ✅ Detailed explanation\n";
            } else {
                echo "      ❌ Too short or generic\n";
            }
        }
    } else {
        echo "   ❌ BAD - Not enough concepts\n";
    }
    echo "\n";
    
    // 3. Code Examples
    echo "3. Code Examples:\n";
    if (!empty($content['code_examples'])) {
        echo "   Found " . count($content['code_examples']) . " examples\n";
        foreach ($content['code_examples'] as $i => $example) {
            echo "   Example " . ($i+1) . ": " . ($example['title'] ?? 'N/A') . "\n";
            
            // Check if code is real
            $code = $example['code'] ?? '';
            if (strlen($code) > 50 && 
                strpos($code, '//') !== false &&
                strpos(strtolower($code), 'placeholder') === false &&
                strpos(strtolower($code), 'follow') === false) {
                echo "      ✅ GOOD - Real working code\n";
                echo "      Preview: " . substr($code, 0, 80) . "...\n";
            } else {
                echo "      ❌ BAD - Placeholder or too short\n";
                echo "      Code: " . substr($code, 0, 100) . "\n";
            }
            
            // Check if output exists
            if (!empty($example['output'])) {
                echo "      ✅ Output provided\n";
            } else {
                echo "      ❌ No output\n";
            }
        }
    } else {
        echo "   ❌ BAD - No code examples\n";
    }
    echo "\n";
    
    // 4. Exercises
    echo "4. Practice Exercises:\n";
    if (!empty($content['exercises'])) {
        echo "   Found " . count($content['exercises']) . " exercises\n";
        foreach ($content['exercises'] as $i => $exercise) {
            echo "   Exercise " . ($i+1) . ": " . ($exercise['title'] ?? 'N/A') . "\n";
            
            // Check solution
            if (!empty($exercise['solution']) && strlen($exercise['solution']) > 50) {
                echo "      ✅ Complete solution provided\n";
            } else {
                echo "      ❌ No proper solution\n";
            }
        }
    } else {
        echo "   ❌ BAD - No exercises\n";
    }
    echo "\n";
    
    // Overall score
    $score = 0;
    if (!empty($content['overview_hi']) && strlen($content['overview_hi']) > 50) $score += 25;
    if (!empty($content['key_concepts']) && count($content['key_concepts']) >= 3) $score += 25;
    if (!empty($content['code_examples']) && count($content['code_examples']) >= 1) $score += 25;
    if (!empty($content['exercises']) && count($content['exercises']) >= 1) $score += 25;
    
    echo "=== OVERALL QUALITY SCORE ===\n";
    echo "$score / 100\n\n";
    
    if ($score >= 75) {
        echo "✅ EXCELLENT - AI is generating high-quality content!\n";
    } elseif ($score >= 50) {
        echo "⚠️ ACCEPTABLE - Content is okay but could be better\n";
    } else {
        echo "❌ POOR - AI is generating placeholder content\n";
        echo "   Check API key and prompt configuration\n";
    }
    
    echo "\n=== SAMPLE CONTENT ===\n\n";
    echo "Overview (Hindi):\n";
    echo $content['overview_hi'] ?? 'N/A';
    echo "\n\n";
    
    if (!empty($content['code_examples'][0])) {
        echo "First Code Example:\n";
        echo "Title: " . $content['code_examples'][0]['title'] . "\n";
        echo "Code:\n" . $content['code_examples'][0]['code'] . "\n";
        echo "Output: " . $content['code_examples'][0]['output'] . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "</pre>";
?>
