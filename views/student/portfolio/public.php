<?php
$avatarSrc  = !empty($owner['avatar']) ? base_url('/uploads/' . $owner['avatar']) : null;
$bannerSrc  = !empty($profile['banner']) ? base_url('/uploads/banners/' . basename($profile['banner'])) : null;
$pCount     = count($projects      ?? []);
$sCount     = count($skills        ?? []);
$aCount     = count($achievements  ?? []);
$cCount     = count($certificates  ?? []);

// Build absolute base URL (never ends with /)
$appUrl   = rtrim((string) config('app.url'), '/');

// Absolute URLs for assets
$avatarAbs = !empty($owner['avatar'])
    ? $appUrl . '/uploads/' . $owner['avatar']
    : null;
$bannerAbs = !empty($profile['banner'])
    ? $appUrl . '/uploads/banners/' . basename($profile['banner'])
    : null;

// OG image priority: banner > avatar > dynamic per-student SVG card
$ogCardUrl = $appUrl . base_url('/portfolio/' . $owner['id'] . '/og-image');
$ogImage   = $bannerAbs ?? $avatarAbs ?? $ogCardUrl;

// WhatsApp prefers square images — use avatar if available, else banner, else SVG card
$ogImageSquare = $avatarAbs ?? $bannerAbs ?? $ogCardUrl;

$ogTitle  = e($owner['name']) . ' — Developer Portfolio · The Code Munk';
$ogDesc   = e(
    ($profile['headline'] ?? 'Developer at The Code Munk')
    . ' · ' . $pCount . ' Projects · ' . $sCount . ' Skills'
    . ($cCount ? ' · ' . $cCount . ' Certificates' : '')
);
$ogUrl    = $appUrl . base_url('/portfolio/' . $owner['id']);
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($owner['name']) ?> — Developer Portfolio · TCM</title>
<meta name="description" content="<?= $ogDesc ?>">

<!-- ── Open Graph (WhatsApp, LinkedIn, Facebook, Telegram) ── -->
<meta property="og:type"        content="profile">
<meta property="og:url"         content="<?= e($ogUrl) ?>">
<meta property="og:title"       content="<?= $ogTitle ?>">
<meta property="og:description" content="<?= $ogDesc ?>">
<meta property="og:image"       content="<?= e($ogImage) ?>">
<meta property="og:image:width"  content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt"    content="<?= e($owner['name']) ?> Developer Portfolio">
<meta property="og:site_name"   content="The Code Munk">
<meta property="og:locale"      content="en_IN">

<!-- ── Twitter / X Card ── -->
<meta name="twitter:card"        content="summary_large_image">
<meta name="twitter:title"       content="<?= $ogTitle ?>">
<meta name="twitter:description" content="<?= $ogDesc ?>">
<meta name="twitter:image"       content="<?= e($ogImage) ?>">
<meta name="twitter:image:alt"   content="<?= e($owner['name']) ?> Developer Portfolio">
<meta name="twitter:site"        content="@thecodemunk">

<!-- ── WhatsApp uses a square image better — provide avatar as secondary ── -->
<meta property="og:image:secure_url" content="<?= e($ogImage) ?>">
<?php if ($avatarAbs): ?>
<link rel="image_src" href="<?= e($avatarAbs) ?>">
<?php endif; ?>

<!-- ── Canonical ── -->
<link rel="canonical" href="<?= e($ogUrl) ?>">

<!-- ── JSON-LD structured data ── -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "ProfilePage",
  "mainEntity": {
    "@type": "Person",
    "name": "<?= e($owner['name']) ?>",
    "description": "<?= e($profile['headline'] ?? 'Developer at The Code Munk') ?>",
    "url": "<?= e($ogUrl) ?>"
    <?php if ($avatarAbs): ?>,
    "image": "<?= e($avatarAbs) ?>"
    <?php endif; ?>
    <?php if (!empty($profile['linkedin_url'])): ?>,
    "sameAs": ["<?= e($profile['linkedin_url']) ?>"<?php if (!empty($profile['github_url'])): ?>, "<?= e($profile['github_url']) ?>"<?php endif; ?>]
    <?php endif; ?>
  },
  "publisher": {
    "@type": "Organization",
    "name": "The Code Munk",
    "url": "<?= e($appUrl) ?>"
  }
}
</script>

