<?php
declare(strict_types=1);
namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;
use TCM\Models\Wallet;

final class WalletController extends Controller
{
    public function index(): void
    {
        $user = Auth::require('student');
        Wallet::ensureTables();

        $wallet       = Wallet::forUser((int) $user['id']);
        $transactions = Wallet::transactions((int) $user['id'], 50);
        $withdrawals  = Wallet::withdrawalRequests((int) $user['id']);

        // Get referral stats (who joined using my code)
        $referralStats = Database::first(
            "SELECT 
                COUNT(DISTINCT ps.id) AS total_referrals,
                COUNT(DISTINCT CASE WHEN ps.status = 'approved' THEN ps.id END) AS approved_referrals,
                COALESCE(SUM(CASE WHEN ps.status = 'approved' THEN ? END), 0) AS total_earned_from_referrals
             FROM payment_submissions ps
             WHERE ps.referrer_id = ?",
            [Wallet::REFERRAL_CREDIT, (int) $user['id']]
        );

        // Get list of students I referred
        $myReferrals = Database::all(
            "SELECT 
                u.name,
                u.email,
                ps.item_title,
                ps.amount,
                ps.status,
                ps.created_at,
                ps.referral_code
             FROM payment_submissions ps
             JOIN users u ON u.id = ps.user_id
             WHERE ps.referrer_id = ?
             ORDER BY ps.created_at DESC
             LIMIT 20",
            [(int) $user['id']]
        );

        $this->view('student/wallet/index', [
            'title'         => 'My Wallet',
            'user'          => $user,
            'wallet'        => $wallet,
            'transactions'  => $transactions,
            'withdrawals'   => $withdrawals,
            'min'           => Wallet::MIN_WITHDRAWAL,
            'referralStats' => $referralStats,
            'myReferrals'   => $myReferrals,
        ], 'student');
    }

    public function requestWithdrawal(): void
    {
        $user = Auth::require('student');
        Wallet::ensureTables();

        $amount = (float) Request::string('amount', '0');
        $upiId  = trim(Request::string('upi_id'));

        if (empty($upiId)) {
            flash('error', 'Please enter your UPI ID.');
            redirect('/student/wallet');
        }
        if ($amount < Wallet::MIN_WITHDRAWAL) {
            flash('error', 'Minimum withdrawal amount is ₹' . Wallet::MIN_WITHDRAWAL . '.');
            redirect('/student/wallet');
        }
        if (Wallet::balance((int) $user['id']) < $amount) {
            flash('error', 'Insufficient wallet balance.');
            redirect('/student/wallet');
        }

        $ok = Wallet::requestWithdrawal((int) $user['id'], $amount, $upiId);
        if ($ok) {
            flash('success', 'Withdrawal request submitted! Admin will process within 48 hours.');
        } else {
            flash('error', 'You already have a pending withdrawal request, or the amount is too low.');
        }
        redirect('/student/wallet');
    }
}
