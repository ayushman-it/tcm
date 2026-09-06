<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Models\Enrollment;
use TCM\Models\EventRegistration;
use TCM\Models\LiveSession;
use TCM\Models\Portfolio;
use TCM\Models\DailyTask;
use TCM\Models\Wallet;

final class DashboardController extends Controller
{
    public function index(): void
    {
        $user = Auth::require('student');
        if ((int) $user['onboarded'] === 0) {
            redirect('/student/onboarding');
        }

        $enrollments   = Enrollment::forUser((int) $user['id']);
        $registrations = EventRegistration::forUser((int) $user['id']);

        // Check if payment_submissions table exists (graceful fallback until migration runs)
        $paymentTableExists = (int) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'payment_submissions'"
        ) > 0;

        // Pending payments count
        $pendingPayments = $paymentTableExists
            ? (int) Database::scalar(
                "SELECT COUNT(*) FROM payment_submissions WHERE user_id = ? AND status = 'pending'",
                [(int) $user['id']]
              )
            : 0;

        // Recent payment submissions (last 5)
        $recentPayments = $paymentTableExists
            ? Database::all(
                'SELECT * FROM payment_submissions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5',
                [(int) $user['id']]
              )
            : [];

        // Peers for community widget (4 random students)
        $peers = Database::all(
            "SELECT u.id, u.name, u.avatar, sp.experience_level
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.role = 'student' AND u.status = 'active'
               AND u.id != ? AND u.onboarded = 1
             ORDER BY RAND() LIMIT 6",
            [(int) $user['id']]
        );

        // Daily tasks (today's tasks)
        DailyTask::ensureTable();
        $todayTasks = DailyTask::getCurrentWeekTasks((int) $user['id']);
        $pendingTasks = array_filter($todayTasks, fn($t) => $t['status'] === 'pending');
        $completedTasks = array_filter($todayTasks, fn($t) => $t['status'] === 'completed');

        // Wallet balance
        Wallet::ensureTables();
        $walletBalance = Wallet::balance((int) $user['id']);

        $this->view('student/dashboard', [
            'title'             => 'My Dashboard',
            'user'              => $user,
            'enrollments'       => $enrollments,
            'registrations'     => $registrations,
            'liveSessions'      => LiveSession::upcomingForUser((int) $user['id']),
            'portfolioStrength' => Portfolio::strength((int) $user['id']),
            'certificates'      => Portfolio::certificates((int) $user['id']),
            'pendingPayments'   => $pendingPayments,
            'recentPayments'    => $recentPayments,
            'peers'             => $peers,
            'todayTasks'        => array_slice($pendingTasks, 0, 3),
            'totalTasks'        => count($todayTasks),
            'completedTasksCount' => count($completedTasks),
            'walletBalance'     => $walletBalance,
        ], 'student');
    }
}
