<?php use TCM\Models\PaymentSubmission; ?>

<div class="tcm-page-head">
    <div>
        <h2>Payments</h2>
        <p>Review student payment submissions and approve or reject access.</p>
    </div>
</div>

<!-- Stats -->
<div class="tcm-stat-grid" style="margin-bottom:18px;grid-template-columns:repeat(3,1fr);">
    <div class="tcm-stat" style="border-color:#fde68a;background:#fffbeb;">
        <i class="bi bi-clock-history icon" style="color:#d97706;"></i>
        <div class="label">Pending Review</div>
        <div class="value"><?= (int)$counts['pending'] ?></div>
    </div>
    <div class="tcm-stat" style="border-color:#bbf7d0;background:#f0fdf4;">
        <i class="bi bi-patch-check icon" style="color:#16a34a;"></i>
        <div class="label">Approved</div>
        <div class="value"><?= (int)$counts['approved'] ?></div>
    </div>
    <div class="tcm-stat" style="border-color:#fecaca;background:#fef2f2;">
        <i class="bi bi-x-circle icon" style="color:#dc2626;"></i>
        <div class="label">Rejected</div>
        <div class="value"><?= (int)$counts['rejected'] ?></div>
    </div>
</div>

<!-- Filters -->
<div class="tcm-card">
    <form method="get" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;align-items:center;">
        <input class="tcm-input" name="q" placeholder="Search student, item..."
               value="<?= e($_GET['q'] ?? '') ?>" style="flex:1;min-width:200px;">
        <select class="tcm-select" name="status" style="width:170px;" onchange="this.form.submit()">
            <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
            <?php foreach (PaymentSubmission::STATUSES as $k => $lbl): ?>
                <option value="<?= $k ?>" <?= ($_GET['status'] ?? '') === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="tcm-btn primary">Filter</button>
    </form>

    <?php if (empty($payments)): ?>
        <div class="tcm-empty">
            <i class="bi bi-receipt"></i>
            No payment submissions found.
        </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="tcm-table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Item</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Referral</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($payments as $p):
                $statusCls = match($p['status']) { 'approved' => 'green', 'rejected' => 'red', default => 'amber' };
            ?>
                <tr>
                    <td>
                        <div style="font-weight:600;font-size:.88rem;color:#111;"><?= e($p['student_name']) ?></div>
                        <div style="font-size:.73rem;color:var(--muted);"><?= e($p['student_email']) ?></div>
                        <?php if (!empty($p['student_id'])): ?>
                            <div style="font-size:.7rem;color:#aaa;font-family:monospace;"><?= e($p['student_id']) ?></div>
                        <?php endif; ?>
                    </td>                    <td>
                        <?php $typeColors = ['course'=>'purple','event'=>'amber','program'=>'green']; ?>
                        <span class="tcm-badge <?= $typeColors[$p['item_type']] ?? 'gray' ?>" style="font-size:.65rem;margin-bottom:4px;display:inline-flex;">
                            <?= e(ucfirst($p['item_type'])) ?>
                        </span>
                        <div style="font-size:.84rem;font-weight:500;color:#111;"><?= e($p['item_title']) ?></div>
                        <?php if ($p['transaction_ref']): ?>
                            <div style="font-size:.72rem;color:var(--muted);">Ref: <?= e($p['transaction_ref']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td style="font-weight:700;font-size:.95rem;"><?= money($p['amount']) ?></td>
                    <td style="font-size:.82rem;color:var(--muted);"><?= e(PaymentSubmission::METHODS[$p['payment_method']] ?? $p['payment_method']) ?></td>
                    <td>
                        <?php if (!empty($p['referral_code']) && !empty($p['referrer_name'])): ?>
                            <div style="font-size:.78rem;font-weight:600;color:#059669;">
                                <i class="bi bi-gift-fill"></i> <?= e($p['referral_code']) ?>
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);">
                                By: <?= e($p['referrer_name']) ?>
                            </div>
                        <?php else: ?>
                            <span style="font-size:.75rem;color:#cbd5e1;">—</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:.82rem;color:var(--muted);"><?= e(date('d M Y', strtotime($p['payment_date']))) ?></td>
                    <td>
                        <span class="tcm-badge <?= $statusCls ?>"><?= e(PaymentSubmission::STATUSES[$p['status']]) ?></span>
                        <?php if ($p['admin_note']): ?>
                            <div style="font-size:.72rem;color:var(--muted);margin-top:3px;max-width:120px;"><?= e($p['admin_note']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                            <a href="<?= base_url('/admin/payments/' . $p['id']) ?>" class="tcm-btn sm">
                                <i class="bi bi-eye"></i> Review
                            </a>
                            <?php if ($p['screenshot']): ?>
                                <a href="<?= base_url('/admin/payments/' . $p['id'] . '/screenshot') ?>"
                                   target="_blank" class="tcm-btn sm" title="View screenshot">
                                    <i class="bi bi-image"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
