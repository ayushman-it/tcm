<?php
use TCM\Models\Wallet;
$balance = $wallet['balance'] ?? 0;
?>
<style>
.wallet-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px; margin-bottom: 20px; }
.wallet-card { 
    background: #fff; border: 1px solid #ececec; border-radius: 14px; padding: 18px 20px; 
    display: flex; flex-direction: column; gap: 8px;
}
.wallet-card.primary {
    background: #111; 
    color: #fff; border: none;
}
.wallet-balance {
    font-size: 2rem; font-weight: 800; color: #fff; 
    margin: 8px 0; font-family: 'Segoe UI', system-ui;
}
.wallet-label { 
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase; 
    letter-spacing: 0.08em; color: rgba(255,255,255,0.6); 
}
.wallet-section { 
    background: #fff; border: 1px solid #ececec; border-radius: 14px; 
    padding: 18px 20px; margin-bottom: 16px;
}
.wallet-section-title {
    font-size: 0.85rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.08em; color: #aaa; margin-bottom: 12px;
    padding-bottom: 10px; border-bottom: 1px solid #f5f5f5;
    display: flex; align-items: center; gap: 8px;
}
.wallet-tx { 
    padding: 12px 0; border-bottom: 1px solid #f5f5f5; 
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
}
.wallet-tx:last-child { border-bottom: none; }
.wallet-tx-info { flex: 1; min-width: 0; }
.wallet-tx-title { font-size: 0.88rem; font-weight: 600; color: #111; }
.wallet-tx-meta { font-size: 0.72rem; color: #888; margin-top: 2px; }
.wallet-tx-amount { 
    font-size: 1rem; font-weight: 800; flex-shrink: 0;
    font-family: 'Consolas', 'Monaco', monospace;
}
.wallet-tx-amount.credit { color: #16a34a; }
.wallet-tx-amount.debit { color: #dc2626; }
.withdraw-status {
    padding: 4px 10px; border-radius: 12px; font-size: 0.7rem; 
    font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;
}
.withdraw-status.pending { background: #fef3c7; color: #92400e; }
.withdraw-status.approved { background: #dcfce7; color: #166534; }
.withdraw-status.rejected { background: #fee2e2; color: #991b1b; }
.empty-state {
    padding: 40px 20px; text-align: center; color: #aaa;
}
.empty-state i { font-size: 2.5rem; opacity: 0.3; margin-bottom: 12px; }
</style>

<div class="wallet-grid">
    <!-- Balance Card -->
    <div class="wallet-card primary">
        <div class="wallet-label"><i class="bi bi-wallet2"></i> Available Balance</div>
        <div class="wallet-balance">₹<?= number_format($balance, 2) ?></div>
        <button class="tcm-btn" style="background: rgba(255,255,255,0.15); color: #fff; margin-top: 8px;"
                onclick="document.getElementById('withdrawModal').style.display='flex'">
            <i class="bi bi-arrow-up-circle-fill"></i> Request Withdrawal
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="wallet-card">
        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #aaa;">
            <i class="bi bi-graph-up"></i> Total Earned
        </div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #111; margin-top: 4px;">
            ₹<?= number_format(array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'credit'), 'amount')), 2) ?>
        </div>
    </div>

    <div class="wallet-card">
        <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #aaa;">
            <i class="bi bi-arrow-down-circle"></i> Total Withdrawn
        </div>
        <div style="font-size: 1.5rem; font-weight: 800; color: #111; margin-top: 4px;">
            ₹<?= number_format(array_sum(array_column(array_filter($transactions, fn($t) => $t['type'] === 'debit'), 'amount')), 2) ?>
        </div>
    </div>
</div>

<script>
function copyReferralCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
        btn.style.background = '#10b981';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.style.background = '#059669';
        }, 2000);
    });
}
</script>

<!-- Pending Withdrawals -->
<?php if (!empty($withdrawals)): ?>
<div class="wallet-section">
    <div class="wallet-section-title"><i class="bi bi-clock-history"></i> Withdrawal Requests</div>
    <?php foreach ($withdrawals as $wd): ?>
    <div class="wallet-tx">
        <div class="wallet-tx-info">
            <div class="wallet-tx-title">
                Withdrawal Request · <?= e($wd['upi_id']) ?>
            </div>
            <div class="wallet-tx-meta">
                <?= date('d M Y, h:i A', strtotime($wd['created_at'])) ?>
                <?php if ($wd['status'] === 'approved' && $wd['processed_at']): ?>
                    · Processed: <?= date('d M Y', strtotime($wd['processed_at'])) ?>
                <?php endif; ?>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <div class="wallet-tx-amount debit">-₹<?= number_format((float)$wd['amount'], 2) ?></div>
            <span class="withdraw-status <?= e($wd['status']) ?>">
                <?= e(ucfirst($wd['status'])) ?>
            </span>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Referral Stats & My Referrals -->
