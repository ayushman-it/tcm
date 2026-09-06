<div class="tcm-page-head">
    <div>
        <h2><?= e($student['name']) ?></h2>
        <div style="display:flex;gap:8px;align-items:center;margin-top:4px;flex-wrap:wrap;">
            <span style="font-size:.78rem;color:#888;"><?= e($student['email']) ?></span>
            <?php if (!empty($student['student_id'])): ?>
                <span class="tcm-badge gray" style="font-family:monospace;">
                    <i class="bi bi-person-badge"></i> <?= e($student['student_id']) ?>
                </span>
            <?php endif; ?>
            <?php if (!empty($student['referral_id'])): ?>
                <span class="tcm-badge gray" style="font-family:monospace;">
                    <i class="bi bi-share"></i> <?= e($student['referral_id']) ?>
                </span>
            <?php endif; ?>
        </div>
    </div>
    <div class="d-flex gap-8">
        <a class="tcm-btn" href="<?= base_url('/admin/students') ?>"><i class="bi bi-arrow-left"></i> Back</a>
        <form method="post" action="<?= base_url('/admin/students/' . $student['id'] . '/toggle') ?>">
            <?= csrf_field() ?>
            <button class="tcm-btn <?= $student['status'] === 'active' ? 'danger' : 'primary' ?>">
                <?= $student['status'] === 'active' ? 'Suspend' : 'Activate' ?>
            </button>
        </form>
    </div>
</div>

<div class="tcm-grid-2" style="align-items:start;">
    <div class="tcm-card">
        <h3 class="mt-0">Profile</h3>
        <p><strong>Headline:</strong> <?= e($profile['headline'] ?? '—') ?></p>
        <p><strong>College:</strong> <?= e($profile['college'] ?? '—') ?></p>
        <p><strong>Experience:</strong> <?= e($profile['experience_level'] ?? '—') ?></p>
        <p><strong>Goal:</strong> <?= e($profile['goal'] ?? '—') ?></p>
        <p class="mb-0"><strong>Bio:</strong> <span class="muted"><?= e($profile['bio'] ?? '—') ?></span></p>
    </div>
    <div class="tcm-card">
        <h3 class="mt-0">Enrolled courses (<?= count($enrollments) ?>)</h3>
        <?php foreach ($enrollments as $en): ?>
            <div class="flex-between" style="padding:8px 0;border-bottom:1px solid var(--tcm-border);">
                <span><?= e($en['title']) ?></span>
                <span class="tcm-badge purple"><?= (int)$en['progress'] ?>%</span>
            </div>
        <?php endforeach; ?>
        <?php if ($enrollments === []): ?><p class="muted mb-0">No enrollments.</p><?php endif; ?>
    </div>
</div>

<div class="tcm-card" style="margin-top:18px;">
    <h3 class="mt-0">Payment Submissions</h3>
    <table class="tcm-table">
        <thead><tr><th>Item</th><th>Amount</th><th>Method</th><th>Date</th><th>Status</th><th>Receipt</th></tr></thead>
        <tbody>
        <?php foreach ($paymentSubmissions as $ps):
            $pCls = match($ps['status']) { 'approved'=>'green','rejected'=>'red', default=>'amber' };
        ?>
            <tr>
                <td>
                    <span class="tcm-badge <?= ['course'=>'purple','event'=>'amber','program'=>'green'][$ps['item_type']] ?? 'gray' ?>" style="font-size:.65rem;"><?= e(ucfirst($ps['item_type'])) ?></span>
                    <div style="font-size:.84rem;margin-top:3px;"><?= e($ps['item_title']) ?></div>
                </td>
                <td style="font-weight:700;"><?= money($ps['amount']) ?></td>
                <td class="muted" style="font-size:.8rem;"><?= e(\TCM\Models\PaymentSubmission::METHODS[$ps['payment_method']] ?? $ps['payment_method']) ?></td>
                <td class="muted" style="font-size:.8rem;"><?= e(date('d M Y', strtotime($ps['payment_date']))) ?></td>
                <td><span class="tcm-badge <?= $pCls ?>"><?= e(\TCM\Models\PaymentSubmission::STATUSES[$ps['status']]) ?></span></td>
                <td>
                    <?php if ($ps['receipt_number']): ?>
                        <span style="font-family:monospace;font-size:.75rem;color:#555;"><?= e($ps['receipt_number']) ?></span>
                    <?php else: ?>
                        <span class="muted">—</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($paymentSubmissions)): ?>
            <tr><td colspan="6" class="muted">No payment submissions.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="tcm-card" style="margin-top:18px;">
    <h3 class="mt-0">Orders</h3>
    <table class="tcm-table">
        <thead><tr><th>Order</th><th>Item</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($orders as $o): ?>
            <tr>
                <td style="font-size:.78rem;font-family:monospace;"><?= e($o['order_number']) ?></td>
                <td><?= e($o['item_title'] ?? $o['item_type']) ?></td>
                <td><?= money($o['amount']) ?></td>
                <td><span class="tcm-badge <?= $o['status'] === 'paid' ? 'green' : 'amber' ?>"><?= e($o['status']) ?></span></td>
                <td class="muted"><?= e(date('d M Y', strtotime($o['created_at']))) ?></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($orders === []): ?><tr><td colspan="5" class="muted">No orders.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