<!-- ── Canonical ── -->
<link rel="canonical" href="<?= e($ogUrl) ?>">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<style>
/* ═══════════════════════════════════════════
   TCM PUBLIC PORTFOLIO  |  Black & White Theme
   ═══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --ink:    #0a0a0a;
    --ink2:   #222;
    --ink3:   #444;
    --muted:  #777;
    --muted2: #bbb;
    --bg:     #f6f6f6;
    --surface:#ffffff;
    --border: #e8e8e8;
    --border2:#d8d8d8;
    --r:      12px;
    --r-lg:   18px;
}

html { scroll-behavior: smooth; }

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
    background: var(--bg);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
    font-size: 14px;
    line-height: 1.6;
}

a { text-decoration: none; color: inherit; }
img { display: block; max-width: 100%; }

/* ── Animations ── */
@keyframes fadeUp  { from { opacity:0; transform:translateY(16px) } to { opacity:1; transform:translateY(0) } }
@keyframes scaleIn { from { opacity:0; transform:scale(.96)       } to { opacity:1; transform:scale(1)       } }
@keyframes blink   { 0%,100%{opacity:1} 50%{opacity:.25} }

.pa { opacity:0; animation: fadeUp .5s ease forwards; }

/* ── Sticky nav ── */
.pnav {
    position: sticky; top: 0; z-index: 200;
    height: 52px;
    background: rgba(255,255,255,.93);
    backdrop-filter: blur(14px);
    border-bottom: 1px solid var(--border);
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 0 20px;
}
.pnav-brand {
    display: flex; align-items: center; gap: 8px;
    font-size: .82rem; font-weight: 800; color: var(--ink);
}
.pnav-icon {
    width: 26px; height: 26px; border-radius: 7px;
    background: var(--ink); color: #fff;
    display: grid; place-items: center; font-size: .7rem;
}
.pnav-share {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px; border-radius: 8px;
    background: var(--ink); color: #fff;
    font-size: .75rem; font-weight: 700;
    border: none; cursor: pointer; font-family: inherit;
    transition: background .15s;
}
.pnav-share:hover { background: #333; }

/* ── Page wrapper ── */
.pw { max-width: 800px; margin: 0 auto; padding: 28px 16px 80px; }

/* ── Hero card ── */
.ph {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    overflow: hidden;
    margin-bottom: 12px;
    animation: scaleIn .45s ease both;
}

.ph-cover {
    height: 130px;
    background: var(--ink);
    position: relative; overflow: hidden;
}
.ph-cover::after {
    content: '';
    position: absolute; inset: 0;
    background:
        radial-gradient(ellipse 60% 80% at 80% 50%, rgba(255,255,255,.04) 0%, transparent 70%),
        radial-gradient(ellipse 40% 60% at 20% 80%, rgba(255,255,255,.03) 0%, transparent 70%);
}
/* subtle dot grid */
.ph-cover::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.06) 1px, transparent 1px);
    background-size: 22px 22px;
}

.ph-body { padding: 0 24px 24px; }

.ph-top-row {
    display: flex; align-items: flex-end;
    justify-content: space-between;
    flex-wrap: wrap; gap: 10px;
    margin-top: -42px; margin-bottom: 14px;
}

.ph-avatar {
    width: 84px; height: 84px;
    border-radius: 50%;
    border: 4px solid var(--surface);
    background: var(--bg);
    overflow: hidden; flex-shrink: 0;
    display: grid; place-items: center;
    font-size: 2rem; font-weight: 900; color: var(--ink);
    box-shadow: 0 4px 20px rgba(0,0,0,.13);
}
.ph-avatar img { width: 100%; height: 100%; object-fit: cover; }

.ph-cta { display: flex; gap: 8px; flex-wrap: wrap; padding-bottom: 4px; }

