<?php
use TCM\Core\Auth;
$current = TCM\Core\Request::path();
$me      = Auth::user();
$nav = [
    ['/student',              'bi-squares-fill',      'Dashboard'],
    ['/student/courses',      'bi-journal-code',      'My Courses'],
    ['/student/programs',     'bi-stack',             'Programs'],
    ['/student/events',       'bi-calendar-event',    'Events'],
    ['/student/community',    'bi-people-fill',       'Community'],
    ['/student/chat',         'bi-chat-dots-fill',    'Chat & Help'],
    ['/student/wallet',       'bi-wallet2',           'Wallet'],
    ['/student/payments',     'bi-receipt',           'Payments'],
    ['/student/applications', 'bi-file-earmark-text', 'Applications'],
    ['/student/portfolio',    'bi-briefcase',         'Portfolio'],
    ['/student/profile',      'bi-person-gear',       'Profile'],
];
function snav_active(string $href, string $current): string {
    if ($href === '/student') return $current === '/student' ? 'active' : '';
    return str_starts_with($current, $href) ? 'active' : '';
}
$fl = strtoupper(substr($me['name'] ?? 'S', 0, 1)); // initial — kept for any template that may reference it
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="app-base" content="<?= base_url('') ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#111111">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="TCM">
    <link rel="manifest" href="<?= base_url('/manifest.json') ?>">
    <link rel="apple-touch-icon" href="<?= base_url('/assets/icons/icon-192.svg') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= base_url('/favicon.svg') ?>">
    <title><?= e($title ?? 'Dashboard') ?> · The Code Munk</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= base_url('/assets/dashboard.css') ?>">
</head>
<body>

<div class="tcm-sidebar-backdrop" id="sidebarBackdrop"></div>

