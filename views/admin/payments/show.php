<?php use TCM\Models\PaymentSubmission; ?>

<div class="tcm-page-head">
    <div>
        <h2>Review Payment</h2>
        <p>Submitted by student — verify details and approve or reject.</p>
    </div>
    <a href="<?= base_url('/admin/payments') ?>" class="tcm-btn"><i class="bi bi-arrow-left"></i> Back</a>
</div>

<div class="tcm-grid-2" style="align-items:start;">

    <!-- Payment Details -->
    <div>
        <div class="tcm-card" style="margin-bottom:14px;">
            <h3 style="margin-bottom:18px;font-size:.95rem;"><i class="bi bi-info-circle" style="color:var(--muted);"></i> Payment Details</h3>

            <table style="width:100%;border-collapse:collapse;font-size:.86rem;">
                <?php $rows = [
                    ['Student',      $payment['student_name'] ?? '—'],
                    ['Email',        $payment['student_email'] ?? '—'],
                    ['Student ID',   $payment['student_id'] ?? '—'],
                    ['Item Type',    ucfirst($payment['item_type'])],
                    ['Item',         $payment['item_title']],
                    ['Amount Paid',  money($payment['amount'])],
                    ['Method',       PaymentSubmission::METHODS[$payment['payment_method']] ?? $payment['payment_method']],
                    ['Payment Date', date('d M Y', strtotime($payment['payment_date']))],
                    ['Ref / UTR',    $payment['transaction_ref'] ?: '—'],
                    ['Submitted At', date('d M Y h:i A', strtotime($payment['created_at']))],
                    ['Status',       strtoupper($payment['status'])],
                ];
                foreach ($rows as [$label, $value]):
                ?>
                <tr style="border-bottom:1px solid #f5f5f5;">
                    <td style="padding:9px 0;color:#888;font-weight:600;width:38%;vertical-align:top;"><?= e($label) ?></td>
                    <td style="padding:9px 0;color:#111;font-weight:500;"><?= e((string)$value) ?></td>
                </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($payment['reason']): ?>
                <div style="margin-top:14px;padding:12px 14px;background:#f9f9f9;border:1px solid #ececec;border-radius:10px;">
                    <div style="font-size:.72rem;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;">Student Note</div>
                    <div style="font-size:.85rem;color:#444;line-height:1.6;"><?= nl2br(e($payment['reason'])) ?></div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Screenshot -->
        <?php if ($payment['screenshot']): ?>
        <div class="tcm-card">
            <h3 style="margin-bottom:14px;font-size:.95rem;"><i class="bi bi-image" style="color:var(--muted);"></i> Payment Screenshot</h3>
            <a href="<?= base_url('/admin/payments/' . $payment['id'] . '/screenshot') ?>" target="_blank">
                <img src="<?= base_url('/admin/payments/' . $payment['id'] . '/screenshot') ?>"
                     alt="Payment proof"
                     style="width:100%;border-radius:10px;border:1px solid #ececec;object-fit:contain;max-height:400px;cursor:zoom-in;">
            </a>
            <a href="<?= base_url('/admin/payments/' . $payment['id'] . '/screenshot') ?>"
               target="_blank" class="tcm-btn sm" style="margin-top:10px;">
                <i class="bi bi-box-arrow-up-right"></i> Open Full Size
            </a>
        </div>
        <?php else: ?>
        <div class="tcm-card">
            <div class="tcm-empty" style="padding:16px 0 8px;">
                <i class="bi bi-image"></i> No screenshot uploaded.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Review Actions -->
    <div>

        <?php if ($payment['status'] === 'pending'): ?>
        <!-- Approve -->
        <div class="tcm-card" style="margin-bottom:14px;border-color:#bbf7d0;background:#f0fdf4;">
            <h3 style="margin-bottom:14px;font-size:.95rem;color:#16a34a;"><i class="bi bi-check2-circle"></i> Approve Payment</h3>
            <form method="post" action="<?= base_url('/admin/payments/' . $payment['id'] . '/approve') ?>">
                <?= csrf_field() ?>
                <div class="tcm-field">
                    <label>Admin Note (optional)</label>
                    <input class="tcm-input" name="admin_note"
                           placeholder="e.g. Verified via UPI confirmation">
                </div>
                <button type="submit" class="tcm-btn green w-full" style="justify-content:center;padding:11px;"
                        onclick="return confirm('Approve this payment? This will activate student access immediately.')">
                    <i class="bi bi-check2-circle"></i> Approve & Activate Access
                </button>
            </form>
            <div style="font-size:.75rem;color:#16a34a;margin-top:8px;text-align:center;">
                Approving will automatically enroll the student and generate a receipt.
            </div>
        </div>

        <!-- Reject -->
        <div class="tcm-card" style="border-color:#fecaca;background:#fef2f2;">
            <h3 style="margin-bottom:14px;font-size:.95rem;color:#dc2626;"><i class="bi bi-x-circle"></i> Reject Payment</h3>
            <form method="post" action="<?= base_url('/admin/payments/' . $payment['id'] . '/reject') ?>">
                <?= csrf_field() ?>
                <div class="tcm-field">
                    <label>Reason for Rejection *</label>
                    <textarea class="tcm-textarea" name="admin_note" required
                              style="min-height:80px;"
                              placeholder="e.g. Screenshot unclear, amount mismatch, payment not received"></textarea>
                </div>
                <button type="submit" class="tcm-btn danger w-full" style="justify-content:center;padding:11px;"
                        onclick="return confirm('Reject this payment? Student will see this rejection reason on their dashboard.')">
                    <i class="bi bi-x-circle"></i> Reject Payment
                </button>
            </form>
        </div>

        <?php else: ?>
        <!-- Already reviewed -->
        <div class="tcm-card">
            <div style="text-align:center;padding:20px 0 10px;">
                <?php if ($payment['status'] === 'approved'): ?>
                    <i class="bi bi-patch-check-fill" style="font-size:2.5rem;color:#16a34a;display:block;margin-bottom:12px;"></i>
                    <div style="font-size:1rem;font-weight:700;color:#111;margin-bottom:6px;">Payment Approved</div>
                    <div style="font-size:.82rem;color:var(--muted);">
                        Receipt: <strong style="color:#111;font-family:monospace;"><?= e($payment['receipt_number'] ?? '—') ?></strong>
                    </div>
                <?php else: ?>
                    <i class="bi bi-x-circle-fill" style="font-size:2.5rem;color:#dc2626;display:block;margin-bottom:12px;"></i>
                    <div style="font-size:1rem;font-weight:700;color:#111;margin-bottom:6px;">Payment Rejected</div>
                <?php endif; ?>
                <?php if ($payment['admin_note']): ?>
                    <div style="margin-top:12px;padding:10px 14px;background:#f9f9f9;border-radius:10px;font-size:.82rem;color:#555;">
                        <?= e($payment['admin_note']) ?>
                    </div>
                <?php endif; ?>
                <div style="font-size:.75rem;color:var(--muted);margin-top:10px;">
                    Reviewed on <?= $payment['reviewed_at'] ? e(date('d M Y h:i A', strtotime($payment['reviewed_at']))) : '—' ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>
