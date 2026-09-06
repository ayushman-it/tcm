<?php
// Individual Note Reading Page - W3Schools Style
?>
<style>
.note-reader { display: grid; grid-template-columns: 280px 1fr; gap: 20px; }
.note-sidebar {
    background: #fff; border: 1px solid #ececec; border-radius: 14px;
    padding: 18px; max-height: calc(100vh - 140px); overflow-y: auto;
    position: sticky; top: 20px;
}
.note-main {
    background: #fff; border: 1px solid #ececec; border-radius: 14px;
    padding: 32px 40px; max-width: 900px;
}
.note-content { line-height: 1.8; font-size: 1rem; color: #333; }
.note-content h1 { font-size: 2rem; font-weight: 800; color: #111; margin: 0 0 8px; }
.note-content h2 { font-size: 1.5rem; font-weight: 700; color: #111; margin: 32px 0 16px; border-bottom: 2px solid #f0f0f0; padding-bottom: 8px; }
.note-content h3 { font-size: 1.2rem; font-weight: 600; color: #111; margin: 24px 0 12px; }
.note-content p { margin: 0 0 16px; }
.note-content code {
    background: #f5f5f5; padding: 2px 6px; border-radius: 4px;
    font-family: 'Consolas', 'Monaco', monospace; font-size: 0.9em; color: #c7254e;
}
.note-content pre {
    background: #2d2d2d; color: #f8f8f2; padding: 16px; border-radius: 8px;
    overflow-x: auto; margin: 16px 0; font-family: 'Consolas', 'Monaco', monospace;
}
.note-content pre code { background: none; color: inherit; padding: 0; }
.note-content blockquote {
    border-left: 4px solid #667eea; background: #f9f9ff; padding: 12px 16px;
    margin: 16px 0; font-style: italic; color: #555;
}
.note-nav {
    display: flex; justify-content: space-between; gap: 12px;
    padding-top: 24px; margin-top: 32px; border-top: 2px solid #f0f0f0;
}
.note-nav-btn {
    flex: 1; padding: 14px 18px; border-radius: 10px; text-decoration: none;
    display: flex; align-items: center; gap: 10px; transition: all 0.2s;
    border: 1px solid #e5e5e5; background: #fff; color: #555; font-weight: 600;
}
.note-nav-btn:hover { border-color: #111; background: #111; color: #fff; transform: translateY(-2px); }
.note-nav-btn.disabled { opacity: 0.3; pointer-events: none; }
.toc-mini-item {
    padding: 8px 10px; border-radius: 6px; margin-bottom: 4px;
    font-size: 0.8rem; color: #666; transition: all 0.15s;
    text-decoration: none; display: block;
}
.toc-mini-item:hover { background: #f5f5f5; color: #111; }
.toc-mini-item.active { background: #667eea; color: #fff; font-weight: 700; }
@media (max-width: 768px) {
    .note-reader { grid-template-columns: 1fr; }
    .note-sidebar { position: static; max-height: none; }
    .note-main { padding: 20px; }
}
</style>

<div style="margin-bottom: 16px;">
    <a href="<?= base_url('/student/notes/' . $course['id']) ?>" class="tcm-btn ghost sm">
        <i class="bi bi-arrow-left"></i> Back to Table of Contents
    </a>
</div>

<div class="note-reader">
    <!-- Sidebar: Mini TOC -->
    <div class="note-sidebar">
        <div style="margin-bottom: 16px;">
            <div style="font-size: 0.7rem; font-weight: 700; text-transform: uppercase; 
                       letter-spacing: 0.08em; color: #aaa; margin-bottom: 8px;">
                Course
            </div>
            <div style="font-size: 0.9rem; font-weight: 700; color: #111;">
                <?= e($course['title']) ?>
            </div>
        </div>

        <div style="margin-bottom: 16px; padding: 10px; background: #f9f9ff; border-radius: 8px;">
            <div style="font-size: 0.7rem; color: #888; margin-bottom: 4px;">Progress</div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <div style="flex: 1; height: 6px; background: #e5e5e5; border-radius: 3px; overflow: hidden;">
                    <div style="height: 100%; width: <?= $progress['percentage'] ?>%; background: #667eea; border-radius: 3px;"></div>
                </div>
                <div style="font-size: 0.85rem; font-weight: 700; color: #667eea;">
                    <?= $progress['percentage'] ?>%
                </div>
            </div>
        </div>

        <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; 
                   letter-spacing: 0.08em; color: #aaa; margin-bottom: 8px;">
            All Chapters
        </div>
        <?php foreach ($toc as $i => $item): ?>
        <a href="<?= base_url('/student/notes/' . $course['id'] . '/' . $item['slug']) ?>" 
           class="toc-mini-item <?= $item['id'] == $note['id'] ? 'active' : '' ?>">
            <?= $i + 1 ?>. <?= e($item['title']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Main: Note Content -->
    <div class="note-main">
        <div class="note-content">
            <?= $note['content'] ?>
        </div>

        <!-- Navigation -->
        <div class="note-nav">
            <?php if ($prev): ?>
            <a href="<?= base_url('/student/notes/' . $course['id'] . '/' . $prev['slug']) ?>" 
               class="note-nav-btn" style="justify-content: flex-start;">
                <i class="bi bi-arrow-left"></i>
                <div>
                    <div style="font-size: 0.7rem; opacity: 0.6;">Previous</div>
                    <div><?= e($prev['title']) ?></div>
                </div>
            </a>
            <?php else: ?>
            <div class="note-nav-btn disabled">
                <i class="bi bi-arrow-left"></i> No previous chapter
            </div>
            <?php endif; ?>

            <?php if ($next): ?>
            <a href="<?= base_url('/student/notes/' . $course['id'] . '/' . $next['slug']) ?>" 
               class="note-nav-btn" style="justify-content: flex-end; text-align: right;">
                <div>
                    <div style="font-size: 0.7rem; opacity: 0.6;">Next</div>
                    <div><?= e($next['title']) ?></div>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>
            <?php else: ?>
            <div class="note-nav-btn disabled" style="text-align: right;">
                No next chapter <i class="bi bi-arrow-right"></i>
            </div>
            <?php endif; ?>
        </div>

        <!-- Completion Button -->
        <div style="margin-top: 24px; text-align: center;">
            <a href="<?= base_url('/student/notes/' . $course['id']) ?>" class="tcm-btn primary">
                <i class="bi bi-check-circle"></i> Chapter Completed
            </a>
        </div>
    </div>
</div>
