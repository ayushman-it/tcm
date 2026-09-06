<?php use TCM\Models\Wallet; ?>

<div class="tcm-page-head">
    <div>
        <h2>💰 Wallet & Withdrawals</h2>
        <p>Manage student wallet transactions, withdrawal requests, and referral earnings.</p>
    </div>
</div>

<!-- Stats -->
<div class="tcm-stat-grid" style="margin-bottom:18px;grid-template-columns:repeat(3,1fr);">
    <div class="tcm-stat" style="border-color:#fde68a;background:#fffbeb;">
        <i class="bi bi-clock-history icon" style="color:#d97706;"></i>
        <div class="label">Pending Withdrawals</div>
        <div class="value"><?= $stats['pending'] ?></div>
    </div>
    <div class="tcm-stat" style="border-color:#bbf7d0;background:#f0fdf4;">
        <i class="bi bi-check-circle icon" style="color:#16a34a;"></i>
        <div class="label">Approved</div>
        <div class="value"><?= $stats['approved'] ?></div>
    </div>
    <div class="tcm-stat" style="border-color:#ddd6fe;background:#faf5ff;">
        <i class="bi bi-cash-stack icon" style="color:#7c3aed;"></i>
        <div class="label">Total Paid Out</div>
        <div class="value">₹<?= number_format($stats['total_paid'], 0) ?></div>
    </div>
</div>

<!-- Tabs -->
<div style="border-bottom: 2px solid #f3f4f6; margin-bottom: 20px;">
    <div style="display: flex; gap: 4px;">
        <button onclick="showTab('withdrawals')" id="tab-withdrawals" class="tab-btn active">
            <i class="bi bi-cash-coin"></i> Withdrawal Requests
        </button>
        <button onclick="showTab('transactions')" id="tab-transactions" class="tab-btn">
            <i class="bi bi-list-ul"></i> All Transactions
        </button>
        <button onclick="showTab('referrals')" id="tab-referrals" class="tab-btn">
            <i class="bi bi-gift"></i> Referral Tree
        </button>
    </div>
</div>

<style>
.tab-btn {
    padding: 10px 18px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.88rem;
    cursor: pointer;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
}
.tab-btn:hover {
    color: #334155;
    background: #f8fafc;
}
.tab-btn.active {
    color: #667eea;
    border-bottom-color: #667eea;
}
.tab-content {
    display: none;
}
.tab-content.active {
    display: block;
}
</style>

