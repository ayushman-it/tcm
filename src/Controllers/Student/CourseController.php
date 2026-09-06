<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;
use TCM\Core\WhatsApp;
use TCM\Models\Course;
use TCM\Models\Enrollment;
use TCM\Models\Lead;

final class CourseController extends Controller
{
    public function browse(): void
    {
        $user = Auth::require('student');
        $courses = Course::listWithCategory([
            'status'   => 'published',
            'audience' => Request::string('audience') ?: null,
            'search'   => Request::string('q') ?: null,
        ]);

        // Mark which courses the student already owns.
        $owned = array_column(Enrollment::forUser((int) $user['id']), 'course_id');
        
        // Get enrollments with progress for enrolled tab
        $enrollments = Enrollment::forUser((int) $user['id']);

        $this->view('student/courses/browse', [
            'title'   => 'Browse Courses',
            'courses' => $courses,
            'owned'   => $owned,
            'enrollments' => $enrollments,
        ], 'student');
    }

    public function show(array $params): void
    {
        $user = Auth::require('student');
        $course = Course::findBySlug($params['slug']);
        if ($course === null) {
            flash('error', 'Course not found.');
            redirect('/student/courses');
        }
        $this->view('student/courses/show', [
            'title'      => $course['title'],
            'course'     => $course,
            'curriculum' => Course::curriculum((int) $course['id']),
            'enrolled'   => Enrollment::exists((int) $user['id'], (int) $course['id']),
        ], 'student');
    }

    /**
     * Enroll in a course. Free courses enrol instantly; paid courses capture a
     * lead and hand the student off to WhatsApp to finalise (no online payment).
     */
    public function purchase(array $params): void
    {
        $user = Auth::require('student');
        $course = Course::find((int) $params['id']);
        if ($course === null) {
            flash('error', 'Course not found.');
            redirect('/student/courses');
        }

        if (Enrollment::exists((int) $user['id'], (int) $course['id'])) {
            flash('error', 'You are already enrolled in this course.');
            redirect('/student/learn/' . $course['id']);
        }

        // Free course -> enrol immediately.
        if ((float) $course['price'] <= 0) {
            Enrollment::enroll((int) $user['id'], (int) $course['id']);
            flash('success', 'Enrolled in ' . $course['title'] . '. Happy learning!');
            redirect('/student/learn/' . $course['id']);
        }

        // Paid course -> capture a lead and route to WhatsApp.
        Lead::capture([
            'user_id'        => (int) $user['id'],
            'name'           => $user['name'],
            'email'          => $user['email'],
            'phone'          => $user['phone'] ?? null,
            'interest_type'  => 'course',
            'interest_id'    => (int) $course['id'],
            'interest_title' => $course['title'],
            'source'         => 'student-dashboard',
        ]);

        if (WhatsApp::isConfigured()) {
            redirect(WhatsApp::link(WhatsApp::enquiryMessage($course['title'], $user['name'], 'course')));
        }
        flash('success', 'Thanks! Our team will reach out to help you enrol in ' . $course['title'] . '.');
        redirect('/student/courses/' . $course['slug']);
    }

    /**
     * Course learning view with progress tracking.
     */
    public function learn(array $params): void
    {
        $user = Auth::require('student');
        $courseId = (int) $params['id'];
        if (!Enrollment::exists((int) $user['id'], $courseId)) {
            flash('error', 'Enroll first to access this course.');
            redirect('/student/courses');
        }
        $course = Course::find($courseId);

        $completed = array_column(
            Database::all(
                'SELECT lesson_id FROM lesson_progress WHERE user_id = ? AND completed = 1',
                [(int) $user['id']]
            ),
            'lesson_id'
        );

        $this->view('student/courses/learn', [
            'title'      => $course['title'] ?? 'Course',
            'course'     => $course,
            'curriculum' => Course::curriculum($courseId),
            'completed'  => array_map('intval', $completed),
        ], 'student');
    }

