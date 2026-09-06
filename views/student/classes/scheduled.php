<?php
// Get live class links for this student
$uid = (int)$user['id'];
$liveLinks = [];

try {
    $lcExists = (bool) \TCM\Core\Database::scalar(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='live_class_links'"
    );
    
    if ($lcExists) {
        $enrolledCourseIds  = array_column($enrollments ?? [], 'course_id');
        $enrolledProgramIds = array_column(
            \TCM\Core\Database::all("SELECT program_id FROM program_enrollments WHERE user_id=?", [$uid]),
            'program_id'
        );

        // Build condition: all | enrolled course | enrolled program
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

        $where = implode(' OR ', $conditions);

        // Get all live class links (scheduled in future or currently live)
        $sql = "SELECT * FROM live_class_links
                WHERE ($where)
                  AND (
                    -- Future scheduled classes (next 30 days)
                    (scheduled_at IS NOT NULL 
                     AND scheduled_at > NOW() 
                     AND scheduled_at <= DATE_ADD(NOW(), INTERVAL 30 DAY))
                    
                    -- OR currently live (started within last 3 hours)
                    OR (scheduled_at IS NOT NULL 
                        AND scheduled_at <= NOW() 
                        AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 3 HOUR))
                    
                    -- OR recent (last 7 days) for reference
                    OR (scheduled_at IS NOT NULL
                        AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                        AND scheduled_at < NOW())
                  )
                ORDER BY
                    -- Live classes first
                    CASE 
                        WHEN scheduled_at IS NOT NULL 
                             AND scheduled_at <= NOW() 
                             AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 3 HOUR) 
                        THEN 0
                        WHEN scheduled_at IS NOT NULL
                             AND scheduled_at > NOW()
                        THEN 1
                        ELSE 2
                    END,
                    scheduled_at ASC";

        $liveLinks = \TCM\Core\Database::all($sql, $params);
    }
} catch (\Throwable $_e) {
    error_log('[Live Classes Page] Error: ' . $_e->getMessage());
}

// Group by status
$liveNow = [];
$upcoming = [];
$recent = [];

foreach ($liveLinks as $ll) {
    $now = time();
    $schedTime = $ll['scheduled_at'] ? strtotime($ll['scheduled_at']) : null;
    $secsLeft = $schedTime ? ($schedTime - $now) : null;
    
    if ($schedTime && $secsLeft !== null && $secsLeft <= 300 && $secsLeft >= -10800) {
        $liveNow[] = $ll;
    } elseif ($schedTime && $secsLeft !== null && $secsLeft > 300) {
        $upcoming[] = $ll;
    } else {
        $recent[] = $ll;
    }
}
?>

<div class="tcm-page-head">
    <div>
        <h2><i class="bi bi-camera-video" style="margin-right: 8px;"></i>Live Classes</h2>
        <p>Join scheduled live sessions and catch up on recent classes.</p>
    </div>
</div>

