<?php
$firstName = explode(' ', $user['name'])[0];
$h         = (int) date('H');
$greeting  = $h < 12 ? 'Good morning' : ($h < 17 ? 'Good afternoon' : 'Good evening');
$pct       = (int) $portfolioStrength;
$dash      = round(15.9 * 2 * M_PI * $pct / 100, 2);
$gap       = round(15.9 * 2 * M_PI - $dash, 2);
$studentId  = $user['student_id']  ?? null;
$referralId = $user['referral_id'] ?? null;
// Pending payments
$pendingCount = $pendingPayments ?? 0;
?>

<style>
/* ── Student Dashboard Extra Styles ── */
.std-hero {
    background: #111;
    border-radius: 20px;
    padding: 26px 30px;
    margin-bottom: 18px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
    justify-content: space-between;
}
.std-hero::before {
    content: '';
    position: absolute;
    top: -50px; right: -50px;
    width: 220px; height: 220px;
    border-radius: 50%;
    background: radial-gradient(circle, rgba(255,255,255,.06) 0%, transparent 70%);
    pointer-events: none;
}
.std-hero-left { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
.std-hero-avatar {
    width: 60px; height: 60px;
    border-radius: 50%; overflow: hidden;
    border: 2.5px solid rgba(255,255,255,.25);
    background: #333;
    display: grid; place-items: center;
    font-size: 1.4rem; font-weight: 800; color: #fff;
    flex-shrink: 0;
    position: relative;
    text-decoration: none;
    transition: border-color .2s, transform .2s;
    box-shadow: 0 4px 16px rgba(0,0,0,.3);
}
.std-hero-avatar:hover { border-color: rgba(255,255,255,.5); transform: scale(1.06); }
.std-hero-avatar img { width: 100%; height: 100%; object-fit: cover; }
.std-hero-avatar .std-hero-edit {
    position: absolute;
    bottom: -1px; right: -1px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: #fff; color: #111;
    display: grid; place-items: center;
    font-size: .55rem;
    border: 1.5px solid #222;
}
.std-hero-date {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: rgba(255,255,255,.45);
    margin-bottom: 7px;
}
.std-hero-dot {
    width: 5px; height: 5px; border-radius: 50%;
    background: #4ade80;
    animation: std-blink 1.5s infinite;
}
@keyframes std-blink { 0%,100%{opacity:1} 50%{opacity:.2} }
.std-hero-name {
    font-size: 1.3rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -.3px;
    margin-bottom: 3px;
}
.std-hero-sub { font-size: .82rem; color: rgba(255,255,255,.45); margin: 0; }
.std-hero-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.std-hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 10px;
    font-size: .83rem;
    font-weight: 600;
    text-decoration: none;
    transition: .15s;
}
.std-hero-btn.white { background: #fff; color: #111; }
.std-hero-btn.white:hover { background: #f0f0f0; color: #111; }
.std-hero-btn.outline { background: rgba(255,255,255,.1); color: rgba(255,255,255,.85); border: 1px solid rgba(255,255,255,.2); }
.std-hero-btn.outline:hover { background: rgba(255,255,255,.18); color: #fff; }

/* ID Cards */
.std-id-strip {
    display: flex;
    gap: 10px;
    margin-bottom: 18px;
    flex-wrap: wrap;
}
.std-id-card {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 12px;
    padding: 12px 16px;
    flex: 1;
    min-width: 200px;
    transition: border-color .15s;
}
.std-id-card:hover { border-color: #ddd; }
.std-id-icon {
    width: 36px; height: 36px;
    border-radius: 10px;
    background: #111;
    color: #fff;
    display: grid;
    place-items: center;
    font-size: .9rem;
    flex-shrink: 0;
}
.std-id-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #aaa; margin-bottom: 3px; }
.std-id-value { font-size: .92rem; font-weight: 800; color: #111; font-family: 'Courier New', monospace; letter-spacing: .5px; }
.std-id-copy {
    margin-left: auto;
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    font-size: .85rem;
    padding: 4px;
    border-radius: 6px;
    transition: color .15s, background .15s;
}
.std-id-copy:hover { color: #111; background: #f5f5f5; }

/* Stats */
.std-stats {
    display: grid;
    grid-template-columns: repeat(4,1fr);
    gap: 10px;
    margin-bottom: 18px;
}
.std-stat {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 14px;
    padding: 16px 18px;
    transition: border-color .15s, transform .15s, box-shadow .15s;
}
.std-stat:hover { border-color: #ddd; transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.05); }
.std-stat-icon { font-size: 1.1rem; color: #888; margin-bottom: 10px; display: block; }
.std-stat-label { font-size: .65rem; font-weight: 700; text-transform: uppercase; letter-spacing: .08em; color: #aaa; margin-bottom: 5px; }
.std-stat-value { font-size: 1.9rem; font-weight: 800; color: #111; letter-spacing: -1px; line-height: 1; }

/* Pending payment banner */
.std-pending-banner {
    background: #fff8e1;
    border: 1.5px solid #ffe082;
    border-radius: 14px;
    padding: 14px 20px;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

/* Section cards */
.std-section-card {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 16px;
    padding: 20px;
    height: 100%;
}
.std-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
    padding-bottom: 14px;
    border-bottom: 1px solid #f5f5f5;
}
.std-section-title {
    font-size: .88rem;
    font-weight: 700;
    color: #111;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.std-section-title i { color: #888; font-size: .85rem; }

/* Course row */
.std-course-row {
    padding: 11px 0;
    border-bottom: 1px solid #f7f7f7;
    display: flex;
    align-items: center;
    gap: 12px;
}
.std-course-row:last-child { border-bottom: none; padding-bottom: 0; }
.std-course-row:first-child { padding-top: 0; }
.std-course-icon {
    width: 38px; height: 38px;
    border-radius: 10px;
    background: #f5f5f5;
    border: 1px solid #ececec;
    display: grid; place-items: center;
    font-size: .95rem; color: #555;
    flex-shrink: 0;
}
.std-course-title { font-size: .86rem; font-weight: 600; color: #111; margin-bottom: 5px; }
.std-course-progress { height: 3px; background: #f0f0f0; border-radius: 99px; overflow: hidden; margin-bottom: 3px; }
.std-course-progress-fill { height: 100%; background: #111; border-radius: 99px; transition: width .5s ease; }
.std-course-sub { font-size: .72rem; color: #aaa; }

/* Portfolio ring */
.std-ring-wrap { position: relative; width: 68px; height: 68px; flex-shrink: 0; }
.std-ring-label { position: absolute; inset: 0; display: grid; place-items: center; font-size: .8rem; font-weight: 800; color: #111; }

@media (max-width: 768px) {
    .std-stats { grid-template-columns: repeat(2,1fr); }
    .std-hero { padding: 20px; }
    .std-hero-name { font-size: 1.1rem; }
    .std-id-strip { flex-direction: column; }
}
@media (max-width: 400px) {
    .std-stats { grid-template-columns: 1fr 1fr; gap: 8px; }
}
</style>

<!-- ── Hero ─────────────────────────────────────────── -->
<div class="std-hero">
    <div class="std-hero-left">
        <a href="<?= base_url('/student/profile') ?>" class="std-hero-avatar" title="Edit profile">
            <?= tcm_avatar($user['avatar'] ?? null, $user['name']) ?>
            <span class="std-hero-edit"><i class="bi bi-pencil-fill"></i></span>
        </a>
        <div>
            <div class="std-hero-date">
                <span class="std-hero-dot"></span>
                <?= date('l, d M Y') ?>
            </div>
            <div class="std-hero-name"><?= e($greeting) ?>, <?= e($firstName) ?> 👋</div>
            <p class="std-hero-sub">Keep building — your next milestone is just a lesson away.</p>
        </div>
    </div>
    <div class="std-hero-actions">
        <a href="<?= base_url('/student/courses') ?>" class="std-hero-btn white">
            <i class="bi bi-journal-code"></i> Browse Courses
        </a>
        <a href="<?= base_url('/student/programs') ?>" class="std-hero-btn outline">
            <i class="bi bi-stack"></i> Programs
        </a>
        <a href="<?= base_url('/student/payments') ?>" class="std-hero-btn outline">
            <i class="bi bi-receipt"></i> Payments
        </a>
    </div>
</div>

<!-- ── Student ID & Referral Cards ─────────────────── -->
<?php if ($studentId || $referralId): ?>
<div class="std-id-strip">
    <?php if ($studentId): ?>
    <div class="std-id-card">
        <div class="std-id-icon"><i class="bi bi-person-badge"></i></div>
        <div>
            <div class="std-id-label">Student ID</div>
            <div class="std-id-value" id="studentId"><?= e($studentId) ?></div>
        </div>
        <button class="std-id-copy" onclick="copyText('<?= e($studentId) ?>', this)" title="Copy">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
    <?php if ($referralId): ?>
    <div class="std-id-card">
        <div class="std-id-icon" style="background:#4ade80;"><i class="bi bi-share-fill" style="color:#fff;"></i></div>
        <div>
            <div class="std-id-label">Referral ID</div>
            <div class="std-id-value" id="referralId"><?= e($referralId) ?></div>
        </div>
        <button class="std-id-copy" onclick="copyText('<?= e($referralId) ?>', this)" title="Copy referral code">
            <i class="bi bi-copy"></i>
        </button>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Live Class Link ─────────────────────────────── -->
<?php
// ── Live Class Links ───────────────────────────────
// Shows: upcoming (next 24h) + currently live (within window) + most recent
$liveLinks = [];
try {
    $lcExists = (bool) \TCM\Core\Database::scalar(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='live_class_links'"
    );
    if ($lcExists) {
        $uid = (int)$user['id'];
        $enrolledCourseIds  = array_column($enrollments ?? [], 'course_id');
        $enrolledProgramIds = array_column(
            \TCM\Core\Database::all("SELECT program_id FROM program_enrollments WHERE user_id=?", [$uid]),
            'program_id'
        );

        // Build condition: all | enrolled course | enrolled program | specific student
        $conditions = ["target = 'all'"];
        $params = [];

        if (!empty($enrolledCourseIds)) {
            $conditions[] = "(target = 'course' AND target_id IN (" . implode(',', array_fill(0, count($enrolledCourseIds), '?')) . "))";
            $params = array_merge($params, $enrolledCourseIds);
        }
        if (!empty($enrolledProgramIds)) {
            $conditions[] = "(target = 'program' AND target_id IN (" . implode(',', array_fill(0, count($enrolledProgramIds), '?')) . "))";
            $params = array_merge($params, $enrolledProgramIds);
        }

        // Check if this student is directly targeted (specific)
        // We store specific_student_ids as comma-separated in a hidden column? No — we pass via notification.
        // For 'specific' target we notify via in-app, but also show on dashboard if student was notified
        $wasNotified = (bool) \TCM\Core\Database::scalar(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND icon = '📡' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)",
            [$uid]
        );
        if ($wasNotified) {
            // Fetch links that match the notification window
            $conditions[] = "target = 'specific'";
        }

        $where = implode(' OR ', $conditions);

        // Show links: 
        // - Scheduled in FUTURE (next 7 days) 
        // - OR currently LIVE (started within last 2 hours but not ended)
        // - Hide old classes (more than 2 hours past scheduled time)
        $sql = "SELECT * FROM live_class_links
                WHERE ($where)
                  AND (
                    -- Future scheduled classes (next 7 days)
                    (scheduled_at IS NOT NULL 
                     AND scheduled_at > NOW() 
                     AND scheduled_at <= DATE_ADD(NOW(), INTERVAL 7 DAY))
                    
                    -- OR currently live (started within last 2 hours)
                    OR (scheduled_at IS NOT NULL 
                        AND scheduled_at <= NOW() 
                        AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR))
                    
                    -- OR no schedule set and created recently
                    OR (scheduled_at IS NULL 
                        AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR))
                  )
                ORDER BY
                    -- Live classes first
                    CASE 
                        WHEN scheduled_at IS NOT NULL 
                             AND scheduled_at <= NOW() 
                             AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR) 
                        THEN 0
                        ELSE 1 
                    END,
                    -- Then upcoming by scheduled time
                    scheduled_at ASC,
                    created_at DESC
                LIMIT 3";

        $liveLinks = \TCM\Core\Database::all($sql, $params);

        // Reminder: if a class is within 30 minutes, save an in-app notification (once)
        foreach ($liveLinks as $ll) {
            if (!$ll['scheduled_at']) continue;
            $secsToClass = strtotime($ll['scheduled_at']) - time();
            if ($secsToClass > 0 && $secsToClass <= 1800) {
                // Check if reminder already sent for this link
                $alreadyReminded = (bool)\TCM\Core\Database::scalar(
                    "SELECT COUNT(*) FROM notifications WHERE user_id=? AND click_url LIKE ? AND created_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)",
                    [$uid, '%live-class-remind-'.$ll['id'].'%']
                );
                if (!$alreadyReminded) {
                    \TCM\Models\Notification::ensureTable();
                    \TCM\Models\Notification::forUser(
                        $uid,
                        '⏰ Class starting in ' . ceil($secsToClass/60) . ' min!',
                        $ll['title'] . ' is about to start. Click to join.',
                        '⏰',
                        base_url('/student') . '?live-class-remind-' . $ll['id']
                    );
                }
            }
        }
    }
} catch (\Throwable $_e) {
    error_log('[Live Classes] Error: ' . $_e->getMessage());
    error_log('[Live Classes] Trace: ' . $_e->getTraceAsString());
}
// Debug logging
error_log('[Live Classes] Found ' . count($liveLinks) . ' links for user: ' . ($uid ?? 'unknown'));
?>
<?php if (!empty($liveLinks)): ?>
<?php foreach ($liveLinks as $ll):
    $now       = time();
    $schedTime = $ll['scheduled_at'] ? strtotime($ll['scheduled_at']) : null;
    $secsLeft  = $schedTime ? ($schedTime - $now) : null;
    $isLive    = $schedTime === null || ($secsLeft !== null && $secsLeft <= 300 && $secsLeft >= -7200);
    $isUpc     = $secsLeft !== null && $secsLeft > 300;
    $minsLeft  = $secsLeft !== null && $secsLeft > 0 ? (int)ceil($secsLeft / 60) : 0;
    if ($isLive) {
        $bg      = '#0d1b0d';
        $badge   = '<span style="display:inline-flex;align-items:center;gap:5px;background:#22c55e;color:#fff;font-size:.62rem;font-weight:800;padding:2px 9px;border-radius:20px;margin-bottom:5px;letter-spacing:.04em;"><span style="width:5px;height:5px;border-radius:50%;background:#fff;animation:std-blink 1s infinite;display:inline-block;"></span>LIVE NOW</span>';
        $btnTxt  = '<i class="bi bi-camera-video-fill"></i> Join Now';
        $btnSty  = 'background:#22c55e;color:#fff;';
        $border  = '1px solid #22c55e44';
    } elseif ($isUpc && $minsLeft <= 30) {
        $bg      = '#1a1500';
        $badge   = '<span style="display:inline-flex;align-items:center;gap:5px;background:#f59e0b;color:#111;font-size:.62rem;font-weight:800;padding:2px 9px;border-radius:20px;margin-bottom:5px;">⏰ Starting in '.$minsLeft.' min</span>';
        $btnTxt  = '<i class="bi bi-camera-video-fill"></i> Join Class';
        $btnSty  = 'background:#f59e0b;color:#111;';
        $border  = '1px solid #f59e0b44';
    } else {
        $bg      = '#111';
        $hstr    = $minsLeft >= 60 ? round($minsLeft/60,1).'h' : $minsLeft.'m';
        $badge   = '<span style="display:inline-flex;align-items:center;gap:5px;background:rgba(255,255,255,.1);color:rgba(255,255,255,.65);font-size:.62rem;font-weight:700;padding:2px 9px;border-radius:20px;margin-bottom:5px;"><i class="bi bi-calendar-event" style="font-size:.6rem;"></i>Scheduled'.($isUpc&&$minsLeft>0?' · '.$hstr.' away':'').'</span>';
        $btnTxt  = '<i class="bi bi-camera-video-fill"></i> Join Class';
        $btnSty  = 'background:#fff;color:#111;';
        $border  = '1px solid transparent';
    }
?>
<div style="background:<?= $bg ?>;border-radius:14px;padding:16px 20px;margin-bottom:10px;
            display:flex;align-items:center;gap:14px;flex-wrap:wrap;border:<?= $border ?>;">
    <div style="width:42px;height:42px;border-radius:11px;
                background:<?= $isLive ? '#22c55e22' : 'rgba(255,255,255,.1)' ?>;
                display:grid;place-items:center;font-size:1.15rem;color:#fff;flex-shrink:0;">
        <i class="bi bi-broadcast"></i>
    </div>
    <div style="flex:1;min-width:0;">
        <?= $badge ?>
        <div style="font-size:.88rem;font-weight:800;color:#fff;line-height:1.3;"><?= e($ll['title']) ?></div>
        <?php if (!empty($ll['description'])): ?>
        <div style="font-size:.74rem;color:rgba(255,255,255,.5);margin-top:2px;"><?= e($ll['description']) ?></div>
        <?php endif; ?>
        <?php if ($schedTime && !$isLive): ?>
        <div style="font-size:.71rem;color:rgba(255,255,255,.35);margin-top:3px;display:flex;align-items:center;gap:4px;">
            <i class="bi bi-clock" style="font-size:.68rem;"></i><?= e(date('d M Y · h:i A', $schedTime)) ?>
        </div>
        <?php endif; ?>
    </div>
    <a href="<?= e($ll['meeting_url']) ?>" target="_blank" rel="noopener"
       style="display:inline-flex;align-items:center;gap:7px;padding:10px 20px;border-radius:10px;
              <?= $btnSty ?>font-size:.84rem;font-weight:800;text-decoration:none;flex-shrink:0;"
       onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
        <?= $btnTxt ?>
    </a>
</div>
<?php endforeach; ?>
<div style="margin-bottom:8px;"></div>
<?php endif; ?>

<!-- ── Pending Payment Alert ────────────────────────── -->
<?php if ($pendingCount > 0): ?>
<div class="std-pending-banner">
    <i class="bi bi-clock-history" style="font-size:1.3rem;color:#f59e0b;flex-shrink:0;"></i>
    <div style="flex:1;">
        <strong style="font-size:.88rem;color:#92400e;display:block;">
            <?= $pendingCount ?> payment<?= $pendingCount > 1 ? 's' : '' ?> under review
        </strong>
        <span style="font-size:.78rem;color:#b45309;">
            Our team is verifying your payment proof. You'll get access once approved.
        </span>
    </div>
    <a href="<?= base_url('/student/payments') ?>" class="tcm-btn sm" style="background:#fff;border-color:#ffe082;">
        <i class="bi bi-eye"></i> View Status
    </a>
</div>
<?php endif; ?>

<!-- ── Stats ─────────────────────────────────────────── -->
<div class="std-stats">
    <div class="std-stat">
        <i class="bi bi-journal-code std-stat-icon"></i>
        <div class="std-stat-label">Enrolled</div>
        <div class="std-stat-value"><?= count($enrollments) ?></div>
    </div>
    <div class="std-stat">
        <i class="bi bi-calendar-check std-stat-icon"></i>
        <div class="std-stat-label">Events</div>
        <div class="std-stat-value"><?= count($registrations) ?></div>
    </div>
    <div class="std-stat">
        <i class="bi bi-patch-check std-stat-icon"></i>
        <div class="std-stat-label">Certificates</div>
        <div class="std-stat-value"><?= count($certificates) ?></div>
    </div>
    <div class="std-stat">
        <i class="bi bi-graph-up-arrow std-stat-icon"></i>
        <div class="std-stat-label">Portfolio</div>
        <div class="std-stat-value"><?= $pct ?>%</div>
        <div style="margin-top:8px;height:3px;background:#f0f0f0;border-radius:99px;overflow:hidden;">
            <div style="height:100%;background:#111;border-radius:99px;width:<?= $pct ?>%;transition:width .5s ease;"></div>
        </div>
    </div>
</div>

<!-- ── Wallet Widget (Compact) ─────────────────────────── -->
<div style="margin-bottom:20px;">
    <a href="<?= base_url('/student/wallet') ?>" 
       style="background:#fff;border:1px solid rgba(0,0,0,0.15);
              border-radius:10px;padding:12px 16px;color:#111;text-decoration:none;
              display:inline-flex;align-items:center;gap:10px;transition:all 0.2s;"
       onmouseover="this.style.borderColor='rgba(0,0,0,0.3)';this.style.transform='translateY(-2px)';this.style.boxShadow='0 2px 8px rgba(0,0,0,0.08)'" 
       onmouseout="this.style.borderColor='rgba(0,0,0,0.15)';this.style.transform='';this.style.boxShadow=''">
        <i class="bi bi-wallet2" style="font-size:1.2rem;color:#111;"></i>
        <div>
            <div style="font-size:0.68rem;font-weight:600;text-transform:uppercase;letter-spacing:0.08em;color:#888;margin-bottom:2px;">
                Wallet Balance
            </div>
            <div style="font-size:1.3rem;font-weight:800;color:#111;letter-spacing:-0.5px;line-height:1;">
                ₹<?= number_format($walletBalance ?? 0, 2) ?>
            </div>
        </div>
    </a>
</div>

<!-- ── Main Grid ─────────────────────────────────────── -->
<div class="tcm-grid-2" style="align-items:start;">

    <!-- My Courses -->
    <div class="std-section-card">
        <div class="std-section-head">
            <h3 class="std-section-title"><i class="bi bi-journal-code"></i> My Courses</h3>
            <a href="<?= base_url('/student/courses') ?>" class="tcm-btn ghost sm">
                Browse <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php if ($enrollments === []): ?>
            <div class="tcm-empty">
                <i class="bi bi-journal-x"></i>
                No courses enrolled yet.<br>
                <a href="<?= base_url('/student/courses') ?>">Explore courses →</a>
            </div>
        <?php else: ?>
            <?php foreach ($enrollments as $en): ?>
            <div class="std-course-row">
                <div class="std-course-icon">
                    <i class="bi <?= e($en['icon'] ?? 'bi-journal-code') ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="std-course-title"><?= e($en['title']) ?></div>
                    <div class="std-course-progress">
                        <div class="std-course-progress-fill" style="width:<?= (int)$en['progress'] ?>%;"></div>
                    </div>
                    <div class="std-course-sub"><?= (int)$en['progress'] ?>% complete</div>
                </div>
                <a class="tcm-btn primary sm" href="<?= base_url('/student/learn/'.$en['course_id']) ?>">
                    Continue
                </a>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Payment History Quick View -->
    <div class="std-section-card">
        <div class="std-section-head">
            <h3 class="std-section-title"><i class="bi bi-receipt"></i> Payment History</h3>
            <a href="<?= base_url('/student/payments') ?>" class="tcm-btn ghost sm">
                View all <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php if (empty($recentPayments)): ?>
            <div class="tcm-empty">
                <i class="bi bi-receipt"></i>
                No payments yet.<br>
                When you enrol in a paid course or event, your payment history appears here.
            </div>
        <?php else: ?>
            <?php foreach (array_slice($recentPayments, 0, 4) as $p):
                $statusCls = match($p['status']) {
                    'approved' => 'green',
                    'rejected' => 'red',
                    default    => 'amber',
                };
                $statusLabel = match($p['status']) {
                    'approved' => '✓ Approved',
                    'rejected' => '✕ Rejected',
                    default    => '⏳ Pending',
                };
            ?>
            <div class="tcm-row">
                <div class="tcm-row-main">
                    <div class="tcm-row-title" style="font-size:.85rem;"><?= e($p['item_title']) ?></div>
                    <div class="tcm-row-sub"><?= e(date('d M Y', strtotime($p['created_at']))) ?> · <?= money($p['amount']) ?></div>
                </div>
                <div style="text-align:right;">
                    <span class="tcm-badge <?= $statusCls ?>" style="font-size:.66rem;"><?= $statusLabel ?></span>
                    <?php if ($p['status'] === 'approved' && $p['receipt_number']): ?>
                        <br><a href="<?= base_url('/student/payments/'.$p['id'].'/receipt') ?>" target="_blank"
                               style="font-size:.72rem;color:#111;font-weight:600;text-decoration:none;margin-top:4px;display:inline-block;">
                            <i class="bi bi-download"></i> Receipt
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid #f5f5f5;">
            <a href="<?= base_url('/student/payments/submit?type=course&id=') ?>"
               class="tcm-btn primary sm w-full" style="justify-content:center;">
                <i class="bi bi-plus-lg"></i> Submit Payment
            </a>
        </div>
    </div>

    <!-- Live Sessions -->
    <div class="std-section-card">
        <div class="std-section-head">
            <h3 class="std-section-title"><i class="bi bi-broadcast"></i> Live Sessions</h3>
            <a href="<?= base_url('/student/programs') ?>" class="tcm-btn ghost sm">
                Programs <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <?php if (empty($liveSessions)): ?>
            <div class="tcm-empty">
                <i class="bi bi-broadcast"></i>
                No sessions scheduled.<br>
                <a href="<?= base_url('/student/programs') ?>">Join a program →</a>
            </div>
        <?php else: ?>
            <?php foreach ($liveSessions as $ls): ?>
            <div class="tcm-row">
                <div class="tcm-row-main">
                    <div class="tcm-row-title">
                        <?php if ($ls['status'] === 'live'): ?>
                            <span class="tcm-badge green" style="font-size:.58rem;padding:2px 7px;">● LIVE</span>
                        <?php endif; ?>
                        <?= e($ls['title']) ?>
                    </div>
                    <div class="tcm-row-sub"><?= e($ls['program_title'] ?? $ls['course_title'] ?? '') ?></div>
                </div>
                <div class="tcm-row-meta">
                    <?= $ls['session_date'] ? e(date('d M', strtotime($ls['session_date']))) : '' ?><br>
                    <?= $ls['start_time'] ? e(date('h:i A', strtotime($ls['start_time']))) : '' ?>
                    <?php if (!empty($ls['meeting_url']) && $ls['status'] === 'live'): ?>
                        <a class="tcm-btn primary sm" href="<?= e($ls['meeting_url']) ?>" target="_blank"
                           style="margin-top:6px;display:inline-flex;">
                            <i class="bi bi-camera-video"></i> Join
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Portfolio + Upcoming Events -->
    <div class="std-section-card">
        <div class="std-section-head">
            <h3 class="std-section-title"><i class="bi bi-briefcase"></i> Portfolio</h3>
            <a href="<?= base_url('/student/portfolio') ?>" class="tcm-btn ghost sm">
                Edit <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="d-flex items-center gap-12" style="padding:4px 0 14px;">
            <div class="std-ring-wrap">
                <svg viewBox="0 0 36 36" width="68" height="68" style="transform:rotate(-90deg);">
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#f0f0f0" stroke-width="3"/>
                    <circle cx="18" cy="18" r="15.9" fill="none" stroke="#111" stroke-width="3"
                            stroke-dasharray="<?= $dash ?> <?= $gap ?>" stroke-linecap="round"/>
                </svg>
                <div class="std-ring-label"><?= $pct ?>%</div>
            </div>
            <div>
                <div style="font-size:.9rem;font-weight:700;color:#111;">
                    <?= $pct >= 80 ? 'Strong profile!' : ($pct >= 40 ? 'Looking good' : 'Just starting') ?>
                </div>
                <div style="font-size:.78rem;color:#aaa;margin-top:3px;line-height:1.55;">
                    <?= $pct < 100 ? 'Add projects &amp; skills to boost.' : 'Portfolio complete 🎉' ?>
                </div>
            </div>
        </div>
        <div style="height:1px;background:#f5f5f5;margin-bottom:14px;"></div>

        <!-- Upcoming events inline -->
        <div style="font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:10px;">
            <i class="bi bi-calendar-event"></i> Upcoming Events
        </div>
        <?php $evShown = 0; foreach ($registrations as $r): if ($r['event_status'] === 'past') continue; $evShown++; ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f7f7f7;gap:8px;">
            <div style="font-size:.83rem;font-weight:500;color:#111;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                <i class="bi bi-circle" style="font-size:.45rem;color:#ccc;margin-right:5px;"></i>
                <?= e($r['title']) ?>
            </div>
            <div style="font-size:.75rem;color:#aaa;flex-shrink:0;">
                <?= $r['event_date'] ? e(date('d M', strtotime($r['event_date']))) : '' ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if ($evShown === 0): ?>
            <div style="font-size:.82rem;color:#bbb;padding:6px 0;">
                No upcoming events. <a href="<?= base_url('/student/events') ?>" style="color:#111;font-weight:600;">Find one →</a>
            </div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;margin-top:14px;flex-wrap:wrap;">
            <a href="<?= base_url('/student/portfolio') ?>" class="tcm-btn primary sm">
                <i class="bi bi-plus-lg"></i> Add Project
            </a>
            <a href="<?= base_url('/portfolio/'.($user['id']??0)) ?>" target="_blank" class="tcm-btn sm">
                <i class="bi bi-box-arrow-up-right"></i> View Public
            </a>
        </div>
    </div>

</div>

<!-- ── Chat & Help Quick Widget ── -->
<?php
// Safely check for chat tables and load recent data
$chatGroups    = [];
$chatRequests  = [];
try {
    $chatTablesExist = (bool) \TCM\Core\Database::scalar(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='chat_groups'"
    );
    if ($chatTablesExist) {
        $chatGroups = \TCM\Core\Database::all(
            "SELECT g.id, g.name, g.type,
                    (SELECT m.body FROM chat_messages m WHERE m.group_id = g.id ORDER BY m.id DESC LIMIT 1) AS last_message,
                    (SELECT m.created_at FROM chat_messages m WHERE m.group_id = g.id ORDER BY m.id DESC LIMIT 1) AS last_at
             FROM chat_groups g
             JOIN chat_group_members cgm ON cgm.group_id = g.id
             WHERE cgm.user_id = ?
             ORDER BY last_at DESC, g.created_at DESC LIMIT 4",
            [(int)$user['id']]
        );
        $chatRequests = \TCM\Core\Database::all(
            "SELECT hr.id, hr.message, u.name AS from_name, u.avatar AS from_avatar
             FROM help_requests hr
             JOIN users u ON u.id = hr.from_user_id
             WHERE hr.to_user_id = ? AND hr.status = 'pending' LIMIT 3",
            [(int)$user['id']]
        );
    }
} catch (\Throwable $_e) {}
?>
<?php if (!empty($chatGroups) || !empty($chatRequests)): ?>
<div class="std-section-card" style="margin-top:16px;margin-bottom:16px;">
    <div class="std-section-head">
        <h3 class="std-section-title">
            <i class="bi bi-chat-dots-fill"></i> Chat &amp; Help
            <?php if (!empty($chatRequests)): ?>
                <span class="tcm-badge red" style="font-size:.65rem;"><?= count($chatRequests) ?> request<?= count($chatRequests)>1?'s':'' ?></span>
            <?php endif; ?>
        </h3>
        <a href="<?= base_url('/student/chat') ?>" class="tcm-btn ghost sm">Open Chat <i class="bi bi-arrow-right"></i></a>
    </div>

    <?php if (!empty($chatRequests)): ?>
    <div style="margin-bottom:12px;padding:10px 14px;background:#fef9c3;border:1px solid #fde047;border-radius:10px;">
        <div style="font-size:.75rem;font-weight:700;color:#854d0e;margin-bottom:8px;">
            <i class="bi bi-hand-index-fill"></i> Pending Help Requests
        </div>
        <?php foreach ($chatRequests as $req): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">
            <div style="width:30px;height:30px;border-radius:50%;overflow:hidden;background:#f0f0f0;display:grid;place-items:center;flex-shrink:0;">
                <?= tcm_avatar($req['from_avatar']??null,$req['from_name']) ?>
            </div>
            <div style="flex:1;font-size:.8rem;color:#111;font-weight:600;"><?= e($req['from_name']) ?></div>
            <div style="display:flex;gap:5px;">
                <form method="post" action="<?= base_url('/student/help/requests/'.$req['id'].'/accept') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button class="tcm-btn primary sm" style="padding:4px 10px;font-size:.72rem;"><i class="bi bi-check2"></i> Accept</button>
                </form>
                <form method="post" action="<?= base_url('/student/help/requests/'.$req['id'].'/decline') ?>" style="display:inline;">
                    <?= csrf_field() ?>
                    <button class="tcm-btn sm danger" style="padding:4px 8px;font-size:.72rem;"><i class="bi bi-x"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($chatGroups)): ?>
    <div style="display:grid;gap:6px;">
        <?php foreach ($chatGroups as $g): ?>
        <a href="<?= base_url('/student/chat') ?>"
           style="display:flex;align-items:center;gap:10px;padding:9px 12px;
                  background:#f9f9f9;border:1px solid #ececec;border-radius:10px;
                  text-decoration:none;transition:border-color .15s;"
           onmouseover="this.style.borderColor='#ddd'" onmouseout="this.style.borderColor='#ececec'">
            <div style="width:34px;height:34px;border-radius:9px;
                        background:<?= $g['type']==='help'?'#6366f1':'#111' ?>;color:#fff;
                        display:grid;place-items:center;font-size:.8rem;flex-shrink:0;">
                <i class="bi <?= $g['type']==='help'?'bi-hand-index-fill':'bi-people-fill' ?>"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.83rem;font-weight:700;color:#111;"><?= e($g['name']) ?></div>
                <div style="font-size:.72rem;color:#aaa;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= $g['last_message'] ? e(mb_substr($g['last_message'],0,50)) : 'No messages yet' ?>
                </div>
            </div>
            <?php if ($g['last_at']): ?>
            <div style="font-size:.65rem;color:#bbb;flex-shrink:0;"><?= date('d M',strtotime($g['last_at'])) ?></div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Community Peers ── -->
<?php if (!empty($peers)): ?>
<div class="std-section-card" style="margin-top:16px;">
    <div class="std-section-head">
        <h3 class="std-section-title"><i class="bi bi-people-fill"></i> Community — Fellow Learners</h3>
        <a href="<?= base_url('/student/community') ?>" class="tcm-btn ghost sm">
            View all <i class="bi bi-arrow-right"></i>
        </a>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;">
        <?php foreach ($peers as $p): ?>
        <a href="<?= base_url('/student/community/' . (int)$p['id']) ?>"
           style="display:flex;flex-direction:column;align-items:center;gap:8px;
                  background:#f9f9f9;border:1px solid #ececec;border-radius:14px;
                  padding:14px 10px;text-decoration:none;text-align:center;
                  transition:border-color .15s,transform .15s,box-shadow .15s;"
           onmouseover="this.style.borderColor='#ddd';this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 14px rgba(0,0,0,.06)'"
           onmouseout="this.style.borderColor='#ececec';this.style.transform='';this.style.boxShadow=''">
            <!-- Mini avatar -->
            <div style="width:44px;height:44px;border-radius:50%;border:2px solid #e0e0e0;
                        background:#fff;overflow:hidden;display:grid;place-items:center;
                        font-size:1.1rem;font-weight:800;color:#111;flex-shrink:0;">
                <?= tcm_avatar($p['avatar'] ?? null, $p['name']) ?>
            </div>
            <!-- Name -->
            <div style="font-size:.78rem;font-weight:700;color:#111;white-space:nowrap;
                        overflow:hidden;text-overflow:ellipsis;width:100%;">
                <?= e($p['name']) ?>
            </div>
            <!-- Level badge -->
            <?php if (!empty($p['experience_level'])): ?>
            <?php $lvlC=['beginner'=>'#dcfce7','intermediate'=>'#fef9c3','advanced'=>'#e0e7ff']; ?>
            <div style="font-size:.62rem;font-weight:700;padding:2px 8px;border-radius:20px;
                        background:<?= $lvlC[$p['experience_level']] ?? '#f5f5f5' ?>;color:#444;">
                <?= ucfirst($p['experience_level']) ?>
            </div>
            <?php endif; ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check2" style="color:#22c55e;"></i>';
        setTimeout(function() { btn.innerHTML = orig; }, 2000);
    });
}
</script>


<style>
/* Lesson Topics Styles */
.std-course-topics {
    background: #fafafa;
    border-radius: 10px;
    padding: 12px;
    border: 1px solid #f0f0f0;
}

.std-lesson-item {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all .2s;
}

.std-lesson-item:hover {
    border-color: #ccc;
    transform: translateX(4px);
}

.std-lesson-item:last-child {
    margin-bottom: 0;
}

.std-lesson-header {
    display: flex;
    align-items: center;
    gap: 10px;
    justify-content: space-between;
}

.std-lesson-title {
    font-size: .82rem;
    font-weight: 600;
    color: #111;
    flex: 1;
}

.std-lesson-expand-icon {
    font-size: .7rem;
    color: #888;
    transition: transform .2s;
}

.std-lesson-concepts {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f0f0f0;
}

.std-concept-item {
    background: #f9f9f9;
    border-left: 3px solid #667eea;
    padding: 10px 12px;
    margin-bottom: 8px;
    border-radius: 6px;
}

.std-concept-title {
    font-size: .78rem;
    font-weight: 700;
    color: #111;
    margin-bottom: 4px;
}

.std-concept-text {
    font-size: .74rem;
    color: #555;
    line-height: 1.5;
}

.std-code-example {
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 12px;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: .76rem;
    margin: 8px 0;
    overflow-x: auto;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<script>
// Store expanded states
const expandedCourses = new Set();
const expandedLessons = new Set();
const lessonsCache = {}; // Cache lessons per course
const conceptsCache = {}; // Cache concepts per lesson

/**
 * Toggle course topics (lessons list)
 */
async function toggleCourseTopics(courseId) {
    const topicsDiv = document.getElementById(`course-topics-${courseId}`);
    const chevron = document.getElementById(`course-chevron-${courseId}`);
    
    if (expandedCourses.has(courseId)) {
        // Collapse
        topicsDiv.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
        expandedCourses.delete(courseId);
    } else {
        // Expand
        topicsDiv.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
        expandedCourses.add(courseId);
        
        // Load lessons if not cached
        if (!lessonsCache[courseId]) {
            await loadCourseLessons(courseId);
        }
    }
}

/**
 * Load lessons for a course
 */
async function loadCourseLessons(courseId) {
    const topicsDiv = document.getElementById(`course-topics-${courseId}`);
    
    try {
        const response = await fetch(`<?= base_url('/student/learn/') ?>${courseId}/lessons`);
        const data = await response.json();
        
        if (data.success && data.lessons && data.lessons.length > 0) {
            lessonsCache[courseId] = data.lessons;
            renderLessons(courseId, data.lessons);
        } else {
            topicsDiv.innerHTML = '<div style="text-align:center;padding:20px;color:#888;font-size:.82rem;">No lessons available yet</div>';
        }
    } catch (error) {
        console.error('Error loading lessons:', error);
        topicsDiv.innerHTML = '<div style="text-align:center;padding:20px;color:#e74c3c;font-size:.82rem;">❌ Failed to load lessons</div>';
    }
}

/**
 * Render lessons list
 */
function renderLessons(courseId, lessons) {
    const topicsDiv = document.getElementById(`course-topics-${courseId}`);
    
    let html = '<div style="font-size:.78rem;color:#666;margin-bottom:10px;font-weight:600;">📚 Click on any lesson to view concepts</div>';
    
    lessons.forEach(lesson => {
        html += `
            <div class="std-lesson-item" onclick="toggleLessonConcepts(${lesson.id})" id="lesson-${lesson.id}">
                <div class="std-lesson-header">
                    <div class="std-lesson-title">
                        ${lesson.position}. ${escapeHtml(lesson.title)}
                        ${lesson.type ? `<span style="font-size:.7rem;color:#888;margin-left:6px;">(${lesson.type})</span>` : ''}
                    </div>
                    <i class="bi bi-chevron-down std-lesson-expand-icon" id="lesson-chevron-${lesson.id}"></i>
                </div>
                <div id="lesson-concepts-${lesson.id}" class="std-lesson-concepts" style="display:none;">
                    <div style="text-align:center;padding:15px;color:#888;font-size:.78rem;">
                        <i class="bi bi-hourglass-split" style="animation:spin 1s linear infinite;"></i>
                        Generating AI concepts...
                    </div>
                </div>
            </div>
        `;
    });
    
    topicsDiv.innerHTML = html;
}

/**
 * Toggle lesson concepts (AI generated)
 */
async function toggleLessonConcepts(lessonId) {
    const conceptsDiv = document.getElementById(`lesson-concepts-${lessonId}`);
    const chevron = document.getElementById(`lesson-chevron-${lessonId}`);
    
    if (expandedLessons.has(lessonId)) {
        // Collapse
        conceptsDiv.style.display = 'none';
        chevron.style.transform = 'rotate(0deg)';
        expandedLessons.delete(lessonId);
    } else {
        // Expand
        conceptsDiv.style.display = 'block';
        chevron.style.transform = 'rotate(180deg)';
        expandedLessons.add(lessonId);
        
        // Load concepts if not cached
        if (!conceptsCache[lessonId]) {
            await loadLessonConcepts(lessonId);
        }
    }
}

/**
 * Load AI-generated concepts for a lesson
 */
async function loadLessonConcepts(lessonId) {
    const conceptsDiv = document.getElementById(`lesson-concepts-${lessonId}`);
    
    try {
        const response = await fetch(`<?= base_url('/student/lesson-concepts/') ?>${lessonId}`);
        const data = await response.json();
        
        if (data.success && data.concepts) {
            conceptsCache[lessonId] = data.concepts;
            renderConcepts(lessonId, data.concepts, data.cached);
        } else {
            conceptsDiv.innerHTML = `<div style="text-align:center;padding:15px;color:#e74c3c;font-size:.78rem;">❌ ${data.message || 'Failed to generate concepts'}</div>`;
        }
    } catch (error) {
        console.error('Error loading concepts:', error);
        conceptsDiv.innerHTML = '<div style="text-align:center;padding:15px;color:#e74c3c;font-size:.78rem;">❌ Failed to load concepts</div>';
    }
}

/**
 * Render AI-generated concepts
 */
function renderConcepts(lessonId, concepts, cached) {
    const conceptsDiv = document.getElementById(`lesson-concepts-${lessonId}`);
    
    let html = '';
    
    // Cached indicator
    if (cached) {
        html += '<div style="font-size:.7rem;color:#10b981;margin-bottom:10px;display:flex;align-items:center;gap:4px;"><i class="bi bi-check-circle-fill"></i> Content loaded from cache</div>';
    } else {
        html += '<div style="font-size:.7rem;color:#667eea;margin-bottom:10px;display:flex;align-items:center;gap:4px;"><i class="bi bi-stars"></i> AI generated content</div>';
    }
    
    // Overview (Hindi)
    if (concepts.overview_hi) {
        html += `
            <div style="background:#f0f4ff;border-left:3px solid #667eea;padding:10px 12px;border-radius:6px;margin-bottom:12px;">
                <div style="font-size:.72rem;font-weight:700;color:#667eea;margin-bottom:4px;">📖 Overview</div>
                <div style="font-size:.76rem;color:#333;line-height:1.6;">${escapeHtml(concepts.overview_hi)}</div>
            </div>
        `;
    }
    
    // Key Concepts
    if (concepts.key_concepts && concepts.key_concepts.length > 0) {
        html += '<div style="font-size:.76rem;font-weight:700;color:#111;margin:12px 0 8px 0;">🎯 Key Concepts:</div>';
        
        concepts.key_concepts.forEach((concept, index) => {
            html += `
                <div class="std-concept-item">
                    <div class="std-concept-title">${index + 1}. ${escapeHtml(concept.title_hi || concept.title_en)}</div>
                    <div class="std-concept-text">${escapeHtml(concept.explanation_hi || concept.explanation_en)}</div>
                </div>
            `;
        });
    }
    
    // Code Examples
    if (concepts.code_examples && concepts.code_examples.length > 0) {
        html += '<div style="font-size:.76rem;font-weight:700;color:#111;margin:14px 0 8px 0;">💻 Code Examples:</div>';
        
        concepts.code_examples.forEach((example, index) => {
            html += `
                <div style="margin-bottom:12px;">
                    <div style="font-size:.74rem;font-weight:600;color:#111;margin-bottom:6px;">${index + 1}. ${escapeHtml(example.title)}</div>
                    ${example.description_hi ? `<div style="font-size:.72rem;color:#666;margin-bottom:6px;">${escapeHtml(example.description_hi)}</div>` : ''}
                    <div class="std-code-example">${escapeHtml(example.code)}</div>
                    ${example.output ? `<div style="font-size:.72rem;color:#666;margin-top:4px;"><strong>Output:</strong> ${escapeHtml(example.output)}</div>` : ''}
                </div>
            `;
        });
    }
    
    // Estimated Time
    if (concepts.estimated_time) {
        html += `
            <div style="font-size:.74rem;color:#888;margin-top:12px;padding-top:10px;border-top:1px solid #f0f0f0;display:flex;align-items:center;gap:6px;">
                <i class="bi bi-clock"></i> Estimated Time: <strong>${concepts.estimated_time} minutes</strong>
            </div>
        `;
    }
    
    conceptsDiv.innerHTML = html;
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Helper function to copy text
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-check"></i>';
        setTimeout(() => {
            btn.innerHTML = original;
        }, 2000);
    });
}
</script>
