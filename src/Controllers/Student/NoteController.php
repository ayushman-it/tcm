<?php
declare(strict_types=1);
namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Models\Course;
use TCM\Models\CourseNote;

final class NoteController extends Controller
{
    /**
     * Show course notes/book page
     */
    public function index(array $params): void
    {
        $user = Auth::require('student');
        $courseId = (int)$params['id'];
        
        $course = Course::find($courseId);
        if (!$course) {
            flash('error', 'Course not found');
            redirect('/student/courses');
        }

        // Check access
        $access = CourseNote::hasAccess((int)$user['id'], $courseId);
        if (!$access['has_access']) {
            $this->handleNoAccess($access['reason'], $access);
            return;
        }

        $notes = CourseNote::getByCourse($courseId);
        $progress = CourseNote::getProgress((int)$user['id'], $courseId);
        $toc = CourseNote::getTableOfContents($courseId);

        $this->view('student/notes/index', [
            'title' => $course['title'] . ' - Notes',
            'user' => $user,
            'course' => $course,
            'notes' => $notes,
            'toc' => $toc,
            'progress' => $progress,
            'access' => $access,
        ], 'student');
    }

    /**
     * Show specific note
     */
    public function show(array $params): void
    {
        $user = Auth::require('student');
        $courseId = (int)$params['id'];
        $slug = $params['slug'];

        $course = Course::find($courseId);
        if (!$course) {
            flash('error', 'Course not found');
            redirect('/student/courses');
        }

        // Check access
        $access = CourseNote::hasAccess((int)$user['id'], $courseId);
        if (!$access['has_access']) {
            $this->handleNoAccess($access['reason'], $access);
            return;
        }

        $nav = CourseNote::getWithNavigation($courseId, $slug);
        if (!$nav['current']) {
            flash('error', 'Note not found');
            redirect('/student/notes/' . $courseId);
        }

        // Mark as read
        CourseNote::markAsRead((int)$user['id'], (int)$nav['current']['id']);

        $toc = CourseNote::getTableOfContents($courseId);
        $progress = CourseNote::getProgress((int)$user['id'], $courseId);

        $this->view('student/notes/show', [
            'title' => $nav['current']['title'],
            'user' => $user,
            'course' => $course,
            'note' => $nav['current'],
            'prev' => $nav['prev'],
            'next' => $nav['next'],
            'toc' => $toc,
            'progress' => $progress,
        ], 'student');
    }

    private function handleNoAccess(string $reason, array $access): void
    {
        if ($reason === 'not_enrolled') {
            flash('error', 'Please enroll in this course to access notes');
            redirect('/student/courses');
        } elseif ($reason === 'expired') {
            flash('error', 'Your access to this course has expired on ' . date('d M Y', strtotime($access['expired_on'])));
            redirect('/student/courses');
        } else {
            flash('error', 'Access denied');
            redirect('/student/courses');
        }
    }
}