.pb-dark {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    background: var(--ink); color: #fff;
    font-size: .78rem; font-weight: 700;
    border: none; cursor: pointer; font-family: inherit;
    transition: background .15s; white-space: nowrap;
}
.pb-dark:hover { background: #333; color: #fff; }

.pb-light {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 9px;
    background: transparent; color: var(--ink);
    font-size: .78rem; font-weight: 700;
    border: 1.5px solid var(--border2);
    cursor: pointer; font-family: inherit;
    transition: border-color .15s, background .15s; white-space: nowrap;
}
.pb-light:hover { border-color: var(--ink); background: var(--bg); color: var(--ink); }

.ph-name {
    font-size: 1.35rem; font-weight: 900;
    color: var(--ink); letter-spacing: -.5px;
    margin-bottom: 4px; line-height: 1.15;
}
.ph-hl {
    font-size: .88rem; color: var(--ink3);
    font-weight: 500; margin-bottom: 10px;
    line-height: 1.5;
}
.ph-meta {
    display: flex; flex-wrap: wrap; gap: 12px;
    margin-bottom: 12px;
}
.ph-meta-i {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: .76rem; color: var(--muted);
}
.ph-meta-i i { font-size: .76rem; }

.ph-open {
    display: inline-flex; align-items: center; gap: 7px;
    background: var(--bg); border: 1px solid var(--border);
    border-radius: 20px; padding: 4px 13px;
    font-size: .73rem; font-weight: 700; color: var(--ink);
    margin-bottom: 14px;
}
.ph-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: #22c55e; box-shadow: 0 0 5px #22c55e;
    animation: blink 1.5s infinite;
}

.ph-socials { display: flex; gap: 8px; flex-wrap: wrap; }
.ph-soc {
    width: 34px; height: 34px; border-radius: 50%;
    border: 1.5px solid var(--border);
    background: var(--surface); color: var(--muted);
    display: grid; place-items: center;
    font-size: .84rem; transition: .18s;
}
.ph-soc:hover { background: var(--ink); color: #fff; border-color: var(--ink); transform: translateY(-2px); }
</style>
<style>
/* ── Stats strip ── */
.ps {
    display: grid; grid-template-columns: repeat(4,1fr);
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--r-lg); margin-bottom: 12px; overflow: hidden;
}
.ps-item {
    text-align: center; padding: 16px 8px;
    border-right: 1px solid var(--border);
}
.ps-item:last-child { border-right: none; }
.ps-num { display: block; font-size: 1.4rem; font-weight: 900; color: var(--ink); letter-spacing: -.6px; }
.ps-lbl { font-size: .68rem; color: var(--muted); font-weight: 600; text-transform: uppercase; letter-spacing: .07em; }

/* ── Section card ── */
.pc {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--r-lg);
    padding: 20px 22px;
    margin-bottom: 12px;
}
.pc-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 16px; padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
}
.pc-title {
    font-size: .72rem; font-weight: 800; text-transform: uppercase;
    letter-spacing: .1em; color: var(--muted);
    display: flex; align-items: center; gap: 8px; margin: 0;
}
.pc-title i { font-size: .82rem; color: var(--ink); }
.pc-badge {
    font-size: .68rem; font-weight: 700; color: var(--muted);
    background: var(--bg); border: 1px solid var(--border);
    padding: 2px 9px; border-radius: 20px;
}

/* ── Bio ── */
.bio { font-size: .87rem; color: var(--ink3); line-height: 1.85; }