    /**
     * Get lessons for a course (API endpoint for dashboard)
     * GET /student/learn/{id}/lessons
     */
    public function getLessons(array $params): void
    {
        try {
            $user = Auth::require('student');
            $courseId = (int) $params['id'];
            
            // Check enrollment
            if (!Enrollment::exists((int) $user['id'], $courseId)) {
                $this->jsonError('Not enrolled in this course', 403);
                return;
            }
            
            // Get all lessons for this course
            $lessons = Database::all("
                SELECT l.id, l.title, l.type, l.duration_minutes, l.position,
                       m.title as module_title
                FROM course_lessons l
                JOIN course_modules m ON l.module_id = m.id
                WHERE m.course_id = ?
                ORDER BY m.position, l.position
            ", [$courseId]);
            
            $this->jsonSuccess([
                'lessons' => $lessons,
                'count' => count($lessons)
            ]);
            
        } catch (\Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    /**
     * Toggle lesson completion and recompute course progress.
     */
    public function toggleLesson(array $params): void
    {
        $user = Auth::require('student');
        $lessonId = (int) $params['lessonId'];

        $existing = Database::first(
            'SELECT * FROM lesson_progress WHERE user_id = ? AND lesson_id = ?',
            [(int) $user['id'], $lessonId]
        );

        if ($existing) {
            $new = (int) $existing['completed'] === 1 ? 0 : 1;
            Database::update('lesson_progress', [
                'completed'    => $new,
                'completed_at' => $new ? date('Y-m-d H:i:s') : null,
            ], ['id' => $existing['id']]);
        } else {
            Database::insert('lesson_progress', [
                'user_id'      => (int) $user['id'],
                'lesson_id'    => $lessonId,
                'completed'    => 1,
                'completed_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $courseId = (int) ($params['id'] ?? 0);
        $this->recomputeProgress((int) $user['id'], $courseId);

        $this->respond(null, 'Progress updated.', '/student/learn/' . $courseId);
    }

    /**
     * Generate AI-powered lesson content (AJAX)
     */
    public function getLessonContent(array $params): void
    {
        // Set JSON header first
        header('Content-Type: application/json');
        
        try {
            Auth::require('student');
            $lessonId = (int) $params['lessonId'];
            
            // Get mode from query string
            $mode = $_GET['mode'] ?? 'basic';
            if (!in_array($mode, ['basic', 'detailed'])) {
                $mode = 'basic';
            }

            // Get lesson details
            $lesson = Database::first(
                'SELECT l.*, m.title as module_title, c.title as course_title 
                 FROM course_lessons l
                 JOIN course_modules m ON m.id = l.module_id
                 JOIN courses c ON c.id = m.course_id
                 WHERE l.id = ?',
                [$lessonId]
            );

            if (!$lesson) {
                echo json_encode(['success' => false, 'message' => 'Lesson not found']);
                exit;
            }

            $content = $this->generateLessonContent($lesson, $mode);
            echo json_encode(['success' => true, 'data' => ['content' => $content]]);
            exit;
            
        } catch (\Exception $e) {
            error_log('[Lesson Content] Error: ' . $e->getMessage());
            error_log('[Lesson Content] Trace: ' . $e->getTraceAsString());
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Generate lesson content using AI
     */
    private function generateLessonContent(array $lesson, string $mode): array
    {
        $apiKey = config('openrouter.api_key', '');
        
        if (empty($apiKey)) {
            return $this->getFallbackContent($lesson, $mode);
        }

        $prompt = $mode === 'detailed' 
            ? $this->buildDetailedPrompt($lesson)
            : $this->buildBasicPrompt($lesson);

        $apiUrl = 'https://openrouter.ai/api/v1/chat/completions';
        
        $data = [
            'model' => 'openai/gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => 'You are an expert coding instructor creating W3Schools/GeeksForGeeks quality tutorials. Always provide complete, working code examples with detailed explanations. Be thorough and practical.'],
                ['role' => 'user', 'content' => $prompt]
            ],
            'temperature' => 0.7,
            'max_tokens' => $mode === 'detailed' ? 3000 : 1500,
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
        $parsed = json_decode($matches[0] ?? '{}', true);
        
        if (!$parsed) {
            throw new \RuntimeException('Failed to parse AI response');
        }

        return $parsed;
    }

    /**
     * Build basic content prompt
     */
    private function buildBasicPrompt(array $lesson): string
    {
        $lessonTitle = $lesson['title'];
        $courseTitle = $lesson['course_title'];
        
        return <<<PROMPT
You are creating a W3Schools-quality programming tutorial. Study this example structure and replicate it:

**Topic**: {$lessonTitle}
**Course**: {$courseTitle}

Create a comprehensive, practical tutorial following W3Schools' exact format:

═══════════════════════════════════════════════════════════════

**REQUIRED STRUCTURE**:

1. **DEFINITION & PURPOSE** (4-5 sentences):
   - What is this concept/tag/feature?
   - What problem does it solve?
   - When and where is it used?
   - Real-world use cases
   
2. **SYNTAX** (if applicable):
   - Exact syntax with proper formatting
   - Parameter/attribute descriptions
   - Default values
   
3. **KEY CONCEPTS** (5-7 specific points):
   - Technical details, not generic statements
   - How it works internally
   - Important properties/attributes
   - Browser compatibility notes
   - Common use patterns
   
4. **COMPLETE CODE EXAMPLE**:
   - Full, copy-paste ready code
   - Well-commented
   - Shows typical usage
   - Includes HTML structure if needed
   - Shows expected output

═══════════════════════════════════════════════════════════════

**QUALITY STANDARDS**:
✅ Technical depth - explain HOW and WHY
✅ Real, executable code - not pseudocode
✅ Specific examples - not generic "example.html"
✅ Professional formatting with proper indentation
✅ Helpful inline comments
❌ No vague statements like "Learn about X"
❌ No placeholders like "// your code here"
❌ No incomplete explanations

═══════════════════════════════════════════════════════════════

**EXAMPLE OUTPUT** (HTML <table> tag):

**Definition**: The <table> tag defines an HTML table. Tables are used to organize data into rows and columns, making information easy to scan and compare. Each table consists of table rows (<tr>), which contain table headers (<th>) or table data (<td>) cells. Tables should only be used for tabular data, not for page layout purposes.

**Syntax**:
```html
<table>
  <tr>
    <th>Header 1</th>
    <th>Header 2</th>
  </tr>
  <tr>
    <td>Data 1</td>
    <td>Data 2</td>
  </tr>
</table>
```

**Key Concepts**:
• Tables consist of rows (<tr>) and cells (<th> for headers, <td> for data)
• The <thead>, <tbody>, and <tfoot> elements group table content for better structure
• Use CSS border-collapse property to control spacing between cells
• The colspan attribute merges cells horizontally, rowspan merges vertically
• Always include <th> headers for accessibility and SEO
• Modern tables use CSS for styling - avoid deprecated attributes like border="1"
• Tables are responsive by default but may need CSS for mobile optimization

**Complete Example**:
```html
<!DOCTYPE html>
<html>
<head>
<style>
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  th {
    background-color: #4CAF50;
    color: white;
  }
</style>
</head>
<body>

<h2>Student Grade Table</h2>
<table>
  <thead>
    <tr>
      <th>Name</th>
      <th>Math</th>
      <th>Science</th>
      <th>Average</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>John Doe</td>
      <td>85</td>
      <td>90</td>
      <td>87.5</td>
    </tr>
    <tr>
      <td>Jane Smith</td>
      <td>92</td>
      <td>88</td>
      <td>90</td>
    </tr>
  </tbody>
</table>

</body>
</html>
```

**Output**: A styled table with green header row showing student names and their grades. Headers are bold and white, data rows have alternating colors for readability.

═══════════════════════════════════════════════════════════════

**FORMAT AS JSON**:
{
  "definition": "4-5 sentence detailed explanation of what it is, why it exists, when to use it, real-world applications",
  "syntax": "Exact syntax with proper formatting (empty string if not applicable)",
  "keyPoints": [
    "Specific technical detail 1 with explanations",
    "Specific technical detail 2 with explanations",
    "Specific technical detail 3 with explanations",
    "Specific technical detail 4 with explanations",
    "Specific technical detail 5 with explanations",
    "Specific technical detail 6 with explanations",
    "Specific technical detail 7 with explanations"
  ],
  "codeExample": {
    "title": "Descriptive title of what example demonstrates",
    "code": "Complete HTML/CSS/JS code with proper structure, indentation, and comments",
    "language": "html|css|javascript|python",
    "output": "Description of what happens when code runs"
  }
}

Now create W3Schools-quality content for: "{$lessonTitle}"
Be as detailed and professional as the example above.
PROMPT;
    }

    /**
     * Build detailed content prompt
     */
    private function buildDetailedPrompt(array $lesson): string
    {
        $lessonTitle = $lesson['title'];
        $courseTitle = $lesson['course_title'];
        
        return <<<PROMPT
Create an ADVANCED W3Schools-quality tutorial with maximum depth and detail.

**Topic**: {$lessonTitle}
**Course**: {$courseTitle}

This should be a COMPLETE reference guide like W3Schools' detailed pages.

═══════════════════════════════════════════════════════════════

**REQUIRED SECTIONS**:

1. **COMPREHENSIVE INTRODUCTION** (6-8 sentences)
   - What is it and why does it exist?
   - Historical context if relevant
   - Current best practices
   - Common use cases
   - When to use vs when not to use

2. **TECHNICAL DEEP DIVE** (3-4 main concepts)
   - Each concept explained in depth
   - How it works under the hood
   - Important properties/methods/attributes
   - Code example for each concept

3. **MULTIPLE COMPLETE EXAMPLES** (4-6 examples)
   - Basic usage
   - Intermediate usage with styling
   - Advanced usage with JavaScript
   - Real-world practical example
   - Edge cases and special scenarios
   - Each with full HTML/CSS/JS as needed

4. **BEST PRACTICES** (5-7 items)
   - Industry standards
   - Performance considerations
   - Accessibility requirements
   - SEO implications
   - Cross-browser compatibility
   - Why each practice matters

5. **COMMON MISTAKES & SOLUTIONS** (5-7 items)
   - What developers often do wrong
   - Why it's problematic
   - How to fix it properly
   - Better alternatives

6. **RELATED TOPICS & NEXT STEPS**
   - What to learn next
   - Related concepts
   - Advanced techniques
   - Recommended progression path

═══════════════════════════════════════════════════════════════

**FORMAT AS JSON**:
{
  "introduction": "6-8 sentence comprehensive introduction covering what, why, when, how, and context",
  "technicalDetails": [
    {
      "concept": "Main Concept Name",
      "explanation": "4-5 sentence detailed explanation with technical depth",
      "example": "Complete code example demonstrating this specific concept",
      "notes": "Additional important notes, browser support, gotchas"
    }
  ],
  "examples": [
    {
      "title": "Example 1: Basic Usage",
      "description": "What this example teaches",
      "code": "Complete, copy-paste ready code with structure and comments",
      "language": "html|css|javascript|python",
      "output": "Detailed description of what happens and why"
    },
    {
      "title": "Example 2: Styling and Layout",
      "description": "What this example teaches",
      "code": "Complete code with CSS styling",
      "language": "html",
      "output": "Visual description of the result"
    },
    {
      "title": "Example 3: Interactive with JavaScript",
      "description": "What this example teaches",
      "code": "Complete code with JS functionality",
      "language": "html",
      "output": "Interaction description"
    },
    {
      "title": "Example 4: Real-World Use Case",
      "description": "Practical application",
      "code": "Production-ready code example",
      "language": "html",
      "output": "Business context and result"
    }
  ],
  "bestPractices": [
    {
      "practice": "Specific best practice statement",
      "reason": "Why this is important - performance, accessibility, SEO, etc.",
      "example": "Code showing right way vs wrong way"
    }
  ],
  "commonMistakes": [
    {
      "mistake": "What developers commonly do wrong",
      "problem": "Why this causes issues",
      "solution": "How to do it correctly",
      "code": "Correct implementation example"
    }
  ],
  "relatedTopics": [
    "Related Topic 1 - why it's related and when to learn it",
    "Related Topic 2 - how it builds on this concept",
    "Related Topic 3 - advanced technique to explore next"
  ]
}

═══════════════════════════════════════════════════════════════

**QUALITY REQUIREMENTS**:
✅ Every example must be COMPLETE and RUNNABLE
✅ Include full HTML structure (<html>, <head>, <body>)
✅ Add CSS in <style> tags when styling is shown
✅ Include JavaScript in <script> tags when interactivity is shown
✅ Use realistic data (student names, product lists, etc.)
✅ Show actual output/results
✅ Explain every important line
✅ Professional formatting and indentation
✅ W3Schools level of detail

Create this comprehensive guide for: "{$lessonTitle}"
Make it as detailed as W3Schools' best tutorial pages.
PROMPT;
    }

    /**
     * Fallback content if AI fails
     */
    private function getFallbackContent(array $lesson, string $mode): array
    {
        if ($mode === 'detailed') {
            return [
                'summary' => "This lesson covers: {$lesson['title']}. Practice the concepts and refer to the course materials for detailed examples.",
                'keyPoints' => [
                    'Understand the core concepts',
                    'Practice with hands-on examples',
                    'Review course materials',
                    'Ask questions if needed',
                    'Complete exercises'
                ],
                'mainConcepts' => [
                    [
                        'concept' => $lesson['title'],
                        'explanation' => 'This is an important topic in ' . $lesson['course_title'] . '. Make sure to practice thoroughly.',
                        'example' => '// Practice code examples from course materials'
                    ]
                ],
                'codeExamples' => [
                    [
                        'title' => 'Basic Example',
                        'code' => '// Refer to course materials for specific code examples',
                        'language' => 'javascript',
                        'explanation' => 'Follow along with the course materials for detailed examples.'
                    ]
                ],
                'bestPractices' => [
                    'Practice regularly',
                    'Write clean, readable code',
                    'Test your code thoroughly'
                ],
                'commonMistakes' => [
                    'Not practicing enough',
                    'Skipping fundamentals'
                ],
                'nextSteps' => 'Continue with the next lesson in the module.'
            ];
        }

        // No AI-generated content, return basic lesson info only
        return [
            'summary' => $lesson['description'] ?? "This lesson covers: {$lesson['title']}",
            'keyPoints' => [],
            'codeExample' => null
        ];
    }

    private function recomputeProgress(int $userId, int $courseId): void
    {
        $total = (int) Database::scalar(
            'SELECT COUNT(*) FROM course_lessons l
             JOIN course_modules m ON m.id = l.module_id
             WHERE m.course_id = ?',
            [$courseId]
        );
        if ($total === 0) {
            return;
        }
        $done = (int) Database::scalar(
            'SELECT COUNT(*) FROM lesson_progress p
             JOIN course_lessons l ON l.id = p.lesson_id
             JOIN course_modules m ON m.id = l.module_id
             WHERE p.user_id = ? AND m.course_id = ? AND p.completed = 1',
            [$userId, $courseId]
        );
        $percent = (int) round($done / $total * 100);

        if ($percent >= 100) {
            Database::run(
                "UPDATE enrollments SET progress = 100, status = 'completed', completed_at = NOW()
                 WHERE user_id = ? AND course_id = ?",
                [$userId, $courseId]
            );
            $this->issueCertificate($userId, $courseId);
        } else {
            Database::run(
                'UPDATE enrollments SET progress = ? WHERE user_id = ? AND course_id = ?',
                [$percent, $userId, $courseId]
            );
        }
    }

    private function issueCertificate(int $userId, int $courseId): void
    {
        $exists = (int) Database::scalar(
            'SELECT COUNT(*) FROM certificates WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
        if ($exists > 0) {
            return;
        }
        $course = Course::find($courseId);
        Database::insert('certificates', [
            'user_id'            => $userId,
            'course_id'          => $courseId,
            'certificate_number' => 'TCM-CERT-' . strtoupper(substr(bin2hex(random_bytes(5)), 0, 8)),
            'title'              => 'Certificate of Completion - ' . ($course['title'] ?? 'Course'),
        ]);
    }
}