<div class="tcm-shell">

    <!-- Sidebar -->
    <aside class="tcm-sidebar" id="sidebar">

        <div class="tcm-brand">
            <i class="bi bi-code-slash"></i> The Code Munk
        </div>

        <ul class="tcm-nav" style="flex:1;">
            <?php foreach ($nav as [$href, $icon, $label]): ?>
            <li>
                <a href="<?= base_url($href) ?>"
                   class="<?= snav_active($href, $current) ?>"
                   onclick="closeSidebar()">
                    <i class="bi <?= e($icon) ?>"></i>
                    <?= e($label) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>

        <div class="tcm-nav-label">Quick links</div>
        <ul class="tcm-nav">
            <li>
                <a href="<?= base_url('/portfolio/' . ($me['id'] ?? 0)) ?>"
                   target="_blank" onclick="closeSidebar()">
                    <i class="bi bi-box-arrow-up-right"></i> Public Portfolio
                </a>
            </li>
            <li>
                <a href="<?= base_url('/') ?>" onclick="closeSidebar()">
                    <i class="bi bi-house"></i> Main Site
                </a>
            </li>
        </ul>

        <div class="tcm-sidebar-spacer"></div>

        <!-- User strip -->
        <div class="tcm-sidebar-footer">
            <div class="tcm-sidebar-user">
                <div class="tcm-avatar" style="width:32px;height:32px;font-size:.75rem;">
                    <?= tcm_avatar($me['avatar'] ?? null, $me['name'] ?? 'S') ?>
                </div>
                <div class="tcm-sidebar-user-info">
                    <div class="tcm-sidebar-user-name"><?= e($me['name'] ?? 'Student') ?></div>
                    <div class="tcm-sidebar-user-role">Student</div>
                </div>
                <form method="post" action="<?= base_url('/auth/logout') ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="tcm-btn ghost sm" title="Sign out"
                            style="padding:6px 8px;">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>

    </aside>

    <!-- Main -->
    <div class="tcm-main">

        <!-- Topbar -->
        <div class="tcm-topbar">
            <div class="tcm-topbar-left">
                <button id="menuToggle"
                        class="tcm-btn ghost sm tcm-menu-toggle"
                        onclick="openSidebar()"
                        aria-label="Open menu"
                        style="padding:7px 9px;">
                    <i class="bi bi-list" style="font-size:1.1rem;"></i>
                </button>
                <h1><?= e($title ?? 'Dashboard') ?></h1>
            </div>
            <div class="tcm-topbar-right">
                <?php 
                // Get wallet balance
                if (class_exists('TCM\Models\Wallet')) {
                    \TCM\Models\Wallet::ensureTables();
                    $walletBalance = \TCM\Models\Wallet::balance((int)($me['id'] ?? 0));
                } else {
                    $walletBalance = 0;
                }
                ?>
                <!-- Wallet Balance (Compact) - Visible on all devices -->
                <a href="<?= base_url('/student/wallet') ?>" 
                   class="tcm-topbar-wallet"
                   style="display: flex; align-items: center; gap: 6px; padding: 5px 12px; 
                          background: #fff; border: 1px solid rgba(0,0,0,0.15); 
                          border-radius: 8px; text-decoration: none; color: #111; 
                          font-weight: 600; font-size: 0.8rem; transition: all 0.2s;"
                   onmouseover="this.style.borderColor='rgba(0,0,0,0.3)'; this.style.transform='translateY(-1px)'" 
                   onmouseout="this.style.borderColor='rgba(0,0,0,0.15)'; this.style.transform='translateY(0)'"
                   title="My Wallet">
                    <i class="bi bi-wallet2" style="font-size: 0.9rem; color: #111;"></i>
                    <span style="color: #111;">₹<?= number_format($walletBalance, 0) ?></span>
                </a>
                
                <!-- Notification Bell - Visible on all devices -->
                <div class="tcm-topbar-notification">
                    <?php require dirname(__DIR__) . '/partials/notification-panel.php'; ?>
                </div>
                
                <!-- Username - Hidden on mobile -->
                <span class="tcm-user-name"><?= e($me['name'] ?? '') ?></span>
                
                <!-- Avatar - Hidden on mobile -->
                <a href="<?= base_url('/student/profile') ?>" class="tcm-avatar tcm-topbar-avatar" title="Profile">
                    <?= tcm_avatar($me['avatar'] ?? null, $me['name'] ?? 'S') ?>
                </a>
                
                <!-- Logout - Hidden on mobile -->
                <form method="post" action="<?= base_url('/auth/logout') ?>" class="tcm-topbar-logout" style="display:contents">
                    <?= csrf_field() ?>
                    <button type="submit" class="tcm-btn ghost sm" title="Sign out"
                            style="padding:7px 9px;">
                        <i class="bi bi-box-arrow-right" style="font-size:.9rem;"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Content -->
        <div class="tcm-content">
            <?php require dirname(__DIR__) . '/partials/flash.php'; ?>
            <?= $content ?? '' ?>
        </div>

    </div>

</div>