/* ── Skills ── */
.sk-list { display: flex; flex-wrap: wrap; gap: 8px; }
.sk {
    padding: 6px 14px; border-radius: 20px;
    background: var(--bg); border: 1px solid var(--border);
    font-size: .77rem; font-weight: 600; color: var(--ink3);
    transition: .15s; cursor: default;
}
.sk:hover { background: var(--ink); color: #fff; border-color: var(--ink); }

/* ── Projects ── */
.proj-grid { display: grid; gap: 10px; }
.proj {
    border: 1px solid var(--border); border-radius: var(--r);
    padding: 16px 18px; position: relative; overflow: hidden;
    transition: border-color .15s, transform .15s, box-shadow .15s;
    background: var(--surface);
}
.proj:hover { border-color: var(--border2); transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,0,0,.07); }
.proj-bar {
    position: absolute; left: 0; top: 0; bottom: 0; width: 3px;
    background: var(--ink); border-radius: 2px 0 0 2px;
}
.proj-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; margin-bottom: 6px; }
.proj-name { font-size: .9rem; font-weight: 800; color: var(--ink); }
.proj-star { font-size: .62rem; font-weight: 800; background: var(--bg); color: var(--muted); border: 1px solid var(--border); padding: 2px 8px; border-radius: 10px; white-space: nowrap; flex-shrink: 0; }
.proj-desc { font-size: .8rem; color: var(--muted); line-height: 1.65; margin-bottom: 10px; }
.proj-stack { display: inline-flex; align-items: center; gap: 5px; font-size: .72rem; font-weight: 600; color: var(--ink3); background: var(--bg); padding: 3px 10px; border-radius: 20px; border: 1px solid var(--border); margin-bottom: 10px; }
.proj-links { display: flex; gap: 7px; }
.plink {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 8px;
    border: 1px solid var(--border); background: var(--surface);
    font-size: .74rem; font-weight: 700; color: var(--ink3);
    transition: .15s;
}
.plink:hover { background: var(--ink); color: #fff; border-color: var(--ink); }

/* ── Achievements ── */
.ach-list { display: grid; gap: 9px; }
.ach {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 13px 15px; border: 1px solid var(--border);
    border-radius: var(--r); background: var(--bg);
}
.ach-icon {
    width: 36px; height: 36px; border-radius: 50%; flex-shrink: 0;
    background: var(--surface); border: 1px solid var(--border);
    display: grid; place-items: center; font-size: .88rem; color: var(--ink);
}
.ach-name { font-size: .84rem; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.ach-sub  { font-size: .72rem; color: var(--muted); }

/* ── Certificates ── */
.cert-list { display: grid; gap: 9px; }
.cert {
    display: flex; align-items: center; gap: 14px;
    padding: 13px 15px; border: 1px solid var(--border);
    border-radius: var(--r); background: var(--surface);
    transition: border-color .15s;
}
.cert:hover { border-color: var(--border2); }
.cert-icon {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    background: var(--ink); border: 1px solid var(--ink);
    display: grid; place-items: center; font-size: .9rem; color: #fff;
}
.cert-name { font-size: .84rem; font-weight: 700; color: var(--ink); margin-bottom: 2px; }
.cert-sub  { font-size: .72rem; color: var(--muted); }

/* ── Footer ── */
.pfooter {
    text-align: center; padding: 28px 0 0;
    font-size: .74rem; color: var(--muted2); line-height: 1.8;
}
.pfooter a { color: var(--ink); font-weight: 800; }

/* ── Responsive ── */
@media (max-width: 600px) {
    .ps { grid-template-columns: repeat(2,1fr); }
    .ps-item:nth-child(2)  { border-right: none; }
    .ps-item:nth-child(1), .ps-item:nth-child(2) { border-bottom: 1px solid var(--border); }
    .ph-cover { height: 100px; }
    .ph-avatar { width: 72px; height: 72px; margin-top: -36px !important; }
    .pw { padding: 20px 12px 60px; }
    .pc { padding: 16px; }
    .ph-body { padding: 0 16px 20px; }
}
</style>
</head>
<body>

<!-- ── Nav ── -->
<nav class="pnav">
    <a href="<?= base_url('/') ?>" class="pnav-brand">
        <div class="pnav-icon"><i class="bi bi-code-slash"></i></div>
        The Code Munk
    </a>
    <button class="pnav-share" onclick="pfCopy(this)">
        <i class="bi bi-share-fill"></i> Share
    </button>
</nav>

<div class="pw">

<!-- ── Hero ── -->
<div class="ph pa" style="animation-delay:.04s">
    <div class="ph-cover">
    <?php if ($bannerSrc): ?>
        <img src="<?= e($bannerSrc) ?>" alt="Cover"
             style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;">
        <div style="position:absolute;inset:0;background:linear-gradient(to bottom,rgba(0,0,0,.1) 0%,rgba(0,0,0,.35) 100%);"></div>
    <?php endif; ?>
</div>
    <div class="ph-body">
        <div class="ph-top-row">
            <div class="ph-avatar" style="margin-top:-44px">
                <?= tcm_avatar($owner['avatar'] ?? null, $owner['name'], '', '', $owner['name']) ?>
            </div>
            <div class="ph-cta">
                <?php if (!empty($profile['linkedin_url'])): ?>
                    <a href="<?= e($profile['linkedin_url']) ?>" target="_blank" rel="noopener" class="pb-dark">
                        <i class="bi bi-linkedin"></i> Connect
                    </a>
                <?php endif; ?>
                <?php if (!empty($profile['github_url'])): ?>
                    <a href="<?= e($profile['github_url']) ?>" target="_blank" rel="noopener" class="pb-light">
                        <i class="bi bi-github"></i> GitHub
                    </a>
                <?php endif; ?>
                <?php if (!empty($profile['website_url'])): ?>
                    <a href="<?= e($profile['website_url']) ?>" target="_blank" rel="noopener" class="pb-light">
                        <i class="bi bi-globe2"></i> Website
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="ph-name"><?= e($owner['name']) ?></div>
        <div class="ph-hl"><?= e($profile['headline'] ?? 'Developer at The Code Munk') ?></div>

        <div class="ph-meta">
            <?php if (!empty($profile['location'])): ?>
                <span class="ph-meta-i"><i class="bi bi-geo-alt-fill"></i> <?= e($profile['location']) ?></span>
            <?php endif; ?>
            <?php if (!empty($profile['college'])): ?>
                <span class="ph-meta-i"><i class="bi bi-mortarboard-fill"></i> <?= e($profile['college']) ?></span>
            <?php endif; ?>
            <?php if (!empty($profile['graduation_year'])): ?>
                <span class="ph-meta-i"><i class="bi bi-calendar3"></i> Class of <?= e((string)$profile['graduation_year']) ?></span>
            <?php endif; ?>
            <?php if (!empty($profile['experience_level'])): ?>
                <span class="ph-meta-i"><i class="bi bi-bar-chart-fill"></i> <?= e(ucfirst($profile['experience_level'])) ?> level</span>
            <?php endif; ?>
        </div>

        <?php if (!empty($profile['goal'])): ?>
            <div style="margin-bottom:14px">
                <span class="ph-open"><span class="ph-dot"></span> Open to opportunities</span>
            </div>
        <?php endif; ?>

        <div class="ph-socials">
            <?php foreach ([
                ['github_url',   'bi-github',    'GitHub'],
                ['linkedin_url', 'bi-linkedin',  'LinkedIn'],
                ['twitter_url',  'bi-twitter-x', 'X'],
                ['website_url',  'bi-globe2',    'Website'],
            ] as [$key, $icon, $label]): ?>
                <?php if (!empty($profile[$key])): ?>
                    <a href="<?= e($profile[$key]) ?>" target="_blank" rel="noopener" class="ph-soc" title="<?= $label ?>">
                        <i class="bi <?= $icon ?>"></i>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Stats ── -->
<div class="ps pa" style="animation-delay:.12s">
    <div class="ps-item"><span class="ps-num"><?= $pCount ?></span><span class="ps-lbl">Projects</span></div>
    <div class="ps-item"><span class="ps-num"><?= $sCount ?></span><span class="ps-lbl">Skills</span></div>
    <div class="ps-item"><span class="ps-num"><?= $aCount ?></span><span class="ps-lbl">Achievements</span></div>
    <div class="ps-item"><span class="ps-num"><?= $cCount ?></span><span class="ps-lbl">Certificates</span></div>
    <?php if (!empty($cookieData['total'])): ?>
    <div class="ps-item"><span class="ps-num"><?= (int)$cookieData['total'] ?>🍪</span><span class="ps-lbl">Cookies</span></div>
    <?php endif; ?>
</div>

<!-- ── About ── -->
<?php if (!empty($profile['bio'])): ?>
<div class="pc pa" style="animation-delay:.18s">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-person-lines-fill"></i> About</h2>
    </div>
    <p class="bio"><?= nl2br(e($profile['bio'])) ?></p>
    <?php if (!empty($profile['goal'])): ?>
        <div style="margin-top:14px;padding:12px 14px;background:var(--bg);border:1px solid var(--border);border-radius:10px;font-size:.82rem;color:var(--ink3);">
            <i class="bi bi-bullseye" style="margin-right:6px;color:var(--ink)"></i>
            <strong>Goal:</strong> <?= e($profile['goal']) ?>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Skills ── -->
<?php if (!empty($skills)): ?>
<div class="pc pa" style="animation-delay:.22s">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-lightning-charge-fill"></i> Skills</h2>
        <span class="pc-badge"><?= $sCount ?></span>
    </div>
    <div class="sk-list">
        <?php foreach ($skills as $sk): ?>
            <span class="sk"><?= e($sk['name']) ?></span>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Projects ── -->
<?php if (!empty($projects)): ?>
<div class="pc pa" style="animation-delay:.26s">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-code-slash"></i> Projects</h2>
        <span class="pc-badge"><?= $pCount ?></span>
    </div>
    <div class="proj-grid">
        <?php foreach ($projects as $p): ?>
        <div class="proj">
            <div class="proj-bar"></div>
            <div class="proj-row">
                <div class="proj-name"><?= e($p['title']) ?></div>
                <?php if ($p['is_featured']): ?>
                    <span class="proj-star">⭐ Featured</span>
                <?php endif; ?>
            </div>
            <?php if (!empty($p['description'])): ?>
                <div class="proj-desc"><?= e($p['description']) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['tech_stack'])): ?>
                <div class="proj-stack"><i class="bi bi-layers"></i> <?= e($p['tech_stack']) ?></div>
            <?php endif; ?>
            <?php if (!empty($p['repo_url']) || !empty($p['live_url'])): ?>
            <div class="proj-links">
                <?php if (!empty($p['repo_url'])): ?>
                    <a href="<?= e($p['repo_url']) ?>" target="_blank" rel="noopener" class="plink">
                        <i class="bi bi-github"></i> Code
                    </a>
                <?php endif; ?>
                <?php if (!empty($p['live_url'])): ?>
                    <a href="<?= e($p['live_url']) ?>" target="_blank" rel="noopener" class="plink">
                        <i class="bi bi-arrow-up-right-square"></i> Live Demo
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Achievements ── -->
<?php if (!empty($achievements)): ?>
<div class="pc pa" style="animation-delay:.30s">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-trophy-fill"></i> Achievements</h2>
        <span class="pc-badge"><?= $aCount ?></span>
    </div>
    <div class="ach-list">
        <?php foreach ($achievements as $a): ?>
        <div class="ach">
            <div class="ach-icon"><i class="bi bi-trophy-fill"></i></div>
            <div>
                <div class="ach-name"><?= e($a['title']) ?></div>
                <div class="ach-sub">
                    <?php if (!empty($a['issuer'])): ?><i class="bi bi-building" style="margin-right:4px"></i><?= e($a['issuer']) ?><?php endif; ?>
                    <?php if (!empty($a['achieved_on'])): ?> &nbsp;·&nbsp; <?= e(date('M Y', strtotime($a['achieved_on']))) ?><?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Certificates ── -->
