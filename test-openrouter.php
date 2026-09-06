<?php
/**
 * OpenRouter AI Connection Test
 * Use this to verify OpenRouter integration is working
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Services/OpenRouterAI.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>OpenRouter AI Test - TCM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-top: 0;
        }
        .test-section {
            background: #f7f9fc;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
        .success {
            color: #10b981;
            font-weight: bold;
        }
        .error {
            color: #ef4444;
            font-weight: bold;
        }
        .warning {
            color: #f59e0b;
            font-weight: bold;
        }
        pre {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
        }
        .code-block {
            background: #f1f5f9;
            padding: 10px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
        }
        .info-box {
            background: #dbeafe;
            border: 1px solid #3b82f6;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .check { color: #10b981; }
        .cross { color: #ef4444; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🤖 OpenRouter AI Connection Test</h1>
        <p>Testing OpenRouter integration for TCM Agent + Lesson Content Generation</p>

        <div class="test-section">
            <h2>📋 Step 1: Environment Check</h2>
            <pre><?php
            echo "Checking .env configuration...\n\n";
            
            $openrouterKey = getenv('OPENROUTER_API_KEY');
            if ($openrouterKey) {
                echo "✅ OPENROUTER_API_KEY: Found (" . substr($openrouterKey, 0, 15) . "...)\n";
                echo "   Length: " . strlen($openrouterKey) . " chars\n";
            } else {
                echo "❌ OPENROUTER_API_KEY: NOT FOUND\n";
                echo "   Add to .env: OPENROUTER_API_KEY=your-key-here\n";
            }
            
            // Check if old keys are removed
            $geminiKey = getenv('GEMINI_API_KEY');
            $openaiKey = getenv('OPENAI_API_KEY');
            
            echo "\n";
            if (empty($geminiKey)) {
                echo "✅ GEMINI_API_KEY: Not set (good, using OpenRouter)\n";
            } else {
                echo "⚠️  GEMINI_API_KEY: Still set (can be removed)\n";
            }
            
            if (empty($openaiKey)) {
                echo "✅ OPENAI_API_KEY: Not set (good, using OpenRouter)\n";
            } else {
                echo "⚠️  OPENAI_API_KEY: Still set (can be removed)\n";
            }
            ?></pre>
        </div>

        <?php if ($openrouterKey): ?>
        <div class="test-section">
            <h2>🔌 Step 2: API Connection Test</h2>
            <pre><?php
            try {
                $result = TCM\Services\OpenRouterAI::test();
                
                if ($result['success']) {
                    echo "<span class='success'>✅ SUCCESS!</span>\n\n";
                    echo "OpenRouter AI is connected and responding.\n";
                    echo "Response: " . $result['response'] . "\n";
                } else {
                    echo "<span class='error'>❌ FAILED</span>\n\n";
                    echo "Error: " . $result['error'] . "\n";
                }
            } catch (Exception $e) {
                echo "<span class='error'>❌ EXCEPTION</span>\n\n";
                echo "Error: " . $e->getMessage() . "\n";
            }
            ?></pre>
        </div>

        <div class="test-section">
            <h2>💬 Step 3: Chat Test (TCM Agent)</h2>
            <pre><?php
            try {
                $ai = new TCM\Services\OpenRouterAI();
                
                echo "Testing chat functionality...\n\n";
                
                // Test 1: Simple greeting
                echo "Test 1 - Simple Chat:\n";
                echo "Question: 'Hello, who are you?'\n";
                $response = $ai->chat('Hello, who are you?', '', ['maxTokens' => 100]);
                echo "Response: " . substr($response, 0, 150) . "...\n\n";
                
                // Test 2: Code generation
                echo "Test 2 - Code Generation:\n";
                echo "Question: 'Write a simple JavaScript function to add two numbers'\n";
                $response = $ai->chat('Write a simple JavaScript function to add two numbers', '', [
                    'temperature' => 0.7,
                    'maxTokens' => 300
                ]);
                echo "Response Preview:\n";
                echo substr($response, 0, 200) . "...\n";
                
                if (str_contains($response, 'function') || str_contains($response, 'const')) {
                    echo "\n<span class='success'>✅ Code generation working!</span>\n";
                } else {
                    echo "\n<span class='warning'>⚠️  Response doesn't contain code</span>\n";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
            }
            ?></pre>
        </div>

        <div class="test-section">
            <h2>📚 Step 4: Lesson Content Test</h2>
            <pre><?php
            try {
                $ai = new TCM\Services\OpenRouterAI();
                
                echo "Testing lesson content generation...\n\n";
                
                // Mock lesson data
                $lesson = [
                    'title' => 'JavaScript Variables - let, const, var',
                    'module_title' => 'JavaScript Fundamentals',
                    'course_title' => 'Complete JavaScript Course',
                    'duration_minutes' => 30
                ];
                
                echo "Generating content for: {$lesson['title']}\n";
                echo "This may take 5-10 seconds...\n\n";
                
                $content = $ai->generateLessonContent($lesson, 'hi+en');
                
                if (isset($content['overview_en'])) {
                    echo "<span class='success'>✅ Content generated successfully!</span>\n\n";
                    
                    echo "Overview (English):\n";
                    echo substr($content['overview_en'], 0, 150) . "...\n\n";
                    
                    if (isset($content['key_concepts']) && count($content['key_concepts']) > 0) {
                        echo "Key Concepts: " . count($content['key_concepts']) . " concepts\n";
                    }
                    
                    if (isset($content['code_examples']) && count($content['code_examples']) > 0) {
                        echo "Code Examples: " . count($content['code_examples']) . " examples\n";
                        $firstExample = $content['code_examples'][0];
                        if (isset($firstExample['code'])) {
                            $codeLength = strlen($firstExample['code']);
                            echo "First example length: {$codeLength} chars\n";
                            
                            if ($codeLength > 50) {
                                echo "<span class='success'>✅ Code examples are substantial!</span>\n";
                            } else {
                                echo "<span class='warning'>⚠️  Code example seems short</span>\n";
                            }
                        }
                    }
                    
                    // Check for generic placeholders
                    $contentStr = json_encode($content);
                    $badPhrases = ['Follow course materials', 'Code examples available', 'Practice with examples'];
                    $foundBad = false;
                    foreach ($badPhrases as $phrase) {
                        if (stripos($contentStr, $phrase) !== false) {
                            echo "<span class='error'>❌ Found generic phrase: '$phrase'</span>\n";
                            $foundBad = true;
                        }
                    }
                    
                    if (!$foundBad) {
                        echo "<span class='success'>✅ No generic placeholders found!</span>\n";
                    }
                    
                } else {
                    echo "<span class='error'>❌ Invalid content format</span>\n";
                    echo "Response: " . substr(json_encode($content), 0, 200) . "...\n";
                }
                
            } catch (Exception $e) {
                echo "<span class='error'>❌ Error: " . $e->getMessage() . "</span>\n";
            }
            ?></pre>
        </div>

        <div class="info-box">
            <h3>✅ All Tests Complete!</h3>
            <p><strong>What to check:</strong></p>
            <ul>
                <li>✅ OpenRouter API key should be found</li>
                <li>✅ Connection test should succeed</li>
                <li>✅ Chat should generate responses</li>
                <li>✅ Code generation should work</li>
                <li>✅ Lesson content should be detailed (no placeholders)</li>
            </ul>
            
            <p><strong>If all green:</strong> Integration is working! Test on actual dashboard.</p>
            <p><strong>If any red:</strong> Check .env file and API key validity.</p>
        </div>

        <?php else: ?>
        <div class="test-section">
            <h2>⚠️ Setup Required</h2>
            <pre>
OPENROUTER_API_KEY is not configured.

To fix:
1. Get API key from: https://openrouter.ai/keys
2. Add to .env file:
   OPENROUTER_API_KEY=your-openrouter-api-key-here
3. Refresh this page
            </pre>
        </div>
        <?php endif; ?>

        <div class="test-section">
            <h2>📁 File Status</h2>
            <pre><?php
            $files = [
                'src/Services/OpenRouterAI.php' => 'OpenRouter Service',
                'src/Services/AIContentGenerator.php' => 'Content Generator',
                'src/Controllers/Student/AgentController.php' => 'Agent Controller',
                'src/Controllers/Student/LessonConceptsController.php' => 'Lesson Concepts'
            ];
            
            foreach ($files as $file => $name) {
                if (file_exists(__DIR__ . '/' . $file)) {
                    echo "<span class='check'>✓</span> {$name}\n";
                } else {
                    echo "<span class='cross'>✗</span> {$name} - MISSING\n";
                }
            }
            ?></pre>
        </div>

        <div class="test-section">
            <h2>🔗 Next Steps</h2>
            <ol>
                <li>If all tests pass, test on actual student dashboard</li>
                <li>Try TCM Agent with: "teach me how to write a class"</li>
                <li>Expand a course lesson to test auto-generation</li>
                <li>Check for quality - no generic placeholders</li>
            </ol>
        </div>
    </div>
</body>
</html>
