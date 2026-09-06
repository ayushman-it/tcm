<style>
/* ── Community Browse - Enhanced Design ── */
.cm-topbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 12px; flex-wrap: wrap; margin-bottom: 24px;
    padding: 20px; background: #111;
    border-radius: 16px; color: #fff;
}
.cm-topbar h2 { font-size: 1.4rem; font-weight: 800; color: #fff; letter-spacing: -.3px; margin: 0; }
.cm-topbar p  { font-size: .85rem; color: rgba(255,255,255,0.9); margin: 6px 0 0; }

/* Filter bar */
.cm-filters {
    display: flex; gap: 10px; flex-wrap: wrap;
    margin-bottom: 24px; align-items: center;
    padding: 16px; background: #fff; border-radius: 12px;
    border: 1px solid #ececec;
}
.cm-filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; }
.cm-tab {
    padding: 8px 16px; border-radius: 24px; font-size: .8rem;
    font-weight: 600; cursor: pointer; border: 2px solid #e5e5e5;
    background: #fff; color: #888; transition: all .2s;
    text-decoration: none;
}
.cm-tab:hover   { border-color: #111; color: #111; transform: translateY(-2px); }
.cm-tab.active  { background: #111; color: #fff; border-color: #111; }

/* Enhanced grid */
.cm-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 18px;
}

/* Enhanced card */
.cm-card {
    background: #fff;
    border: 1px solid #ececec;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all .3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}
.cm-card:hover {
    border-color: #667eea;
    box-shadow: 0 12px 40px rgba(102, 126, 234, 0.15);
    transform: translateY(-6px);
}

/* Card top — solid banner */
.cm-card-banner {
    height: 70px;
    background: #111;
    position: relative;
    overflow: hidden;
}
.cm-card-banner::before {
    content: '';
    position: absolute; inset: 0;
    background-image: radial-gradient(rgba(255,255,255,.12) 1.5px, transparent 1.5px);
    background-size: 20px 20px;
}
.cm-card-banner::after {
    content: '';
    position: absolute; bottom: -20px; right: -20px;
    width: 100px; height: 100px;
    background: rgba(255,255,255,0.1);
    border-radius: 50%;
}

/* Avatar overlapping banner */
.cm-card-avatar-wrap {
    display: flex; justify-content: center;
    margin-top: -35px; margin-bottom: 12px;
    position: relative; z-index: 1;
}
.cm-card-avatar {
    width: 70px; height: 70px; border-radius: 50%;
    border: 4px solid #fff;
    background: #f5f5f5;
    overflow: hidden; flex-shrink: 0;
    display: grid; place-items: center;
    font-size: 1.6rem; font-weight: 800; color: #667eea;
    box-shadow: 0 8px 24px rgba(0,0,0,.15);
}
.cm-card-avatar img { width: 100%; height: 100%; object-fit: cover; }

/* Card body */
.cm-card-body {
    padding: 0 18px 20px;
    text-align: center;
    flex: 1; display: flex; flex-direction: column;
}
.cm-card-name {
    font-size: .95rem; font-weight: 800; color: #111;
    margin-bottom: 4px; white-space: nowrap;
    overflow: hidden; text-overflow: ellipsis;
}
.cm-card-hl {
    font-size: .78rem; color: #666; line-height: 1.5;
    margin-bottom: 10px;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
    min-height: 2.4em;
}
.cm-card-level {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 12px; border-radius: 24px;
    font-size: .7rem; font-weight: 700; margin-bottom: 12px;
    align-self: center;
}
.cm-card-level.beginner     { background: #dcfce7; color: #166534; }
.cm-card-level.intermediate { background: #fef3c7; color: #92400e; }
.cm-card-level.advanced     { background: #dbeafe; color: #1e40af; }

/* Mini stats row */
.cm-card-stats {
    display: flex; justify-content: space-around; gap: 8px;
    margin-bottom: 14px; padding: 12px 0;
    background: #f9fafb; border-radius: 10px;
}
.cm-card-stat { text-align: center; flex: 1; }
.cm-card-stat strong { display: block; font-size: .95rem; font-weight: 800; color: #667eea; }
.cm-card-stat span   { font-size: .65rem; color: #888; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; }

/* Actions */
.cm-card-actions { display: flex; gap: 8px; margin-top: auto; }
.cm-card-btn {
    flex: 1; padding: 10px 12px; border-radius: 10px;
    font-size: .78rem; font-weight: 700; border: none;
    cursor: pointer; font-family: inherit; transition: all .2s;
    display: flex; align-items: center; justify-content: center; gap: 6px;
    text-decoration: none;
}
.cm-card-btn.primary   { 
    background: #111; 
    color: #fff; 
}
.cm-card-btn.primary:hover { 
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    transform: translateY(-2px);
}
.cm-card-btn.outline   { 
    background: #fff; color: #667eea; border: 2px solid #e5e7eb; 
}
.cm-card-btn.outline:hover { 
    border-color: #667eea; background: #f9fafb;
}

/* Location tag */
.cm-card-loc {
    font-size: .72rem; color: #999;
    display: flex; align-items: center; justify-content: center;
    gap: 4px; margin-bottom: 8px;
}

/* Empty */
.cm-empty {
    grid-column: 1/-1; text-align: center;
    padding: 80px 20px; color: #aaa;
    background: #fafafa; border-radius: 16px;
}
.cm-empty i { font-size: 3.5rem; display: block; margin-bottom: 16px; color: #ddd; }

/* Count badge */
.cm-count { 
    font-size: .8rem; color: rgba(255,255,255,0.8); 
    margin-left: 8px; font-weight: 400;
    background: rgba(255,255,255,0.15);
    padding: 3px 10px; border-radius: 20px;
}

@media(max-width: 768px) {
    .cm-grid { grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 14px; }
}
@media(max-width: 480px) {
    .cm-grid { grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .cm-card-name { font-size: .85rem; }
    .cm-card-avatar { width: 60px; height: 60px; }
}
@media(max-width: 360px) {
    .cm-grid { grid-template-columns: 1fr; }
}
</style>

<!-- Topbar -->
<div class="cm-topbar">
    <div>
        <h2><i class="bi bi-people-fill" style="margin-right:8px;color:#888;"></i>Community <span class="cm-count"><?= count($students) ?> students</span></h2>
        <p>Discover and connect with fellow learners at The Code Munk.</p>
    </div>
</div>

<!-- Filters + Search -->
<form method="get" style="margin-bottom:18px;">
    <div class="cm-filters">
        <input class="tcm-input" name="q" placeholder="Search by name, headline, location..."
               value="<?= e($search) ?>" style="flex:1;min-width:200px;">

        <div class="cm-filter-tabs">
            <?php
            $tabs = [''=>'All', 'beginner'=>'🌱 Beginner', 'intermediate'=>'⚡ Intermediate', 'advanced'=>'🔥 Advanced'];
            foreach ($tabs as $val => $label):
            ?>
                <a href="?<?= http_build_query(['q' => $search, 'filter' => $val]) ?>"
                   class="cm-tab <?= $filter === $val ? 'active' : '' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <button class="tcm-btn primary" type="submit">
            <i class="bi bi-search"></i> Search
        </button>
    </div>
</form>

<!-- Grid -->
<div class="cm-grid">
    <?php if (empty($students)): ?>
        <div class="cm-empty">
            <i class="bi bi-people"></i>
            <div style="font-size:.95rem;font-weight:700;color:#555;margin-bottom:6px;">No students found</div>
            <div style="font-size:.82rem;">Try a different search or filter.</div>
        </div>
    <?php endif; ?>

    <?php foreach ($students as $s):
        $lvl  = $s['experience_level'] ?? '';
        $lvlLabels = ['beginner'=>'🌱 Beginner','intermediate'=>'⚡ Intermediate','advanced'=>'🔥 Advanced'];
    ?>
    <div class="cm-card">
        <!-- Dark banner -->
        <div class="cm-card-banner"></div>

        <!-- Avatar -->
        <div class="cm-card-avatar-wrap">
            <div class="cm-card-avatar">
                <?= tcm_avatar($s['avatar'] ?? null, $s['name'], '', '', $s['name']) ?>
            </div>
        </div>

        <!-- Body -->
        <div class="cm-card-body">
            <div class="cm-card-name" title="<?= e($s['name']) ?>"><?= e($s['name']) ?></div>
            <div class="cm-card-hl"><?= e($s['headline'] ?? 'Student at The Code Munk') ?></div>

            <?php if (!empty($s['location'])): ?>
                <div class="cm-card-loc">
                    <i class="bi bi-geo-alt-fill"></i> <?= e($s['location']) ?>
                </div>
            <?php endif; ?>

            <?php if ($lvl): ?>
                <div class="cm-card-level <?= e($lvl) ?>">
                    <?= e($lvlLabels[$lvl] ?? ucfirst($lvl)) ?>
                </div>
            <?php endif; ?>

            <!-- Mini stats -->
            <div class="cm-card-stats">
                <div class="cm-card-stat">
                    <strong><?= (int)$s['project_count'] ?></strong>
                    <span>Projects</span>
                </div>
                <div class="cm-card-stat">
                    <strong><?= (int)$s['skill_count'] ?></strong>
                    <span>Skills</span>
                </div>
                <div class="cm-card-stat">
                    <strong><?= (int)$s['cert_count'] ?></strong>
                    <span>Certs</span>
                </div>
            </div>

            <!-- Actions -->
            <div class="cm-card-actions">
                <a href="<?= base_url('/student/community/' . (int)$s['id']) ?>"
                   class="cm-card-btn primary">
                    <i class="bi bi-person-fill"></i> View Profile
                </a>
                <a href="<?= base_url('/student/chat') ?>" class="cm-card-btn outline" title="Chat">
                    <i class="bi bi-chat-dots"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