<?php if (!empty($certificates)): ?>
<div class="pc pa" style="animation-delay:.34s">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-patch-check-fill"></i> Certificates</h2>
        <span class="pc-badge"><?= $cCount ?></span>
    </div>
    <div class="cert-list">
        <?php foreach ($certificates as $c): ?>
        <div class="cert">
            <div class="cert-icon"><i class="bi bi-patch-check-fill"></i></div>
            <div>
                <div class="cert-name"><?= e($c['title']) ?></div>
                <div class="cert-sub">
                    <i class="bi bi-hash" style="font-size:.68rem"></i><?= e($c['certificate_number']) ?>
                    &nbsp;·&nbsp;
                    <i class="bi bi-calendar3" style="font-size:.68rem"></i> <?= e(date('d M Y', strtotime($c['issued_at']))) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- ── Empty state ── -->
<?php if (!$pCount && !$sCount && !$aCount && !$cCount): ?>
<div class="pc" style="text-align:center;padding:48px 20px;">
    <div style="font-size:2.5rem;margin-bottom:12px">🛠️</div>
    <div style="font-size:.95rem;font-weight:700;color:var(--ink);margin-bottom:6px">Portfolio in progress</div>
    <div style="font-size:.82rem;color:var(--muted)">This student is currently building their profile. Check back soon.</div>