<!-- Live Now Section -->
<?php if (!empty($liveNow)): ?>
<div style="background: #111; border-radius: 14px; padding: 20px; margin-bottom: 20px; border: 2px solid #111;">
    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
        <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; animation: std-blink 1s infinite;"></span>
        <h3 style="margin: 0; color: #fff; font-size: 0.85rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase;">
            <i class="bi bi-broadcast"></i> Live Now
        </h3>
    </div>
    
    <div style="display: grid; gap: 14px;">
        <?php foreach ($liveNow as $ll): ?>
        <?php
        $now = time();
        $schedTime = $ll['scheduled_at'] ? strtotime($ll['scheduled_at']) : null;
        $secsLeft = $schedTime ? ($schedTime - $now) : null;
        $minsAgo = $secsLeft !== null && $secsLeft < 0 ? abs((int)ceil($secsLeft / 60)) : 0;
        ?>
        <div style="background: #fff; border: 1px solid #ececec; border-radius: 12px; padding: 16px; display: flex; align-items: center; gap: 14px; flex-wrap: wrap; transition: all 0.2s;"
             onmouseover="this.style.borderColor='#111'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 14px rgba(0,0,0,0.08)'"
             onmouseout="this.style.borderColor='#ececec'; this.style.transform='translateY(0)'; this.style.boxShadow=''">
            <div style="width: 48px; height: 48px; border-radius: 10px; background: #111; display: grid; place-items: center; font-size: 1.3rem; color: #fff; flex-shrink: 0;">
                <i class="bi bi-camera-video-fill"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.92rem; font-weight: 800; color: #111; margin-bottom: 4px;">
                    <?= e($ll['title']) ?>
                </div>
                <?php if (!empty($ll['description'])): ?>
                <div style="font-size: 0.78rem; color: #666; margin-bottom: 6px; line-height: 1.4;">
                    <?= e($ll['description']) ?>
                </div>
                <?php endif; ?>
                <div style="font-size: 0.72rem; color: #22c55e; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                    <?php if ($minsAgo > 0): ?>
                        <i class="bi bi-clock-history"></i> Started <?= $minsAgo ?> min<?= $minsAgo > 1 ? 's' : '' ?> ago
                    <?php else: ?>
                        <i class="bi bi-circle-fill" style="font-size: 0.4rem;"></i> LIVE NOW
                    <?php endif; ?>
                </div>
            </div>
            <a href="<?= e($ll['meeting_url']) ?>" target="_blank" rel="noopener"
               style="display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; border-radius: 10px; background: #111; color: #fff; font-size: 0.85rem; font-weight: 700; text-decoration: none; flex-shrink: 0; transition: all 0.2s; border: 2px solid #111;"
               onmouseover="this.style.background='#333'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='#111'; this.style.transform='translateY(0)'">
                <i class="bi bi-play-circle-fill"></i> Join Class
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Upcoming Classes -->
<?php if (!empty($upcoming)): ?>
<div style="margin-bottom: 20px;">
    <h3 style="font-size: 0.85rem; font-weight: 800; color: #111; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; letter-spacing: 0.05em; text-transform: uppercase;">
        <i class="bi bi-calendar-event"></i> Upcoming Classes
    </h3>
    
    <div style="display: grid; gap: 12px;">
        <?php foreach ($upcoming as $ll): ?>
        <?php
        $schedTime = strtotime($ll['scheduled_at']);
        $secsLeft = $schedTime - time();
        $daysLeft = floor($secsLeft / 86400);
        $hoursLeft = floor(($secsLeft % 86400) / 3600);
        $minsLeft = floor(($secsLeft % 3600) / 60);
        
        if ($daysLeft > 0) {
            $timeStr = $daysLeft . ' day' . ($daysLeft > 1 ? 's' : '');
        } elseif ($hoursLeft > 0) {
            $timeStr = $hoursLeft . ' hour' . ($hoursLeft > 1 ? 's' : '');
        } else {
            $timeStr = $minsLeft . ' min' . ($minsLeft > 1 ? 's' : '');
        }
        ?>
        <div style="background: #fff; border: 1px solid #ececec; border-radius: 12px; padding: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; transition: all 0.2s;"
             onmouseover="this.style.borderColor='#ddd'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.06)'"
             onmouseout="this.style.borderColor='#ececec'; this.style.transform='translateY(0)'; this.style.boxShadow=''">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: #f5f5f5; border: 1px solid #e0e0e0; display: grid; place-items: center; font-size: 1.1rem; color: #111; flex-shrink: 0;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.88rem; font-weight: 700; color: #111; margin-bottom: 3px;">
                    <?= e($ll['title']) ?>
                </div>
                <?php if (!empty($ll['description'])): ?>
                <div style="font-size: 0.76rem; color: #888; margin-bottom: 6px; line-height: 1.4;">
                    <?= e($ll['description']) ?>
                </div>
                <?php endif; ?>
                <div style="font-size: 0.72rem; color: #666; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <span><i class="bi bi-clock"></i> <?= e(date('d M Y · h:i A', $schedTime)) ?></span>
                    <span style="background: #f5f5f5; color: #111; padding: 2px 8px; border-radius: 6px; font-weight: 600; border: 1px solid #e0e0e0;">
                        <i class="bi bi-hourglass-split"></i> in <?= $timeStr ?>
                    </span>
                </div>
            </div>
            <a href="<?= e($ll['meeting_url']) ?>" target="_blank" rel="noopener"
               style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 8px; background: #fff; border: 1.5px solid #111; color: #111; font-size: 0.82rem; font-weight: 700; text-decoration: none; flex-shrink: 0; transition: all 0.2s;"
               onmouseover="this.style.background='#111'; this.style.color='#fff'"
               onmouseout="this.style.background='#fff'; this.style.color='#111'">
                <i class="bi bi-link-45deg"></i> Class Link
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Classes -->
<?php if (!empty($recent)): ?>
<div style="margin-bottom: 20px;">
    <h3 style="font-size: 0.85rem; font-weight: 800; color: #111; margin-bottom: 12px; display: flex; align-items: center; gap: 8px; letter-spacing: 0.05em; text-transform: uppercase;">
        <i class="bi bi-clock-history"></i> Recent Classes
    </h3>
    
    <div style="display: grid; gap: 10px;">
        <?php foreach ($recent as $ll): ?>
        <?php $schedTime = strtotime($ll['scheduled_at']); ?>
        <div style="background: #f9f9f9; border: 1px solid #ececec; border-radius: 10px; padding: 14px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <div style="width: 40px; height: 40px; border-radius: 8px; background: #fff; border: 1px solid #e0e0e0; display: grid; place-items: center; font-size: 1rem; color: #888; flex-shrink: 0;">
                <i class="bi bi-camera-video"></i>
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.88rem; font-weight: 600; color: #444; margin-bottom: 2px;">
                    <?= e($ll['title']) ?>
                </div>
                <div style="font-size: 0.72rem; color: #999;">
                    <i class="bi bi-calendar-x"></i> <?= e(date('d M Y · h:i A', $schedTime)) ?>
                </div>
            </div>
            <a href="<?= e($ll['meeting_url']) ?>" target="_blank" rel="noopener"
               style="display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; border-radius: 7px; background: #fff; border: 1px solid #ddd; color: #666; font-size: 0.8rem; font-weight: 600; text-decoration: none; flex-shrink: 0; transition: all 0.2s;"
               onmouseover="this.style.borderColor='#111'; this.style.color='#111'"
               onmouseout="this.style.borderColor='#ddd'; this.style.color='#666'">
                <i class="bi bi-box-arrow-up-right"></i> Recording
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Empty State -->
<?php if (empty($liveNow) && empty($upcoming) && empty($recent)): ?>
<div class="tcm-empty" style="padding: 80px 20px;">
    <i class="bi bi-camera-video-off" style="font-size: 3rem; color: #ddd; margin-bottom: 16px;"></i>
    <h3 style="font-size: 1.1rem; font-weight: 700; color: #444; margin-bottom: 8px;">No Live Classes Scheduled</h3>
    <p style="color: #888; font-size: 0.9rem; margin-bottom: 20px;">
        There are no live classes scheduled at the moment.<br>
        Check back soon or enroll in programs to get live class access!
    </p>
    <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <a href="<?= base_url('/student/programs') ?>" class="tcm-btn primary">
            <i class="bi bi-stack"></i> Browse Programs
        </a>
        <a href="<?= base_url('/student/courses') ?>" class="tcm-btn">
            <i class="bi bi-journal-code"></i> Browse Courses
        </a>
    </div>
</div>
<?php endif; ?>

<style>
@keyframes std-blink {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.3; }
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .tcm-page-head h2 {
        font-size: 1.1rem;
    }
    .tcm-page-head p {
        font-size: 0.82rem;
    }
}
</style>