<?php if ($user['referral_id']): ?>
<div class="wallet-section" style="background: #f9f9f9; border: 1px solid #e5e5e5;">
    <div class="wallet-section-title" style="color: #111; border-color: #e5e5e5;">
        <i class="bi bi-gift-fill"></i> Your Referral Program
    </div>
    
    <!-- Referral Code -->
    <div style="background: rgba(255,255,255,0.7); padding: 16px; border-radius: 10px; margin-bottom: 14px;">
        <div style="font-size: 0.75rem; font-weight: 700; color: #059669; margin-bottom: 6px;">YOUR REFERRAL CODE</div>
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px;">
            <div style="font-size: 1.4rem; font-weight: 800; font-family: monospace; color: #047857;">
                <?= e($user['referral_id']) ?>
            </div>
            <button onclick="copyReferralCode('<?= e($user['referral_id']) ?>')" 
                    class="tcm-btn sm" style="background: #059669; border-color: #059669; color: #fff;">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>
        <div style="font-size: 0.72rem; color: #047857; margin-top: 6px;">
            💰 Earn ₹<?= number_format(Wallet::REFERRAL_CREDIT, 0) ?> for each successful referral!
        </div>
    </div>

    <!-- Referral Stats -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px;">
        <div style="background: rgba(255,255,255,0.7); padding: 12px; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.3rem; font-weight: 800; color: #047857;">
                <?= (int)($referralStats['total_referrals'] ?? 0) ?>
            </div>
            <div style="font-size: 0.7rem; font-weight: 600; color: #059669;">Total Referrals</div>
        </div>
        <div style="background: rgba(255,255,255,0.7); padding: 12px; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.3rem; font-weight: 800; color: #047857;">
                <?= (int)($referralStats['approved_referrals'] ?? 0) ?>
            </div>
            <div style="font-size: 0.7rem; font-weight: 600; color: #059669;">Approved</div>
        </div>
        <div style="background: rgba(255,255,255,0.7); padding: 12px; border-radius: 8px; text-align: center;">
            <div style="font-size: 1.3rem; font-weight: 800; color: #047857;">
                ₹<?= number_format($referralStats['total_earned_from_referrals'] ?? 0, 0) ?>
            </div>
            <div style="font-size: 0.7rem; font-weight: 600; color: #059669;">Earned</div>
        </div>
    </div>

    <!-- My Referrals List -->
    <?php if (!empty($myReferrals)): ?>
    <details style="background: rgba(255,255,255,0.7); padding: 14px; border-radius: 8px;">
        <summary style="font-size: 0.82rem; font-weight: 700; color: #047857; cursor: pointer; user-select: none;">
            View My Referrals (<?= count($myReferrals) ?>)
        </summary>
        <div style="margin-top: 12px;">
            <?php foreach ($myReferrals as $ref): ?>
            <div style="padding: 10px 0; border-bottom: 1px solid #d1fae5; display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: #111;"><?= e($ref['name']) ?></div>
                    <div style="font-size: 0.7rem; color: #059669; margin-top: 2px;">
                        <?= e($ref['item_title']) ?> · ₹<?= number_format($ref['amount'], 0) ?>
                    </div>
                    <div style="font-size: 0.68rem; color: #6b7280; margin-top: 2px;">
                        <?= date('d M Y', strtotime($ref['created_at'])) ?>
                    </div>
                </div>
                <span class="tcm-badge <?= $ref['status'] === 'approved' ? 'green' : ($ref['status'] === 'pending' ? 'amber' : 'gray') ?>" 
                      style="font-size: 0.65rem;">
                    <?= ucfirst($ref['status']) ?>
                </span>
            </div>
            <?php endforeach; ?>
        </div>
    </details>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Transaction History -->
<div class="wallet-section">
    <div class="wallet-section-title"><i class="bi bi-clock-history"></i> Transaction History</div>
    <?php if (empty($transactions)): ?>
    <div class="empty-state">
        <i class="bi bi-receipt"></i>
        <div style="font-size: 0.88rem; font-weight: 600; margin-bottom: 4px;">No transactions yet</div>
        <div style="font-size: 0.75rem;">Your earnings and withdrawals will appear here</div>
    </div>
    <?php else: ?>
        <?php foreach ($transactions as $tx): ?>
        <div class="wallet-tx">
            <div class="wallet-tx-info">
                <div class="wallet-tx-title"><?= e($tx['description']) ?></div>
                <div class="wallet-tx-meta">
                    <?= date('d M Y, h:i A', strtotime($tx['created_at'])) ?>
                    <?php if (!empty($tx['reference'])): ?>
                        · Ref: <?= e($tx['reference']) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="wallet-tx-amount <?= $tx['type'] ?>">
                <?= $tx['type'] === 'credit' ? '+' : '-' ?>₹<?= number_format((float)$tx['amount'], 2) ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Withdrawal Modal -->
<div id="withdrawModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); 
     z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #fff; border-radius: 16px; padding: 24px; max-width: 420px; width: 100%;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
            <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0;">Request Withdrawal</h3>
            <button onclick="document.getElementById('withdrawModal').style.display='none'" 
                    class="tcm-btn ghost sm" style="padding: 6px 10px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <form method="post" action="<?= base_url('/student/wallet/withdraw') ?>">
            <?= csrf_field() ?>
            <div class="tcm-field" style="margin-bottom: 12px;">
                <label>Amount (₹)</label>
                <input class="tcm-input" type="number" name="amount" step="1" min="<?= $min ?>"
                       max="<?= (int)$balance ?>" placeholder="<?= $min ?>" required>
                <div style="font-size: 0.72rem; color: #888; margin-top: 4px;">
                    Minimum: ₹<?= $min ?> · Available: ₹<?= number_format($balance, 2) ?>
                </div>
            </div>
            <div class="tcm-field" style="margin-bottom: 16px;">
                <label>UPI ID</label>
                <input class="tcm-input" type="text" name="upi_id" placeholder="yourname@paytm" required>
                <div style="font-size: 0.72rem; color: #888; margin-top: 4px;">
                    <i class="bi bi-info-circle"></i> Payment will be sent to this UPI ID
                </div>
            </div>
            <button type="submit" class="tcm-btn primary w-full" style="justify-content: center;">
                <i class="bi bi-send-check-fill"></i> Submit Request
            </button>
            <p style="text-align: center; font-size: 0.7rem; color: #aaa; margin-top: 10px;">
                Requests are processed within 48 hours
            </p>
        </form>
    </div>
</div>
