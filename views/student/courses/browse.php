<?php use TCM\Models\Course; ?>

<!-- ── Lead Interest Modal ── -->
<div id="leadModal" style="display:none;position:fixed;inset:0;z-index:1000;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;">
    <div style="background:#fff;border-radius:20px;max-width:500px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.18);overflow:hidden;animation:slideUp .25s ease;">
        <!-- Modal Header -->
        <div style="background:#111;padding:22px 26px 18px;position:relative;">
            <div style="font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:6px;">
                <i class="bi bi-journal-code" style="margin-right:5px;"></i> Course Enquiry
            </div>
            <div style="font-size:1.05rem;font-weight:800;color:#fff;" id="modalItemTitle">—</div>
            <div style="font-size:.82rem;color:rgba(255,255,255,.5);margin-top:3px;" id="modalItemPrice"></div>
            <button onclick="closeLeadModal()" style="position:absolute;top:16px;right:16px;background:rgba(255,255,255,.1);border:none;color:#fff;width:30px;height:30px;border-radius:50%;cursor:pointer;font-size:1rem;display:grid;place-items:center;transition:.15s;" onmouseover="this.style.background='rgba(255,255,255,.2)'" onmouseout="this.style.background='rgba(255,255,255,.1)'">
                <i class="bi bi-x"></i>
            </button>
        </div>
        <!-- Modal Body -->
        <div style="padding:22px 26px 26px;">
            <p style="font-size:.85rem;color:#555;margin-bottom:18px;line-height:1.6;">
                Tell us a bit about yourself and we'll get in touch to confirm your enrollment. Our team usually responds within a few hours.
            </p>
            <form method="post" action="<?= base_url('/student/interest') ?>" id="leadModalForm">
                <?= csrf_field() ?>
                <input type="hidden" name="item_type" value="course">
                <input type="hidden" name="item_id" id="modalItemId">
                <div class="tcm-field" style="margin-bottom:12px;">
                    <label>Your Phone / WhatsApp</label>
                    <input class="tcm-input" name="phone" placeholder="+91 98765 43210" type="tel">
                </div>
                <div class="tcm-field" style="margin-bottom:16px;">
                    <label>Quick Message <span style="font-weight:400;color:#aaa;">(optional)</span></label>
                    <textarea class="tcm-textarea" name="message" style="min-height:70px;" placeholder="e.g. I want to join the next batch, when does it start?"></textarea>
                </div>
                <button type="submit" class="tcm-btn primary w-full" style="justify-content:center;padding:12px;font-size:.92rem;">
                    <i class="bi bi-send"></i> Submit Interest &amp; Continue to Payment
                </button>
                <div style="text-align:center;font-size:.72rem;color:#aaa;margin-top:10px;">
                    <i class="bi bi-shield-check"></i> Your info is private. No spam ever.
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes slideUp { from{opacity:0;transform:translateY(20px)} to{opacity:1;transform:translateY(0)} }
</style>

<div class="tcm-page-head">
    <div><h2>Courses</h2><p>Find a learning path that fits your goals.</p></div>
</div>

<!-- Tab Navigation -->
<div style="display: flex; gap: 8px; margin-bottom: 18px; border-bottom: 2px solid #ececec; overflow-x: auto; -webkit-overflow-scrolling: touch;">
    <button onclick="switchTab('all')" id="tabAll" 
            style="padding: 10px 20px; background: none; border: none; border-bottom: 3px solid #111; color: #111; font-weight: 700; font-size: 0.875rem; cursor: pointer; white-space: nowrap; transition: all 0.2s; margin-bottom: -2px;">
        <i class="bi bi-grid-3x3-gap"></i> All Courses
    </button>
    <button onclick="switchTab('enrolled')" id="tabEnrolled"
            style="padding: 10px 20px; background: none; border: none; border-bottom: 3px solid transparent; color: #888; font-weight: 600; font-size: 0.875rem; cursor: pointer; white-space: nowrap; transition: all 0.2s; margin-bottom: -2px;">
        <i class="bi bi-bookmark-check-fill"></i> Enrolled <span style="background: #111; color: #fff; padding: 2px 7px; border-radius: 10px; font-size: 0.7rem; margin-left: 4px;"><?= count($owned) ?></span>
    </button>
</div>

