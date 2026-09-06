<?php

declare(strict_types=1);

namespace TCM\Controllers\Admin;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Request;
use TCM\Models\PaymentSubmission;

final class PaymentController extends Controller
{
    public function index(): void
    {
        Auth::require('admin');

        $payments = PaymentSubmission::adminList([
            'status' => Request::string('status') ?: 'all',
            'search' => Request::string('q') ?: null,
        ]);

        $this->view('admin/payments/index', [
            'title'    => 'Payments',
            'payments' => $payments,
            'counts'   => PaymentSubmission::counts(),
        ], 'admin');
    }

    public function show(array $params): void
    {
        Auth::require('admin');
        $payment = PaymentSubmission::find((int) $params['id']);
        if ($payment === null) {
            flash('error', 'Payment not found.');
            redirect('/admin/payments');
        }

        $this->view('admin/payments/show', [
            'title'   => 'Review Payment',
            'payment' => $payment,
        ], 'admin');
    }

    public function approve(array $params): void
    {
        $admin = Auth::require('admin');
        $note  = Request::string('admin_note');

        PaymentSubmission::approve((int) $params['id'], (int) $admin['id'], $note);

        flash('success', 'Payment approved and student enrollment activated.');
        redirect('/admin/payments');
    }

    public function reject(array $params): void
    {
        $admin = Auth::require('admin');
        $note  = Request::string('admin_note');

        PaymentSubmission::reject((int) $params['id'], (int) $admin['id'], $note);

        flash('success', 'Payment rejected. Student has been notified via their dashboard.');
        redirect('/admin/payments');
    }

    /**
     * View screenshot of the payment.
     */
    public function viewScreenshot(array $params): void
    {
        Auth::require('admin');
        $payment = PaymentSubmission::find((int) $params['id']);
        if ($payment === null || empty($payment['screenshot'])) {
            http_response_code(404);
            echo 'Screenshot not found.';
            exit;
        }

        $config = config('uploads');
        $file   = $config['path'] . '/payments/' . basename((string)$payment['screenshot']);

        if (!is_file($file)) {
            http_response_code(404);
            echo 'File not found.';
            exit;
        }

        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'webp' => 'image/webp',
            default => 'image/jpeg',
        };

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="payment-' . (int) $params['id'] . '.' . $ext . '"');
        readfile($file);
        exit;
    }
}
