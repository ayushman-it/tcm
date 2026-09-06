<?php
namespace TCM\Services;

use Exception;

/**
 * Gemini AI Service
 * Handles all interactions with Google Gemini API
 */
class GeminiAI
{
    private string $apiKey;
    private string $model = 'gemini-1.5-pro';
    private string $endpoint;

    public function __construct(?string $apiKey = null)
    {
        $this->apiKey = $apiKey ?? getenv('GEMINI_API_KEY') ?? '';
        
        if (empty($this->apiKey)) {
            throw new Exception('GEMINI_API_KEY not configured in .env file');
        }
        
        $this->endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent";
    }

    /**
     * Send a chat message and get AI response
     * 
     * @param string $userMessage User's question/message
     * @param string $systemContext System instructions for AI
     * @param array $options Additional options (temperature, maxTokens, etc.)
     * @return string AI's response
     */
    public function chat(string $userMessage, string $systemContext = '', array $options = []): string
    {
        // Build the prompt
        $fullPrompt = $systemContext ? "$systemContext\n\nUser: $userMessage" : $userMessage;
        
        // Prepare request data
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $fullPrompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $options['temperature'] ?? 0.7,
                'maxOutputTokens' => $options['maxTokens'] ?? 1000,
                'topK' => $options['topK'] ?? 40,
                'topP' => $options['topP'] ?? 0.95,
            ],
            'safetySettings' => [
                ['category' => 'HARM_CATEGORY_HARASSMENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_HATE_SPEECH', 'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH']
            ]
        ];

        // Make API call
        $response = $this->callAPI($data);
        
        // Extract and return text
        return $this->extractText($response);
    }

    /**
     * Generate code with explanation
     * 
     * @param string $request What code to generate
     * @param string $language Programming language
     * @return array ['code' => string, 'explanation' => string]
     */
    public function generateCode(string $request, string $language = 'javascript'): array
    {
        $prompt = <<<PROMPT
Generate working $language code for: $request

Provide:
1. Complete, runnable code
2. Clear explanation of what it does
3. Example usage if applicable

Format your response as:
CODE:
[your code here]

EXPLANATION:
[your explanation here]

USAGE:
[example usage if applicable]
PROMPT;

        $response = $this->chat($prompt, '', ['temperature' => 0.8, 'maxTokens' => 2000]);
        
        // Parse response
        $code = '';
        $explanation = '';
        
        if (preg_match('/CODE:\s*(.*?)\s*EXPLANATION:/s', $response, $matches)) {
            $code = trim($matches[1]);
        }
        
        if (preg_match('/EXPLANATION:\s*(.*?)(\s*USAGE:|$)/s', $response, $matches)) {
            $explanation = trim($matches[1]);
        }
        
        // Fallback if parsing fails
        if (empty($code)) {
            $code = $response;
            $explanation = "AI-generated code. Review and test before using.";
        }
        
        return [
            'code' => $code,
            'explanation' => $explanation,
            'raw_response' => $response
        ];
    }

    /**
     * Explain a concept
     * 
     * @param string $concept What to explain
     * @param string $level Difficulty level (beginner, intermediate, advanced)
     * @return string Explanation
     */
    public function explainConcept(string $concept, string $level = 'beginner'): string
    {
        $prompt = <<<PROMPT
Explain "$concept" to a $level level student in simple terms.

Use:
- Simple language
- Real-world examples
- Hinglish where natural for Indian students
- Emojis for visual appeal
- Step-by-step breakdown

Keep it concise but thorough (200-300 words).
PROMPT;

        return $this->chat($prompt, '', ['temperature' => 0.7, 'maxTokens' => 800]);
    }

    /**
     * Debug code and suggest fixes
     * 
     * @param string $code Code with errors
     * @param string $error Error message (optional)
     * @return array ['issues' => array, 'fixed_code' => string, 'explanation' => string]
     */
    public function debugCode(string $code, string $error = ''): array
    {
        $errorInfo = $error ? "\n\nError message: $error" : '';
        
        $prompt = <<<PROMPT
Debug this code:$errorInfo

```
$code
```

Provide:
1. List of issues found
2. Fixed code
3. Explanation of fixes

Format:
ISSUES:
- Issue 1
- Issue 2

FIXED CODE:
[corrected code]

EXPLANATION:
[what was wrong and how you fixed it]
PROMPT;

        $response = $this->chat($prompt, '', ['temperature' => 0.5, 'maxTokens' => 2000]);
        
        // Parse response
        $issues = [];
        $fixedCode = '';
        $explanation = '';
        
        if (preg_match('/ISSUES:\s*(.*?)\s*FIXED CODE:/s', $response, $matches)) {
            $issuesText = trim($matches[1]);
            $issues = array_filter(array_map('trim', explode("\n", $issuesText)));
        }
        
        if (preg_match('/FIXED CODE:\s*(.*?)\s*EXPLANATION:/s', $response, $matches)) {
            $fixedCode = trim($matches[1]);
        }
        
        if (preg_match('/EXPLANATION:\s*(.*?)$/s', $response, $matches)) {
            $explanation = trim($matches[1]);
        }
        
        return [
            'issues' => $issues,
            'fixed_code' => $fixedCode,
            'explanation' => $explanation,
            'raw_response' => $response
        ];
    }

    /**
     * Make API call to Gemini
     */
    private function callAPI(array $data): array
    {
        $url = $this->endpoint . '?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("cURL Error: $error");
        }

        if ($httpCode !== 200) {
            throw new Exception("Gemini API Error (HTTP $httpCode): $response");
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
        if (!isset($response['candidates'][0]['content']['parts'][0]['text'])) {
            // Check for safety block
            if (isset($response['candidates'][0]['finishReason'])) {
                $reason = $response['candidates'][0]['finishReason'];
                if ($reason === 'SAFETY') {
                    throw new Exception('Response blocked by safety filters. Please rephrase your question.');
                }
            }
            
            throw new Exception('Invalid response format from Gemini API');
        }

        return trim($response['candidates'][0]['content']['parts'][0]['text']);
    }

    /**
     * Test if API key is valid
     */
    public static function testConnection(?string $apiKey = null): array
    {
        try {
            $gemini = new self($apiKey);
            $response = $gemini->chat('Hello, respond with "OK" if you can read this.');
            
            return [
                'success' => true,
                'message' => 'Gemini API connected successfully!',
                'response' => $response
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Gemini API connection failed',
                'error' => $e->getMessage()
            ];
        }
    }
}
