<style>
.set-section {
    background:#fff; border:1px solid #ececec; border-radius:16px;
    overflow:hidden; margin-bottom:16px;
}
.set-section-head {
    display:flex; align-items:center; gap:10px;
    padding:16px 22px; border-bottom:1px solid #f0f0f0;
    background:#fafafa;
}
.set-section-icon {
    width:34px; height:34px; border-radius:9px;
    display:grid; place-items:center; font-size:.9rem; flex-shrink:0;
}
.set-section-head h3 { font-size:.88rem; font-weight:700; color:#111; margin:0; }
.set-section-head p  { font-size:.74rem; color:#888; margin:2px 0 0; }
.set-body { padding:20px 22px; }
</style>

<div class="tcm-page-head">
    <div><h2>Settings</h2><p>Site, contact and payment configuration.</p></div>
</div>

<form method="post" action="<?= base_url('/admin/settings') ?>" style="max-width:720px;">
    <?= csrf_field() ?>

    <!-- ── General ── -->
    <div class="set-section">
        <div class="set-section-head">
            <div class="set-section-icon" style="background:#f5f5f5;color:#111;">
                <i class="bi bi-globe2"></i>
            </div>
            <div>
                <h3>General</h3>
                <p>Site name, tagline and display counters</p>
            </div>
        </div>
        <div class="set-body">
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Site Name</label>
                    <input class="tcm-input" name="site_name" value="<?= e($settings['site_name'] ?? 'The Code Munk') ?>">
                </div>
                <div class="tcm-field">
                    <label>Currency</label>
                    <input class="tcm-input" name="currency" value="<?= e($settings['currency'] ?? 'INR') ?>" style="max-width:120px;">
                </div>
            </div>
            <div class="tcm-field">
                <label>Tagline</label>
                <input class="tcm-input" name="site_tagline" value="<?= e($settings['site_tagline'] ?? '') ?>" placeholder="Learn. Build. Grow.">
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Students Count <span style="font-weight:400;color:#aaa;">(shown on site)</span></label>
                    <input class="tcm-input" name="students_count" value="<?= e($settings['students_count'] ?? '') ?>" placeholder="1,200+">
                </div>
                <div class="tcm-field">
                    <label>Courses Count <span style="font-weight:400;color:#aaa;">(shown on site)</span></label>
                    <input class="tcm-input" name="courses_count" value="<?= e($settings['courses_count'] ?? '') ?>" placeholder="10+">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Contact ── -->
    <div class="set-section">
        <div class="set-section-head">
            <div class="set-section-icon" style="background:#eff6ff;color:#3b82f6;">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
                <h3>Contact</h3>
                <p>Email, phone and WhatsApp for lead routing</p>
            </div>
        </div>
        <div class="set-body">
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Contact Email</label>
                    <input class="tcm-input" type="email" name="contact_email" value="<?= e($settings['contact_email'] ?? '') ?>" placeholder="hello@thecodemunk.com">
                </div>
                <div class="tcm-field">
                    <label>Contact Phone</label>
                    <input class="tcm-input" name="contact_phone" value="<?= e($settings['contact_phone'] ?? '') ?>" placeholder="+91 98765 43210">
                </div>
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label><i class="bi bi-whatsapp" style="color:#25d366;"></i> WhatsApp Number</label>
                    <input class="tcm-input" name="whatsapp_number" value="<?= e($settings['whatsapp_number'] ?? '') ?>" placeholder="919876543210">
                    <div style="font-size:.71rem;color:#aaa;margin-top:4px;">International format, digits only. e.g. 919876543210</div>
                </div>
                <div class="tcm-field">
                    <label>WhatsApp Message Prefix</label>
                    <input class="tcm-input" name="whatsapp_message" value="<?= e($settings['whatsapp_message'] ?? 'Hi The Code Munk! I am interested in') ?>">
                </div>
            </div>
        </div>
    </div>

    <!-- ── Payment Details ── -->
    <div class="set-section" style="border-color:#d1fae5;">
        <div class="set-section-head" style="background:#f0fdf4;border-color:#d1fae5;">
            <div class="set-section-icon" style="background:#dcfce7;color:#16a34a;">
                <i class="bi bi-cash-coin"></i>
            </div>
            <div>
                <h3 style="color:#15803d;">Payment Details</h3>
                <p>These appear on the student payment submit page</p>
            </div>
        </div>
        <div class="set-body">

            <!-- UPI -->
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                        color:#aaa;margin-bottom:10px;display:flex;align-items:center;gap:7px;">
                <span style="width:20px;height:20px;background:#111;border-radius:5px;display:grid;place-items:center;color:#fff;font-size:.7rem;">📱</span>
                UPI / GPay / PhonePe
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>UPI ID</label>
                    <input class="tcm-input" name="payment_upi_id"
                           value="<?= e($settings['payment_upi_id'] ?? '') ?>"
                           placeholder="thecodemunk@upi">
                </div>
                <div class="tcm-field">
                    <label>UPI Display Name</label>
                    <input class="tcm-input" name="payment_upi_name"
                           value="<?= e($settings['payment_upi_name'] ?? '') ?>"
                           placeholder="The Code Munk">
                </div>
            </div>

            <div style="height:1px;background:#f0f0f0;margin:14px 0;"></div>

            <!-- Bank -->
            <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;
                        color:#aaa;margin-bottom:10px;display:flex;align-items:center;gap:7px;">
                <span style="width:20px;height:20px;background:#111;border-radius:5px;display:grid;place-items:center;color:#fff;font-size:.7rem;">🏦</span>
                Bank Transfer / NEFT
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Bank Name</label>
                    <input class="tcm-input" name="payment_bank_name"
                           value="<?= e($settings['payment_bank_name'] ?? '') ?>"
                           placeholder="e.g. HDFC Bank">
                </div>
                <div class="tcm-field">
                    <label>Account Holder Name</label>
                    <input class="tcm-input" name="payment_account_name"
                           value="<?= e($settings['payment_account_name'] ?? '') ?>"
                           placeholder="Ayushman Chourasiya">
                </div>
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Account Number</label>
                    <input class="tcm-input" name="payment_account_number"
                           value="<?= e($settings['payment_account_number'] ?? '') ?>"
                           placeholder="XXXX XXXX XXXX">
                </div>
                <div class="tcm-field">
                    <label>IFSC Code</label>
                    <input class="tcm-input" name="payment_ifsc"
                           value="<?= e($settings['payment_ifsc'] ?? '') ?>"
                           placeholder="HDFC0001234">
                </div>
            </div>

            <div style="height:1px;background:#f0f0f0;margin:14px 0;"></div>

            <div class="tcm-field" style="margin-bottom:0;">
                <label>Payment Instructions Note <span style="font-weight:400;color:#aaa;">(shown to student)</span></label>
                <textarea class="tcm-textarea" name="payment_note" style="min-height:72px;"
                          placeholder="e.g. After payment, upload your screenshot here. Access will be activated within 24 hours."><?= e($settings['payment_note'] ?? '') ?></textarea>
            </div>

        </div>
    </div>

    <button type="submit" class="tcm-btn primary" style="padding:11px 28px;">
        <i class="bi bi-check2-circle"></i> Save All Settings
    </button>
</form>