<!-- All Courses Tab -->
<div id="contentAll">
    <div class="tcm-card" style="margin-bottom:18px;">
        <form method="get" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <input class="tcm-input" name="q" placeholder="Search courses..."
                   value="<?= e($_GET['q'] ?? '') ?>" style="flex:1;min-width:200px;">
            <select class="tcm-select" name="audience" style="width:200px;" onchange="this.form.submit()">
                <option value="">All audiences</option>
                <?php foreach (['college'=>'College','beginners'=>'Beginners','working'=>'Working Pros'] as $k=>$lbl): ?>
                    <option value="<?= $k ?>" <?= ($_GET['audience'] ?? '') === $k ? 'selected' : '' ?>><?= $lbl ?></option>
                <?php endforeach; ?>
            </select>
            <button class="tcm-btn primary">Search</button>
        </form>
    </div>

    <div class="grid-cards">
        <?php foreach ($courses as $c):
            $isOwned = in_array((int)$c['id'], array_map('intval', $owned), true);
            $isPaid  = (float)$c['price'] > 0;
        ?>
        <div class="tcm-card" style="display:flex;flex-direction:column;gap:0;">
            <!-- Icon + badge -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <div style="width:46px;height:46px;border-radius:12px;background:#f5f5f5;border:1px solid #ececec;display:grid;place-items:center;font-size:1.3rem;color:#111;">
                    <i class="bi <?= e($c['icon'] ?? 'bi-journal-code') ?>"></i>
                </div>
                <div style="display:flex;gap:6px;align-items:center;">
                    <?php if ($c['original_price'] && $c['original_price'] > $c['price']): ?>
                        <span class="tcm-badge green"><?= Course::discountPercent($c) ?>% off</span>
                    <?php endif; ?>
                    <?php if ($isOwned): ?>
                        <span class="tcm-badge green"><i class="bi bi-check2"></i> Enrolled</span>
                    <?php endif; ?>
                </div>
            </div>

            <h3 style="margin:0 0 5px;font-size:.95rem;font-weight:700;color:#111;"><?= e($c['title']) ?></h3>
            <p style="color:#888;font-size:.82rem;margin-bottom:12px;flex:1;min-height:36px;line-height:1.5;"><?= e($c['subtitle'] ?? '') ?></p>

            <!-- Price + rating -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 14px;">
                <div>
                    <strong style="font-size:1.05rem;color:#111;"><?= $isPaid ? money($c['price']) : '<span style="color:#16a34a;font-weight:700;">Free</span>' ?></strong>
                    <?php if ($c['original_price'] && $isPaid): ?>
                        <span style="color:#bbb;text-decoration:line-through;font-size:.8rem;margin-left:5px;"><?= money($c['original_price']) ?></span>
                    <?php endif; ?>
                </div>
                <?php if ((float)$c['rating'] > 0): ?>
                    <span style="font-size:.78rem;color:#888;display:flex;align-items:center;gap:4px;">
                        <i class="bi bi-star-fill" style="color:#f59e0b;"></i> <?= e((string)$c['rating']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 8px;">
                <a class="tcm-btn" href="<?= base_url('/student/courses/' . $c['slug']) ?>" style="flex:1;justify-content:center;">
                    Details
                </a>
                <?php if ($isOwned): ?>
                    <a class="tcm-btn primary" href="<?= base_url('/student/learn/' . $c['id']) ?>" style="flex:1;justify-content:center;">
                        <i class="bi bi-play-fill"></i> Continue
                    </a>
                <?php elseif ($isPaid): ?>
                    <!-- Paid: open lead form popup -->
                    <button type="button"
                            onclick="openLeadModal(<?= (int)$c['id'] ?>, '<?= e(addslashes($c['title'])) ?>', '<?= money($c['price']) ?>')"
                            class="tcm-btn primary" style="flex:1;justify-content:center;">
                        <i class="bi bi-cart-check"></i> Buy <?= money($c['price']) ?>
                    </button>
                <?php else: ?>
                    <!-- Free: enroll directly -->
                    <form method="post" action="<?= base_url('/student/courses/' . $c['id'] . '/buy') ?>" style="flex:1;">
                        <?= csrf_field() ?>
                        <button class="tcm-btn primary" style="width:100%;justify-content:center;">
                            <i class="bi bi-check2-circle"></i> Enroll Free
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php if ($courses === []): ?>
            <div style="grid-column:1/-1;" class="tcm-empty">
                <i class="bi bi-journal-x"></i>No courses match your search.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enrolled Courses Tab -->
<div id="contentEnrolled" style="display: none;">
    <?php 
    $enrolledCourses = array_filter($courses, function($c) use ($owned) {
        return in_array((int)$c['id'], array_map('intval', $owned), true);
    });
    ?>
    
    <?php if (empty($enrolledCourses)): ?>
        <div class="tcm-empty" style="padding: 60px 20px;">
            <i class="bi bi-journal-x"></i>
            <p>You haven't enrolled in any courses yet.</p>
            <button onclick="switchTab('all')" class="tcm-btn primary" style="margin-top: 16px;">
                <i class="bi bi-search"></i> Browse Courses
            </button>
        </div>
    <?php else: ?>
        <div class="grid-cards">
            <?php foreach ($enrolledCourses as $c): 
                $isPaid  = (float)$c['price'] > 0;
            ?>
            <div class="tcm-card" style="display:flex;flex-direction:column;gap:0;">
                <!-- Icon + badge -->
                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                    <div style="width:46px;height:46px;border-radius:12px;background:#f5f5f5;border:1px solid #ececec;display:grid;place-items:center;font-size:1.3rem;color:#111;">
                        <i class="bi <?= e($c['icon'] ?? 'bi-journal-code') ?>"></i>
                    </div>
                    <span class="tcm-badge green"><i class="bi bi-check2"></i> Enrolled</span>
                </div>

                <h3 style="margin:0 0 5px;font-size:.95rem;font-weight:700;color:#111;"><?= e($c['title']) ?></h3>
                <p style="color:#888;font-size:.82rem;margin-bottom:12px;flex:1;min-height:36px;line-height:1.5;"><?= e($c['subtitle'] ?? '') ?></p>

                <!-- Progress bar -->
                <?php 
                $enrollment = array_values(array_filter($enrollments ?? [], function($e) use ($c) {
                    return (int)$e['course_id'] === (int)$c['id'];
                }))[0] ?? null;
                $progress = $enrollment ? (int)$enrollment['progress'] : 0;
                ?>
                <div style="margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 0.75rem; color: #888; font-weight: 600;">Progress</span>
                        <span style="font-size: 0.75rem; color: #111; font-weight: 700;"><?= $progress ?>%</span>
                    </div>
                    <div style="height: 4px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                        <div style="height: 100%; background: #111; border-radius: 10px; width: <?= $progress ?>%; transition: width 0.5s ease;"></div>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 8px;">
                    <a class="tcm-btn" href="<?= base_url('/student/courses/' . $c['slug']) ?>" style="flex:1;justify-content:center;">
                        Details
                    </a>
                    <a class="tcm-btn primary" href="<?= base_url('/student/learn/' . $c['id']) ?>" style="flex:1;justify-content:center;">
                        <i class="bi bi-play-fill"></i> Continue Learning
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function switchTab(tab) {
    // Update tab buttons
    const tabAll = document.getElementById('tabAll');
    const tabEnrolled = document.getElementById('tabEnrolled');
    const contentAll = document.getElementById('contentAll');
    const contentEnrolled = document.getElementById('contentEnrolled');
    
    if (tab === 'all') {
        tabAll.style.borderBottomColor = '#111';
        tabAll.style.color = '#111';
        tabAll.style.fontWeight = '700';
        tabEnrolled.style.borderBottomColor = 'transparent';
        tabEnrolled.style.color = '#888';
        tabEnrolled.style.fontWeight = '600';
        contentAll.style.display = 'block';
        contentEnrolled.style.display = 'none';
    } else {
        tabAll.style.borderBottomColor = 'transparent';
        tabAll.style.color = '#888';
        tabAll.style.fontWeight = '600';
        tabEnrolled.style.borderBottomColor = '#111';
        tabEnrolled.style.color = '#111';
        tabEnrolled.style.fontWeight = '700';
        contentAll.style.display = 'none';
        contentEnrolled.style.display = 'block';
    }
}

function openLeadModal(id, title, price) {
    document.getElementById('modalItemId').value = id;
    document.getElementById('modalItemTitle').textContent = title;
    document.getElementById('modalItemPrice').textContent = price;
    var m = document.getElementById('leadModal');
    m.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLeadModal() {
    document.getElementById('leadModal').style.display = 'none';
    document.body.style.overflow = '';
}
// Close on backdrop click
document.getElementById('leadModal').addEventListener('click', function(e) {
    if (e.target === this) closeLeadModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLeadModal();
});
</script>
