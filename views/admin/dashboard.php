<?php
$hour      = (int) date('H');
$greeting  = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$adminName = explode(' ', $user['name'] ?? 'Admin')[0];
$pendingPayments = $stats['pending_payments'] ?? 0;
?>
<style>
.adm-hero{background:#111;border-radius:20px;padding:28px 32px;margin-bottom:20px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;}
.adm-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:260px;height:260px;border-radius:50%;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,transparent 70%);pointer-events:none;}
.adm-hero-label{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;padding:5px 13px;border-radius:50px;margin-bottom:12px;}
.adm-hero-dot{width:6px;height:6px;border-radius:50%;background:#4ade80;animation:adm-blink 1.5s infinite;}
@keyframes adm-blink{0%,100%{opacity:1}50%{opacity:.3}}
.adm-hero h2{font-size:1.55rem;font-weight:800;color:#fff;letter-spacing:-.4px;margin-bottom:5px;}
.adm-hero p{font-size:.85rem;color:rgba(255,255,255,.5);margin:0;}
.adm-hero-actions{display:flex;gap:8px;flex-wrap:wrap;}
.adm-hero-btn{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:.83rem;font-weight:600;text-decoration:none;border:none;cursor:pointer;font-family:inherit;transition:.15s;}
.adm-hero-btn.white{background:#fff;color:#111;}
.adm-hero-btn.white:hover{background:#f0f0f0;color:#111;}
.adm-hero-btn.outline{background:rgba(255,255,255,.1);color:rgba(255,255,255,.85);border:1px solid rgba(255,255,255,.2);}
.adm-hero-btn.outline:hover{background:rgba(255,255,255,.18);color:#fff;}
.adm-stats{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:20px;}
.adm-stat-card{background:#fff;border:1px solid #ececec;border-radius:14px;padding:16px 18px;transition:border-color .15s,transform .15s,box-shadow .15s;}
.adm-stat-card:hover{border-color:#ddd;transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,.06);}
.adm-stat-card.alert{border-color:#fecaca;background:#fff8f8;}
.adm-stat-icon{width:36px;height:36px;border-radius:10px;background:#f5f5f5;border:1px solid #ececec;display:grid;place-items:center;font-size:.9rem;color:#555;margin-bottom:12px;}
.adm-stat-card.alert .adm-stat-icon{background:#fef2f2;border-color:#fecaca;color:#dc2626;}
.adm-stat-label{font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#aaa;margin-bottom:5px;}
.adm-stat-value{font-size:1.8rem;font-weight:800;color:#111;letter-spacing:-1px;line-height:1;}
.adm-stat-link{display:inline-flex;align-items:center;gap:4px;font-size:.72rem;color:#aaa;margin-top:8px;text-decoration:none;font-weight:600;transition:color .15s;}
.adm-stat-link:hover{color:#111;}
.adm-stat-link.red{color:#dc2626;}
.adm-alert-banner{background:#fff8e1;border:1.5px solid #ffe082;border-radius:14px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.adm-alert-icon{font-size:1.3rem;color:#f59e0b;flex-shrink:0;}
.adm-alert-text strong{font-size:.88rem;color:#92400e;font-weight:700;display:block;}
.adm-alert-text span{font-size:.78rem;color:#b45309;}
.adm-quick{display:grid;grid-template-columns:repeat(auto-fill,minmax(130px,1fr));gap:10px;margin-bottom:20px;}
.adm-quick-item{display:flex;flex-direction:column;align-items:flex-start;gap:10px;background:#fff;border:1px solid #ececec;border-radius:14px;padding:14px;text-decoration:none;transition:border-color .15s,box-shadow .15s,transform .15s;position:relative;}
.adm-quick-item:hover{border-color:#ddd;box-shadow:0 4px 14px rgba(0,0,0,.06);transform:translateY(-2px);}
.adm-quick-item.highlight{border-color:#fde68a;background:#fffbeb;}
.adm-quick-icon{width:34px;height:34px;background:#f5f5f5;border:1px solid #ececec;border-radius:10px;display:grid;place-items:center;font-size:.9rem;color:#111;}
.adm-quick-item.highlight .adm-quick-icon{background:#fef3c7;border-color:#fde68a;color:#d97706;}
.adm-quick-label{font-size:.82rem;font-weight:600;color:#111;}
.adm-quick-badge{position:absolute;top:8px;right:8px;background:#dc2626;color:#fff;font-size:.6rem;font-weight:800;padding:2px 6px;border-radius:50px;}
.adm-table-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:8px;}
.adm-table-head h3{font-size:.92rem;font-weight:700;color:#111;display:flex;align-items:center;gap:8px;margin:0;}
.adm-table-head h3 i{color:var(--muted);}
@media(max-width:1200px){.adm-stats{grid-template-columns:repeat(3,1fr);}}
@media(max-width:768px){.adm-stats{grid-template-columns:repeat(2,1fr);}.adm-hero{padding:22px 20px;}.adm-hero h2{font-size:1.25rem;}}
@media(max-width:400px){.adm-stats{grid-template-columns:1fr 1fr;}}
</style>

<!-- Hero -->
<div class="adm-hero">
    <div>
        <div class="adm-hero-label"><span class="adm-hero-dot"></span><?= date('l, d M Y') ?></div>
        <h2><?= e($greeting) ?>, <?= e($adminName) ?> 👋</h2>
        <p>Here's what's happening with The Code Munk today.</p>
    </div>
    <div class="adm-hero-actions">
        <a href="<?= base_url('/admin/courses/create') ?>" class="adm-hero-btn white"><i class="bi bi-plus-lg"></i> Add Course</a>
        <a href="<?= base_url('/admin/events/create') ?>" class="adm-hero-btn outline"><i class="bi bi-calendar-plus"></i> Add Event</a>
        <a href="<?= base_url('/admin/leads') ?>" class="adm-hero-btn outline"><i class="bi bi-megaphone"></i> Leads</a>
    </div>
</div>

<?php if ($pendingPayments > 0): ?>
<div class="adm-alert-banner">
    <i class="bi bi-clock-history adm-alert-icon"></i>
    <div class="adm-alert-text" style="flex:1;">
        <strong><?= $pendingPayments ?> payment<?= $pendingPayments > 1 ? 's' : '' ?> waiting for approval</strong>
        <span>Students submitted payment proof — review and approve to activate access.</span>
    </div>
    <a href="<?= base_url('/admin/payments') ?>" class="tcm-btn primary sm"><i class="bi bi-check2-circle"></i> Review Now</a>
</div>
<?php endif; ?>

<!-- Stats -->
<div class="adm-stats">
    <div class="adm-stat-card">
        <div class="adm-stat-icon"><i class="bi bi-people-fill"></i></div>
        <div class="adm-stat-label">Students</div>
        <div class="adm-stat-value"><?= number_format($stats['students']) ?></div>
        <a href="<?= base_url('/admin/students') ?>" class="adm-stat-link">View all <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-icon"><i class="bi bi-journal-code"></i></div>
        <div class="adm-stat-label">Courses</div>
        <div class="adm-stat-value"><?= number_format($stats['courses']) ?></div>
        <a href="<?= base_url('/admin/courses') ?>" class="adm-stat-link">Manage <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-icon"><i class="bi bi-calendar-event"></i></div>
        <div class="adm-stat-label">Events</div>
        <div class="adm-stat-value"><?= number_format($stats['events']) ?></div>
        <a href="<?= base_url('/admin/events') ?>" class="adm-stat-link">Manage <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-icon"><i class="bi bi-mortarboard"></i></div>
        <div class="adm-stat-label">Enrollments</div>
        <div class="adm-stat-value"><?= number_format($stats['enrollments']) ?></div>
        <span class="adm-stat-link">Active learners</span>
    </div>
    <div class="adm-stat-card">
        <div class="adm-stat-icon"><i class="bi bi-currency-rupee"></i></div>
        <div class="adm-stat-label">Revenue</div>
        <div class="adm-stat-value" style="font-size:1.3rem;"><?= money($stats['revenue']) ?></div>
        <a href="<?= base_url('/admin/payments') ?>" class="adm-stat-link">Payments <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="adm-stat-card <?= $stats['new_messages'] > 0 ? 'alert' : '' ?>">
        <div class="adm-stat-icon"><i class="bi bi-envelope-fill"></i></div>
        <div class="adm-stat-label">Messages</div>
        <div class="adm-stat-value"><?= number_format($stats['new_messages']) ?></div>
        <?php if ($stats['new_messages'] > 0): ?>
            <a href="<?= base_url('/admin/messages') ?>" class="adm-stat-link red">Reply now <i class="bi bi-arrow-right"></i></a>
        <?php else: ?>
            <span class="adm-stat-link">All read</span>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Nav -->
<div class="adm-quick">
<?php $quick=[
    ['/admin/payments','bi-cash-coin','Payments',$pendingPayments>0],
    ['/admin/leads','bi-megaphone','Leads',false],
    ['/admin/students','bi-people','Students',false],
    ['/admin/programs','bi-stack','Programs',false],
    ['/admin/internships','bi-file-earmark-person','Internships',false],
    ['/admin/posts','bi-newspaper','Blog Posts',false],
    ['/admin/settings','bi-gear','Settings',false],
];
foreach($quick as [$href,$icon,$label,$alert]): ?>
    <a href="<?= base_url($href) ?>" class="adm-quick-item <?= $alert?'highlight':'' ?>">
        <div class="adm-quick-icon"><i class="bi <?= e($icon) ?>"></i></div>
        <span class="adm-quick-label"><?= e($label) ?></span>
        <?php if($alert&&$pendingPayments>0): ?><span class="adm-quick-badge"><?= $pendingPayments ?></span><?php endif; ?>
    </a>
<?php endforeach; ?>
</div>

<!-- Tables -->
<div class="tcm-grid-2" style="align-items:start;">

    <!-- Recent Orders with delete -->
    <div class="tcm-card">
        <div class="adm-table-head">
            <h3><i class="bi bi-receipt"></i> Recent Orders</h3>
            <div class="d-flex gap-8">
                <a href="<?= base_url('/admin/payments') ?>" class="tcm-btn ghost sm">All <i class="bi bi-arrow-right"></i></a>
                <?php if ($recentOrders !== []): ?>
                <form method="post" action="<?= base_url('/admin/orders/clear') ?>"
                      onsubmit="return confirm('Delete ALL order history? This cannot be undone.');">
                    <?= csrf_field() ?>
                    <button class="tcm-btn sm danger" title="Clear all orders">
                        <i class="bi bi-trash"></i> Clear All
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($recentOrders === []): ?>
            <div class="tcm-empty" style="padding:20px 0 8px;"><i class="bi bi-receipt"></i> No orders yet.</div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="tcm-table">
                <thead><tr><th>Order</th><th>Student</th><th>Item</th><th>₹</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($recentOrders as $o): ?>
                    <tr>
                        <td style="font-size:.73rem;font-family:monospace;color:var(--muted);"><?= e(substr($o['order_number'],0,14)) ?></td>
                        <td style="font-weight:600;font-size:.84rem;"><?= e($o['student_name']) ?></td>
                        <td style="max-width:110px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:.82rem;"><?= e($o['item_title']??ucfirst($o['item_type'])) ?></td>
                        <td style="font-weight:700;"><?= money($o['amount']) ?></td>
                        <td><span class="tcm-badge <?= $o['status']==='paid'?'green':'amber' ?>"><?= e($o['status']) ?></span></td>
                        <td>
                            <form method="post" action="<?= base_url('/admin/orders/'.$o['id'].'/delete') ?>"
                                  onsubmit="return confirm('Delete this order?');">
                                <?= csrf_field() ?>
                                <button class="tcm-btn sm danger" style="padding:4px 8px;"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Recent students -->
    <div class="tcm-card">
        <div class="adm-table-head">
            <h3><i class="bi bi-people"></i> New Students</h3>
            <a href="<?= base_url('/admin/students') ?>" class="tcm-btn ghost sm">All <i class="bi bi-arrow-right"></i></a>
        </div>
        <?php if ($recentStudents === []): ?>
            <div class="tcm-empty" style="padding:20px 0 8px;"><i class="bi bi-people"></i> No students yet.</div>
        <?php else: ?>
            <?php foreach ($recentStudents as $s): ?>
            <div class="tcm-row">
                <div class="tcm-row-main">
                    <div class="d-flex items-center gap-8">
                        <div class="tcm-avatar" style="width:32px;height:32px;font-size:.75rem;flex-shrink:0;overflow:hidden;">
                            <?= tcm_avatar($s['avatar'] ?? null, $s['name']) ?>
                        </div>
                        <div>
                            <div style="font-size:.85rem;font-weight:600;color:#111;">
                                <a href="<?= base_url('/admin/students/'.$s['id']) ?>" style="color:#111;text-decoration:none;"><?= e($s['name']) ?></a>
                            </div>
                            <div style="font-size:.72rem;color:var(--muted);"><?= e($s['email']) ?>
                                <?php if(!empty($s['student_id'])): ?>
                                    &nbsp;·&nbsp;<span style="font-family:monospace;color:#888;"><?= e($s['student_id']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tcm-row-meta"><?= e(date('d M',strtotime($s['created_at']))) ?></div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
