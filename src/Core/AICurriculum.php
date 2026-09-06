<?php

declare(strict_types=1);

namespace TCM\Core;

/**
 * AI Curriculum Generator
 * Uses OpenRouter API to auto-generate course/event curriculum.
 * Called silently on course/event creation — never blocks the save.
 */
final class AICurriculum
{
    private const API_URL = 'https://openrouter.ai/api/v1/chat/completions';

    /**
     * Generate curriculum modules + lessons for a course or event.
     *
     * @return list<array{title:string,summary:string,position:int,lessons:list<array>}>
     */
    public static function generate(
        string $title,
        string $duration    = '',
        string $level       = 'beginner',
        string $description = '',
        string $type        = 'course'   // 'course' | 'event'
    ): array {
        $apiKey = (string) env('OPENROUTER_API_KEY', '');
        if (empty($apiKey)) return [];

        $prompt  = $type === 'event'
            ? self::eventPrompt($title, $duration, $level, $description)
            : self::coursePrompt($title, $duration, $level, $description);

        $raw = self::callAI($apiKey, $prompt);
        if ($raw === null) return [];

        return self::parse($raw);
    }

    // ── Prompts ──────────────────────────────────────────────────

    private static function coursePrompt(
        string $title,
        string $duration,
        string $level,
        string $description
    ): string {
        $d = $duration    ? "Duration: $duration." : '';
        $s = $description ? "Description: $description." : '';

        return <<<P
You are a curriculum designer for The Code Munk, a tech education platform in India targeting students.
Generate a detailed course curriculum for:
- Title: "$title"
- Level: $level
- $d $s

Return ONLY a valid JSON array of modules:
[
  {
    "title": "Module name",
    "summary": "One-line description",
    "lessons": [
      {"title": "Lesson name", "type": "live|video|project|reading|quiz", "duration_minutes": 60}
    ]
  }
]

Rules:
- 5-8 modules
- 3-5 lessons per module
- Mix of live, project, and quiz lessons
- At least 2 project lessons in the whole course
- Practical, India-relevant content
- Return ONLY the JSON array, no markdown
P;
    }

    private static function eventPrompt(
        string $title,
        string $duration,
        string $level,
        string $description
    ): string {
        $d = $duration    ?: '3 hours';
        $s = $description ? "Description: $description." : '';

        return <<<P
You are an event planner for The Code Munk, a tech education platform in India.
Generate a session agenda for this event:
- Title: "$title"
- Duration: $d
- Level: $level
- $s

Return ONLY a valid JSON array of sessions:
[
  {
    "title": "Session name",
    "summary": "What participants will do",
    "lessons": [
      {"title": "Activity name", "type": "live|project|quiz", "duration_minutes": 30}
    ]
  }
]

Rules:
- 2-4 sessions
- 1-3 activities per session
- Include hands-on activity and Q&A
- Return ONLY the JSON array, no markdown
P;
    }

    // ── API Call ─────────────────────────────────────────────────

    private static function callAI(string $apiKey, string $prompt): ?string
    {
        if (!function_exists('curl_init')) return null;

        $payload = json_encode([
            'model'       => 'openai/gpt-4o-mini',
            'messages'    => [
                ['role' => 'system', 'content' => 'You are a curriculum designer. Always respond with valid JSON array only. No markdown fences, no extra text.'],
                ['role' => 'user',   'content' => $prompt],
            ],
            'temperature' => 0.65,
            'max_tokens'  => 2000,
        ]);

        $ch = curl_init(self::API_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: https://thecodemunk.in',
                'X-Title: The Code Munk',
            ],
            CURLOPT_POSTFIELDS => $payload,
        ]);

        $resp = curl_exec($ch);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($err || !$resp) {
            error_log('[AICurriculum] cURL error: ' . $err);
            return null;
        }

        $data = json_decode((string)$resp, true);
        $content = $data['choices'][0]['message']['content'] ?? null;

        if (!$content) {
            error_log('[AICurriculum] Empty response: ' . $resp);
        }

        return $content;
    }

    // ── Parse ────────────────────────────────────────────────────

    /**
     * @return list<array{title:string,summary:string,position:int,lessons:list<array>}>
     */
    private static function parse(?string $raw): array
    {
        if (!$raw) return [];

        // Strip markdown code fences if AI added them
        $raw = preg_replace('/```(?:json)?\s*([\s\S]*?)```/', '$1', trim($raw));

        $decoded = json_decode(trim($raw), true);
        if (!$decoded) return [];

        // Unwrap if AI returned object with key
        if (is_array($decoded) && !isset($decoded[0])) {
            $decoded = $decoded['curriculum']
                ?? $decoded['modules']
                ?? $decoded['sessions']
                ?? $decoded['agenda']
                ?? array_values($decoded)[0]
                ?? [];
        }

        if (!is_array($decoded)) return [];

        $result = [];
        foreach ($decoded as $i => $m) {
            if (!is_array($m)) continue;

            $lessons = [];
            foreach ((array)($m['lessons'] ?? []) as $j => $l) {
                $lessons[] = [
                    'title'            => (string)($l['title'] ?? 'Lesson ' . ($j+1)),
                    'type'             => self::validType($l['type'] ?? 'live'),
                    'duration_minutes' => max(5, (int)($l['duration_minutes'] ?? 60)),
                    'is_preview'       => 0,
                    'position'         => $j,
                ];
            }

            $result[] = [
                'title'    => (string)($m['title'] ?? 'Module ' . ($i+1)),
                'summary'  => (string)($m['summary'] ?? ''),
                'position' => $i,
                'lessons'  => $lessons,
            ];
        }

        return $result;
    }

    private static function validType(string $t): string
    {
        return in_array($t, ['live','video','project','reading','quiz'], true) ? $t : 'live';
    }
}
