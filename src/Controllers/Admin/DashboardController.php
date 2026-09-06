<?php

declare(strict_types=1);

namespace TCM\Controllers\Admin;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;

final class DashboardController extends Controller
{
    public function index(): void
    {
        Auth::require('admin');

        // Safe pending payments count (table may not exist yet)
        $paymentsTableExists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = 'payment_submissions'"
        );

        $stats = [
            'students'         => (int) Database::scalar("SELECT COUNT(*) FROM users WHERE role = 'student'"),
            'courses'          => (int) Database::scalar('SELECT COUNT(*) FROM courses'),
            'events'           => (int) Database::scalar('SELECT COUNT(*) FROM events'),
            'enrollments'      => (int) Database::scalar('SELECT COUNT(*) FROM enrollments'),
            'revenue'          => (float) Database::scalar("SELECT COALESCE(SUM(amount),0) FROM orders WHERE status = 'paid'"),
            'new_messages'     => (int) Database::scalar("SELECT COUNT(*) FROM contact_messages WHERE status = 'new'"),
            'pending_payments' => $paymentsTableExists
                ? (int) Database::scalar("SELECT COUNT(*) FROM payment_submissions WHERE status = 'pending'")
                : 0,
        ];

        $recentOrders = Database::all(
            'SELECT o.*, u.name AS student_name FROM orders o
             JOIN users u ON u.id = o.user_id
             ORDER BY o.created_at DESC LIMIT 10'
        );

        $recentStudents = Database::all(
            "SELECT id, name, email, created_at FROM users WHERE role = 'student'
             ORDER BY created_at DESC LIMIT 8"
        );

        $this->view('admin/dashboard', [
            'title'          => 'Dashboard',
            'user'           => Auth::user(),
            'stats'          => $stats,
            'recentOrders'   => $recentOrders,
            'recentStudents' => $recentStudents,
        ], 'admin');
    }

    /** DELETE /admin/orders/{id} — remove a single order record */
    public function deleteOrder(array $params): void
    {
        Auth::require('admin');
        $id    = (int) $params['id'];
        $order = Database::first('SELECT * FROM orders WHERE id = ?', [$id]);

        if ($order) {
            Database::delete('orders', ['id' => $id]);
        }

        $this->respond(null, 'Order deleted.', '/admin');
    }

    /** POST /admin/orders/clear — wipe ALL order history */
    public function clearOrders(): void
    {
        Auth::require('admin');
        Database::run('DELETE FROM orders');
        $this->respond(null, 'All orders cleared.', '/admin');
    }

    /** POST /admin/events/{id}/reset — reset seats_filled back to 0 */
    public function resetEvent(array $params): void
    {
        Auth::require('admin');
        $id = (int) $params['id'];
        Database::update('events', ['seats_filled' => 0], ['id' => $id]);
        // Also remove all registrations for this event
        Database::run('DELETE FROM event_registrations WHERE event_id = ?', [$id]);
        $this->respond(null, 'Event seats reset.', '/admin/events');
    }
}
