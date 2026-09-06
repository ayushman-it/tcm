<!-- Page header -->
<div class="tcm-page-head">
    <div>
        <h2>My Profile</h2>
        <p>Manage your account, personal details and profile photo.</p>
    </div>
</div>

<!-- Student ID & Referral ID card -->
<?php if (!empty($user['student_id']) || !empty($user['referral_id'])): ?>
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
    <?php if (!empty($user['student_id'])): ?>
    <div style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #ececec;border-radius:14px;padding:14px 18px;flex:1;min-width:200px;">
        <div style="width:38px;height:38px;border-radius:10px;background:#111;color:#fff;display:grid;place-items:center;font-size:.9rem;flex-shrink:0;">
            <i class="bi bi-person-badge"></i>
        </div>
        <div>
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:3px;">Student ID</div>
            <div style="font-size:.95rem;font-weight:800;color:#111;font-family:monospace;letter-spacing:.5px;"><?= e($user['student_id']) ?></div>
        </div>
        <button onclick="navigator.clipboard.writeText('<?= e($user['student_id']) ?>').then(function(){this.innerHTML='<i class=\'bi bi-check2\'></i>';setTimeout(function(){document.querySelectorAll(\'.id-copy-btn\')[0].innerHTML=\'<i class=\\\"bi bi-copy\\\"></i>\'},2000)}.bind(this))" class="tcm-btn sm id-copy-btn" style="margin-left:auto;" title="Copy">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
    <?php if (!empty($user['referral_id'])): ?>
    <div style="display:flex;align-items:center;gap:12px;background:#fff;border:1px solid #ececec;border-radius:14px;padding:14px 18px;flex:1;min-width:200px;">
        <div style="width:38px;height:38px;border-radius:10px;background:#4ade80;color:#fff;display:grid;place-items:center;font-size:.9rem;flex-shrink:0;">
            <i class="bi bi-share-fill"></i>
        </div>
        <div>
            <div style="font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:3px;">Referral ID</div>
            <div style="font-size:.95rem;font-weight:800;color:#111;font-family:monospace;letter-spacing:.5px;"><?= e($user['referral_id']) ?></div>
        </div>
        <button onclick="navigator.clipboard.writeText('<?= e($user['referral_id']) ?>').then(function(){this.innerHTML='<i class=\'bi bi-check2\'></i>';setTimeout(function(){document.querySelectorAll(\'.id-copy-btn\')[1].innerHTML=\'<i class=\\\"bi bi-copy\\\"></i>\'},2000)}.bind(this))" class="tcm-btn sm id-copy-btn" style="margin-left:auto;" title="Copy">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="tcm-grid-2" style="align-items:start;">

    <!-- ── Left: Profile details ── -->
    <div>
        <form method="post"
              action="<?= base_url('/student/profile') ?>"
              enctype="multipart/form-data"
              class="tcm-card">
            <?= csrf_field() ?>

            <!-- Avatar upload widget -->
            <div class="avatar-upload-wrap">
                <div class="avatar-upload-preview" id="avatarPreview"
                     onclick="document.getElementById('avatarFile').click()"
                     title="Click to change photo">

                    <?php if (!empty($user['avatar'])): ?>
                        <img class="avatar-img"
                             id="avatarImgPreview"
                             src="<?= base_url('/uploads/' . e($user['avatar'])) ?>"
                             alt="Profile photo">
                    <?php else: ?>
                        <div class="avatar-letter" id="avatarLetterPreview" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:inherit;">
                            <?= tcm_avatar(null, $user['name'] ?? 'S') ?>
                        </div>
                    <?php endif; ?>

                    <div class="avatar-overlay">
                        <i class="bi bi-camera"></i>
                    </div>
                </div>

                <input type="file"
                       name="avatar"
                       id="avatarFile"
                       class="avatar-upload-input"
                       accept="image/jpeg,image/png,image/webp,image/gif">

                <div class="avatar-upload-info">
                    <button type="button"
                            class="tcm-btn sm"
                            onclick="document.getElementById('avatarFile').click()">
                        <i class="bi bi-camera"></i> Change Photo
                    </button>
                    <p>JPG, PNG or WebP · Max 2 MB<br>
                       Click the photo or the button to upload.</p>
                </div>
            </div>

            <div class="tcm-divider"></div>

            <h3 style="margin-bottom:16px;"><i class="bi bi-person"></i> Basic Info</h3>

            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Full Name</label>
                    <input class="tcm-input" name="name"
                           value="<?= e($user['name']) ?>" required>
                </div>
                <div class="tcm-field">
                    <label>Phone</label>
                    <input class="tcm-input" name="phone"
                           value="<?= e($user['phone'] ?? '') ?>"
                           placeholder="+91 00000 00000">
                </div>
            </div>

            <div class="tcm-field">
                <label>Email (read-only)</label>
                <input class="tcm-input" value="<?= e($user['email']) ?>"
                       disabled style="background:var(--s2);color:var(--muted);">
            </div>

            <div class="tcm-field">
                <label>Headline</label>
                <input class="tcm-input" name="headline"
                       placeholder="e.g. Full Stack Developer"
                       value="<?= e($profile['headline'] ?? '') ?>">
            </div>

            <div class="tcm-field">
                <label>Bio</label>
                <textarea class="tcm-textarea" name="bio"
                          placeholder="A short bio for your portfolio…"><?= e($profile['bio'] ?? '') ?></textarea>
            </div>

            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>College / Organisation</label>
                    <input class="tcm-input" name="college"
                           value="<?= e($profile['college'] ?? '') ?>">
                </div>
                <div class="tcm-field">
                    <label>Graduation Year</label>
                    <input class="tcm-input" type="number" name="graduation_year"
                           value="<?= e((string)($profile['graduation_year'] ?? '')) ?>"
                           placeholder="2026">
                </div>
            </div>

            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label>Experience Level</label>
                    <select class="tcm-select" name="experience_level">
                        <?php foreach (['beginner' => '🌱 Beginner', 'intermediate' => '⚡ Intermediate', 'advanced' => '🔥 Advanced'] as $val => $label): ?>
                            <option value="<?= $val ?>"
                                <?= ($profile['experience_level'] ?? 'beginner') === $val ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="tcm-field">
                    <label>Location</label>
                    <input class="tcm-input" name="location"
                           placeholder="Mumbai, India"
                           value="<?= e($profile['location'] ?? '') ?>">
                </div>
            </div>

            <div class="tcm-field">
                <label>Your Goal</label>
                <input class="tcm-input" name="goal"
                       placeholder="e.g. Land a frontend internship"
                       value="<?= e($profile['goal'] ?? '') ?>">
            </div>

            <div class="tcm-divider"></div>
            <h3 style="margin-bottom:16px;"><i class="bi bi-link-45deg"></i> Social Links</h3>

            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label><i class="bi bi-github"></i> GitHub</label>
                    <input class="tcm-input" name="github_url"
                           placeholder="https://github.com/..."
                           value="<?= e($profile['github_url'] ?? '') ?>">
                </div>
                <div class="tcm-field">
                    <label><i class="bi bi-linkedin"></i> LinkedIn</label>
                    <input class="tcm-input" name="linkedin_url"
                           placeholder="https://linkedin.com/in/..."
                           value="<?= e($profile['linkedin_url'] ?? '') ?>">
                </div>
            </div>
            <div class="tcm-grid-2">
                <div class="tcm-field">
                    <label><i class="bi bi-globe"></i> Website</label>
                    <input class="tcm-input" name="website_url"
                           placeholder="https://yoursite.com"
                           value="<?= e($profile['website_url'] ?? '') ?>">
                </div>
                <div class="tcm-field">
                    <label><i class="bi bi-twitter-x"></i> Twitter / X</label>
                    <input class="tcm-input" name="twitter_url"
                           placeholder="https://x.com/..."
                           value="<?= e($profile['twitter_url'] ?? '') ?>">
                </div>
            </div>

            <button type="submit" class="tcm-btn primary" style="margin-top:4px;">
                <i class="bi bi-check2"></i> Save Profile
            </button>
        </form>
    </div>

    <!-- ── Right: Password + Orders ── -->
    <div>

        <!-- Change password -->
        <form method="post"
              action="<?= base_url('/student/profile/password') ?>"
              class="tcm-card">
            <?= csrf_field() ?>
            <h3 style="margin-bottom:16px;"><i class="bi bi-shield-lock"></i> Change Password</h3>

            <div class="tcm-field">
                <label>Current Password</label>
                <input class="tcm-input" type="password"
                       name="current_password" required
                       placeholder="Your current password">
            </div>
            <div class="tcm-field">
                <label>New Password</label>
                <input class="tcm-input" type="password"
                       name="password" required
                       placeholder="Min 6 characters">
            </div>
            <div class="tcm-field">
                <label>Confirm New Password</label>
                <input class="tcm-input" type="password"
                       name="password_confirmation" required
                       placeholder="Repeat new password">
            </div>

            <button type="submit" class="tcm-btn primary">
                <i class="bi bi-shield-check"></i> Update Password
            </button>
        </form>

        <!-- Order history -->
        <div class="tcm-card" style="margin-top:14px;">
            <h3 style="margin-bottom:14px;"><i class="bi bi-receipt"></i> Order History</h3>

            <?php if ($orders === []): ?>
                <div class="tcm-empty" style="padding:16px 0 8px;">
                    <i class="bi bi-bag"></i>
                    No orders yet.
                </div>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                <div class="tcm-row">
                    <div class="tcm-row-main">
                        <div class="tcm-row-title">
                            <?= e($o['item_title'] ?? $o['item_type']) ?>
                        </div>
                        <div class="tcm-row-sub">
                            <?= e(date('d M Y', strtotime($o['created_at']))) ?>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:.85rem;font-weight:700;color:#111;">
                            <?= money($o['amount']) ?>
                        </div>
                        <span class="tcm-badge <?= $o['status'] === 'paid' ? 'green' : 'amber' ?>">
                            <?= e($o['status']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>

</div>

<!-- Avatar JS: live preview on file select -->
<script>
document.getElementById('avatarFile').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    if (file.size > 2 * 1024 * 1024) {
        alert('File is too large. Max 2 MB.');
        this.value = '';
        return;
    }

    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('avatarPreview');

        // Remove letter fallback if present
        const letter = document.getElementById('avatarLetterPreview');
        if (letter) letter.remove();

        // Show image preview
        let img = document.getElementById('avatarImgPreview');
        if (!img) {
            img = document.createElement('img');
            img.id = 'avatarImgPreview';
            img.className = 'avatar-img';
            img.alt = 'Profile photo';
            // Insert before overlay
            const overlay = preview.querySelector('.avatar-overlay');
            preview.insertBefore(img, overlay);
        }
        img.src = e.target.result;

        // Trigger spin animation
        preview.style.animation = 'none';
        preview.offsetHeight; // reflow
    };
    reader.readAsDataURL(file);
});
</script>
