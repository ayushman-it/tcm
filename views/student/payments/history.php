<?php
use TCM\Models\PaymentSubmission;
$pending   = count(array_filter($payments, fn($p) => $p['status'] === 'pending'));
$approved  = count(array_filter($payments, fn($p) => $p['status'] === 'approved'));
$rejected  = count(array_filter($payments, fn($p) => $p['status'] === 'rejected'));
$totalPaid = array_sum(array_map(fn($p) => $p['status'] === 'approved' ? (float)$p['amount'] : 0, $payments));
$studentId  = $user['student_id']  ?? null;
$referralId = $user['referral_id'] ?? null;
?>

<style>
/* ── Payment History Page ── */
.ph-hero {
    background: #111;
    border-radius: 18px;
    padding: 24px 28px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    position: relative;
    overflow: hidden;
}
.ph-hero::before {
    content: '';
    position: absolute;
    top: -40px; right: -40px;
    width: 180px; height: 180px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.06) 0%, transparent 70%);
    pointer-events: none;
}
.ph-hero-left h2 { font-size: 1.2rem; font-weight: 800; color: #fff; letter-spacing: -.3px; margin-bottom: 4px; }
.ph-hero-left p  { font-size: .8rem; color: rgba(255,255,255,.45); margin: 0; }
.ph-hero-btn {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 10px 20px; border-radius: 10px;
    font-size: .84rem; font-weight: 700;
    text-decoration: none; transition: .15s; flex-shrink: 0;
}
.ph-hero-btn.white  { background: #fff; color: #111; }
.ph-hero-btn.white:hover { background: #f0f0f0; color: #111; }
.ph-hero-btn.outline { background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.2); }
.ph-hero-btn.outline:hover { background: rgba(255,255,255,.18); }

/* ID strip */
.ph-id-strip { display: flex; gap: 10px; margin-bottom: 18px; flex-wrap: wrap; }
.ph-id-card {
    display: flex; align-items: center; gap: 10px;
    background: #fff; border: 1px solid #ececec;
    border-radius: 12px; padding: 11px 16px;
    flex: 1; min-width: 190px; transition: border-color .15s;
}
.ph-id-card:hover { border-color: #ddd; }
.ph-id-icon {
    width: 34px; height: 34px; border-radius: 9px;
    background: #111; color: #fff;
    display: grid; place-items: center;
    font-size: .85rem; flex-shrink: 0;
}
.ph-id-label { font-size: .62rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #aaa; }
.ph-id-value { font-size: .88rem; font-weight: 800; color: #111; font-family: 'Courier New', monospace; letter-spacing: .5px; }
.ph-id-copy  {
    margin-left: auto; background: none; border: none;
    color: #ccc; cursor: pointer; font-size: .82rem;
    padding: 4px; border-radius: 6px; transition: .15s;
}
.ph-id-copy:hover { color: #111; background: #f5f5f5; }

/* Stats */
.ph-stats { display: grid; grid-template-columns: repeat(4,1fr); gap: 10px; margin-bottom: 20px; }
.ph-stat {
    background: #fff; border: 1px solid #ececec; border-radius: 14px;
    padding: 14px 16px; transition: .15s;
}
.ph-stat:hover { border-color: #ddd; transform: translateY(-1px); }
.ph-stat-icon  { font-size: 1rem; margin-bottom: 8px; display: block; }
.ph-stat-label { font-size: .63rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #aaa; margin-bottom: 4px; }
.ph-stat-value { font-size: 1.7rem; font-weight: 800; color: #111; letter-spacing: -1px; line-height: 1; }

/* Payment card (mobile-friendly card layout instead of table) */
.ph-card-list { display: flex; flex-direction: column; gap: 12px; }
.ph-payment-card {
    background: #fff; border: 1px solid #ececec;
    border-radius: 14px; overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.ph-payment-card:hover { border-color: #ddd; box-shadow: 0 4px 16px rgba(0,0,0,.05); }
.ph-payment-card.status-pending  { border-left: 3px solid #f59e0b; }
.ph-payment-card.status-approved { border-left: 3px solid #22c55e; }
.ph-payment-card.status-rejected { border-left: 3px solid #ef4444; }

.ph-card-top {
    display: flex; align-items: flex-start;
    justify-content: space-between; gap: 12px;
    padding: 16px 18px; flex-wrap: wrap;
}
.ph-card-title { font-size: .92rem; font-weight: 700; color: #111; margin-bottom: 4px; }
.ph-card-meta  { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.ph-card-meta span { font-size: .74rem; color: #888; display: flex; align-items: center; gap: 4px; }
.ph-card-right { display: flex; flex-direction: column; align-items: flex-end; gap: 6px; flex-shrink: 0; }
.ph-card-amount { font-size: 1.05rem; font-weight: 800; color: #111; }

/* Screenshot section */
.ph-screenshot-row {
    padding: 12px 18px;
    background: #fafafa;
    border-top: 1px solid #f0f0f0;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
}
.ph-screenshot-thumb {
    width: 52px; height: 52px; border-radius: 8px;
    object-fit: cover; border: 1px solid #e5e5e5;
    cursor: pointer; flex-shrink: 0;
    transition: transform .15s, box-shadow .15s;
}
.ph-screenshot-thumb:hover { transform: scale(1.05); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.ph-no-screenshot {
    width: 52px; height: 52px; border-radius: 8px;
    border: 2px dashed #e5e5e5; background: #f5f5f5;
    display: grid; place-items: center;
    font-size: 1.1rem; color: #ccc; flex-shrink: 0;
}
.ph-screenshot-info { flex: 1; min-width: 140px; }
.ph-screenshot-info strong { display: block; font-size: .82rem; font-weight: 700; color: #111; margin-bottom: 2px; }
.ph-screenshot-info span   { font-size: .74rem; color: #888; line-height: 1.5; }

/* Upload form inline */
.ph-upload-form { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.ph-upload-label {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 14px; border-radius: 8px; cursor: pointer;
    font-size: .78rem; font-weight: 600;
    border: 1.5px solid #e5e5e5; background: #fff; color: #111;
    transition: .15s;
}
.ph-upload-label:hover { border-color: #111; background: #f5f5f5; }
.ph-upload-label.uploading { opacity: .6; pointer-events: none; }

/* Admin note */
.ph-admin-note {
    margin: 0 18px 12px;
    padding: 10px 14px;
    background: #fef2f2; border: 1px solid #fecaca;
    border-radius: 9px; font-size: .78rem; color: #dc2626;
    display: flex; align-items: flex-start; gap: 8px;
}
.ph-admin-note i { flex-shrink: 0; margin-top: 1px; }

/* Actions row */
.ph-card-actions {
    padding: 10px 18px;
    border-top: 1px solid #f0f0f0;
    display: flex; gap: 8px; align-items: center; flex-wrap: wrap;
}

/* Empty */
.ph-empty {
    text-align: center; padding: 48px 20px; color: #aaa;
}
.ph-empty i { font-size: 2.5rem; display: block; margin-bottom: 12px; color: #ddd; }

/* Screenshot lightbox */
#ph-lightbox {
    display: none; position: fixed; inset: 0; z-index: 99999;
    background: rgba(0,0,0,.85); backdrop-filter: blur(6px);
    align-items: center; justify-content: center; padding: 20px;
}
#ph-lightbox img {
    max-width: 100%; max-height: 90vh;
    border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.5);
    object-fit: contain;
}
#ph-lightbox-close {
    position: absolute; top: 16px; right: 16px;
    background: rgba(255,255,255,.15); border: none; color: #fff;
    width: 40px; height: 40px; border-radius: 50%;
    font-size: 1.1rem; cursor: pointer; display: grid; place-items: center;
    transition: .15s;
}
#ph-lightbox-close:hover { background: rgba(255,255,255,.3); }

@media(max-width:640px) {
    .ph-stats { grid-template-columns: repeat(2,1fr); }
    .ph-hero  { padding: 18px 20px; }
    .ph-card-top { flex-direction: column; }
    .ph-card-right { align-items: flex-start; flex-direction: row; }
}
</style>

<!-- ── Hero ── -->
<div class="ph-hero">
    <div class="ph-hero-left">
        <h2><i class="bi bi-receipt" style="margin-right:8px;"></i>Payment History</h2>
        <p>Track submissions, update screenshots and download approved receipts.</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?= base_url('/student/payments/submit') ?>" class="ph-hero-btn white">
            <i class="bi bi-plus-lg"></i> Add Payment
        </a>
        <a href="<?= base_url('/student') ?>" class="ph-hero-btn outline">
            <i class="bi bi-house"></i> Dashboard
        </a>
    </div>
</div>

<!-- ── Student ID & Referral ── -->
<?php if ($studentId || $referralId): ?>
<div class="ph-id-strip">
    <?php if ($studentId): ?>
    <div class="ph-id-card">
        <div class="ph-id-icon"><i class="bi bi-person-badge"></i></div>
        <div>
            <div class="ph-id-label">Student ID</div>
            <div class="ph-id-value"><?= e($studentId) ?></div>
        </div>
        <button class="ph-id-copy" onclick="phCopy('<?= e($studentId) ?>', this)" title="Copy">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
    <?php if ($referralId): ?>
    <div class="ph-id-card">
        <div class="ph-id-icon" style="background:#4ade80;">
            <i class="bi bi-share-fill"></i>
        </div>
        <div>
            <div class="ph-id-label">Referral Code</div>
            <div class="ph-id-value"><?= e($referralId) ?></div>
        </div>
        <button class="ph-id-copy" onclick="phCopy('<?= e($referralId) ?>', this)" title="Copy">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Stats ── -->
<div class="ph-stats">
    <div class="ph-stat">
        <i class="bi bi-clock-history ph-stat-icon" style="color:#f59e0b;"></i>
        <div class="ph-stat-label">Pending</div>
        <div class="ph-stat-value"><?= $pending ?></div>
    </div>
    <div class="ph-stat">
        <i class="bi bi-patch-check-fill ph-stat-icon" style="color:#22c55e;"></i>
        <div class="ph-stat-label">Approved</div>
        <div class="ph-stat-value"><?= $approved ?></div>
    </div>
    <div class="ph-stat">
        <i class="bi bi-x-circle-fill ph-stat-icon" style="color:#ef4444;"></i>
        <div class="ph-stat-label">Rejected</div>
        <div class="ph-stat-value"><?= $rejected ?></div>
    </div>
    <div class="ph-stat">
        <i class="bi bi-currency-rupee ph-stat-icon" style="color:#111;"></i>
        <div class="ph-stat-label">Total Paid</div>
        <div class="ph-stat-value" style="font-size:1.35rem;"><?= money($totalPaid) ?></div>
    </div>
</div>

<!-- ── Payment Cards ── -->
<?php if (empty($payments)): ?>

<div class="tcm-card">
    <div class="ph-empty">
        <i class="bi bi-receipt-cutoff"></i>
        <div style="font-size:.95rem;font-weight:600;color:#555;margin-bottom:6px;">No payment submissions yet</div>
        <div style="font-size:.82rem;margin-bottom:18px;">
            Pay for a course, event or program and submit your proof here.
        </div>
        <a href="<?= base_url('/student/payments/submit') ?>" class="tcm-btn primary">
            <i class="bi bi-plus-lg"></i> Submit Your First Payment
        </a>
    </div>
</div>

<?php else: ?>

<div class="ph-card-list">
<?php foreach ($payments as $p):
    $statusCls   = match($p['status']) { 'approved'=>'status-approved', 'rejected'=>'status-rejected', default=>'status-pending' };
    $badgeCls    = match($p['status']) { 'approved'=>'green', 'rejected'=>'red', default=>'amber' };
    $badgeLabel  = match($p['status']) { 'approved'=>'<i class="bi bi-check2-circle"></i> Approved', 'rejected'=>'<i class="bi bi-x-circle"></i> Rejected', default=>'<i class="bi bi-clock"></i> Under Review' };
    $typeColors  = ['course'=>'purple','event'=>'amber','program'=>'green'];
    $hasShot     = !empty($p['screenshot']);
    $shotUrl     = $hasShot ? base_url('/uploads/payments/' . basename($p['screenshot'])) : '';
    $isPdf       = $hasShot && str_ends_with(strtolower($p['screenshot']), '.pdf');
    $canUpdate   = in_array($p['status'], ['pending','rejected'], true);
?>
<div class="ph-payment-card <?= $statusCls ?>">

    <!-- Top section -->
    <div class="ph-card-top">
        <div style="flex:1;min-width:0;">
            <div class="ph-card-title"><?= e($p['item_title']) ?></div>
            <div class="ph-card-meta">
                <span class="tcm-badge <?= $typeColors[$p['item_type']] ?? 'gray' ?>" style="font-size:.65rem;">
                    <?= e(ucfirst($p['item_type'])) ?>
                </span>
                <span><i class="bi bi-calendar3"></i> <?= e(date('d M Y', strtotime($p['payment_date']))) ?></span>
                <span><i class="bi bi-credit-card"></i> <?= e(PaymentSubmission::METHODS[$p['payment_method']] ?? $p['payment_method']) ?></span>
                <?php if ($p['transaction_ref']): ?>
                    <span><i class="bi bi-hash"></i> <?= e($p['transaction_ref']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="ph-card-right">
            <div class="ph-card-amount"><?= money($p['amount']) ?></div>
            <span class="tcm-badge <?= $badgeCls ?>"><?= $badgeLabel ?></span>
        </div>
    </div>

    <!-- Admin rejection note -->
    <?php if ($p['status'] === 'rejected' && $p['admin_note']): ?>
    <div class="ph-admin-note">
        <i class="bi bi-exclamation-circle-fill"></i>
        <div>
            <strong>Rejection reason:</strong> <?= e($p['admin_note']) ?>
            <?php if ($canUpdate): ?>
                <br><span style="color:#b45309;">Please update your screenshot below and resubmit.</span>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Screenshot row -->
    <div class="ph-screenshot-row">
        <!-- Thumbnail / placeholder -->
        <?php if ($hasShot && !$isPdf): ?>
            <img src="<?= e($shotUrl) ?>" alt="Payment proof"
                 class="ph-screenshot-thumb"
                 onclick="phLightbox('<?= e($shotUrl) ?>')"
                 title="Click to view full size">
        <?php elseif ($hasShot && $isPdf): ?>
            <a href="<?= e($shotUrl) ?>" target="_blank"
               style="display:flex;flex-direction:column;align-items:center;gap:3px;text-decoration:none;flex-shrink:0;">
                <div style="width:52px;height:52px;border-radius:8px;background:#fef2f2;border:1px solid #fecaca;display:grid;place-items:center;font-size:1.4rem;color:#dc2626;">
                    <i class="bi bi-file-pdf-fill"></i>
                </div>
                <span style="font-size:.62rem;color:#888;">PDF</span>
            </a>
        <?php else: ?>
            <div class="ph-no-screenshot" title="No screenshot uploaded">
                <i class="bi bi-image"></i>
            </div>
        <?php endif; ?>

        <!-- Info -->
        <div class="ph-screenshot-info">
            <?php if ($hasShot): ?>
                <strong>Screenshot uploaded</strong>
                <span>
                    <?php if ($p['status'] === 'pending'): ?>
                        Admin is reviewing this. You can update if needed.
                    <?php elseif ($p['status'] === 'rejected'): ?>
                        Update with a clearer screenshot and it will be reviewed again.
                    <?php else: ?>
                        Payment verified ✓
                    <?php endif; ?>
                </span>
            <?php else: ?>
                <strong style="color:#dc2626;">No screenshot</strong>
                <span>Upload your payment proof to get verified faster.</span>
            <?php endif; ?>
        </div>

        <!-- Update screenshot form (for pending/rejected) -->
        <?php if ($canUpdate): ?>
        <form method="post"
              action="<?= base_url('/student/payments/' . (int)$p['id'] . '/screenshot') ?>"
              enctype="multipart/form-data"
              class="ph-upload-form"
              id="uploadForm<?= (int)$p['id'] ?>">
            <?= csrf_field() ?>
            <label class="ph-upload-label" for="shot<?= (int)$p['id'] ?>">
                <i class="bi bi-cloud-upload"></i>
                <?= $hasShot ? 'Update Screenshot' : 'Upload Screenshot' ?>
                <input type="file"
                       id="shot<?= (int)$p['id'] ?>"
                       name="screenshot"
                       accept="image/jpeg,image/png,image/webp,application/pdf"
                       style="display:none;"
                       onchange="phAutoSubmit(this, <?= (int)$p['id'] ?>)">
            </label>
            <button type="submit" id="submitBtn<?= (int)$p['id'] ?>"
                    class="tcm-btn sm primary" style="display:none;">
                <i class="bi bi-send"></i> Submit
            </button>
        </form>
        <?php endif; ?>
    </div>

    <!-- Actions -->
    <div class="ph-card-actions">
        <?php if ($p['status'] === 'approved' && $p['receipt_number']): ?>
            <a href="<?= base_url('/student/payments/' . (int)$p['id'] . '/receipt') ?>"
               target="_blank" class="tcm-btn sm primary">
                <i class="bi bi-download"></i> Download Receipt
            </a>
            <span style="font-size:.73rem;color:#888;font-family:monospace;">
                <?= e($p['receipt_number']) ?>
            </span>
        <?php elseif ($p['status'] === 'pending'): ?>
            <span style="font-size:.78rem;color:#888;display:flex;align-items:center;gap:5px;">
                <span style="width:7px;height:7px;border-radius:50%;background:#f59e0b;display:inline-block;animation:phPulse 1.5s infinite;"></span>
                Under review — usually within 24 hours
            </span>
        <?php elseif ($p['status'] === 'rejected'): ?>
            <span style="font-size:.78rem;color:#dc2626;display:flex;align-items:center;gap:5px;">
                <i class="bi bi-arrow-up-circle"></i>
                Update your screenshot above to request re-review
            </span>
        <?php endif; ?>
        <div style="margin-left:auto;font-size:.72rem;color:#bbb;">
            Submitted <?= e(date('d M Y', strtotime($p['created_at']))) ?>
        </div>
    </div>

</div>
<?php endforeach; ?>
</div>

<?php endif; ?>

<!-- How it works -->
<div class="tcm-card" style="margin-top:20px;background:#f9f9f9;border-color:#e8e8e8;">
    <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:14px;">
        <i class="bi bi-info-circle"></i> How Payment Verification Works
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:14px;">
        <?php
        $steps = [
            ['bi-cash-coin',     '#f59e0b', 'Pay',          'UPI, cash or bank transfer'],
            ['bi-cloud-upload',  '#3b82f6', 'Upload Proof', 'Screenshot of your payment'],
            ['bi-person-check',  '#8b5cf6', 'Admin Reviews','Within 24 hours'],
            ['bi-unlock-fill',   '#22c55e', 'Access Unlocked','Download receipt anytime'],
        ];
        foreach ($steps as [$icon, $color, $title, $sub]):
        ?>
        <div style="display:flex;align-items:flex-start;gap:10px;">
            <div style="width:34px;height:34px;border-radius:10px;background:<?= $color ?>;display:grid;place-items:center;font-size:.85rem;color:#fff;flex-shrink:0;">
                <i class="bi <?= $icon ?>"></i>
            </div>
            <div>
                <div style="font-size:.82rem;font-weight:700;color:#111;"><?= $title ?></div>
                <div style="font-size:.74rem;color:#888;margin-top:2px;"><?= $sub ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Screenshot lightbox -->
<div id="ph-lightbox" onclick="if(event.target===this)phCloseLightbox()">
    <button id="ph-lightbox-close" onclick="phCloseLightbox()">
        <i class="bi bi-x-lg"></i>
    </button>
    <img id="ph-lightbox-img" src="" alt="Payment proof">
</div>

<style>
@keyframes phPulse { 0%,100%{opacity:1} 50%{opacity:.3} }
</style>

<script>
// Copy to clipboard
function phCopy(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2" style="color:#22c55e;"></i>';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}

// Lightbox
function phLightbox(src) {
    document.getElementById('ph-lightbox-img').src = src;
    var lb = document.getElementById('ph-lightbox');
    lb.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function phCloseLightbox() {
    document.getElementById('ph-lightbox').style.display = 'none';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') phCloseLightbox();
});

// Auto-submit form when file selected + show preview filename
function phAutoSubmit(input, id) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        alert('File too large. Max 5MB allowed.');
        input.value = '';
        return;
    }
    // Show submit button + label with filename
    var label = input.closest('.ph-upload-label') || input.parentElement;
    if (label) {
        label.innerHTML = '<i class="bi bi-file-earmark-check" style="color:#22c55e;"></i> ' +
            file.name.substring(0, 20) + (file.name.length > 20 ? '…' : '');
        label.classList.add('uploading');
    }
    var submitBtn = document.getElementById('submitBtn' + id);
    if (submitBtn) { submitBtn.style.display = 'inline-flex'; }
    // Auto-submit after short delay for UX
    setTimeout(function() {
        var form = document.getElementById('uploadForm' + id);
        if (form) form.submit();
    }, 800);
}
</script>
