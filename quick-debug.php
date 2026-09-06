<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Quick Debug</h1><pre>";

// 1. Check .env
echo "1. Checking .env file:\n";
if (file_exists('.env')) {
    $env = file_get_contents('.env');
    if (strpos($env, 'GEMINI_API_KEY') !== false) {
        preg_match('/GEMINI_API_KEY=(.+)/', $env, $matches);
        $key = trim($matches[1] ?? '');
        if ($key) {
            echo "   ✅ GEMINI_API_KEY found: " . substr($key, 0, 10) . "...\n";
            echo "   Length: " . strlen($key) . " chars\n";
        } else {
            echo "   ❌ GEMINI_API_KEY empty\n";
        }
    } else {
        echo "   ❌ GEMINI_API_KEY not in .env\n";
    }
} else {
    echo "   ❌ .env file not found\n";
}

// 2. Test Gemini directly
echo "\n2. Testing Gemini API directly:\n";
$key = getenv('GEMINI_API_KEY') ?: (isset($key) ? $key : '');
if ($key) {
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-pro:generateContent?key=" . $key;
    
    $data = json_encode([
        'contents' => [[
            'parts' => [['text' => 'Say "Hello, I am working!" if you can read this.']]
        ]]
    ]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "   HTTP Code: $httpCode\n";
    if ($error) {
        echo "   ❌ cURL Error: $error\n";
    } elseif ($httpCode === 200) {
        $json = json_decode($response, true);
        if (isset($json['candidates'][0]['content']['parts'][0]['text'])) {
            echo "   ✅ API Working! Response: " . $json['candidates'][0]['content']['parts'][0]['text'] . "\n";
        } else {
            echo "   ❌ Invalid response format\n";
            echo "   Response: " . substr($response, 0, 200) . "\n";
        }
    } else {
        echo "   ❌ API Error: $response\n";
    }
} else {
    echo "   ❌ No API key to test\n";
}

// 3. Check files
echo "\n3. Checking files:\n";
$files = [
    'src/Services/GeminiAI.php',
    'src/Controllers/Student/AgentController.php',
    'src/Services/AIContentGenerator.php'
];
foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file MISSING\n";
    }
}

// 4. Check database
echo "\n4. Checking database:\n";
try {
    require_once 'config/config.php';
    $db = getDbConnection();
    echo "   ✅ Database connected\n";
    
    $count = $db->query("SELECT COUNT(*) FROM lesson_content")->fetchColumn();
    echo "   📊 lesson_content records: $count\n";
    
    if ($count > 0) {
        $sample = $db->query("SELECT lesson_id, LENGTH(overview_en) as len, ai_model FROM lesson_content LIMIT 1")->fetch(PDO::FETCH_ASSOC);
        echo "   Sample: Lesson {$sample['lesson_id']}, Overview length: {$sample['len']}, Model: {$sample['ai_model']}\n";
        
        if ($sample['len'] < 100) {
            echo "   ⚠️ WARNING: Content seems too short (generic)\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Database error: " . $e->getMessage() . "\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
if (!$key) {
    echo "❌ Add GEMINI_API_KEY to .env file\n";
}
if ($httpCode !== 200) {
    echo "❌ Fix Gemini API connection\n";
}
echo "\nDone! Check above for issues.\n";
echo "</pre>";
?>
