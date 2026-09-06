<?php
use TCM\Models\PaymentSubmission;
use TCM\Models\Setting;

$isPreFilled = ($item !== null);
$prePrice    = $item ? (float)($item['price'] ?? 0) : 0;
$preType     = $itemType ?: 'course';
$preId       = $itemId   ?: 0;

// Load payment settings (set by admin)
$upiId      = Setting::get('payment_upi_id',      'thecodemunk@upi');
$upiName    = Setting::get('payment_upi_name',     'The Code Munk');
$bankName   = Setting::get('payment_bank_name',    '');
$bankHolder = Setting::get('payment_account_name', '');
$bankAcc    = Setting::get('payment_account_number','');
$bankIfsc   = Setting::get('payment_ifsc',         '');
$payNote    = Setting::get('payment_note',         'After payment, upload your screenshot below. Access will be activated within 24 hours of verification.');
$waNumber   = Setting::get('whatsapp_number',      '919999999999');
?>
<style>
.ps2-page  { max-width: 900px; margin: 0 auto; }
.ps2-grid  { display: grid; grid-template-columns: 1fr 300px; gap: 16px; align-items: start; }
.ps2-card  { background:#fff; border:1px solid #ececec; border-radius:14px; padding:18px 20px; margin-bottom:12px; }
.ps2-card:last-child { margin-bottom:0; }
.ps2-label {
    font-size:.68rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.09em; color:#aaa;
    margin-bottom:12px; padding-bottom:9px;
    border-bottom:1px solid #f5f5f5;
    display:flex; align-items:center; gap:7px;
}
/* Drop zone */
.ps2-drop {
    border:2px dashed #ddd; border-radius:12px; padding:26px 16px;
    text-align:center; cursor:pointer; transition:all .2s; background:#fafafa;
}
.ps2-drop:hover, .ps2-drop.over { border-color:#111; background:#f5f5f5; }
.ps2-drop.done { border-color:#22c55e; border-style:solid; background:#f0fdf4; }
.ps2-drop-icon {
    width:52px; height:52px; border-radius:50%;
    background:#f0f0f0; border:1px solid #e5e5e5;
    display:grid; place-items:center; font-size:1.3rem; color:#888;
    margin:0 auto 12px; transition:all .2s;
}
.ps2-drop:hover .ps2-drop-icon { background:#111; color:#fff; border-color:#111; }
.ps2-drop.done  .ps2-drop-icon { background:#dcfce7; color:#16a34a; border-color:#bbf7d0; }
.ps2-drop h4 { font-size:.88rem; font-weight:700; color:#111; margin:0 0 3px; }
.ps2-drop p  { font-size:.73rem; color:#aaa; margin:0; }
.ps2-drop u  { color:#111; cursor:pointer; }
.ps2-preview { display:none; text-align:center; }
.ps2-preview img { max-height:160px; border-radius:9px; border:1px solid #e5e5e5; margin:0 auto 8px; display:block; object-fit:contain; }
.ps2-preview-change { font-size:.72rem; color:#888; text-decoration:underline; cursor:pointer; }
/* Method tiles */
.ps2-methods { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.ps2-method {
    border:1.5px solid #ececec; border-radius:10px;
    padding:10px 12px; cursor:pointer; transition:.15s;
    display:flex; align-items:center; gap:9px;
}
.ps2-method:hover   { border-color:#bbb; }
.ps2-method.active  { border-color:#111; background:#fafafa; }
.ps2-method-emoji   { font-size:1.1rem; line-height:1; flex-shrink:0; }
.ps2-method strong  { display:block; font-size:.8rem; font-weight:700; color:#111; }
.ps2-method span    { font-size:.7rem; color:#888; }
/* Pay-to card */
.ps2-payto {
    background:#f9f9f9; border:1px solid #ececec;
    border-radius:11px; padding:12px 14px; margin-bottom:10px;
}
.ps2-payto-label { font-size:.65rem; font-weight:700; text-transform:uppercase; letter-spacing:.08em; color:#aaa; margin-bottom:5px; }
.ps2-payto-value { font-size:.88rem; font-weight:800; color:#111; font-family:monospace; letter-spacing:.3px; }
.ps2-payto-sub   { font-size:.72rem; color:#888; margin-top:1px; }
/* Submit */
.ps2-submit {
    width:100%; padding:13px; background:#111; color:#fff;
    border:none; border-radius:11px; font-size:.92rem;
    font-weight:700; font-family:inherit; cursor:pointer;
    display:flex; align-items:center; justify-content:center;
    gap:8px; transition:background .15s, transform .1s; margin-top:2px;
}
.ps2-submit:hover  { background:#333; transform:translateY(-1px); }
.ps2-submit:active { transform:scale(.98); }
/* Item banner */
.ps2-item-banner {
    background:#111; border-radius:14px;
    padding:14px 18px; margin-bottom:14px;
    display:flex; align-items:center; gap:14px;
}
.ps2-item-icon {
    width:42px; height:42px; border-radius:10px;
    background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.18);
    display:grid; place-items:center; font-size:1rem; color:#fff; flex-shrink:0;
}
/* Type tabs */
.ps2-tabs { display:flex; gap:6px; margin-bottom:10px; flex-wrap:wrap; }
.ps2-tab {
    padding:6px 13px; border-radius:20px; font-size:.77rem; font-weight:600;
    cursor:pointer; border:1.5px solid #e5e5e5; background:#fff; color:#888; transition:.15s;
}
.ps2-tab.active { background:#111; color:#fff; border-color:#111; }
@media(max-width:720px) {
    .ps2-grid { grid-template-columns:1fr; }
    .ps2-methods { grid-template-columns:1fr 1fr; }
}
@media(max-width:400px) { .ps2-methods { grid-template-columns:1fr; } }
</style>

<div class="ps2-page">

<!-- Header -->
<div class="tcm-page-head" style="margin-bottom:16px;">
    <div>
        <h2 style="font-size:1.25rem;">Submit Payment</h2>
        <p style="margin:2px 0 0;font-size:.82rem;color:#888;">
            <?= $isPreFilled ? 'Pay for <strong>' . e($item['title']) . '</strong> and upload proof to get access.' : 'Already paid? Add your payment here.' ?>
        </p>
    </div>
    <a href="<?= base_url('/student/payments') ?>" class="tcm-btn">
        <i class="bi bi-arrow-left"></i> History
    </a>
</div>

<!-- Pre-filled banner -->
<?php if ($isPreFilled): ?>
<div class="ps2-item-banner">
    <div class="ps2-item-icon">
        <i class="bi <?= $preType==='course' ? 'bi-journal-code' : ($preType==='event' ? 'bi-calendar-event' : 'bi-stack') ?>"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.45);"><?= e(ucfirst($preType)) ?></div>
        <div style="font-size:.95rem;font-weight:800;color:#fff;"><?= e($item['title']) ?></div>
    </div>
    <?php if ($prePrice > 0): ?>
    <div style="text-align:right;flex-shrink:0;">
        <div style="font-size:1.1rem;font-weight:800;color:#fff;"><?= money($prePrice) ?></div>
        <div style="font-size:.68rem;color:rgba(255,255,255,.4);">to pay</div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="ps2-grid">

<!-- ═══ LEFT: FORM ═══ -->
<form method="post" action="<?= base_url('/student/payments/submit') ?>"
      enctype="multipart/form-data" id="ps2Form">
    <?= csrf_field() ?>
    <input type="hidden" name="item_type" id="ps2ItemType" value="<?= e($preType) ?>">
    <input type="hidden" name="item_id"   id="ps2ItemId"   value="<?= e((string)$preId) ?>">

    <?php if (!$isPreFilled): ?>
    <!-- What are you paying for -->
    <div class="ps2-card">
        <div class="ps2-label"><i class="bi bi-tag"></i> What are you paying for?</div>
        <div class="ps2-tabs" id="ps2Tabs">
            <button type="button" class="ps2-tab active" onclick="ps2SwitchType('course',this)">📚 Course</button>
            <button type="button" class="ps2-tab" onclick="ps2SwitchType('event',this)">🗓 Event</button>
            <button type="button" class="ps2-tab" onclick="ps2SwitchType('program',this)">🎓 Program</button>
            <button type="button" class="ps2-tab" onclick="ps2SwitchType('other',this)">✏️ Other</button>
        </div>
        <div id="ps2SelCourse">
            <select class="tcm-select" id="selCourse" onchange="ps2PickItem(this)">
                <option value="">— Choose course —</option>
                <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>" data-price="<?= (float)$c['price'] ?>" data-title="<?= e(addslashes($c['title'])) ?>">
                    <?= e($c['title']) ?><?= (float)$c['price']>0 ? ' — '.money($c['price']) : ' (Free)' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="ps2SelEvent" style="display:none">
            <select class="tcm-select" id="selEvent" onchange="ps2PickItem(this)">
                <option value="">— Choose event —</option>
                <?php foreach ($events as $ev): ?>
                <option value="<?= $ev['id'] ?>" data-price="<?= (float)$ev['price'] ?>" data-title="<?= e(addslashes($ev['title'])) ?>">
                    <?= e($ev['title']) ?><?= (float)$ev['price']>0 ? ' — '.money($ev['price']) : ' (Free)' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="ps2SelProgram" style="display:none">
            <select class="tcm-select" id="selProgram" onchange="ps2PickItem(this)">
                <option value="">— Choose program —</option>
                <?php foreach ($programs as $pg): ?>
                <option value="<?= $pg['id'] ?>" data-price="<?= (float)$pg['price'] ?>" data-title="<?= e(addslashes($pg['title'])) ?>">
                    <?= e($pg['title']) ?><?= (float)$pg['price']>0 ? ' — '.money($pg['price']) : ' (Free)' ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div id="ps2SelOther" style="display:none">
            <input class="tcm-input" name="item_title_manual" placeholder="e.g. Batch 3 Registration, Workshop fee..." id="ps2ManualTitle">
        </div>
    </div>
    <?php endif; ?>

    <!-- Screenshot upload -->
    <div class="ps2-card">
        <div class="ps2-label"><i class="bi bi-camera-fill"></i> Payment Screenshot <span style="color:#dc2626;font-weight:400;">*</span></div>
        <div class="ps2-drop" id="ps2Drop" onclick="document.getElementById('ps2File').click()">
            <div id="ps2Placeholder">
                <div class="ps2-drop-icon"><i class="bi bi-cloud-arrow-up-fill"></i></div>
                <h4><u>Click to upload</u> or drag &amp; drop</h4>
                <p>JPG · PNG · WEBP · PDF · Max 5 MB</p>
            </div>
            <div class="ps2-preview" id="ps2Preview">
                <img id="ps2PreviewImg" src="" alt="preview">
                <div id="ps2PdfBadge" style="display:none;margin:0 auto 8px;display:none;align-items:center;justify-content:center;gap:7px;font-size:.83rem;font-weight:600;color:#111;">
                    <i class="bi bi-file-pdf-fill" style="color:#dc2626;font-size:1.1rem;"></i> <span id="ps2PdfName"></span>
                </div>
                <div class="ps2-preview-change" onclick="event.stopPropagation();document.getElementById('ps2File').click()">
                    <i class="bi bi-pencil"></i> Change file
                </div>
            </div>
        </div>
        <input type="file" id="ps2File" name="screenshot"
               accept="image/jpeg,image/png,image/webp,application/pdf"
               style="display:none;" required>
    </div>

    <!-- Amount + Date -->
    <div class="ps2-card">
        <div class="ps2-label"><i class="bi bi-receipt"></i> Payment Info</div>
        <div class="tcm-grid-2">
            <div class="tcm-field" style="margin-bottom:10px;">
                <label>Amount Paid (₹) *</label>
                <input class="tcm-input" type="number" step="1" name="amount" id="ps2Amount"
                       value="<?= $prePrice>0 ? (int)$prePrice : '' ?>"
                       required placeholder="999" style="font-size:1rem;font-weight:700;">
            </div>
            <div class="tcm-field" style="margin-bottom:10px;">
                <label>Payment Date *</label>
                <input class="tcm-input" type="date" name="payment_date"
                       value="<?= date('Y-m-d') ?>" required max="<?= date('Y-m-d') ?>">
            </div>
        </div>
        <!-- Method tiles -->
        <div style="margin-bottom:10px;">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#aaa;margin-bottom:7px;">Method</div>
            <div class="ps2-methods">
                <div class="ps2-method active" onclick="ps2Method('upi',this)">
                    <span class="ps2-method-emoji">📱</span>
                    <div><strong>UPI / GPay</strong><span>PhonePe · Paytm</span></div>
                </div>
                <div class="ps2-method" onclick="ps2Method('bank_transfer',this)">
                    <span class="ps2-method-emoji">🏦</span>
                    <div><strong>Bank Transfer</strong><span>NEFT / IMPS</span></div>
                </div>
                <div class="ps2-method" onclick="ps2Method('cash',this)">
                    <span class="ps2-method-emoji">💵</span>
                    <div><strong>Cash</strong><span>In person</span></div>
                </div>
                <div class="ps2-method" onclick="ps2Method('other',this)">
                    <span class="ps2-method-emoji">💳</span>
                    <div><strong>Other</strong><span>Any method</span></div>
                </div>
            </div>
            <input type="hidden" name="payment_method" id="ps2MethodVal" value="upi">
        </div>
        <div class="tcm-field" style="margin-bottom:10px;">
            <label>Transaction ID / UTR <span id="ps2RefReq" style="font-weight:400;color:#aaa;font-size:.75em;">(required for UPI)</span></label>
            <input class="tcm-input" name="transaction_ref" id="ps2Ref" placeholder="e.g. T2506161234ABCXYZ">
        </div>
        <div class="tcm-field" style="margin-bottom:10px;">
            <label>Referral Code <span style="font-weight:400;color:#aaa;font-size:.75em;">(optional)</span></label>
            <input class="tcm-input" name="referral_code" placeholder="e.g. TCM123456" maxlength="20">
            <div style="font-size:.7rem;color:#888;margin-top:4px;">
                <i class="bi bi-info-circle"></i> Enter a referrer's code to give them rewards
            </div>
        </div>
        <div class="tcm-field" style="margin-bottom:0;">
            <label>Note <span style="font-weight:400;color:#aaa;font-size:.75em;">(optional)</span></label>
            <textarea class="tcm-textarea" name="reason" style="min-height:60px;"
                      placeholder="Any additional info for admin..."></textarea>
        </div>
    </div>

    <button type="submit" class="ps2-submit">
        <i class="bi bi-send-check-fill"></i> Submit for Verification
    </button>
    <p style="text-align:center;font-size:.7rem;color:#aaa;margin-top:8px;display:flex;align-items:center;justify-content:center;gap:4px;">
        <i class="bi bi-shield-check-fill" style="color:#22c55e;"></i>
        Reviewed within 24 hours · Secure &amp; private
    </p>
</form>

<!-- ═══ RIGHT: PAY TO ═══ -->
<div>
    <div class="ps2-card" style="border-color:#d1fae5;background:#f9fffe;">
        <div class="ps2-label" style="color:#15803d;border-color:#d1fae5;"><i class="bi bi-cash-stack" style="color:#16a34a;"></i> Pay To</div>

        <?php if ($upiId): ?>
        <div class="ps2-payto" style="border-color:#bbf7d0;background:#f0fdf4;">
            <div class="ps2-payto-label">📱 UPI / GPay / PhonePe</div>
            <div class="ps2-payto-value" style="color:#15803d;font-size:1rem;"><?= e($upiId) ?></div>
            <?php if ($upiName): ?>
            <div class="ps2-payto-sub"><?= e($upiName) ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($bankAcc): ?>
        <div class="ps2-payto">
            <div class="ps2-payto-label">🏦 Bank Transfer / NEFT</div>
            <?php if ($bankHolder): ?><div class="ps2-payto-value"><?= e($bankHolder) ?></div><?php endif; ?>
            <?php if ($bankName): ?><div class="ps2-payto-sub"><?= e($bankName) ?></div><?php endif; ?>
            <div style="margin-top:6px;font-size:.78rem;color:#555;line-height:1.6;">
                <?php if ($bankAcc): ?>Acc: <strong style="color:#111;"><?= e($bankAcc) ?></strong><br><?php endif; ?>
                <?php if ($bankIfsc): ?>IFSC: <strong style="color:#111;"><?= e($bankIfsc) ?></strong><?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($waNumber): ?>
        <a href="https://wa.me/<?= e(preg_replace('/\D/','',$waNumber)) ?>?text=<?= urlencode('Hi TCM! I want to make a payment.') ?>"
           target="_blank"
           class="tcm-btn w-full" style="justify-content:center;text-decoration:none;">
            <i class="bi bi-whatsapp" style="color:#25d366;"></i> Ask on WhatsApp
        </a>
        <?php endif; ?>

        <?php if ($payNote): ?>
        <div style="margin-top:12px;font-size:.75rem;color:#555;line-height:1.6;padding-top:10px;border-top:1px solid #e5e5e5;">
            <i class="bi bi-info-circle" style="color:#3b82f6;"></i> <?= nl2br(e($payNote)) ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Tips -->
    <div class="ps2-card" style="background:#fafafa;">
        <div class="ps2-label"><i class="bi bi-lightbulb"></i> Screenshot Tips</div>
        <?php foreach ([
            'Shows amount, date and transaction ID',
            'Clear and not blurry or cropped',
            'UPI success screen works best',
            'Bank SMS or email also accepted',
        ] as $tip): ?>
        <div style="display:flex;align-items:center;gap:7px;padding:5px 0;font-size:.78rem;color:#555;border-bottom:1px solid #f5f5f5;">
            <i class="bi bi-check-circle-fill" style="color:#22c55e;font-size:.78rem;flex-shrink:0;"></i> <?= $tip ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

</div><!-- end grid -->
</div><!-- end page -->

<script>
(function(){
    var file = document.getElementById('ps2File');
    var drop = document.getElementById('ps2Drop');
    var ph   = document.getElementById('ps2Placeholder');
    var prev = document.getElementById('ps2Preview');
    var img  = document.getElementById('ps2PreviewImg');
    var pdf  = document.getElementById('ps2PdfBadge');
    var pdfN = document.getElementById('ps2PdfName');

    function showFile(f) {
        if (!f) return;
        if (f.size > 5*1024*1024) { alert('Max 5MB'); file.value=''; return; }
        ph.style.display='none'; prev.style.display='block';
        drop.classList.add('done');
        if (f.type==='application/pdf') {
            img.style.display='none'; pdf.style.display='inline-flex'; pdfN.textContent=f.name;
        } else {
            pdf.style.display='none'; img.style.display='block';
            var r=new FileReader(); r.onload=function(e){img.src=e.target.result;}; r.readAsDataURL(f);
        }
    }
    file.addEventListener('change', function(){ showFile(this.files[0]); });
    ['dragenter','dragover'].forEach(function(e){ drop.addEventListener(e,function(ev){ev.preventDefault();drop.classList.add('over');}); });
    ['dragleave','dragend'].forEach(function(e){ drop.addEventListener(e,function(){drop.classList.remove('over');}); });
    drop.addEventListener('drop',function(e){
        e.preventDefault(); drop.classList.remove('over');
        var f=e.dataTransfer.files[0]; if(!f)return;
        try{var dt=new DataTransfer();dt.items.add(f);file.files=dt.files;}catch(err){}
        showFile(f);
    });

    window.ps2Method = function(key, el) {
        document.querySelectorAll('.ps2-method').forEach(function(m){m.classList.remove('active');});
        el.classList.add('active');
        document.getElementById('ps2MethodVal').value = key;
        var ref=document.getElementById('ps2Ref'), req=document.getElementById('ps2RefReq');
        if (key==='cash') { ref.required=false; ref.placeholder='Not needed for cash'; req.textContent='(optional)'; }
        else if (key==='upi'||key==='bank_transfer') { ref.required=true; ref.placeholder='Transaction ID / UTR'; req.textContent='(required)'; }
        else { ref.required=false; ref.placeholder='Reference number'; req.textContent='(optional)'; }
    };

    window.ps2SwitchType = function(type, btn) {
        document.querySelectorAll('.ps2-tab').forEach(function(t){t.classList.remove('active');});
        btn.classList.add('active');
        document.getElementById('ps2ItemType').value = type;
        document.getElementById('ps2ItemId').value = '0';
        document.getElementById('ps2Amount').value = '';
        ['Course','Event','Program','Other'].forEach(function(n){
            var el=document.getElementById('ps2Sel'+n); if(el)el.style.display='none';
        });
        var show=document.getElementById('ps2Sel'+type.charAt(0).toUpperCase()+type.slice(1));
        if(show)show.style.display='block';
    };

    window.ps2PickItem = function(sel) {
        var opt=sel.options[sel.selectedIndex]; if(!opt||!opt.value)return;
        document.getElementById('ps2ItemId').value = opt.value;
        var price=parseFloat(opt.getAttribute('data-price')||'0');
        if(price>0) document.getElementById('ps2Amount').value=price;
    };

    document.getElementById('ps2Form').addEventListener('submit',function(e){
        if(!file.files||!file.files[0]){
            e.preventDefault();
            drop.style.borderColor='#dc2626'; drop.style.background='#fef2f2';
            drop.scrollIntoView({behavior:'smooth',block:'center'});
            setTimeout(function(){drop.style.borderColor='';drop.style.background='';},3000);
            alert('Please upload your payment screenshot.');
        }
    });
})();
</script>
