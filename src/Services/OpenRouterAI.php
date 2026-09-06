<?php
namespace TCM\Services;

use Exception;

/**
 * OpenRouter AI Service - Unified AI for everything
 * Handles: TCM Agent chat, Lesson content generation, Code generation
 */
class OpenRouterAI
{
    private string $apiKey;
    private string $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
    private string $model = 'openai/gpt-4o-mini'; // Fast and good
    private string $siteUrl;

    public function __construct(?string $apiKey = null, ?string $siteUrl = null)
    {
        $this->apiKey = $apiKey ?? getenv('OPENROUTER_API_KEY') ?? '';
        $this->siteUrl = $siteUrl ?? getenv('APP_URL') ?? 'https://thecodemunk.in';
        
        if (empty($this->apiKey)) {
            throw new Exception('OPENROUTER_API_KEY not configured in .env file');
        }
    }

    /**
     * Chat with AI - For TCM Agent
     */
    public function chat(string $userMessage, string $systemContext = '', array $options = []): string
    {
        $messages = [];
        
        if ($systemContext) {
            $messages[] = [
                'role' => 'system',
                'content' => $systemContext
            ];
        }
        
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage
        ];
        
        $data = [
            'model' => $options['model'] ?? $this->model,
            'messages' => $messages,
            'temperature' => $options['temperature'] ?? 0.7,
            'max_tokens' => $options['maxTokens'] ?? 1000,
        ];
        
