<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt · The Code Munk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', -apple-system, sans-serif; background: #f9f9f9; color: #111; padding: 40px 20px; -webkit-font-smoothing: antialiased; }
        .receipt-wrap { max-width: 560px; margin: 0 auto; }
        .receipt-card { background: #fff; border: 1px solid #e5e5e5; border-radius: 20px; overflow: hidden; box-shadow: 0 8px 40px rgba(0,0,0,.08); }

        /* Header */
        .receipt-header { background: #111; padding: 32px 36px 28px; text-align: center; }
        .receipt-brand { font-size: 1.1rem; font-weight: 800; color: #fff; letter-spacing: -.2px; margin-bottom: 4px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .receipt-brand i { font-size: 1rem; }
        .receipt-sub { font-size: .8rem; color: rgba(255,255,255,.5); }

        /* Status badge */
        .receipt-status {
            display: flex; align-items: center; justify-content: center;
            gap: 10px; padding: 16px 36px;
            background: #f0fdf4; border-bottom: 1px solid #d1fae5;
        }
        .receipt-status i { font-size: 1.4rem; color: #16a34a; }
        .receipt-status-text strong { display: block; font-size: .95rem; font-weight: 700; color: #15803d; }
        .receipt-status-text span { font-size: .78rem; color: #16a34a; }

        /* Body */
        .receipt-body { padding: 28px 36px 32px; }
        .receipt-number {
            text-align: center; padding: 16px; background: #f9f9f9;
            border: 1px solid #ececec; border-radius: 12px; margin-bottom: 24px;
        }
        .receipt-number-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #aaa; margin-bottom: 6px; }
        .receipt-number-value { font-size: 1.3rem; font-weight: 800; color: #111; font-family: 'Courier New', monospace; letter-spacing: 1px; }

        /* Row */
        .receipt-row { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; padding: 10px 0; border-bottom: 1px solid #f5f5f5; font-size: .86rem; }
        .receipt-row:last-child { border-bottom: none; }
        .receipt-row-label { color: #888; font-weight: 500; flex-shrink: 0; }
        .receipt-row-value { color: #111; font-weight: 600; text-align: right; }
        .receipt-amount-row { padding: 14px 0; border-top: 2px solid #111 !important; margin-top: 6px; }
        .receipt-amount-row .receipt-row-label { font-weight: 700; color: #111; font-size: .92rem; }
        .receipt-amount-row .receipt-row-value { font-size: 1.2rem; font-weight: 800; color: #111; }

        /* Footer */
        .receipt-footer { padding: 18px 36px 24px; background: #fafafa; border-top: 1px solid #f0f0f0; text-align: center; font-size: .75rem; color: #aaa; line-height: 1.7; }
        .receipt-footer strong { color: #888; }

        /* Print actions */
        .receipt-actions { display: flex; gap: 10px; justify-content: center; margin-top: 24px; }
        .receipt-btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 10px 22px; border-radius: 10px;
            font-size: .85rem; font-weight: 600; cursor: pointer;
            text-decoration: none; font-family: inherit; border: none;
            transition: .15s;
        }
        .receipt-btn.primary { background: #111; color: #fff; }
        .receipt-btn.primary:hover { background: #333; color: #fff; }
        .receipt-btn.outline { background: #fff; color: #111; border: 1.5px solid #e5e5e5; }
        .receipt-btn.outline:hover { border-color: #111; }

        @media print {
            body { background: #fff; padding: 0; }
            .receipt-wrap { max-width: 100%; }
            .receipt-card { box-shadow: none; border: none; }
            .receipt-actions { display: none; }
        }
        @media (max-width: 480px) {
            .receipt-body, .receipt-header, .receipt-status, .receipt-footer { padding-left: 22px; padding-right: 22px; }
        }
    </style>
</head>
<body>

<div class="receipt-wrap">
    <div class="receipt-card">

        <!-- Header -->
        <div class="receipt-header">
            <div class="receipt-brand">
                <i class="bi bi-code-slash"></i>
                The Code Munk
            </div>
            <div class="receipt-sub">Payment Receipt · Official Document</div>
        </div>

        <!-- Approved badge -->
        <div class="receipt-status">
            <i class="bi bi-patch-check-fill"></i>
            <div class="receipt-status-text">
                <strong>Payment Verified & Approved</strong>
                <span>This receipt confirms your payment has been processed</span>
            </div>
        </div>

        <!-- Body -->
        <div class="receipt-body">

            <!-- Receipt number -->
            <div class="receipt-number">
                <div class="receipt-number-label">Receipt Number</div>
                <div class="receipt-number-value"><?= e($payment['receipt_number']) ?></div>
            </div>

            <!-- Details table -->
            <div class="receipt-row">
                <span class="receipt-row-label">Date Issued</span>
                <span class="receipt-row-value"><?= e(date('d M Y, h:i A', strtotime($payment['reviewed_at'] ?? $payment['created_at']))) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Student Name</span>
                <span class="receipt-row-value"><?= e($user['name']) ?></span>
            </div>
            <?php if (!empty($user['student_id'])): ?>
            <div class="receipt-row">
                <span class="receipt-row-label">Student ID</span>
                <span class="receipt-row-value" style="font-family:monospace;"><?= e($user['student_id']) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row">
                <span class="receipt-row-label">Email</span>
                <span class="receipt-row-value"><?= e($user['email']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Item</span>
                <span class="receipt-row-value"><?= e($payment['item_title']) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Item Type</span>
                <span class="receipt-row-value"><?= e(ucfirst($payment['item_type'])) ?></span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Payment Method</span>
                <span class="receipt-row-value">
                    <?= e(\TCM\Models\PaymentSubmission::METHODS[$payment['payment_method']] ?? $payment['payment_method']) ?>
                </span>
            </div>
            <div class="receipt-row">
                <span class="receipt-row-label">Payment Date</span>
                <span class="receipt-row-value"><?= e(date('d M Y', strtotime($payment['payment_date']))) ?></span>
            </div>
            <?php if ($payment['transaction_ref']): ?>
            <div class="receipt-row">
                <span class="receipt-row-label">Transaction Ref</span>
                <span class="receipt-row-value" style="font-family:monospace;"><?= e($payment['transaction_ref']) ?></span>
            </div>
            <?php endif; ?>
            <div class="receipt-row receipt-amount-row">
                <span class="receipt-row-label">Total Amount Paid</span>
                <span class="receipt-row-value"><?= money($payment['amount']) ?></span>
            </div>
        </div>

        <!-- Footer -->
        <div class="receipt-footer">
            <strong>The Code Munk</strong><br>
            thecodemunk@gmail.com · thecodemunk.com<br><br>
            This is an official receipt generated by The Code Munk platform.<br>
            Keep this for your records.
        </div>

    </div>

    <!-- Actions -->
    <div class="receipt-actions">
        <button class="receipt-btn primary" onclick="window.print()">
            <i class="bi bi-printer-fill"></i> Print / Save PDF
        </button>
        <a href="<?= base_url('/student/payments') ?>" class="receipt-btn outline">
            <i class="bi bi-arrow-left"></i> Back to Payments
        </a>
    </div>
</div>

</body>
</html>
