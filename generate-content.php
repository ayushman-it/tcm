#!/usr/bin/env php
<?php
/**
 * CLI tool to generate AI-powered lesson content
 * 
 * Usage:
 *   php generate-content.php --lesson=4               # Generate for specific lesson
 *   php generate-content.php --module=3               # Generate for all lessons in module
 *   php generate-content.php --course=1               # Generate for all lessons in course
 *   php generate-content.php --all                    # Generate for all lessons
 *   php generate-content.php --status                 # Show content generation status
 *   php generate-content.php --regenerate --lesson=4  # Regenerate existing content
 */

require_once __DIR__ . '/config/config.php';

use TCM\Services\AIContentGenerator;

// Parse command line arguments
$options = getopt('', [
    'lesson:',
    'module:',
    'course:',
    'all',
    'status',
    'regenerate',
    'lang:',
    'help'
]);

if (isset($options['help']) || empty($options)) {
    echo <<<HELP
AI Content Generator CLI Tool
==============================

Usage:
  php generate-content.php [options]

Options:
  --lesson=ID        Generate content for a specific lesson ID
  --module=ID        Generate content for all lessons in a module
  --course=ID        Generate content for all lessons in a course
  --all              Generate content for all lessons without content
  --status           Show content generation status for all lessons
  --regenerate       Regenerate existing content (use with --lesson)
  --lang=LANG        Language preference: hi, en, or hi+en (default: hi+en)
  --help             Show this help message

Examples:
  php generate-content.php --lesson=4
  php generate-content.php --module=3 --lang=hi+en
  php generate-content.php --status
  php generate-content.php --regenerate --lesson=4

HELP;
    exit(0);
}

$db = getDbConnection();
$generator = new AIContentGenerator($db);
$language = $options['lang'] ?? 'hi+en';