</div>
<?php endif; ?>

<!-- ── Peer Cookies ── -->
<?php
$cd = $cookieData ?? ['total'=>0,'avg'=>0,'reviews'=>[]];
if ($cd['total'] > 0):
?>
<div class="pc pa" style="animation-delay:.36s;">
    <div class="pc-head">
        <h2 class="pc-title"><i class="bi bi-emoji-smile-fill"></i> Peer Recognition</h2>
        <span class="pc-badge"><?= (int)$cd['total'] ?> 🍪</span>
    </div>

    <!-- Cookie summary bar -->
    <div style="display:flex;align-items:center;gap:16px;padding:14px 16px;
                background:#fef9c3;border:1px solid #fde047;border-radius:12px;margin-bottom:16px;">
        <div style="font-size:2.2rem;flex-shrink:0;">🍪</div>
        <div>
            <div style="font-size:1rem;font-weight:800;color:#92400e;letter-spacing:-.3px;">
                <?= (int)$cd['total'] ?> cookie<?= $cd['total']>1?'s':'' ?> received
            </div>
            <div style="font-size:.76rem;color:#b45309;margin-top:2px;">
                Avg <?= number_format((float)$cd['avg'],1) ?>/5 &nbsp;·&nbsp; Given by peers for helpful support &amp; collaboration
            </div>
        </div>
    </div>

    <?php if (!empty($cd['reviews'])): ?>
    <div style="display:grid;gap:10px;">
        <?php foreach ($cd['reviews'] as $rv): ?>
        <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 14px;
                    background:var(--bg);border:1px solid var(--border);border-radius:12px;">
            <div style="width:32px;height:32px;border-radius:50%;overflow:hidden;
                        flex-shrink:0;background:#e5e5e5;display:grid;place-items:center;font-size:.72rem;font-weight:700;">
                <?php if (!empty($rv['from_avatar'])): ?>
                    <img src="<?= base_url('/uploads/'.e($rv['from_avatar'])) ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else: ?>
                    <?= strtoupper(substr($rv['from_name'],0,1)) ?>
                <?php endif; ?>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;flex-wrap:wrap;">
                    <span style="font-size:.82rem;font-weight:700;color:var(--ink);"><?= e($rv['from_name']) ?></span>
                    <span style="font-size:.78rem;color:#d97706;"><?= str_repeat('🍪',(int)$rv['cookies']) ?></span>
                    <span style="font-size:.67rem;color:var(--muted);margin-left:auto;"><?= date('d M Y',strtotime($rv['created_at'])) ?></span>
                </div>
                <div style="font-size:.8rem;color:var(--ink3);line-height:1.6;font-style:italic;">
                    "<?= e($rv['review']) ?>"
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── TCM Branding CTA ── -->
<div class="pa" style="animation-delay:.38s;margin-bottom:14px;">
    <div style="background:#111;border-radius:20px;padding:32px 28px;position:relative;overflow:hidden;text-align:center;">
        <!-- dot texture -->
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,.05) 1px,transparent 1px);background-size:20px 20px;pointer-events:none;"></div>
        <!-- glow -->
        <div style="position:absolute;top:-60px;right:-60px;width:200px;height:200px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);pointer-events:none;"></div>

        <!-- TCM logo badge -->
        <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);border-radius:50px;padding:6px 16px;margin-bottom:16px;position:relative;z-index:1;">
            <div style="width:22px;height:22px;background:#fff;border-radius:6px;display:grid;place-items:center;font-size:.7rem;color:#111;font-weight:800;flex-shrink:0;">
                <i class="bi bi-code-slash"></i>
            </div>
            <span style="font-size:.72rem;font-weight:700;color:rgba(255,255,255,.7);letter-spacing:.08em;text-transform:uppercase;">The Code Munk</span>
        </div>

        <div style="position:relative;z-index:1;">
            <div style="font-size:1.2rem;font-weight:800;color:#fff;letter-spacing:-.3px;margin-bottom:8px;line-height:1.25;">
                Build your developer portfolio like <?= e($owner['name']) ?>
            </div>
            <div style="font-size:.84rem;color:rgba(255,255,255,.5);margin-bottom:22px;max-width:420px;margin-left:auto;margin-right:auto;line-height:1.65;">
                Join 1,200+ students learning real skills, building projects and showcasing their work on The Code Munk platform.
            </div>

            <!-- Feature pills -->
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;margin-bottom:22px;">
                <?php foreach ([
                    ['bi-journal-code',   'Free Portfolio'],
                    ['bi-broadcast',      'Live Classes'],
                    ['bi-patch-check',    'Certificates'],
                    ['bi-people-fill',    'Community'],
                ] as [$icon, $label]): ?>
                <span style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.14);border-radius:20px;padding:5px 13px;font-size:.74rem;font-weight:600;color:rgba(255,255,255,.75);">
                    <i class="bi <?= $icon ?>" style="font-size:.76rem;"></i> <?= $label ?>
                </span>
                <?php endforeach; ?>
            </div>

            <!-- CTAs -->
            <div style="display:flex;gap:10px;justify-content:center;flex-wrap:wrap;">
                <a href="<?= base_url('/auth/register') ?>"
                   style="display:inline-flex;align-items:center;gap:8px;padding:11px 24px;border-radius:11px;background:#fff;color:#111;font-size:.88rem;font-weight:800;text-decoration:none;transition:background .15s,transform .15s;"
                   onmouseover="this.style.background='#f0f0f0';this.style.transform='translateY(-1px)'"
                   onmouseout="this.style.background='#fff';this.style.transform=''">
                    <i class="bi bi-person-plus-fill"></i> Join Free — Build Your Portfolio
                </a>
                <a href="<?= base_url('/') ?>"
                   style="display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:11px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.2);font-size:.84rem;font-weight:700;text-decoration:none;transition:background .15s;"
                   onmouseover="this.style.background='rgba(255,255,255,.18)'"
                   onmouseout="this.style.background='rgba(255,255,255,.1)'">
                    <i class="bi bi-info-circle"></i> Learn More
                </a>
            </div>

            <div style="margin-top:14px;font-size:.72rem;color:rgba(255,255,255,.3);">
                Free to join · No credit card · Start in 2 minutes
            </div>
        </div>
    </div>
