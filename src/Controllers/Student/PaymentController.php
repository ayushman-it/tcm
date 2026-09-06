<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\FirebaseNotification;
use TCM\Core\Request;
use TCM\Core\Upload;
use TCM\Models\Course;
use TCM\Models\Event;
use TCM\Models\Lead;
use TCM\Models\Notification;
use TCM\Models\PaymentSubmission;
use TCM\Models\Program;

final class PaymentController extends Controller
{
    /** Show payment history list. */
    public function history(): void
    {
        $user = Auth::require('student');
        $this->view('student/payments/history', [
            'title'    => 'Payment History',
            'user'     => $user,
            'payments' => PaymentSubmission::forUser((int) $user['id']),
        ], 'student');
    }

    /**
     * Show the payment submission form (open or pre-filled).
     * GET /student/payments/submit
     * GET /student/payments/submit?type=course&id=5
     */
    public function showForm(): void
    {
        $user = Auth::require('student');
        $type = Request::string('type');
        $id   = Request::int('id');

        $item = null;
        if ($type !== '' && $id > 0) {
            $item = $this->resolveItem($type, $id);
            if ($item === null) { $type = ''; $id = 0; }
        }

        if ($item !== null && PaymentSubmission::hasPendingOrApproved((int) $user['id'], $type, $id)) {
            flash('error', 'You already have a pending or approved payment for this item.');
            redirect('/student/payments');
        }

        $this->view('student/payments/submit', [
            'title'    => 'Submit Payment',
            'user'     => $user,
            'item'     => $item,
            'itemType' => $type,
            'itemId'   => $id,
            'courses'  => Database::all("SELECT id, title, price FROM courses  WHERE status='published' ORDER BY title"),
            'events'   => Database::all("SELECT id, title, price FROM events   WHERE status!='past'    ORDER BY title"),
            'programs' => Database::all("SELECT id, title, price FROM programs WHERE status='published' ORDER BY title"),
        ], 'student');
    }