try {
    // Show status
    if (isset($options['status'])) {
        showStatus($db);
        exit(0);
    }

    // Generate for specific lesson
    if (isset($options['lesson'])) {
        $lessonId = (int) $options['lesson'];
        echo "🤖 Generating content for lesson ID: $lessonId\n";
        echo "Language: $language\n";
        echo "---\n";
        
        if (isset($options['regenerate'])) {
            echo "Regenerating existing content...\n";
            $content = $generator->regenerateContent($lessonId, $language);
        } else {
            $content = $generator->generateLessonContent($lessonId, $language);
        }
        
        echo "✅ Content generated successfully!\n";
        echo "📝 Overview (EN): " . substr($content['overview_en'], 0, 100) . "...\n";
        echo "📊 Key Concepts: " . count($content['key_concepts']) . "\n";
        echo "💻 Code Examples: " . count($content['code_examples']) . "\n";
        echo "🎯 Exercises: " . count($content['exercises']) . "\n";
        exit(0);
    }

    // Generate for module
    if (isset($options['module'])) {
        $moduleId = (int) $options['module'];
        echo "🤖 Generating content for all lessons in module ID: $moduleId\n";
        echo "Language: $language\n";
        echo "---\n";
        
        $results = $generator->generateForModule($moduleId, $language);
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($results as $lessonId => $result) {
            if ($result['success']) {
                echo "✅ Lesson $lessonId: Success\n";
                $successCount++;
            } else {
                echo "❌ Lesson $lessonId: " . $result['error'] . "\n";
                $failCount++;
            }
        }
        
        echo "---\n";
        echo "Summary: $successCount successful, $failCount failed\n";
        exit(0);
    }

    // Generate for course
    if (isset($options['course'])) {
        $courseId = (int) $options['course'];
        echo "🤖 Generating content for all lessons in course ID: $courseId\n";
        echo "Language: $language\n";
        echo "---\n";
        
        $stmt = $db->prepare("SELECT id FROM course_modules WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $moduleIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $totalSuccess = 0;
        $totalFail = 0;
        
        foreach ($moduleIds as $moduleId) {
            echo "\n📚 Processing Module $moduleId...\n";
            $results = $generator->generateForModule($moduleId, $language);
            
            foreach ($results as $lessonId => $result) {
                if ($result['success']) {
                    echo "  ✅ Lesson $lessonId\n";
                    $totalSuccess++;
                } else {
                    echo "  ❌ Lesson $lessonId: " . $result['error'] . "\n";
                    $totalFail++;
                }
            }
        }
        
        echo "\n---\n";
        echo "Summary: $totalSuccess successful, $totalFail failed\n";
        exit(0);
    }

    // Generate for all lessons without content
    if (isset($options['all'])) {
        echo "🤖 Generating content for all lessons without content\n";
        echo "Language: $language\n";
        echo "---\n";
        
        $stmt = $db->query("
            SELECT l.id, l.title
            FROM course_lessons l
            LEFT JOIN lesson_content lc ON l.id = lc.lesson_id
            WHERE lc.id IS NULL
            ORDER BY l.id
        ");
        $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($lessons);
        echo "Found $total lessons without content\n\n";
        
        $successCount = 0;
        $failCount = 0;
        
        foreach ($lessons as $index => $lesson) {
            $num = $index + 1;
            echo "[$num/$total] Processing: {$lesson['title']} (ID: {$lesson['id']})\n";
            
            try {
                $content = $generator->generateLessonContent($lesson['id'], $language);
                echo "  ✅ Success\n";
                $successCount++;
            } catch (Exception $e) {
                echo "  ❌ Failed: " . $e->getMessage() . "\n";
                $failCount++;
            }
            
            // Add small delay to avoid rate limiting
            sleep(2);
        }
        
        echo "\n---\n";
        echo "Summary: $successCount successful, $failCount failed\n";
        exit(0);
    }

    echo "❌ No valid option provided. Use --help for usage information.\n";
    exit(1);

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function showStatus(PDO $db): void
{
    echo "📊 Lesson Content Generation Status\n";
    echo "===================================\n\n";
    
    $stmt = $db->query("
        SELECT 
            c.title as course_title,
            m.title as module_title,
            l.id as lesson_id,
            l.title as lesson_title,
            lc.status,
            lc.generated_at,
            lc.language
        FROM courses c
        JOIN course_modules m ON m.course_id = c.id
        JOIN course_lessons l ON l.module_id = m.id
        LEFT JOIN lesson_content lc ON lc.lesson_id = l.id
        WHERE c.status = 'published'
        ORDER BY c.id, m.position, l.position
    ");
    $lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $currentCourse = '';
    $currentModule = '';
    $stats = ['draft' => 0, 'reviewed' => 0, 'published' => 0, 'missing' => 0];
    
    foreach ($lessons as $lesson) {
        if ($lesson['course_title'] !== $currentCourse) {
            $currentCourse = $lesson['course_title'];
            echo "\n📚 Course: $currentCourse\n";
        }
        
        if ($lesson['module_title'] !== $currentModule) {
            $currentModule = $lesson['module_title'];
            echo "  📖 Module: $currentModule\n";
        }
        
        $status = $lesson['status'] ?? 'missing';
        $stats[$status]++;
        
        $statusIcon = match($status) {
            'published' => '✅',
            'reviewed' => '🟡',
            'draft' => '⚪',
            default => '❌'
        };
        
        $lang = $lesson['language'] ?? '-';
        echo "    $statusIcon [{$lesson['lesson_id']}] {$lesson['lesson_title']} [$status] [$lang]\n";
    }
    
    echo "\n📊 Summary:\n";
    echo "  ✅ Published: {$stats['published']}\n";
    echo "  🟡 Reviewed: {$stats['reviewed']}\n";
    echo "  ⚪ Draft: {$stats['draft']}\n";
    echo "  ❌ Missing: {$stats['missing']}\n";
    echo "  📝 Total: " . array_sum($stats) . "\n";
}