</div>

<!-- ── Footer ── -->
<div class="pfooter pa" style="animation-delay:.4s">
    <div style="margin-bottom:10px">
        <a href="<?= base_url('/') ?>" style="display:inline-flex;align-items:center;gap:8px;font-size:.8rem;font-weight:800;color:var(--ink)">
            <span style="background:var(--ink);color:#fff;padding:3px 9px;border-radius:6px;font-size:.72rem">TCM</span>
            The Code Munk
        </a>
    </div>
    Powered by <a href="<?= base_url('/') ?>">The Code Munk</a> · Learn. Build. Get Hired.<br>
    <span style="color:var(--muted2);font-size:.7rem">© <?= date('Y') ?> The Code Munk. All rights reserved.</span>
</div>

</div><!-- /.pw -->

<script>
// Copy share link
function pfCopy(btn) {
    var url = window.location.href;
    navigator.clipboard.writeText(url).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
        setTimeout(function() { btn.innerHTML = orig; }, 2500);
    }).catch(function() {
        prompt('Copy this link:', url);
    });
}

// Show sticky CTA bar after scroll
(function() {
    var bar = document.getElementById('tcm-sticky-bar');
    if (!bar) return;
    var shown = false;
    window.addEventListener('scroll', function() {
        if (window.scrollY > 400 && !shown) {
            shown = true;
            bar.style.transform = 'translateY(0)';
            bar.style.opacity   = '1';
        }
    }, { passive: true });
})();