<!-- ── Bottom Navigation (Mobile Only) ── -->
<nav style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: #fff; border-top: 1px solid #ececec; padding: 8px 0 max(8px, env(safe-area-inset-bottom)); z-index: 100; box-shadow: 0 -2px 16px rgba(0,0,0,0.06);">
    <style>
        @media (max-width: 768px) {
            nav[style*="bottom: 0"] { display: flex !important; }
            .tcm-content { padding-bottom: 80px !important; }
            /* Hide desktop-only items on mobile - Keep wallet and notification visible */
            .tcm-user-name,
            .tcm-topbar-avatar,
            .tcm-topbar-logout { display: none !important; }
        }
    </style>
    
    <!-- 1. Home -->
    <a href="<?= base_url('/student') ?>" 
       style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; flex: 1; padding: 6px 0; color: <?= $current === '/student' ? '#111' : '#888' ?>; text-decoration: none; transition: all 0.16s; position: relative;">
        <?php if ($current === '/student'): ?>
        <span style="position: absolute; top: 0px; width: 4px; height: 4px; border-radius: 50%; background: #111;"></span>
        <?php endif; ?>
        <i class="bi bi-house-door-fill" style="font-size: 1.4rem;"></i>
        <span style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.02em;">Home</span>
    </a>
    
    <!-- 2. Live Class -->
    <?php
    // Check if there are any live classes
    $hasLiveClass = false;
    try {
        $lcExists = (bool) \TCM\Core\Database::scalar(
            "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='live_class_links'"
        );
        if ($lcExists) {
            $hasLiveClass = (bool) \TCM\Core\Database::scalar(
                "SELECT COUNT(*) FROM live_class_links 
                 WHERE scheduled_at IS NOT NULL 
                 AND scheduled_at <= NOW() 
                 AND scheduled_at >= DATE_SUB(NOW(), INTERVAL 2 HOUR)
                 LIMIT 1"
            );
        }
    } catch (\Throwable $_e) {}
    ?>
    <a href="<?= base_url('/student/live-classes') ?>" 
       style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; flex: 1; padding: 6px 0; color: <?= str_starts_with($current, '/student/live-classes') ? '#111' : '#888' ?>; text-decoration: none; transition: all 0.16s; position: relative;">
        <?php if (str_starts_with($current, '/student/live-classes')): ?>
        <span style="position: absolute; top: 0px; width: 4px; height: 4px; border-radius: 50%; background: #111;"></span>
        <?php endif; ?>
        <?php if ($hasLiveClass): ?>
        <span style="position: absolute; top: 8px; right: calc(50% - 12px); width: 6px; height: 6px; border-radius: 50%; background: #22c55e; animation: std-blink 1s infinite;"></span>
        <?php endif; ?>
        <i class="bi bi-camera-video-fill" style="font-size: 1.4rem;"></i>
        <span style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.02em;">Live Class</span>
    </a>
    
    <!-- 3. Courses -->
    <a href="<?= base_url('/student/courses') ?>" 
       style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; flex: 1; padding: 6px 0; color: <?= str_starts_with($current, '/student/courses') || str_starts_with($current, '/student/learn') ? '#111' : '#888' ?>; text-decoration: none; transition: all 0.16s; position: relative;">
        <?php if (str_starts_with($current, '/student/courses') || str_starts_with($current, '/student/learn')): ?>
        <span style="position: absolute; top: 0px; width: 4px; height: 4px; border-radius: 50%; background: #111;"></span>
        <?php endif; ?>
        <i class="bi bi-book-fill" style="font-size: 1.4rem;"></i>
        <span style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.02em;">Courses</span>
    </a>
    
    <!-- 4. Community -->
    <a href="<?= base_url('/student/community') ?>" 
       style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; flex: 1; padding: 6px 0; color: <?= str_starts_with($current, '/student/community') || str_starts_with($current, '/student/chat') ? '#111' : '#888' ?>; text-decoration: none; transition: all 0.16s; position: relative;">
        <?php if (str_starts_with($current, '/student/community') || str_starts_with($current, '/student/chat')): ?>
        <span style="position: absolute; top: 0px; width: 4px; height: 4px; border-radius: 50%; background: #111;"></span>
        <?php endif; ?>
        <i class="bi bi-people-fill" style="font-size: 1.4rem;"></i>
        <span style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.02em;">Community</span>
    </a>
    
    <!-- 5. Profile -->
    <a href="<?= base_url('/student/profile') ?>" 
       style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 3px; flex: 1; padding: 6px 0; color: <?= str_starts_with($current, '/student/profile') || str_starts_with($current, '/student/portfolio') ? '#111' : '#888' ?>; text-decoration: none; transition: all 0.16s; position: relative;">
        <?php if (str_starts_with($current, '/student/profile') || str_starts_with($current, '/student/portfolio')): ?>
        <span style="position: absolute; top: 0px; width: 4px; height: 4px; border-radius: 50%; background: #111;"></span>
        <?php endif; ?>
        <i class="bi bi-person-circle" style="font-size: 1.4rem;"></i>
        <span style="font-size: 0.65rem; font-weight: 600; letter-spacing: 0.02em;">Profile</span>
    </a>
</nav>

<script>
function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarBackdrop').classList.add('active');
    document.body.style.overflow = 'hidden';
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarBackdrop').classList.remove('active');
    document.body.style.overflow = '';
}
document.getElementById('sidebarBackdrop').addEventListener('click', closeSidebar);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeSidebar(); });
</script>

<!-- ── Firebase Push Notifications (Student) ── -->
<script src="<?= base_url('/assets/tcm-notifications.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.TCMNotifications) {
        TCMNotifications.init('student', <?= (int)($me['id'] ?? 0) ?>);
    }
});
</script>
</body>
</html>
