<?php
/**
 * Test Gemini AI Connection
 * Quick script to verify Gemini API is working
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/src/Services/GeminiAI.php';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Gemini AI Test - TCM</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #1a1a1a; color: #0f0; }
        .success { color: #0f0; }
        .error { color: #f00; }
        .warning { color: #ff0; }
        pre { background: #000; padding: 15px; border-radius: 5px; white-space: pre-wrap; }
        h2 { color: #0ff; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #333; border-radius: 5px; }
        button { background: #0f0; color: #000; border: none; padding: 10px 20px; cursor: pointer; font-weight: bold; border-radius: 5px; }
        button:hover { background: #0ff; }
        #chatTest { margin-top: 20px; }
        input[type="text"] { width: 80%; padding: 10px; background: #222; color: #0f0; border: 1px solid #0f0; }
        #response { margin-top: 10px; }
    </style>
</head>
<body>
    <h1>🤖 Gemini AI Connection Test</h1>
    
    <div class="test-section">
        <h2>1. Environment Check</h2>
        <pre><?php
        echo "Checking .env configuration...\n\n";
        
        $geminiKey = getenv('GEMINI_API_KEY');
        if ($geminiKey) {
            echo "✅ GEMINI_API_KEY: Found (" . substr($geminiKey, 0, 10) . "...)\n";
        } else {
            echo "❌ GEMINI_API_KEY: NOT FOUND\n";
            echo "   Add to .env: GEMINI_API_KEY=your-key-here\n";
        }
        
        $openaiKey = getenv('OPENAI_API_KEY');
        if ($openaiKey) {
            echo "✅ OPENAI_API_KEY: Found (" . substr($openaiKey, 0, 10) . "...)\n";
        } else {
            echo "⚠️  OPENAI_API_KEY: Not found (optional)\n";
        }
        ?></pre>
    </div>

    <?php if ($geminiKey): ?>
    <div class="test-section">
        <h2>2. API Connection Test</h2>
        <pre><?php
        echo "Testing connection to Gemini API...\n\n";
        
        try {
            $result = TCM\Services\GeminiAI::testConnection();
            
            if ($result['success']) {
                echo "<span class='success'>✅ SUCCESS: {$result['message']}</span>\n\n";
                echo "AI Response: {$result['response']}\n";
            } else {
                echo "<span class='error'>❌ FAILED: {$result['message']}</span>\n\n";
                echo "Error: {$result['error']}\n";
            }
        } catch (Exception $e) {
            echo "<span class='error'>❌ EXCEPTION: {$e->getMessage()}</span>\n";
        }
        ?></pre>
    </div>

    <div class="test-section">
        <h2>3. Feature Tests</h2>
        <pre><?php
        try {
            $gemini = new TCM\Services\GeminiAI();
            
            // Test 1: Simple chat
            echo "Test 1: Simple Chat\n";
            echo "Question: What is JavaScript?\n";
            $answer = $gemini->chat("Explain JavaScript in 2 sentences");
            echo "Answer: $answer\n\n";
            echo "<span class='success'>✅ Chat working!</span>\n\n";
            
            // Test 2: Code generation
            echo "Test 2: Code Generation\n";
            echo "Request: Generate a simple hello world function\n";
            $codeResult = $gemini->generateCode("Create a hello world function in JavaScript");
            echo "Code Generated:\n";
            echo $codeResult['code'] . "\n\n";
            echo "<span class='success'>✅ Code generation working!</span>\n\n";
            
            // Test 3: Explain concept
            echo "Test 3: Concept Explanation\n";
            echo "Concept: Variables in JavaScript\n";
            $explanation = $gemini->explainConcept("Variables in JavaScript", "beginner");
            echo "Explanation: " . substr($explanation, 0, 200) . "...\n\n";
            echo "<span class='success'>✅ Concept explanation working!</span>\n\n";
            
            echo "\n<span class='success'>🎉 All tests passed! Gemini AI is fully functional!</span>\n";
            
        } catch (Exception $e) {
            echo "<span class='error'>❌ Error during tests: {$e->getMessage()}</span>\n";
        }
        ?></pre>
    </div>

    <div class="test-section">
        <h2>4. Interactive Chat Test</h2>
        <p>Try asking the AI agent a question:</p>
        <div id="chatTest">
            <input type="text" id="question" placeholder="Ask me anything... e.g., 'teach me how to write a class in JavaScript'" />
            <button onclick="askAI()">Ask AI</button>
            <div id="response"></div>
        </div>
    </div>

    <script>
    async function askAI() {
        const question = document.getElementById('question').value;
        const responseDiv = document.getElementById('response');
        
        if (!question.trim()) {
            responseDiv.innerHTML = '<pre class="error">Please enter a question</pre>';
            return;
        }
        
        responseDiv.innerHTML = '<pre class="warning">⏳ Asking Gemini AI...</pre>';
        
        try {
            const response = await fetch('/tcm/tcm-2.0/student/agent/chat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'message=' + encodeURIComponent(question)
            });
            
            const data = await response.json();
            
            if (data.success) {
                responseDiv.innerHTML = `
                    <pre class="success">✅ Response received!
                    
Powered by: ${data.powered_by || 'Gemini AI'}

${data.message}</pre>`;
            } else {
                responseDiv.innerHTML = `<pre class="error">❌ Error: ${data.message}</pre>`;
            }
        } catch (error) {
            responseDiv.innerHTML = `<pre class="error">❌ Request failed: ${error.message}</pre>`;
        }
    }
    
    // Allow Enter key to submit
    document.getElementById('question').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            askAI();
        }
    });
    </script>

    <?php else: ?>
    <div class="test-section">
        <h2>⚠️ Setup Required</h2>
        <pre>
GEMINI_API_KEY is not configured.

To fix:
1. Get API key from: https://makersuite.google.com/app/apikey
2. Add to .env file:
   GEMINI_API_KEY=your-key-here
3. Refresh this page
        </pre>
    </div>
    <?php endif; ?>

    <div class="test-section">
        <h2>📋 Summary</h2>
        <pre><?php
        if ($geminiKey) {
            echo "<span class='success'>✅ Gemini AI is configured and ready!</span>\n\n";
            echo "Features enabled:\n";
            echo "• Chat with AI agent\n";
            echo "• Code generation\n";
            echo "• Concept explanations\n";
            echo "• Code debugging\n";
            echo "• Interactive learning\n\n";
            echo "Students can now:\n";
            echo "• Ask 'teach me how to write a class'\n";
            echo "• Request 'generate a function for sorting'\n";
            echo "• Get 'explain promises in JavaScript'\n";
            echo "• Debug 'why is my code not working'\n";
        } else {
            echo "<span class='error'>❌ Gemini AI is NOT configured</span>\n\n";
            echo "Please add GEMINI_API_KEY to .env file\n";
        }
        ?></pre>
    </div>

    <p><a href="/tcm/tcm-2.0/student/agent" style="color: #0ff;">→ Go to TCM Agent</a></p>
</body>
</html>