// Scroll-triggered animations
(function() {
    var items = document.querySelectorAll('.pa');
    if (!('IntersectionObserver' in window)) {
        items.forEach(function(el) { el.style.opacity='1'; el.style.transform='none'; });
        return;
    }
    var obs = new IntersectionObserver(function(entries) {
        entries.forEach(function(e) {
            if (e.isIntersecting) {
                e.target.style.animationPlayState = 'running';
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });

    items.forEach(function(el) {
        // items with explicit delay run on load; others wait for scroll
        if (!el.style.animationDelay || parseFloat(el.style.animationDelay) <= 0.2) {
            el.style.animationPlayState = 'running';
        } else {
            el.style.animationPlayState = 'paused';
            obs.observe(el);
        }
    });
})();
</script>
<!-- ── Sticky bottom CTA bar (appears on scroll) ── -->
<div id="tcm-sticky-bar" style="
    position:fixed; bottom:0; left:0; right:0; z-index:500;
    background:#111; border-top:1px solid rgba(255,255,255,.1);
    padding:12px 20px;
    display:flex; align-items:center; justify-content:space-between;
    gap:12px; flex-wrap:wrap;
    transform:translateY(100%); opacity:0;
    transition:transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s ease;
    font-family:-apple-system,BlinkMacSystemFont,'Inter',sans-serif;
">
    <div style="display:flex;align-items:center;gap:10px;flex:1;min-width:0;">
        <div style="width:30px;height:30px;background:#fff;border-radius:8px;display:grid;place-items:center;font-size:.78rem;color:#111;font-weight:800;flex-shrink:0;">
            <i class="bi bi-code-slash"></i>
        </div>
        <div style="min-width:0;">
            <div style="font-size:.8rem;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Build your portfolio on TCM</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.45);">Free · Live classes · Real projects</div>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-shrink:0;">
        <a href="<?= base_url('/auth/register') ?>"
           style="display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:9px;background:#fff;color:#111;font-size:.78rem;font-weight:800;text-decoration:none;white-space:nowrap;">
            <i class="bi bi-person-plus-fill"></i> Join Free
        </a>
        <button onclick="document.getElementById('tcm-sticky-bar').style.display='none'"
                style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.2);color:rgba(255,255,255,.6);cursor:pointer;display:grid;place-items:center;font-size:.85rem;">
            <i class="bi bi-x"></i>
        </button>
    </div>
</div>

</body>
</html>