<!-- Tab 1: Withdrawal Requests -->
<div id="content-withdrawals" class="tab-content active">
    <div class="tcm-card">
        <form method="get" style="display:flex;gap:10px;margin-bottom:16px;">
            <select class="tcm-select" name="status" style="width:170px;" onchange="this.form.submit()">
                <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                <option value="approved" <?= ($_GET['status'] ?? '') === 'approved' ? 'selected' : '' ?>>Approved</option>
                <option value="rejected" <?= ($_GET['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </form>

        <?php if (empty($requests)): ?>
            <div class="tcm-empty">
                <i class="bi bi-wallet2"></i>
                No withdrawal requests found.
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="tcm-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Amount</th>
                        <th>UPI ID</th>
                        <th>Current Balance</th>
                        <th>Status</th>
                        <th>Requested</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $req):
                    $statusColor = match($req['status']) {
                        'approved' => 'green',
                        'rejected' => 'red',
                        default => 'amber'
                    };
                ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:.88rem;"><?= e($req['student_name']) ?></div>
                            <div style="font-size:.73rem;color:var(--muted);"><?= e($req['student_email']) ?></div>
                        </td>
                        <td style="font-weight:700;font-size:.95rem;">₹<?= number_format($req['amount'], 0) ?></td>
                        <td style="font-family:monospace;font-size:.85rem;"><?= e($req['upi_id']) ?></td>
                        <td style="font-weight:600;color:#059669;">₹<?= number_format($req['current_balance'] ?? 0, 0) ?></td>
                        <td>
                            <span class="tcm-badge <?= $statusColor ?>"><?= ucfirst($req['status']) ?></span>
                            <?php if ($req['admin_note']): ?>
                                <div style="font-size:.72rem;color:var(--muted);margin-top:3px;"><?= e($req['admin_note']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:.82rem;color:var(--muted);">
                            <?= date('d M Y', strtotime($req['created_at'])) ?>
                        </td>
                        <td>
                            <?php if ($req['status'] === 'pending'): ?>
                                <div style="display:flex;gap:6px;">
                                    <form method="post" action="<?= base_url('/admin/wallet/' . $req['id'] . '/approve') ?>" style="margin:0;">
                                        <input type="hidden" name="note" value="Approved">
                                        <button class="tcm-btn sm primary" title="Approve">
                                            <i class="bi bi-check-lg"></i> Approve
                                        </button>
                                    </form>
                                    <form method="post" action="<?= base_url('/admin/wallet/' . $req['id'] . '/reject') ?>" style="margin:0;">
                                        <input type="hidden" name="note" value="Rejected">
                                        <button class="tcm-btn sm" style="background:#ef4444;border-color:#ef4444;" title="Reject">
                                            <i class="bi bi-x-lg"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span style="font-size:.75rem;color:#94a3b8;">
                                    <?= $req['processed_at'] ? date('d M Y', strtotime($req['processed_at'])) : '—' ?>
                                </span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab 2: All Transactions -->
<div id="content-transactions" class="tab-content">
    <div class="tcm-card">
        <h3 style="font-size:1rem;margin-bottom:14px;color:#111;">
            <i class="bi bi-list-ul"></i> Recent Wallet Transactions (Last 100)
        </h3>
        
        <?php if (empty($allTransactions)): ?>
            <div class="tcm-empty">
                <i class="bi bi-receipt"></i>
                No wallet transactions yet.
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="tcm-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Student</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Description</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allTransactions as $txn): ?>
                    <tr>
                        <td style="font-size:.82rem;color:var(--muted);">
                            <?= date('d M Y H:i', strtotime($txn['created_at'])) ?>
                        </td>
                        <td>
                            <div style="font-weight:600;font-size:.85rem;"><?= e($txn['student_name']) ?></div>
                            <div style="font-size:.7rem;color:var(--muted);font-family:monospace;"><?= e($txn['student_ref_code'] ?? '—') ?></div>
                        </td>
                        <td>
                            <?php if ($txn['type'] === 'credit'): ?>
                                <span class="tcm-badge green">+ Credit</span>
                            <?php else: ?>
                                <span class="tcm-badge red">- Debit</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:700;font-size:.9rem;<?= $txn['type'] === 'credit' ? 'color:#16a34a;' : 'color:#dc2626;' ?>">
                            <?= $txn['type'] === 'credit' ? '+' : '-' ?>₹<?= number_format($txn['amount'], 0) ?>
                        </td>
                        <td style="font-size:.82rem;"><?= e($txn['description']) ?></td>
                        <td style="font-weight:600;font-size:.85rem;">₹<?= number_format($txn['balance_after'], 0) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Tab 3: Referral Tree -->
<div id="content-referrals" class="tab-content">
    <div class="tcm-card">
        <h3 style="font-size:1rem;margin-bottom:14px;color:#111;">
            <i class="bi bi-gift"></i> Referral Performance (Who Brought Students)
        </h3>
        
        <?php if (empty($referralStats)): ?>
            <div class="tcm-empty">
                <i class="bi bi-people"></i>
                No referrals recorded yet.
            </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="tcm-table">
                <thead>
                    <tr>
                        <th>Referrer</th>
                        <th>Referral Code</th>
                        <th>Total Referrals</th>
                        <th>Approved Payments</th>
                        <th>Total Earned</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($referralStats as $stat): ?>
                    <tr>
                        <td>
                            <div style="font-weight:600;font-size:.88rem;"><?= e($stat['referrer_name']) ?></div>
                            <div style="font-size:.73rem;color:var(--muted);">ID: <?= e($stat['referrer_id']) ?></div>
                        </td>
                        <td>
                            <span style="font-family:monospace;font-weight:600;color:#059669;background:#d1fae5;padding:4px 10px;border-radius:6px;font-size:.8rem;">
                                <?= e($stat['referral_code']) ?>
                            </span>
                        </td>
                        <td style="font-weight:600;font-size:.9rem;text-align:center;">
                            <?= (int)$stat['total_referrals'] ?>
                        </td>
                        <td style="font-weight:600;font-size:.9rem;text-align:center;color:#16a34a;">
                            <?= (int)$stat['approved_referrals'] ?>
                        </td>
                        <td style="font-weight:700;font-size:.95rem;color:#7c3aed;">
                            ₹<?= number_format($stat['total_earned'], 0) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:16px;padding:12px;background:#fef3c7;border-left:3px solid #f59e0b;border-radius:6px;font-size:.82rem;">
            <strong>💡 Note:</strong> Each approved referral earns ₹<?= number_format(Wallet::REFERRAL_CREDIT, 0) ?> for the referrer.
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function showTab(tab) {
    // Hide all
    document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    // Show selected
    document.getElementById('content-' + tab).classList.add('active');
    document.getElementById('tab-' + tab).classList.add('active');
}
</script>
