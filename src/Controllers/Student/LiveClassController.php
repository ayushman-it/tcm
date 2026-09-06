<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Models\Enrollment;

final class LiveClassController extends Controller
{
    /**
     * Show all scheduled live classes
     */
    public function scheduled(): void
    {
        $user = Auth::require('student');
        
        // Get user's enrollments for filtering
        $enrollments = Enrollment::forUser((int) $user['id']);
        
        $this->view('student/classes/scheduled', [
            'title' => 'Live Classes',
            'user' => $user,
            'enrollments' => $enrollments,
        ], 'student');
    }
}
