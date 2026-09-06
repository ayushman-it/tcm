<?php

declare(strict_types=1);

namespace TCM\Controllers\Api;

use TCM\Core\AICurriculum;
use TCM\Core\Auth;
use TCM\Core\Request;
use TCM\Core\Response;

/**
 * AI-powered curriculum generator — admin only.
 * POST /api/ai/curriculum
 */
final class AIController
{
    public function curriculum(): void
    {
        $user = Auth::user();
        if ($user === null || $user['role'] !== 'admin') {
            Response::error('Admin access required.', 403);
        }

        $body        = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
        $title       = trim((string)($body['title']       ?? ''));
        $duration    = trim((string)($body['duration']    ?? ''));
        $level       = trim((string)($body['level']       ?? 'beginner'));
        $type        = trim((string)($body['type']        ?? 'course'));
        $description = trim((string)($body['description'] ?? ''));

        if (empty($title)) {
            Response::error('Course/event title is required.', 422);
        }

        $curriculum = AICurriculum::generate($title, $duration, $level, $description, $type);

        if (empty($curriculum)) {
            Response::error('AI curriculum generation failed. Check OPENROUTER_API_KEY in .env.', 503);
        }

        Response::success(['curriculum' => $curriculum], 'Curriculum generated successfully.');
    }
}
