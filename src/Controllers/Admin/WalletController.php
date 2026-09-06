<?php
declare(strict_types=1);
namespace TCM\Controllers\Admin;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Core\Request;
use TCM\Models\Notification;
use TCM\Models\Wallet;

final class WalletController extends Controller
{
    public function index(): void
    {
        Auth::require('admin');
        Wallet::ensureTables();

        // Get withdrawal requests
        $requests = Wallet::allWithdrawalRequests([
            'status' => Request::string('status') ?: 'all',
        ]);
        
        // Get stats
        $stats = [
            'pending'  => (int) Database::scalar("SELECT COUNT(*) FROM withdrawal_requests WHERE status='pending'"),
            'approved' => (int) Database::scalar("SELECT COUNT(*) FROM withdrawal_requests WHERE status='approved'"),
            'total_paid' => (float) (Database::scalar("SELECT COALESCE(SUM(amount),0) FROM withdrawal_requests WHERE status='approved'") ?? 0),
        ];

        // Get all wallet transactions (recent 100)
        $allTransactions = Database::all(
            "SELECT wt.*, u.name AS student_name, u.email AS student_email, u.referral_id AS student_ref_code
             FROM wallet_transactions wt
             JOIN users u ON u.id = wt.user_id
             ORDER BY wt.created_at DESC
             LIMIT 100"
        );

        // Get referral tree stats
        $referralStats = Database::all(
            "SELECT 
                ref_user.id AS referrer_id,
                ref_user.name AS referrer_name,
                ref_user.referral_id AS referral_code,
                COUNT(DISTINCT ps.id) AS total_referrals,
                COUNT(DISTINCT CASE WHEN ps.status = 'approved' THEN ps.id END) AS approved_referrals,
                COALESCE(SUM(CASE WHEN ps.status = 'approved' THEN " . Wallet::REFERRAL_CREDIT . " END), 0) AS total_earned
             FROM users ref_user
             LEFT JOIN payment_submissions ps ON ps.referrer_id = ref_user.id
             WHERE ref_user.referral_id IS NOT NULL
             GROUP BY ref_user.id, ref_user.name, ref_user.referral_id
             HAVING total_referrals > 0
             ORDER BY total_earned DESC, total_referrals DESC"
        );

        $this->view('admin/wallet/index', [
            'title'            => 'Wallet & Withdrawals',
            'requests'         => $requests,
            'stats'            => $stats,
            'allTransactions'  => $allTransactions,
            'referralStats'    => $referralStats,
        ], 'admin');
    }

    public function approveWithdrawal(array $params): void
    {
        Auth::require('admin');
        Wallet::ensureTables();

        $id  = (int) $params['id'];
        $req = Database::first('SELECT * FROM withdrawal_requests WHERE id = ?', [$id]);
        if ($req === null || $req['status'] !== 'pending') {
            flash('error', 'Request not found or already processed.');
            redirect('/admin/wallet');
        }

        // Debit wallet
        $ok = Wallet::debit((int) $req['user_id'], (float) $req['amount'], 'Withdrawal processed — UPI: ' . $req['upi_id']);
        if (!$ok) {
            flash('error', 'Insufficient balance in student wallet.');
            redirect('/admin/wallet');
        }

        Database::update('withdrawal_requests', [
            'status'       => 'approved',
            'admin_note'   => Request::string('note'),
            'processed_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        // Notify student
        Notification::ensureTable();
        Notification::forUser(
            (int) $req['user_id'],
            '✅ Withdrawal Approved!',
            '₹' . number_format((float)$req['amount'], 0) . ' has been sent to your UPI: ' . $req['upi_id'],
            '💸',
            base_url('/student/wallet')
        );
        FirebaseNotification::notifyStudent(
            (int) $req['user_id'],
            '✅ Withdrawal Approved!',
            '₹' . number_format((float)$req['amount'], 0) . ' sent to ' . $req['upi_id'],
            ['tag' => 'withdrawal-approved'],
            base_url('/student/wallet')
        );

        flash('success', 'Withdrawal approved and student notified.');
        redirect('/admin/wallet');
    }

    public function rejectWithdrawal(array $params): void
    {
        Auth::require('admin');
        Wallet::ensureTables();

        $id  = (int) $params['id'];
        $req = Database::first('SELECT * FROM withdrawal_requests WHERE id = ?', [$id]);
        if ($req === null) { redirect('/admin/wallet'); }

        Database::update('withdrawal_requests', [
            'status'       => 'rejected',
            'admin_note'   => Request::string('note'),
            'processed_at' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);

        // Notify student
        Notification::ensureTable();
        Notification::forUser(
            (int) $req['user_id'],
            '❌ Withdrawal Rejected',
            'Your withdrawal of ₹' . number_format((float)$req['amount'], 0) . ' was rejected. ' . (Request::string('note') ?: 'Contact admin for details.'),
            '❌',
            base_url('/student/wallet')
        );

        flash('info', 'Withdrawal rejected.');
        redirect('/admin/wallet');
    }
}
