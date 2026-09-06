#!/usr/bin/env php
<?php
/**
 * AI Content Generator Setup Script
 * 
 * Automatically sets up the AI content generation system:
 * - Creates database tables
 * - Checks API key configuration
 * - Tests OpenAI connection
 * - Generates sample content
 * 
 * Usage: php setup-ai-content.php
 */

echo "🤖 AI Content Generator Setup\n";
echo "================================\n\n";

require_once __DIR__ . '/config/config.php';

// Step 1: Database Setup
echo "📦 Step 1: Database Setup\n";
echo "---\n";

$db = getDbConnection();

try {
    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database/lesson_content.sql');
    
    // Split by statements (basic approach)
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        fn($s) => !empty($s) && !str_starts_with($s, '--')
    );
    
    foreach ($statements as $statement) {
        if (preg_match('/^\s*(CREATE|ALTER|INSERT|SET)/i', $statement)) {
            $db->exec($statement);
        }
    }
    
    echo "✅ Database tables created successfully\n\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "ℹ️  Tables already exist (skipping)\n\n";
    } else {
        echo "❌ Database error: " . $e->getMessage() . "\n\n";
        exit(1);
    }
}

// Step 2: Check API Key
echo "🔑 Step 2: OpenAI API Key Check\n";
echo "---\n";

$apiKey = getenv('OPENAI_API_KEY') ?: '';

if (empty($apiKey)) {
    echo "⚠️  WARNING: OPENAI_API_KEY not found in .env file!\n";
    echo "\n";
    echo "To fix this:\n";
    echo "1. Get your API key from: https://platform.openai.com/api-keys\n";
    echo "2. Add to .env file: OPENAI_API_KEY=sk-proj-xxxxx\n";
    echo "3. Restart this script\n\n";
    
    $continue = readline("Continue without API key? (y/n): ");
    if (strtolower($continue) !== 'y') {
        echo "\nSetup cancelled.\n";
        exit(0);
    }
    echo "\n";
} else {
    echo "✅ API Key found: " . substr($apiKey, 0, 20) . "...\n\n";
}

// Step 3: Test API Connection (if key exists)
if (!empty($apiKey)) {
    echo "🌐 Step 3: Testing OpenAI Connection\n";
    echo "---\n";
    
    try {
        $ch = curl_init('https://api.openai.com/v1/models');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "✅ OpenAI API connection successful\n";
            $data = json_decode($response, true);
            $models = array_filter(
                array_column($data['data'] ?? [], 'id'),
                fn($m) => str_contains($m, 'gpt')
            );
            echo "   Available models: " . implode(', ', array_slice($models, 0, 3)) . "...\n\n";
        } else {
            echo "❌ API connection failed (HTTP $httpCode)\n";
            echo "   Response: $response\n\n";
            echo "   Please check:\n";
            echo "   - API key is valid\n";
            echo "   - Billing is set up: https://platform.openai.com/settings/organization/billing\n";
            echo "   - No rate limits exceeded\n\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Connection test failed: " . $e->getMessage() . "\n\n";
    }
} else {
    echo "⏭️  Step 3: Skipping API connection test (no API key)\n\n";
}

// Step 4: Show Statistics
echo "📊 Step 4: Current Content Statistics\n";
echo "---\n";

try {
    // Count lessons with/without content
    $stats = $db->query("
        SELECT 
            COUNT(DISTINCT l.id) as total_lessons,
            COUNT(DISTINCT lc.id) as lessons_with_content,
            COUNT(DISTINCT CASE WHEN lc.status = 'published' THEN lc.id END) as published,
            COUNT(DISTINCT CASE WHEN lc.status = 'draft' THEN lc.id END) as draft
        FROM course_lessons l
        LEFT JOIN lesson_content lc ON l.id = lc.lesson_id
    ")->fetch(PDO::FETCH_ASSOC);
    
    $missing = $stats['total_lessons'] - $stats['lessons_with_content'];
    
    echo "Total Lessons: {$stats['total_lessons']}\n";
    echo "  ✅ Published Content: {$stats['published']}\n";
    echo "  📝 Draft Content: {$stats['draft']}\n";
    echo "  ❌ Missing Content: $missing\n\n";
    
    if ($missing > 0) {
        echo "💡 Tip: Generate missing content with:\n";
        echo "   php generate-content.php --all\n\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Could not fetch statistics: " . $e->getMessage() . "\n\n";
}

// Step 5: Generate Sample Content (optional)
if (!empty($apiKey)) {
    echo "🎯 Step 5: Generate Sample Content (Optional)\n";
    echo "---\n";
    
    $generate = readline("Generate sample content for first lesson? (y/n): ");
    
    if (strtolower($generate) === 'y') {
        echo "\n";
        
        try {
            // Find first lesson without content
            $stmt = $db->query("
                SELECT l.id, l.title, m.title as module_title
                FROM course_lessons l
                JOIN course_modules m ON l.module_id = m.id
                LEFT JOIN lesson_content lc ON l.id = lc.lesson_id
                WHERE lc.id IS NULL
                LIMIT 1
            ");
            
            $lesson = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($lesson) {
                echo "Generating content for:\n";
                echo "  Lesson: {$lesson['title']}\n";
                echo "  Module: {$lesson['module_title']}\n";
                echo "  ID: {$lesson['id']}\n\n";
                
                echo "⏳ This may take 30-60 seconds...\n\n";
                
                // Run CLI command
                $output = [];
                $returnCode = 0;
                exec("php generate-content.php --lesson={$lesson['id']} 2>&1", $output, $returnCode);
                
                echo implode("\n", $output) . "\n\n";
                
                if ($returnCode === 0) {
                    echo "✅ Sample content generated successfully!\n";
                    echo "   View it in admin panel: /admin/ai-content\n\n";
                } else {
                    echo "❌ Generation failed. Check the error above.\n\n";
                }
                
            } else {
                echo "ℹ️  All lessons already have content!\n\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Generation failed: " . $e->getMessage() . "\n\n";
        }
    } else {
        echo "⏭️  Skipping sample generation\n\n";
    }
} else {
    echo "⏭️  Step 5: Skipping sample generation (no API key)\n\n";
}

// Final Summary
echo "🎉 Setup Complete!\n";
echo "================================\n\n";

echo "Next Steps:\n";
echo "1. 📝 Add/verify your OpenAI API key in .env\n";
echo "2. 🌐 Visit admin panel: http://localhost:8000/admin/ai-content\n";
echo "3. 🤖 Generate content for your lessons\n";
echo "4. 📚 Read the guide: AI_CONTENT_GENERATOR_GUIDE.md\n\n";

echo "Quick Commands:\n";
echo "  php generate-content.php --status         # Check status\n";
echo "  php generate-content.php --lesson=4       # Generate single lesson\n";
echo "  php generate-content.php --module=3       # Generate whole module\n";
echo "  php generate-content.php --all            # Generate all missing\n\n";

echo "Happy Teaching! 🚀\n";
