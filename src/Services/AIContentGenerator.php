<?php
namespace TCM\Services;

use PDO;
use Exception;

/**
 * AI-Powered Content Generator for Lessons
 * Automatically generates detailed content, examples, and exercises
 */
class AIContentGenerator
{
    private PDO $db;
    private string $apiKey;
    private string $provider; // 'openai' or 'gemini'
    private string $apiEndpoint;
    private string $model;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        
        // Use OpenRouter for everything
        $this->provider = 'openrouter';
        $this->apiKey = getenv('OPENROUTER_API_KEY') ?: '';
        $this->model = 'openai/gpt-4o'; // Good model for content generation
        $this->apiEndpoint = 'https://openrouter.ai/api/v1/chat/completions';
        
        if (empty($this->apiKey)) {
            $this->provider = 'none';
        }
    }

    /**
     * Generate complete lesson content for a lesson
     * 
     * @param int $lessonId The lesson ID
     * @param string $language 'hi' for Hindi, 'en' for English, 'hi+en' for both
     * @return array Generated content
     */
    public function generateLessonContent(int $lessonId, string $language = 'hi+en'): array
    {
        // Get lesson details
        $stmt = $this->db->prepare("
            SELECT l.*, m.title as module_title, c.title as course_title
            FROM course_lessons l
            JOIN course_modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE l.id = ?
        ");
        $stmt->execute([$lessonId]);
        $lesson = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$lesson) {
            throw new Exception("Lesson not found");
        }

        // Build the prompt
        $prompt = $this->buildPrompt($lesson, $language);

        // Call OpenAI API
        $response = $this->callAI($prompt);

        // Parse and structure the response
        $content = $this->parseAIResponse($response, $language);

        // Save to database
        $this->saveContent($lessonId, $content, $language);

        return $content;
    }

    /**
     * Build the AI prompt based on lesson details
     */
    private function buildPrompt(array $lesson, string $language): string
    {
        $langInstruction = match($language) {
            'hi' => 'Provide all content in Hindi (Devanagari script)',
            'en' => 'Provide all content in English',
            'hi+en' => 'Provide content in both Hindi and English. Use Hinglish style where appropriate for Indian students.',
            default => 'Provide content in English'
        };

        $prompt = <<<PROMPT
You are an expert programming instructor at an Indian ed-tech platform. Generate DETAILED, PRACTICAL lesson content for:

**Lesson Title:** {$lesson['title']}
**Module:** {$lesson['module_title']}
**Course:** {$lesson['course_title']}
**Type:** {$lesson['type']}
**Duration:** {$lesson['duration_minutes']} minutes

{$langInstruction}

⚠️ CRITICAL REQUIREMENTS:
1. Generate REAL, WORKING code - NOT placeholders like "// Code here" or "Follow course materials"
2. Provide SPECIFIC explanations about THIS EXACT TOPIC - not generic advice
3. Include ACTUAL code examples with REAL output
4. Create PRACTICAL exercises with COMPLETE solutions
5. NO generic statements like "Practice with examples" or "Review materials"
6. Be SPECIFIC to the lesson topic in title

Generate the following content in JSON format:

{
    "overview_hi": "Brief overview in Hindi (2-3 sentences)",
    "overview_en": "Brief overview in English (2-3 sentences)",
    "key_concepts": [
        {
            "title_hi": "Concept name in Hindi",
            "title_en": "Concept name in English",
            "explanation_hi": "Detailed explanation in Hindi",
            "explanation_en": "Detailed explanation in English",
            "importance": "Why this concept matters"
        }
    ],
    "explanation_hi": "Complete detailed explanation in Hindi with markdown formatting",
    "explanation_en": "Complete detailed explanation in English with markdown formatting",
    "code_examples": [
        {
            "title": "Example title",
            "description_hi": "What this example demonstrates (Hindi)",
            "description_en": "What this example demonstrates (English)",
            "code": "Complete working code example",
            "output": "Expected output",
            "explanation_hi": "Line-by-line explanation (Hindi)",
            "explanation_en": "Line-by-line explanation (English)"
        }
    ],
    "exercises": [
        {
            "title": "Exercise title",
            "difficulty": "beginner|intermediate|advanced",
            "description_hi": "Exercise description (Hindi)",
            "description_en": "Exercise description (English)",
            "starter_code": "Initial code template",
            "hints": ["Hint 1", "Hint 2"],
            "solution": "Complete solution code",
            "explanation": "How to solve this"
        }
    ],
    "resources": [
        {
            "type": "documentation|article|video|tool",
            "title": "Resource title",
            "url": "Resource URL (if available)",
            "description_hi": "Brief description (Hindi)",
            "description_en": "Brief description (English)"
        }
    ],
    "estimated_time": 30
}

**IMPORTANT REQUIREMENTS:**
1. Provide REAL, WORKING code examples - not just comments or placeholders
2. For "{$lesson['title']}", explain the ACTUAL concept, not generic advice
3. Code examples MUST run without errors and produce real output
4. Exercises MUST have complete, working starter code and solutions
5. Use modern best practices relevant to this specific topic
6. Include real-world use cases for Indian developers
7. Explanations should be beginner-friendly but technically accurate

**EXAMPLES OF WHAT TO AVOID:**
❌ "Follow course materials for examples"
❌ "// Code examples available"
❌ "Practice with examples"
❌ "Review materials"
❌ Generic placeholder text

**EXAMPLES OF WHAT TO PROVIDE:**
✅ Actual working code that students can copy and run
✅ Specific explanations about {$lesson['title']}
✅ Real output/results from code
✅ Step-by-step instructions
✅ Practical examples relevant to the topic

Generate comprehensive, high-quality content suitable for self-paced learning.
PROMPT;

        return $prompt;
    }

    /**
     * Call AI API (OpenRouter only)
     */
    private function callAI(string $prompt): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("OpenRouter API key not configured. Set OPENROUTER_API_KEY in .env");
        }

        // Use OpenRouter unified service
        require_once __DIR__ . '/OpenRouterAI.php';
        
        try {
            $ai = new OpenRouterAI($this->apiKey);
            
            // For lesson content, we need JSON response
            $systemPrompt = "You are a programming instructor. Respond with VALID JSON only. No markdown, no explanation outside JSON.";
            
            $response = $ai->chat($prompt, $systemPrompt, [
                'temperature' => 0.8,
                'maxTokens' => 4000,
                'model' => 'openai/gpt-4o'
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            throw new Exception("OpenRouter API Error: " . $e->getMessage());
        }
    }

    /**
     * Parse AI response into structured content
     */
    private function parseAIResponse(string $response, string $language): array
    {
        $content = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Failed to parse AI response: ' . json_last_error_msg());
        }

        // Validate required fields
        $required = ['overview_en', 'key_concepts', 'explanation_en', 'code_examples', 'exercises'];
        foreach ($required as $field) {
            if (!isset($content[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        return $content;
    }

    /**
     * Save generated content to database
     */
    private function saveContent(int $lessonId, array $content, string $language): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO lesson_content (
                lesson_id,
                overview_hi,
                overview_en,
                key_concepts,
                explanation_hi,
                explanation_en,
                code_examples,
                exercises,
                resources,
                estimated_time,
                language,
                ai_generated,
                ai_model,
                generated_at,
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, NOW(), 'draft')
            ON DUPLICATE KEY UPDATE
                overview_hi = VALUES(overview_hi),
                overview_en = VALUES(overview_en),
                key_concepts = VALUES(key_concepts),
                explanation_hi = VALUES(explanation_hi),
                explanation_en = VALUES(explanation_en),
                code_examples = VALUES(code_examples),
                exercises = VALUES(exercises),
                resources = VALUES(resources),
                estimated_time = VALUES(estimated_time),
                generated_at = NOW(),
                updated_at = NOW()
        ");

        $stmt->execute([
            $lessonId,
            $content['overview_hi'] ?? null,
            $content['overview_en'] ?? '',
            json_encode($content['key_concepts'] ?? []),
            $content['explanation_hi'] ?? null,
            $content['explanation_en'] ?? '',
            json_encode($content['code_examples'] ?? []),
            json_encode($content['exercises'] ?? []),
            json_encode($content['resources'] ?? []),
            $content['estimated_time'] ?? 30,
            $language,
            $this->provider . ':' . $this->model
        ]);
    }

    /**
     * Get lesson content from database
     */
    public function getContent(int $lessonId, string $preferredLanguage = 'hi'): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM lesson_content WHERE lesson_id = ?
        ");
        $stmt->execute([$lessonId]);
        $content = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$content) {
            return null;
        }

        // Decode JSON fields
        $content['key_concepts'] = json_decode($content['key_concepts'], true);
        $content['code_examples'] = json_decode($content['code_examples'], true);
        $content['exercises'] = json_decode($content['exercises'], true);
        $content['resources'] = json_decode($content['resources'], true);

        return $content;
    }

    /**
     * Generate content for all lessons in a module
     */
    public function generateForModule(int $moduleId, string $language = 'hi+en'): array
    {
        $stmt = $this->db->prepare("SELECT id FROM course_lessons WHERE module_id = ? ORDER BY position");
        $stmt->execute([$moduleId]);
        $lessons = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $results = [];
        foreach ($lessons as $lessonId) {
            try {
                $content = $this->generateLessonContent($lessonId, $language);
                $results[$lessonId] = ['success' => true, 'content' => $content];
            } catch (Exception $e) {
                $results[$lessonId] = ['success' => false, 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Regenerate content for a lesson (if content is outdated or needs improvement)
     */
    public function regenerateContent(int $lessonId, string $language = 'hi+en'): array
    {
        return $this->generateLessonContent($lessonId, $language);
    }
}
