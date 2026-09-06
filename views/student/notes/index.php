<?php
// Course Notes Index - W3Schools Style
$expiresAt = $access['expires_at'] ?? null;
$daysLeft = null;
if ($expiresAt) {
    $expiry = new DateTime($expiresAt);
    $today = new DateTime();
    $daysLeft = $today->diff($expiry)->days;
}
?>
<style>
.notes-header { 
    background: #111;
    color: #fff; padding: 24px; border-radius: 16px; margin-bottom: 20px;
}
.notes-progress-bar {
    width: 100%; height: 6px; background: rgba(255,255,255,0.2); border-radius: 3px;
    overflow: hidden; margin-top: 12px;
}
.notes-progress-fill {
    height: 100%; background: #fff; border-radius: 3px;
    transition: width 0.3s ease;
}
.notes-grid { display: grid; grid-template-columns: 280px 1fr; gap: 20px; }
.notes-sidebar {
    background: #fff; border: 1px solid #ececec; border-radius: 14px;
    padding: 18px; max-height: calc(100vh - 200px); overflow-y: auto; position: sticky; top: 20px;
}
.notes-content {
    background: #fff; border: 1px solid #ececec; border-radius: 14px; padding: 24px;
}
.toc-item {
    padding: 10px 12px; border-radius: 8px; margin-bottom: 6px;
    display: flex; align-items: center; gap: 10px; cursor: pointer;
    transition: all 0.2s; text-decoration: none; color: #555;
}
.toc-item:hover { background: #f5f5f5; color: #111; }
.toc-item.read { opacity: 0.6; }
.toc-item.read .toc-icon { color: #22c55e; }
.toc-icon {
    width: 24px; height: 24px; border-radius: 50%; background: #f0f0f0;
    display: grid; place-items: center; font-size: 0.75rem; flex-shrink: 0;
}
.toc-title { font-size: 0.82rem; font-weight: 600; flex: 1; }
.toc-time { font-size: 0.7rem; color: #aaa; }
.note-card {
    border: 1px solid #e5e5e5; border-radius: 12px; padding: 16px;
    margin-bottom: 12px; transition: all 0.2s; cursor: pointer;
}
.note-card:hover { border-color: #111; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
.access-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;
    background: rgba(255,255,255,0.2); color: #fff;
}
@media (max-width: 768px) {
    .notes-grid { grid-template-columns: 1fr; }
    .notes-sidebar { position: static; max-height: none; }
}
</style>

<div class="notes-header">
    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
        <div>
            <h2 style="font-size: 1.4rem; margin: 0 0 6px;"><?= e($course['title']) ?></h2>
            <p style="margin: 0; opacity: 0.9; font-size: 0.88rem;">📚 Course Reading Material</p>
        </div>
        <?php if ($expiresAt && $daysLeft !== null): ?>
        <div class="access-badge">
            <i class="bi bi-clock"></i> <?= $daysLeft ?> days left
        </div>
        <?php endif; ?>
    </div>
    <div style="display: flex; align-items: center; gap: 12px;">
        <div style="flex: 1;">
            <div style="font-size: 0.75rem; opacity: 0.8; margin-bottom: 4px;">
                Progress: <?= $progress['read'] ?>/<?= $progress['total'] ?> chapters
            </div>
            <div class="notes-progress-bar">
                <div class="notes-progress-fill" style="width: <?= $progress['percentage'] ?>%"></div>
            </div>
        </div>
        <div style="font-size: 1.8rem; font-weight: 800; opacity: 0.9;">
            <?= $progress['percentage'] ?>%
        </div>
    </div>
</div>

<div class="notes-grid">
    <!-- Sidebar: Table of Contents -->
    <div class="notes-sidebar">
        <h3 style="font-size: 0.85rem; font-weight: 800; text-transform: uppercase; 
                   letter-spacing: 0.08em; color: #aaa; margin: 0 0 12px; padding-bottom: 10px; 
                   border-bottom: 1px solid #f0f0f0;">
            <i class="bi bi-list-ul"></i> Table of Contents
        </h3>
        
        <?php foreach ($notes as $i => $note): ?>
        <a href="<?= base_url('/student/notes/' . $course['id'] . '/' . $note['slug']) ?>" 
           class="toc-item">
            <div class="toc-icon">
                <?= $i + 1 ?>
            </div>
            <div class="toc-title"><?= e($note['title']) ?></div>
            <div class="toc-time"><?= $note['estimated_reading_time'] ?>m</div>
        </a>
        <?php endforeach; ?>
        
        <?php if (empty($notes)): ?>
        <div style="text-align: center; padding: 20px; color: #aaa;">
            <i class="bi bi-inbox" style="font-size: 2rem; margin-bottom: 8px; opacity: 0.3;"></i>
            <p style="font-size: 0.82rem; margin: 0;">No notes available yet</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Content: Notes List -->
    <div class="notes-content">
        <h3 style="font-size: 1.1rem; font-weight: 800; margin: 0 0 16px;">
            All Chapters (<?= count($notes) ?>)
        </h3>
        
        <?php foreach ($notes as $i => $note): ?>
        <a href="<?= base_url('/student/notes/' . $course['id'] . '/' . $note['slug']) ?>" 
           class="note-card" style="display: block; text-decoration: none; color: inherit;">
            <div style="display: flex; align-items: start; gap: 14px;">
                <div style="width: 48px; height: 48px; border-radius: 12px; background: #111;
                            display: grid; place-items: center; color: #fff; font-weight: 800; font-size: 1.1rem; flex-shrink: 0;">
                    <?= $i + 1 ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #111; margin: 0 0 6px;">
                        <?= e($note['title']) ?>
                    </h4>
                    <?php if ($note['excerpt']): ?>
                    <p style="font-size: 0.82rem; color: #666; margin: 0 0 8px; line-height: 1.5;">
                        <?= e($note['excerpt']) ?>
                    </p>
                    <?php endif; ?>
                    <div style="display: flex; align-items: center; gap: 12px; font-size: 0.75rem; color: #888;">
                        <span><i class="bi bi-clock"></i> <?= $note['estimated_reading_time'] ?> min read</span>
                        <span><i class="bi bi-calendar"></i> <?= date('d M Y', strtotime($note['created_at'])) ?></span>
                    </div>
                </div>
                <i class="bi bi-arrow-right-circle" style="font-size: 1.5rem; color: #ccc;"></i>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