        $response = $this->callAPI($data);
        return $this->extractText($response);
    }

    /**
     * Generate detailed lesson content - For courses
     */
    public function generateLessonContent(array $lesson, string $language = 'hi+en'): array
    {
        $systemPrompt = "You are an expert programming instructor who creates comprehensive, easy-to-understand learning content for Indian students. Always respond with valid JSON only - no markdown, no text before or after the JSON.";

        $userPrompt = <<<PROMPT
Create a COMPLETE learning module for the topic: "{$lesson['title']}"

This is part of "{$lesson['module_title']}" module in "{$lesson['course_title']}" course.

📚 LEARNING GOALS:
- Explain the concept from scratch (assume beginner level)
- Provide 3-4 KEY CONCEPTS with detailed explanations (150+ words each)
- Include 2-3 REAL working code examples (minimum 25 lines each)
- Add practice exercises with complete solutions
- Use simple Hindi + English (Hinglish style for Indian students)

🎯 RESPONSE FORMAT (Valid JSON only):
{
  "overview_hi": "यह lesson में आप सीखेंगे... (3-4 sentences)",
  "overview_en": "In this lesson you will learn... (3-4 sentences)",
  "key_concepts": [
    {
      "title_hi": "कॉन्सेप्ट का नाम",
      "title_en": "Concept Name",
      "explanation_hi": "विस्तार से explain करें - क्या है, कैसे काम करता है, क्यों important है (150+ words in Hindi)",
      "explanation_en": "Detailed explanation - what it is, how it works, why important (150+ words in English)",
      "importance": "Real-world use case - where this is actually used"
    }
  ],
  "explanation_hi": "# पूरा Lesson\n\n## Introduction\n(Complete tutorial in Hindi with examples)\n\n## Main Concepts\n...\n\n## How to Use\n...",
  "explanation_en": "# Complete Lesson\n\n## Introduction\n(Complete tutorial in English with examples)\n\n## Main Concepts\n...\n\n## How to Use\n...",
  "code_examples": [
    {
      "title": "Example 1: Basic Usage",
      "description_hi": "यह example दिखाता है कि... (Hindi)",
      "description_en": "This example demonstrates... (English)",
      "code": "// REAL WORKING CODE - minimum 25 lines\n// Include imports, setup, main logic, usage\nconst example = 'real code here';\n// ... at least 25 lines of actual code",
      "output": "Expected output when code runs",
      "explanation_hi": "Code की line-by-line explanation (Hindi)",
      "explanation_en": "Line-by-line code explanation (English)"
    }
  ],
  "exercises": [
    {
      "title": "Practice Exercise 1",
      "difficulty": "beginner",
      "description_hi": "Task: ... (Hindi)",
      "description_en": "Task: ... (English)",
      "starter_code": "// Starting template code\n",
      "hints": ["Hint 1: Try...", "Hint 2: Remember..."],
      "solution": "// Complete working solution\n// At least 15-20 lines",
      "explanation": "Step-by-step solution explanation"
    }
  ],
  "resources": [
    {
      "type": "documentation",
      "title": "Official Docs",
      "url": "https://developer.mozilla.org/...",
      "description_hi": "यहाँ और जानकारी पाएं",
      "description_en": "Find more information here"
    }
  ],
  "estimated_time": {$lesson['duration_minutes']}
}

⚠️ STRICT RULES:
1. Output MUST be valid JSON (use json_encode style)
2. Provide 3-4 key_concepts minimum
3. Each code example MUST be 25+ lines of REAL, WORKING code
4. NO placeholders like "// code here" or "// add your code"
5. NO generic text like "refer to materials" or "follow examples"
6. Explanations must be SPECIFIC to "{$lesson['title']}"
7. Use simple Hinglish that Indian students understand

Generate NOW - Remember: VALID JSON ONLY!
PROMPT;

        $response = $this->chat($userPrompt, $systemPrompt, [
            'temperature' => 0.7,
            'maxTokens' => 6000,
            'model' => 'openai/gpt-4o' // Better model for quality content
        ]);
        
        // Clean up response - remove markdown if present
        $cleaned = trim($response);
        $cleaned = preg_replace('/^```json\s*/i', '', $cleaned);
        $cleaned = preg_replace('/\s*```$/i', '', $cleaned);
        $cleaned = trim($cleaned);
        
        // Parse JSON response
        $content = json_decode($cleaned, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            // Log the actual response for debugging
            error_log("OpenRouter JSON Parse Error: " . json_last_error_msg());
            error_log("Raw Response: " . substr($response, 0, 500));
            throw new Exception('AI returned invalid JSON format. Error: ' . json_last_error_msg());
        }
        
        // Validate required fields
        $required = ['overview_en', 'key_concepts', 'code_examples'];
        foreach ($required as $field) {
            if (!isset($content[$field]) || empty($content[$field])) {
                throw new Exception("AI response missing required field: $field");
            }
        }
        
        return $content;
    }

    /**
     * Generate code with explanation - For TCM Agent
     */
    public function generateCode(string $request, string $language = 'javascript'): array
    {
        $prompt = <<<PROMPT
Generate COMPLETE, WORKING $language code for: $request

Provide response in this format:

**CODE:**
```$language
[your complete, working code here - minimum 15 lines]
```

**EXPLANATION:**
[Step by step explanation in simple language, use Hinglish for Indian students]

**USAGE:**
```$language
[Example of how to use the code]
```

**OUTPUT:**
[What the code will output/do]

Remember: Provide REAL, RUNNABLE code - not placeholders or comments!
PROMPT;

        $response = $this->chat($prompt, '', ['temperature' => 0.8, 'maxTokens' => 2000]);
        
        return [
            'code' => $this->extractSection($response, 'CODE'),
            'explanation' => $this->extractSection($response, 'EXPLANATION'),
            'usage' => $this->extractSection($response, 'USAGE'),
            'output' => $this->extractSection($response, 'OUTPUT'),
            'raw_response' => $response
        ];
    }

    /**
     * Make API call to OpenRouter
     */
    private function callAPI(array $data): array
    {
        $ch = curl_init($this->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . $this->siteUrl,
                'X-Title: TCM Learning Platform'
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("OpenRouter API Error (HTTP $httpCode): $response");
        }

        $decoded = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON response: " . json_last_error_msg());
        }

        return $decoded;
    }

    /**
     * Extract text from API response
     */
    private function extractText(array $response): string
    {
        if (!isset($response['choices'][0]['message']['content'])) {
            throw new Exception('Invalid response format from OpenRouter API');
        }

        return trim($response['choices'][0]['message']['content']);
    }

    /**
     * Extract section from formatted response
     */
    private function extractSection(string $text, string $section): string
    {
        // Try to extract content between **SECTION:** and next **
        $pattern = '/\*\*' . preg_quote($section, '/') . ':\*\*\s*(.*?)(?=\*\*|\z)/s';
        if (preg_match($pattern, $text, $matches)) {
            return trim($matches[1]);
        }
        
        // Fallback: look for code blocks
        if ($section === 'CODE' && preg_match('/```[\w]*\s*(.*?)```/s', $text, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }

    /**
     * Test API connection
     */
    public static function test(?string $apiKey = null): array
    {
        try {
            $ai = new self($apiKey);
            $response = $ai->chat('Say "OK" if you can read this.');
            
            return [
                'success' => true,
                'message' => 'OpenRouter AI connected successfully!',
                'response' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Connection failed',
                'error' => $e->getMessage()
            ];
        }
    }
}