    /** Process new payment submission. POST /student/payments/submit */
    public function store(): void
    {
        try {
            $user      = Auth::require('student');
            $type      = Request::string('item_type');
            $itemId    = Request::int('item_id');
            $item      = ($type !== '' && $itemId > 0) ? $this->resolveItem($type, $itemId) : null;
            $itemTitle = $item['title'] ?? Request::string('item_title_manual', 'Manual Payment');

            if ($item === null && $itemId > 0) { $itemId = 0; }

            // Auto-create payment_submissions table if migration hasn't run yet
            $tableExists = (bool) Database::scalar(
                "SELECT COUNT(*) FROM information_schema.tables
                 WHERE table_schema = DATABASE() AND table_name = 'payment_submissions'"
            );
            if (!$tableExists) {
                Database::run("CREATE TABLE IF NOT EXISTS payment_submissions (
                    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                    user_id BIGINT UNSIGNED NOT NULL,
                    item_type ENUM('course','event','program') NOT NULL,
                    item_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
                    item_title VARCHAR(200) NOT NULL,
                    amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    payment_method ENUM('cash','upi','bank_transfer','online','other') NOT NULL DEFAULT 'upi',
                    payment_date DATE NOT NULL,
                    screenshot VARCHAR(255) DEFAULT NULL,
                    reason VARCHAR(255) DEFAULT NULL,
                    transaction_ref VARCHAR(120) DEFAULT NULL,
                    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
                    admin_note VARCHAR(255) DEFAULT NULL,
                    receipt_number VARCHAR(50) DEFAULT NULL,
                    reviewed_by BIGINT UNSIGNED DEFAULT NULL,
                    reviewed_at DATETIME DEFAULT NULL,
                    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_ps_user (user_id),
                    KEY idx_ps_status (status),
                    CONSTRAINT fk_ps_user_pay FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            }

            if ($item !== null && PaymentSubmission::hasPendingOrApproved((int) $user['id'], $type, $itemId)) {
                flash('error', 'You already submitted a payment for this item.');
                redirect('/student/payments');
            }

            $screenshotPath = $this->handleScreenshotUpload($type, $itemId);
            if ($screenshotPath === false) return;

            $finalType = ($type !== '') ? $type : 'course';
            $finalId   = $itemId > 0   ? $itemId : 0;

            // Handle referral code if provided
            $referralCode = trim(Request::string('referral_code'));
            $referrerId = null;
            
            if ($referralCode !== '') {
                // Find user with this referral ID
                $referrer = Database::first("SELECT id FROM users WHERE referral_id = ?", [$referralCode]);
                if ($referrer) {
                    $referrerId = (int) $referrer['id'];
                }
            }

            PaymentSubmission::create([
                'user_id'         => (int) $user['id'],
                'item_type'       => $finalType,
                'item_id'         => $finalId,
                'item_title'      => $itemTitle,
                'amount'          => (float) Request::string('amount', '0'),
                'payment_method'  => Request::string('payment_method', 'upi'),
                'payment_date'    => Request::string('payment_date') ?: date('Y-m-d'),
                'screenshot'      => $screenshotPath,
                'reason'          => Request::string('reason'),
                'transaction_ref' => Request::string('transaction_ref'),
                'referral_code'   => $referralCode !== '' ? $referralCode : null,
                'referrer_id'     => $referrerId,
                'status'          => 'pending',
            ]);

            Lead::capture([
                'user_id'        => (int) $user['id'],
                'name'           => $user['name'],
                'email'          => $user['email'],
                'phone'          => $user['phone'] ?? null,
                'interest_type'  => $finalType,
                'interest_id'    => $finalId ?: null,
                'interest_title' => $itemTitle,
                'message'        => 'Payment submitted — pending approval.',
                'source'         => 'payment-submission',
            ]);

            // 🔔 In-app + push: notify admins about new payment waiting for review
            $amount = (float) Request::string('amount', '0');
            Notification::ensureTable();
            Notification::toAdmins(
                '💰 Payment Submitted — ' . $user['name'],
                $user['name'] . ' submitted ₹' . number_format($amount) . ' for ' . $itemTitle . '. Review and approve.',
                '💰',
                base_url('/admin/payments')
            );
            FirebaseNotification::notifyAdmins(
                '💰 Payment Submitted — ' . $user['name'],
                $user['name'] . ' submitted ₹' . number_format($amount) . ' for ' . $itemTitle . '. Review and approve.',
                ['tag' => 'payment-pending'],
                base_url('/admin/payments')
            );

            flash('success', 'Payment submitted! Our team will verify and activate your access within 24 hours.');
            redirect('/student/payments');
            
        } catch (\Throwable $e) {
            // Log error for debugging
            error_log('[Payment Submission Error] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            error_log('[Payment Stack Trace] ' . $e->getTraceAsString());
            
            // Show user-friendly error
            if (config('app.debug')) {
                flash('error', 'Payment submission failed: ' . $e->getMessage());
            } else {
                flash('error', 'Payment submission failed. Please try again or contact support.');
            }
            redirect('/student/payments/submit' . ($type && $itemId ? "?type={$type}&id={$itemId}" : ''));
        }
    }

    /** Download receipt for an approved payment. */
    public function receipt(array $params): void
    {
        $user = Auth::require('student');
        $sub  = PaymentSubmission::find((int) $params['id']);

        if ($sub === null || (int) $sub['user_id'] !== (int) $user['id']) {
            flash('error', 'Receipt not found.');
            redirect('/student/payments');
        }
        if ($sub['status'] !== 'approved') {
            flash('error', 'Receipt is only available for approved payments.');
            redirect('/student/payments');
        }

        $this->view('student/payments/receipt', [
            'title'   => 'Payment Receipt',
            'user'    => $user,
            'payment' => $sub,
        ], null);
    }

    /**
     * Update/replace screenshot for a pending or rejected payment.
     * POST /student/payments/{id}/screenshot
     */
    public function updateScreenshot(array $params): void
    {
        $user = Auth::require('student');
        $sub  = PaymentSubmission::find((int) $params['id']);

        if ($sub === null || (int) $sub['user_id'] !== (int) $user['id']) {
            flash('error', 'Payment not found.');
            redirect('/student/payments');
        }
        if ($sub['status'] === 'approved') {
            flash('error', 'This payment is already approved — no update needed.');
            redirect('/student/payments');
        }
        if (!Upload::present($_FILES['screenshot'] ?? null)) {
            flash('error', 'Please select a screenshot file.');
            redirect('/student/payments');
        }

        $uploadDir = config('uploads.path') . '/payments';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }

        try {
            $path = Upload::store(
                $_FILES['screenshot'],
                $uploadDir,
                ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                5 * 1024 * 1024
            );
        } catch (\RuntimeException $e) {
            flash('error', 'Upload failed: ' . $e->getMessage());
            redirect('/student/payments');
        }

        Database::update('payment_submissions', [
            'screenshot' => $path,
            'status'     => 'pending',  // reset for re-review
        ], ['id' => (int) $params['id']]);

        flash('success', 'Screenshot updated! Our team will re-review within 24 hours.');
        redirect('/student/payments');
    }

    /** Handle interest lead capture (from course/event popup modal). */
    public function submitInterest(): void
    {
        $user   = Auth::require('student');
        $type   = Request::string('item_type');
        $itemId = Request::int('item_id');
        $item   = $this->resolveItem($type, $itemId);

        if ($item === null) {
            flash('error', 'Item not found.');
            redirect('/student');
        }

        Lead::capture([
            'user_id'        => (int) $user['id'],
            'name'           => $user['name'],
            'email'          => $user['email'],
            'phone'          => Request::string('phone') ?: ($user['phone'] ?? null),
            'interest_type'  => $type,
            'interest_id'    => $itemId,
            'interest_title' => $item['title'],
            'message'        => Request::string('message') ?: ('Interested in ' . $item['title']),
            'source'         => 'student-interest-form',
        ]);

        flash('success', 'Interest registered! Our team will contact you shortly.');
        redirect('/student/payments/submit?type=' . $type . '&id=' . $itemId);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /**
     * Upload screenshot file, return path string or null (no file).
     * Returns false and redirects on error.
     *
     * @return string|null|false
     */
    private function handleScreenshotUpload(string $type, int $itemId): string|null|false
    {
        if (!Upload::present($_FILES['screenshot'] ?? null)) {
            return null;
        }

        $uploadDir = config('uploads.path') . '/payments';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }

        try {
            return Upload::store(
                $_FILES['screenshot'],
                $uploadDir,
                ['jpg', 'jpeg', 'png', 'webp', 'pdf'],
                5 * 1024 * 1024
            );
        } catch (\RuntimeException $e) {
            flash('error', 'Screenshot upload failed: ' . $e->getMessage());
            redirect('/student/payments/submit?type=' . $type . '&id=' . $itemId);
            return false;
        }
    }

    /** @return array<string,mixed>|null */
    private function resolveItem(string $type, int $id): ?array
    {
        return match ($type) {
            'course'  => Course::find($id),
            'event'   => Event::find($id),
            'program' => Program::find($id),
            default   => null,
        };
    }
}
