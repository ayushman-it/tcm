<?php
declare(strict_types=1);
namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Request;
use TCM\Core\Response;
use TCM\Models\DailyTask;

final class TaskController extends Controller
{
    /**
     * Show current week's tasks
     */
    public function index(): void
    {
        $user = Auth::require('student');
        DailyTask::ensureTable();

        $tasks = DailyTask::getCurrentWeekTasks((int)$user['id']);

        // Calculate stats
        $total = count($tasks);
        $completed = count(array_filter($tasks, fn($t) => $t['status'] === 'completed'));
        $pending = count(array_filter($tasks, fn($t) => $t['status'] === 'pending'));
        $inProgress = count(array_filter($tasks, fn($t) => $t['status'] === 'in_progress'));

        $this->view('student/tasks/index', [
            'title' => 'My Daily Tasks',
            'user' => $user,
            'tasks' => $tasks,
            'stats' => compact('total', 'completed', 'pending', 'inProgress'),
        ], 'student');
    }

    /**
     * Show task detail with tutorial (W3Schools/GeeksforGeeks style)
     */
    public function show(array $params): void
    {
        $user = Auth::require('student');
        $taskId = (int)$params['id'];
        
        DailyTask::ensureTable();
        $task = DailyTask::find($taskId);
        
        if (!$task || (int)$task['user_id'] !== (int)$user['id']) {
            flash('error', 'Task not found.');
            redirect('/student/tasks');
        }

        // Generate tutorial content using AI
        $tutorial = $this->generateTutorial($task);

        $this->view('student/tasks/show', [
            'title' => $task['title'],
            'user' => $user,
            'task' => $task,
            'tutorial' => $tutorial,
        ], 'student');
    }

    /**
     * Update task status (AJAX)
     */
    public function updateStatus(array $params): void
    {
        $user = Auth::require('student');
        $taskId = (int)$params['id'];
        $status = Request::string('status');

        if (!in_array($status, ['pending', 'in_progress', 'completed', 'skipped'])) {
            Response::error('Invalid status', 400);
            return;
        }

        $updated = DailyTask::updateStatus($taskId, (int)$user['id'], $status);

        if ($updated) {
            Response::success(['message' => 'Task status updated']);
        } else {
            Response::error('Task not found or already updated', 404);
        }
    }

    /**
     * Generate new tasks manually (if needed)
     */
    public function generate(): void
    {
        $user = Auth::require('student');
        
        try {
            $tasks = DailyTask::generateForStudent((int)$user['id']);
            flash('success', count($tasks) . ' new tasks generated for you!');
        } catch (\Exception $e) {
            flash('error', 'Failed to generate tasks: ' . $e->getMessage());
        }

        redirect('/student/tasks');
    }

    /**
     * Generate W3Schools/GeeksforGeeks style tutorial for task
     */
    private function generateTutorial(array $task): array
    {
        // Try AI-generated tutorial first
        $apiKey = config('openrouter.api_key', '');
        
        if (!empty($apiKey)) {
            try {
                return $this->generateAITutorial($task);
            } catch (\Exception $e) {
                // Fallback to template
                error_log('[Tutorial Generation] AI failed: ' . $e->getMessage());
            }
        }

        // Fallback: Generate structured tutorial from task description
        return $this->generateTemplateTutorial($task);
    }

    /**
     * Generate AI-powered tutorial
     */
    private function generateAITutorial(array $task): array
    {
        $apiKey = config('openrouter.api_key');
        $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
        
        $prompt = <<<PROMPT
Create a detailed, step-by-step coding tutorial for this task:

**Task**: {$task['title']}
**Description**: {$task['description']}
**Difficulty**: {$task['difficulty']}
**Course**: {$task['course_title']}

Generate a W3Schools/GeeksforGeeks style tutorial with:

1. **Introduction** (2-3 sentences explaining what we'll learn)
2. **Prerequisites** (what student should know)
3. **Step-by-Step Guide** (5-8 detailed steps with code examples)
4. **Code Example** (complete working code with comments)
5. **Expected Output** (what the code produces)
6. **Try It Yourself** (modifications they can try)
7. **Common Mistakes** (what to avoid)
8. **Summary** (key takeaways)

Format as JSON:
{
  "introduction": "string",
  "prerequisites": ["string"],
  "steps": [{"title": "string", "explanation": "string", "code": "string"}],
  "example": {"code": "string", "language": "html|css|javascript|python"},
  "output": "string",
  "exercises": ["string"],
  "mistakes": ["string"],
  "summary": "string"
}

Be specific, practical, and beginner-friendly. Include actual runnable code.
PROMPT;

        $data = [
            'model' => 'openai/gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert coding instructor. Create detailed, practical tutorials with real code examples.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => 2000,
        ];

        $ch = curl_init($apiUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
                'HTTP-Referer: ' . config('app.url'),
                'X-Title: TCM Learning Platform'
            ],
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            throw new \RuntimeException("OpenRouter API error: HTTP {$httpCode}");
        }

        $decoded = json_decode($response, true);
        $content = $decoded['choices'][0]['message']['content'] ?? '';
        
        // Extract JSON from response
        preg_match('/\{.*\}/s', $content, $matches);
        $tutorial = json_decode($matches[0] ?? '{}', true);
        
        if (!$tutorial) {
            throw new \RuntimeException('Failed to parse AI tutorial');
        }

        return $tutorial;
    }

    /**
     * Generate template-based tutorial (fallback)
     */
    private function generateTemplateTutorial(array $task): array
    {
        return [
            'introduction' => "In this tutorial, you'll learn: " . $task['description'],
            'prerequisites' => [
                'Basic understanding of ' . ($task['course_title'] ?? 'programming'),
                'Text editor or IDE installed',
                'Willingness to practice and experiment'
            ],
            'steps' => [
                [
                    'title' => 'Step 1: Understand the Concept',
                    'explanation' => 'Before writing code, understand what you need to build. Read the task description carefully and identify the key requirements.',
                    'code' => ''
                ],
                [
                    'title' => 'Step 2: Plan Your Approach',
                    'explanation' => 'Break down the problem into smaller parts. Think about what functions or components you need.',
                    'code' => ''
                ],
                [
                    'title' => 'Step 3: Write the Code',
                    'explanation' => 'Start coding step by step. Test each part as you go.',
                    'code' => '// Your code here\n// Start simple and build up'
                ],
                [
                    'title' => 'Step 4: Test and Debug',
                    'explanation' => 'Run your code and fix any errors. Make sure it works as expected.',
                    'code' => ''
                ],
                [
                    'title' => 'Step 5: Improve and Refactor',
                    'explanation' => 'Once it works, look for ways to make your code cleaner and more efficient.',
                    'code' => ''
                ]
            ],
            'example' => [
                'code' => '// Example code will be shown here\n// Follow the steps above to create your own solution',
                'language' => 'javascript'
            ],
            'output' => 'Your program should produce the expected result as described in the task.',
            'exercises' => [
                'Try different inputs to test your code',
                'Add error handling for edge cases',
                'Optimize your solution for better performance'
            ],
            'mistakes' => [
                'Not reading the task description carefully',
                'Skipping the planning phase',
                'Not testing code before submitting'
            ],
            'summary' => "You've learned how to approach and solve: " . $task['title'] . ". Practice this skill to become more proficient!"
        ];
    }
}
