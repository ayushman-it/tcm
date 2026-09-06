<?php

declare(strict_types=1);

namespace TCM\Controllers\Admin;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Core\Request;
use TCM\Models\Category;
use TCM\Models\Course;
use TCM\Models\Notification;

final class CourseController extends Controller
{
    public function index(): void
    {
        Auth::require('admin');
        $courses = Course::listWithCategory(['search' => Request::string('q') ?: null]);
        $this->view('admin/courses/index', [
            'title'   => 'Courses',
            'courses' => $courses,
        ], 'admin');
    }

    public function create(): void
    {
        Auth::require('admin');
        $this->view('admin/courses/form', [
            'title'      => 'New Course',
            'course'     => null,
            'categories' => Category::all('sort_order'),
        ], 'admin');
    }

    public function store(): void
    {
        Auth::require('admin');
        $data = $this->validate([
            'title'  => 'required|min:3|max:180',
            'price'  => 'required|numeric',
            'status' => 'in:draft,published,archived',
        ], '/admin/courses/create');

        $slug     = $this->uniqueSlug(slugify($data['title']));
        $courseId = Course::create($this->payload($slug));

        // 🤖 Auto-generate curriculum via AI (silent — never blocks save)
        try {
            $curriculum = \TCM\Core\AICurriculum::generate(
                $data['title'],
                Request::string('duration', ''),
                Request::string('level', 'beginner'),
                Request::string('description', ''),
                'course'
            );
            foreach ($curriculum as $i => $module) {
                $moduleId = Database::insert('course_modules', [
                    'course_id' => $courseId,
                    'title'     => $module['title'],
                    'summary'   => $module['summary'] ?? '',
                    'position'  => $i,
                ]);
                foreach (($module['lessons'] ?? []) as $j => $lesson) {
                    Database::insert('course_lessons', [
                        'module_id'        => $moduleId,
                        'title'            => $lesson['title'],
                        'type'             => $lesson['type'] ?? 'live',
                        'duration_minutes' => (int)($lesson['duration_minutes'] ?? 60),
                        'position'         => $j,
                        'is_preview'       => 0,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            error_log('[AI Curriculum] Failed for course ' . $courseId . ': ' . $e->getMessage());
            // Curriculum generation failed — course is still saved, admin can add manually
        }

        // 🔔 In-app + push: notify all students when a new course is published
        if (Request::string('status') === 'published') {
            Notification::ensureTable();
            Notification::toStudents(
                '🎓 New Course — ' . $data['title'],
                'A new course has just been added to The Code Munk. Check it out!',
                '🎓',
                base_url('/student/courses')
            );
            FirebaseNotification::notifyAllStudents(
                '🎓 New Course — ' . $data['title'],
                'A new course has just been added to The Code Munk. Check it out!',
                ['tag' => 'course-new'],
                base_url('/student/courses')
            );
        }

        $this->respond(['id' => $courseId], 'Course created.', '/admin/courses/' . $courseId . '/edit');
    }

    /**
     * POST /admin/courses/{id}/ai-curriculum
     * Regenerate curriculum for an existing course using AI.
     * Clears existing modules+lessons and replaces with AI-generated ones.
     */
    public function regenerateCurriculum(array $params): void
    {
        Auth::require('admin');
        $id     = (int) $params['id'];
        $course = Course::find($id);

        if ($course === null) {
            flash('error', 'Course not found.');
            redirect('/admin/courses');
        }

        try {
            $curriculum = \TCM\Core\AICurriculum::generate(
                $course['title'],
                $course['duration'] ?? '',
                $course['level']    ?? 'beginner',
                $course['description'] ?? '',
                'course'
            );

            if (empty($curriculum)) {
                flash('error', 'AI could not generate curriculum. Check your OpenRouter API key.');
                redirect('/admin/courses/' . $id . '/edit');
            }

            // Delete existing modules (cascades to lessons)
            $existing = Database::all('SELECT id FROM course_modules WHERE course_id = ?', [$id]);
            foreach ($existing as $m) {
                Database::delete('course_modules', ['id' => $m['id']]);
            }

            // Insert AI-generated curriculum
            foreach ($curriculum as $i => $module) {
                $moduleId = Database::insert('course_modules', [
                    'course_id' => $id,
                    'title'     => $module['title'],
                    'summary'   => $module['summary'] ?? '',
                    'position'  => $i,
                ]);
                foreach (($module['lessons'] ?? []) as $j => $lesson) {
                    Database::insert('course_lessons', [
                        'module_id'        => $moduleId,
                        'title'            => $lesson['title'],
                        'type'             => $lesson['type'] ?? 'live',
                        'duration_minutes' => (int)($lesson['duration_minutes'] ?? 60),
                        'position'         => $j,
                        'is_preview'       => 0,
                    ]);
                }
            }

            flash('success', 'Curriculum regenerated with AI — ' . count($curriculum) . ' modules created.');
        } catch (\Throwable $e) {
            flash('error', 'AI generation failed: ' . $e->getMessage());
        }

        redirect('/admin/courses/' . $id . '/edit');
    }

    public function edit(array $params): void
    {
        Auth::require('admin');
        $course = Course::find((int) $params['id']);
        if ($course === null) {
            flash('error', 'Course not found.');
            redirect('/admin/courses');
        }
        $this->view('admin/courses/form', [
            'title'      => 'Edit Course',
            'course'     => $course,
            'categories' => Category::all('sort_order'),
            'modules'    => Course::curriculum((int) $course['id']),
        ], 'admin');
    }

    public function update(array $params): void
    {
        Auth::require('admin');
        $id = (int) $params['id'];
        $course = Course::find($id);
        if ($course === null) {
            flash('error', 'Course not found.');
            redirect('/admin/courses');
        }

        $this->validate([
            'title'  => 'required|min:3|max:180',
            'price'  => 'required|numeric',
            'status' => 'in:draft,published,archived',
        ], '/admin/courses/' . $id . '/edit');

        $slug = $course['slug'];
        if (Request::string('title') !== $course['title']) {
            $slug = $this->uniqueSlug(slugify(Request::string('title')), $id);
        }

        Course::update($id, $this->payload($slug));
        $this->respond(['id' => $id], 'Course updated.', '/admin/courses/' . $id . '/edit');
    }

    public function destroy(array $params): void
    {
        Auth::require('admin');
        Course::delete((int) $params['id']);
        $this->respond(null, 'Course deleted.', '/admin/courses');
    }

    // --- Curriculum: modules & lessons -----------------------------------

    public function addModule(array $params): void
    {
        Auth::require('admin');
        $courseId = (int) $params['id'];
        Database::insert('course_modules', [
            'course_id' => $courseId,
            'title'     => Request::string('title', 'Untitled Module'),
            'summary'   => Request::string('summary'),
            'position'  => Request::int('position'),
        ]);
        $this->respond(null, 'Module added.', '/admin/courses/' . $courseId . '/edit');
    }

    public function deleteModule(array $params): void
    {
        Auth::require('admin');
        $module = Database::first('SELECT course_id FROM course_modules WHERE id = ?', [(int) $params['moduleId']]);
        Database::delete('course_modules', ['id' => (int) $params['moduleId']]);
        $this->respond(null, 'Module removed.', '/admin/courses/' . ($module['course_id'] ?? '') . '/edit');
    }

    public function addLesson(array $params): void
    {
        Auth::require('admin');
        $moduleId = (int) $params['moduleId'];
        $module = Database::first('SELECT course_id FROM course_modules WHERE id = ?', [$moduleId]);
        Database::insert('course_lessons', [
            'module_id'        => $moduleId,
            'title'            => Request::string('title', 'Untitled Lesson'),
            'type'             => Request::string('type', 'live'),
            'duration_minutes' => Request::int('duration_minutes') ?: null,
            'position'         => Request::int('position'),
            'is_preview'       => Request::int('is_preview'),
        ]);
        $this->respond(null, 'Lesson added.', '/admin/courses/' . ($module['course_id'] ?? '') . '/edit');
    }

    public function deleteLesson(array $params): void
    {
        Auth::require('admin');
        $lessonId = (int) $params['lessonId'];
        $row = Database::first(
            'SELECT m.course_id FROM course_lessons l JOIN course_modules m ON m.id = l.module_id WHERE l.id = ?',
            [$lessonId]
        );
        Database::delete('course_lessons', ['id' => $lessonId]);
        $this->respond(null, 'Lesson removed.', '/admin/courses/' . ($row['course_id'] ?? '') . '/edit');
    }

    /**
     * Build the course column payload from the request.
     *
     * @return array<string,mixed>
     */
    private function payload(string $slug): array
    {
        return [
            'category_id'    => Request::int('category_id') ?: null,
            'title'          => Request::string('title'),
            'slug'           => $slug,
            'subtitle'       => Request::string('subtitle'),
            'description'    => Request::string('description'),
            'icon'           => Request::string('icon', 'bi-journal-code'),
            'level'          => Request::string('level', 'beginner'),
            'language'       => Request::string('language', 'Hindi + English'),
            'duration'       => Request::string('duration'),
            'price'          => (float) Request::string('price', '0'),
            'original_price' => Request::string('original_price') !== '' ? (float) Request::string('original_price') : null,
            'total_seats'    => Request::int('total_seats'),
            'seats_left'     => Request::int('seats_left'),
            'certificate'    => Request::int('certificate'),
            'schedule'       => Request::string('schedule'),
            'starts_at'      => Request::string('starts_at') ?: null,
            'is_featured'    => Request::int('is_featured'),
            'is_bestseller'  => Request::int('is_bestseller'),
            'status'         => Request::string('status', 'draft'),
        ];
    }

    private function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = $slug;
        $i = 1;
        while (true) {
            $sql = 'SELECT COUNT(*) FROM courses WHERE slug = ?';
            $params = [$slug];
            if ($ignoreId !== null) {
                $sql .= ' AND id <> ?';
                $params[] = $ignoreId;
            }
            if ((int) Database::scalar($sql, $params) === 0) {
                return $slug;
            }
            $slug = $base . '-' . (++$i);
        }
    }
}
