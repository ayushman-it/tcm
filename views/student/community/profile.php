<style>
/* ── Community Profile View ── */
.cp-wrap { max-width: 760px; }

/* Hero */
.cp-hero {
    background: #fff; border: 1px solid #ececec;
    border-radius: 18px; overflow: hidden; margin-bottom: 14px;
}
.cp-cover {
    height: 120px; background: #111; position: relative; overflow: hidden;
}
.cp-cover::before {
    content: ''; position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.05) 1px, transparent 1px);
    background-size: 20px 20px;
}
.cp-body { padding: 0 24px 22px; }
.cp-top-row {
    display: flex; align-items: flex-end;
    justify-content: space-between; gap: 10px;
    margin-top: -38px; margin-bottom: 12px; flex-wrap: wrap;
}
.cp-avatar {
    width: 76px; height: 76px; border-radius: 50%;
    border: 4px solid #fff; background: #f0f0f0;
    overflow: hidden; flex-shrink: 0;
    display: grid; place-items: center;
    font-size: 1.6rem; font-weight: 800; color: #111;
    box-shadow: 0 4px 16px rgba(0,0,0,.12);
}
.cp-avatar img { width: 100%; height: 100%; object-fit: cover; }
.cp-actions { display: flex; gap: 8px; flex-wrap: wrap; padding-bottom: 4px; }
.cp-btn-dark {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    background: #111; color: #fff;
    font-size: .78rem; font-weight: 700; border: none;
    cursor: pointer; font-family: inherit; transition: .15s; text-decoration: none;
}
.cp-btn-dark:hover { background: #333; color: #fff; }
.cp-btn-out {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    background: #fff; color: #111;
    font-size: .78rem; font-weight: 700;
    border: 1.5px solid #e5e5e5;
    cursor: pointer; font-family: inherit; transition: .15s; text-decoration: none;
}
.cp-btn-out:hover { border-color: #111; }
.cp-name { font-size: 1.2rem; font-weight: 800; color: #111; letter-spacing: -.4px; margin-bottom: 3px; }
.cp-hl   { font-size: .86rem; color: #555; margin-bottom: 8px; font-weight: 500; line-height: 1.5; }
.cp-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 10px; }
.cp-meta span { display: inline-flex; align-items: center; gap: 5px; font-size: .76rem; color: #888; }
.cp-meta span i { font-size: .76rem; }
.cp-goal {
    display: inline-flex; align-items: center; gap: 6px;
    background: #f5f5f5; border: 1px solid #e5e5e5;
    border-radius: 20px; padding: 3px 12px;
    font-size: .73rem; font-weight: 600; color: #555; margin-bottom: 12px;
}
.cp-socials { display: flex; gap: 7px; flex-wrap: wrap; }
.cp-soc {
    width: 32px; height: 32px; border-radius: 50%;
    border: 1.5px solid #ececec; background: #fff; color: #888;
    display: grid; place-items: center; font-size: .82rem;
    text-decoration: none; transition: .18s;
}
.cp-soc:hover { background: #111; color: #fff; border-color: #111; transform: translateY(-2px); }

/* Sections */
.cp-section { background: #fff; border: 1px solid #ececec; border-radius: 16px; padding: 18px 20px; margin-bottom: 12px; }
.cp-section-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #f5f5f5;
}
.cp-section-title { font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #aaa; display: flex; align-items: center; gap: 7px; margin: 0; }
.cp-section-title i { font-size: .82rem; color: #111; }
.cp-badge { font-size: .68rem; font-weight: 700; background: #f5f5f5; border: 1px solid #ececec; padding: 2px 8px; border-radius: 20px; color: #888; }

/* Skills */
.cp-skills { display: flex; flex-wrap: wrap; gap: 7px; }
.cp-skill {
    padding: 5px 13px; border-radius: 20px;
    background: #f5f5f5; border: 1px solid #ececec;
    font-size: .76rem; font-weight: 600; color: #444;
    transition: .15s;
}
.cp-skill:hover { background: #111; color: #fff; border-color: #111; }

/* Projects */
.cp-proj-list { display: grid; gap: 10px; }
.cp-proj { border: 1px solid #ececec; border-radius: 12px; padding: 14px 16px; position: relative; overflow: hidden; }
.cp-proj::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 3px; background: #111; }
.cp-proj-name { font-size: .88rem; font-weight: 700; color: #111; margin-bottom: 4px; }
.cp-proj-desc { font-size: .78rem; color: #777; line-height: 1.6; margin-bottom: 8px; }
.cp-proj-stack { font-size: .71rem; font-weight: 600; color: #555; background: #f5f5f5; padding: 3px 9px; border-radius: 12px; border: 1px solid #ececec; display: inline-block; margin-bottom: 8px; }
.cp-proj-links { display: flex; gap: 6px; }
.cp-plink { display: inline-flex; align-items: center; gap: 4px; padding: 4px 11px; border-radius: 7px; border: 1px solid #ececec; font-size: .73rem; font-weight: 700; color: #444; transition: .15s; text-decoration: none; }
.cp-plink:hover { background: #111; color: #fff; border-color: #111; }

/* Certs + Achievements */
.cp-cert { display: flex; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f5f5f5; }
.cp-cert:last-child { border-bottom: none; padding-bottom: 0; }
.cp-cert-icon { width: 36px; height: 36px; border-radius: 9px; background: #111; color: #fff; display: grid; place-items: center; font-size: .85rem; flex-shrink: 0; }
.cp-cert-name { font-size: .83rem; font-weight: 700; color: #111; }
.cp-cert-sub  { font-size: .71rem; color: #aaa; }
.cp-ach { display: flex; align-items: flex-start; gap: 12px; padding: 11px 0; border-bottom: 1px solid #f5f5f5; }
.cp-ach:last-child { border-bottom: none; padding-bottom: 0; }
.cp-ach-icon { width: 34px; height: 34px; border-radius: 50%; background: #f5f5f5; border: 1px solid #ececec; display: grid; place-items: center; font-size: .82rem; color: #111; flex-shrink: 0; }
.cp-ach-name { font-size: .83rem; font-weight: 700; color: #111; margin-bottom: 2px; }
.cp-ach-sub  { font-size: .71rem; color: #aaa; }

/* Bio */
.cp-bio { font-size: .85rem; color: #555; line-height: 1.8; }

/* Empty section */
.cp-none { font-size: .82rem; color: #bbb; padding: 6px 0; }

@media(max-width:600px) {
    .cp-body { padding: 0 16px 18px; }
    .cp-cover { height: 90px; }
    .cp-avatar { width: 64px; height: 64px; border-width: 3px; margin-top: -32px !important; }
}
</style>

<div class="tcm-page-head">
    <div>
        <h2><?= e($student['name']) ?></h2>
        <p><?= e($student['headline'] ?? 'Student at The Code Munk') ?></p>
    </div>
    <a href="<?= base_url('/student/community') ?>" class="tcm-btn">
        <i class="bi bi-arrow-left"></i> Back to Community
    </a>
</div>

<div class="cp-wrap">

    <!-- Hero card -->
    <div class="cp-hero">
        <div class="cp-cover">
            <?php
            $cBanner = !empty($student['banner'] ?? '') ? base_url('/uploads/banners/' . basename($student['banner'])) : null;
            if ($cBanner): ?>
                <img src="<?= e($cBanner) ?>" alt="Cover"
                     style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
                <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1),rgba(0,0,0,.3));"></div>
            <?php endif; ?>
        </div>
        <div class="cp-body">
            <div class="cp-top-row">
                <div class="cp-avatar" style="margin-top:-40px">
                    <?= tcm_avatar($student['avatar'] ?? null, $student['name'], '', '', $student['name']) ?>
                </div>
                <div class="cp-actions">
                    <!-- Help Request Form -->
                    <button onclick="document.getElementById('helpReqModal').style.display='flex'"
                            class="cp-btn-dark">
                        <i class="bi bi-hand-index-fill"></i> Help Request
                    </button>
                    <?php if (!empty($student['linkedin_url'])): ?>
                        <a href="<?= e($student['linkedin_url']) ?>" target="_blank" rel="noopener" class="cp-btn-out">
                            <i class="bi bi-linkedin"></i> Connect
                        </a>
                    <?php endif; ?>
                    <a href="<?= base_url('/portfolio/' . (int)$student['id']) ?>" target="_blank" class="cp-btn-out">
                        <i class="bi bi-box-arrow-up-right"></i> Portfolio
                    </a>
                </div>
            </div>

            <div class="cp-name"><?= e($student['name']) ?></div>
            <div class="cp-hl"><?= e($student['headline'] ?? 'Student at The Code Munk') ?></div>

            <div class="cp-meta">
                <?php if (!empty($student['location'])): ?>
                    <span><i class="bi bi-geo-alt-fill"></i> <?= e($student['location']) ?></span>
                <?php endif; ?>
                <?php if (!empty($student['college'])): ?>
                    <span><i class="bi bi-mortarboard-fill"></i> <?= e($student['college']) ?></span>
                <?php endif; ?>
                <?php if (!empty($student['graduation_year'])): ?>
                    <span><i class="bi bi-calendar3"></i> Class of <?= e((string)$student['graduation_year']) ?></span>
                <?php endif; ?>
                <?php if (!empty($student['experience_level'])): ?>
                    <?php $lvlMap = ['beginner'=>'🌱 Beginner','intermediate'=>'⚡ Intermediate','advanced'=>'🔥 Advanced']; ?>
                    <span><i class="bi bi-bar-chart-fill"></i> <?= e($lvlMap[$student['experience_level']] ?? ucfirst($student['experience_level'])) ?></span>
                <?php endif; ?>
                <span><i class="bi bi-clock"></i> Member since <?= e(date('M Y', strtotime($student['created_at']))) ?></span>
            </div>

            <?php if (!empty($student['goal'])): ?>
                <div class="cp-goal">
                    <i class="bi bi-bullseye"></i> <?= e($student['goal']) ?>
                </div>
            <?php endif; ?>

            <div class="cp-socials">
                <?php foreach ([
                    ['github_url','bi-github','GitHub'],
                    ['linkedin_url','bi-linkedin','LinkedIn'],
                    ['twitter_url','bi-twitter-x','X'],
                    ['website_url','bi-globe2','Website'],
                ] as [$key,$icon,$lbl]): ?>
                    <?php if (!empty($student[$key])): ?>
                        <a href="<?= e($student[$key]) ?>" target="_blank" rel="noopener" class="cp-soc" title="<?= $lbl ?>">
                            <i class="bi <?= $icon ?>"></i>
                        </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Stats mini strip -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);background:#fff;border:1px solid #ececec;border-radius:14px;margin-bottom:12px;overflow:hidden;">
        <?php foreach ([
            [count($projects),'Projects'],
            [count($skills),'Skills'],
            [count($achievements),'Achievements'],
            [count($certificates),'Certificates'],
        ] as $i => [$val,$lbl]): ?>
        <div style="text-align:center;padding:14px 8px;<?= $i < 3 ? 'border-right:1px solid #ececec;' : '' ?>">
            <div style="font-size:1.3rem;font-weight:900;color:#111;letter-spacing:-.5px;"><?= $val ?></div>
            <div style="font-size:.65rem;color:#aaa;font-weight:600;text-transform:uppercase;letter-spacing:.06em;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Peer Cookies -->
    <?php if (!empty($cookieData) && $cookieData['total'] > 0): ?>
    <div class="cp-section" style="margin-bottom:12px;">
        <div class="cp-section-head">
            <h3 class="cp-section-title"><i class="bi bi-emoji-smile-fill"></i> Peer Recognition</h3>
            <span class="cp-badge"><?= $cookieData['total'] ?> 🍪</span>
        </div>
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:14px;padding:12px 14px;background:#fef9c3;border-radius:10px;border:1px solid #fde047;">
            <div style="font-size:2rem;">🍪</div>
            <div>
                <div style="font-size:1.1rem;font-weight:800;color:#92400e;"><?= $cookieData['total'] ?> cookie<?= $cookieData['total']>1?'s':'' ?></div>
                <div style="font-size:.78rem;color:#b45309;">Avg <?= number_format($cookieData['avg'],1) ?>/5 · Given by peers for helpful support</div>
            </div>
        </div>
        <?php foreach ($cookieData['reviews'] as $rv): ?>
        <div style="padding:10px 0;border-bottom:1px solid #f5f5f5;display:flex;gap:10px;align-items:flex-start;">
            <div style="width:28px;height:28px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#e5e5e5;display:grid;place-items:center;font-size:.7rem;font-weight:700;">
                <?= tcm_avatar($rv['from_avatar']??null, $rv['from_name']) ?>
            </div>
            <div style="flex:1;">
                <div style="font-size:.8rem;font-weight:700;color:#111;margin-bottom:2px;">
                    <?= e($rv['from_name']) ?>
                    <span style="font-size:.72rem;color:#d97706;margin-left:6px;"><?= str_repeat('🍪',(int)$rv['cookies']) ?></span>
                </div>
                <div style="font-size:.78rem;color:#555;line-height:1.5;">"<?= e($rv['review']) ?>"</div>
                <div style="font-size:.67rem;color:#bbb;margin-top:2px;"><?= date('d M Y',strtotime($rv['created_at'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- About -->
    <?php if (!empty($student['bio'])): ?>
    <div class="cp-section">
        <div class="cp-section-head">
            <h3 class="cp-section-title"><i class="bi bi-person-lines-fill"></i> About</h3>
        </div>
        <p class="cp-bio"><?= nl2br(e($student['bio'])) ?></p>
    </div>
    <?php endif; ?>

    <!-- Skills -->
    <?php if (!empty($skills)): ?>
    <div class="cp-section">
        <div class="cp-section-head">
            <h3 class="cp-section-title"><i class="bi bi-lightning-charge-fill"></i> Skills</h3>
            <span class="cp-badge"><?= count($skills) ?></span>
        </div>
        <div class="cp-skills">
            <?php foreach ($skills as $sk): ?>
                <span class="cp-skill"><?= e($sk['name']) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Projects -->
    <?php if (!empty($projects)): ?>
    <div class="cp-section">
        <div class="cp-section-head">
            <h3 class="cp-section-title"><i class="bi bi-code-slash"></i> Projects</h3>
            <span class="cp-badge"><?= count($projects) ?></span>
        </div>
        <div class="cp-proj-list">
            <?php foreach ($projects as $p): ?>
            <div class="cp-proj">
                <div class="cp-proj-name"><?= e($p['title']) ?><?php if ($p['is_featured']): ?> <span style="font-size:.62rem;background:#f5f5f5;color:#888;border:1px solid #ececec;padding:1px 7px;border-radius:10px;font-weight:700;margin-left:5px;">⭐ Featured</span><?php endif; ?></div>
                <?php if (!empty($p['description'])): ?><div class="cp-proj-desc"><?= e($p['description']) ?></div><?php endif; ?>
                <?php if (!empty($p['tech_stack'])): ?><div class="cp-proj-stack"><?= e($p['tech_stack']) ?></div><?php endif; ?>
                <?php if (!empty($p['repo_url']) || !empty($p['live_url'])): ?>
                <div class="cp-proj-links">
                    <?php if (!empty($p['repo_url'])): ?><a href="<?= e($p['repo_url']) ?>" target="_blank" rel="noopener" class="cp-plink"><i class="bi bi-github"></i> Code</a><?php endif; ?>
                    <?php if (!empty($p['live_url'])): ?><a href="<?= e($p['live_url']) ?>" target="_blank" rel="noopener" class="cp-plink"><i class="bi bi-arrow-up-right-square"></i> Demo</a><?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Achievements + Certificates side by side on desktop -->
    <?php if (!empty($achievements) || !empty($certificates)): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;" class="tcm-responsive-grid">

        <?php if (!empty($achievements)): ?>
        <div class="cp-section" style="margin-bottom:0;">
            <div class="cp-section-head">
                <h3 class="cp-section-title"><i class="bi bi-trophy-fill"></i> Achievements</h3>
                <span class="cp-badge"><?= count($achievements) ?></span>
            </div>
            <?php foreach ($achievements as $a): ?>
            <div class="cp-ach">
                <div class="cp-ach-icon"><i class="bi bi-trophy-fill"></i></div>
                <div>
                    <div class="cp-ach-name"><?= e($a['title']) ?></div>
                    <div class="cp-ach-sub"><?= e($a['issuer'] ?? '') ?><?php if (!empty($a['achieved_on'])): ?> · <?= e(date('M Y', strtotime($a['achieved_on']))) ?><?php endif; ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($certificates)): ?>
        <div class="cp-section" style="margin-bottom:0;">
            <div class="cp-section-head">
                <h3 class="cp-section-title"><i class="bi bi-patch-check-fill"></i> Certificates</h3>
                <span class="cp-badge"><?= count($certificates) ?></span>
            </div>
            <?php foreach ($certificates as $c): ?>
            <div class="cp-cert">
                <div class="cp-cert-icon"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <div class="cp-cert-name"><?= e($c['title']) ?></div>
                    <div class="cp-cert-sub">#<?= e($c['certificate_number']) ?> · <?= e(date('d M Y', strtotime($c['issued_at']))) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div>
    <?php endif; ?>

</div>

<style>
@media(max-width:600px) {
    .tcm-responsive-grid { grid-template-columns: 1fr !important; }
    .cp-wrap { max-width: 100%; }
}
</style>

<!-- ── Help Request Modal ── -->
<div id="helpReqModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9000;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:18px;padding:26px;width:100%;max-width:420px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">
        <h3 style="font-size:1rem;font-weight:800;color:#111;margin-bottom:6px;">
            <i class="bi bi-hand-index-fill" style="margin-right:8px;color:#6366f1;"></i>
            Send Help Request
        </h3>
        <p style="font-size:.82rem;color:#888;margin-bottom:16px;">
            Ask <strong><?= e($student['name']) ?></strong> for help. They'll get a notification and can accept or decline.
        </p>
        <form method="post" action="<?= base_url('/student/help/request/' . (int)$student['id']) ?>">
            <?= csrf_field() ?>
            <div class="tcm-field">
                <label>Your message</label>
                <textarea class="tcm-textarea" name="message" rows="3"
                    placeholder="Briefly describe what you need help with..."
                    maxlength="500">Hi <?= e($student['name']) ?>! I'd love your help with something.</textarea>
            </div>
            <div style="display:flex;gap:8px;margin-top:8px;">
                <button type="submit" class="tcm-btn primary"><i class="bi bi-send"></i> Send Request</button>
                <button type="button" class="tcm-btn" onclick="document.getElementById('helpReqModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>
<script>
document.getElementById('helpReqModal').addEventListener('click', function(e){
    if(e.target===this) this.style.display='none';
});
</script>
