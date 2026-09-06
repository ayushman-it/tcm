<?php
$totalLessons = 0;
$doneCount = 0;
foreach ($curriculum as $m) {
    foreach ($m['lessons'] as $l) {
        $totalLessons++;
        if (in_array((int) $l['id'], $completed, true)) {
            $doneCount++;
        }
    }
}
$percent = $totalLessons > 0 ? (int) round($doneCount / $totalLessons * 100) : 0;
?>

<style>
/* Lesson Card - Expandable */
.lesson-item {
    border-bottom: 1px solid #e5e5e5;
    padding: 12px 0;
}
.lesson-item:last-child { border-bottom: none; }

.lesson-header {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}

.lesson-check {
    width: 24px;
    height: 24px;
    flex-shrink: 0;
}

.lesson-title {
    flex: 1;
    font-size: 0.95rem;
    font-weight: 600;
    color: #111;
}

.lesson-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.lesson-expand-btn {
    background: none;
    border: none;
    padding: 6px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    color: #666;
    cursor: pointer;
    border-radius: 6px;
    transition: all 0.2s;
}

.lesson-expand-btn:hover {
    background: #f5f5f5;
    color: #111;
}

.lesson-expand-btn i {
    transition: transform 0.3s;
}

.lesson-item.expanded .lesson-expand-btn i {
    transform: rotate(180deg);
}

.lesson-toggle-btn {
    padding: 6px 16px;
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 6px;
    border: 1px solid #e5e5e5;
    background: #fff;
    color: #666;
    cursor: pointer;
    transition: all 0.2s;
}

.lesson-toggle-btn:hover {
    background: #111;
    color: #fff;
    border-color: #111;
}

/* Lesson Content - Hidden by default */
.lesson-content {
    display: none;
    padding: 16px 0 8px 36px;
    animation: slideDown 0.3s ease-out;
}

.lesson-item.expanded .lesson-content {
    display: block;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.lesson-loading {
    padding: 20px;
    text-align: center;
    color: #888;
}

.lesson-summary {
    padding: 12px 16px;
    background: #f9f9f9;
    border-left: 3px solid #111;
    border-radius: 6px;
    margin-bottom: 16px;
    font-size: 0.9rem;
    line-height: 1.6;
    color: #333;
}

.lesson-points {
    margin: 16px 0;
}

.lesson-points h4 {
    font-size: 0.85rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #666;
    margin: 0 0 10px 0;
}

.lesson-points ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.lesson-points li {
    padding: 6px 0 6px 24px;
    position: relative;
    font-size: 0.88rem;
    color: #333;
    line-height: 1.5;
}

.lesson-points li::before {
    content: '•';
    position: absolute;
    left: 8px;
    color: #111;
    font-weight: 700;
}

.lesson-code {
    margin: 16px 0;
}

.lesson-code-title {
    font-size: 0.85rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 8px 0;
}

.code-block {
    background: #1e293b;
    color: #e2e8f0;
    padding: 16px;
    border-radius: 8px;
    overflow-x: auto;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.6;
    position: relative;
}

.code-block::before {
    content: attr(data-lang);
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.05em;
}

.code-explanation {
    margin-top: 8px;
    font-size: 0.85rem;
    color: #666;
    line-height: 1.5;
}

.lesson-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e5e5e5;
}

.teach-more-btn {
    padding: 10px 20px;
    background: #111;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.teach-more-btn:hover {
    background: #333;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.teach-more-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.detailed-concepts {
    margin: 20px 0;
}

.concept-card {
    background: #f9f9f9;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 12px;
}

.concept-card h5 {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 8px 0;
}

.concept-card p {
    font-size: 0.88rem;
    color: #333;
    line-height: 1.6;
    margin: 0 0 12px 0;
}

.concept-card pre {
    background: #1e293b;
    color: #e2e8f0;
    padding: 12px;
    border-radius: 6px;
    overflow-x: auto;
    font-size: 0.8rem;
    margin: 0;
}

.tips-section {
    margin: 16px 0;
}

.tips-section h4 {
    font-size: 0.9rem;
    font-weight: 700;
    color: #111;
    margin: 0 0 12px 0;
}

.tips-list {
    background: #fffef7;
    border-left: 3px solid #fbbf24;
    padding: 12px 16px;
    border-radius: 6px;
}

.tips-list li {
    font-size: 0.85rem;
    color: #333;
    line-height: 1.5;
    padding: 4px 0;
}

.mistakes-list {
    background: #fef2f2;
    border-left: 3px solid #ef4444;
    padding: 12px 16px;
    border-radius: 6px;
}

.mistakes-list li {
    font-size: 0.85rem;
    color: #333;
    line-height: 1.5;
    padding: 4px 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .lesson-header {
        flex-wrap: wrap;
    }
    
    .lesson-title {
        font-size: 0.9rem;
        flex: 1 1 100%;
        margin-bottom: 8px;
    }
    
    .lesson-meta {
        flex: 1 1 100%;
        justify-content: space-between;
    }
    
    .lesson-expand-btn,
    .lesson-toggle-btn {
        font-size: 0.75rem;
        padding: 4px 10px;
    }
    
    .lesson-content {
        padding: 12px 0 8px 0;
    }
    
    .code-block {
        font-size: 0.75rem;
        padding: 12px;
    }
    
    .teach-more-btn {
        width: 100%;
        justify-content: center;
        padding: 12px 16px;
    }
    
    .concept-card {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .lesson-check {
        width: 20px;
        height: 20px;
    }
    
    .lesson-title {
        font-size: 0.85rem;
    }
    
    .lesson-summary {
        padding: 10px 12px;
        font-size: 0.85rem;
    }
    
    .code-block {
        font-size: 0.7rem;
        padding: 10px;
    }
}
</style>

<div class="tcm-page-head">
    <div><h2><?= e($course['title']) ?></h2><p><?= $doneCount ?> of <?= $totalLessons ?> lessons complete</p></div>
    <a class="tcm-btn" href="<?= base_url('/student') ?>"><i class="bi bi-arrow-left"></i> Dashboard</a>
</div>

<div class="tcm-card" style="margin-bottom:18px;">
    <div class="flex-between" style="margin-bottom:8px;">
        <strong>Your progress</strong><span class="tcm-badge purple"><?= $percent ?>%</span>
    </div>
    <div class="tcm-progress"><span style="width:<?= $percent ?>%"></span></div>
    <?php if ($percent >= 100): ?>
        <p class="muted" style="margin:12px 0 0;"><i class="bi bi-patch-check-fill" style="color:var(--tcm-accent);"></i>
            Course complete — your certificate is now in your portfolio!</p>
    <?php endif; ?>
</div>

<?php foreach ($curriculum as $m): ?>
    <div class="tcm-card" style="margin-bottom:14px;">
        <h3 class="mt-0"><?= e($m['title']) ?></h3>
        <?php foreach ($m['lessons'] as $l): 
            $isDone = in_array((int)$l['id'], $completed, true); 
            $lessonId = (int)$l['id'];
        ?>
            <div class="lesson-item" data-lesson-id="<?= $lessonId ?>">
                <div class="lesson-header">
                    <i class="bi <?= $isDone ? 'bi-check-circle-fill' : 'bi-circle' ?> lesson-check" 
                       style="color:<?= $isDone ? '#111' : '#ccc' ?>;"></i>
                    <div class="lesson-title"><?= e($l['title']) ?></div>
                    <div class="lesson-meta">
                        <span class="tcm-badge gray"><?= e($l['type']) ?></span>
                        <button class="lesson-expand-btn" onclick="toggleLesson(<?= $lessonId ?>)">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <form method="post" action="<?= base_url('/student/learn/' . $course['id'] . '/lessons/' . $lessonId) ?>" 
                              style="display: inline;" onsubmit="return handleToggle(event, <?= $lessonId ?>, <?= $isDone ? 1 : 0 ?>)">
                            <?= csrf_field() ?>
                            <button type="submit" class="lesson-toggle-btn">
                                <?= $isDone ? 'Undone' : 'Done' ?>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="lesson-content" id="lesson-content-<?= $lessonId ?>">
                    <div class="lesson-loading">
                        <i class="bi bi-hourglass-split"></i> Loading content...
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if ($m['lessons'] === []): ?><p class="muted mb-0">No lessons in this module yet.</p><?php endif; ?>
    </div>
<?php endforeach; ?>
<?php if ($curriculum === []): ?><div class="tcm-card"><p class="muted mb-0">Curriculum will be available soon.</p></div><?php endif; ?>

<script>
const expandedLessons = new Set();
const lessonCache = new Map();

async function toggleLesson(lessonId) {
    const item = document.querySelector(`[data-lesson-id="${lessonId}"]`);
    const content = document.getElementById(`lesson-content-${lessonId}`);
    
    if (item.classList.contains('expanded')) {
        item.classList.remove('expanded');
        expandedLessons.delete(lessonId);
        return;
    }
    
    item.classList.add('expanded');
    expandedLessons.add(lessonId);
    
    // Check cache first
    if (lessonCache.has(lessonId)) {
        content.innerHTML = lessonCache.get(lessonId);
        return;
    }
    
    // Load basic content
    const url = `<?= base_url('/student/lessons/') ?>${lessonId}/content?mode=basic`;
    console.log('[Lesson] Loading from:', url);
    
    try {
        const response = await fetch(url);
        console.log('[Lesson] Response status:', response.status);
        
        const data = await response.json();
        console.log('[Lesson] Response data:', data);
        
        if (data.success) {
            const html = renderLessonContent(data.data.content, lessonId);
            lessonCache.set(lessonId, html);
            content.innerHTML = html;
        } else {
            console.error('[Lesson] API returned error:', data.message);
            content.innerHTML = '<div class="lesson-loading" style="color: #ef4444;">Failed to load content: ' + (data.message || 'Unknown error') + '</div>';
        }
    } catch (error) {
        console.error('[Lesson] Fetch error:', error);
        content.innerHTML = '<div class="lesson-loading" style="color: #ef4444;">Error loading content. Please try again.</div>';
    }
}

function renderLessonContent(data, lessonId) {
    let html = '';
    
    // Definition (if present)
    if (data.definition) {
        html += `<div class="lesson-summary" style="background: #f0f7ff; border-left-color: #0066cc;">
            <strong style="display: block; margin-bottom: 8px; color: #0066cc;">📖 Definition</strong>
            ${escapeHtml(data.definition)}
        </div>`;
    }
    
    // Summary (fallback for old format)
    if (data.summary && !data.definition) {
        html += `<div class="lesson-summary">${escapeHtml(data.summary)}</div>`;
    }
    
    // Syntax (if present)
    if (data.syntax) {
        html += `<div class="lesson-code" style="margin-top: 16px;">
            <div class="lesson-code-title">⚙️ Syntax</div>
            <div class="code-block" data-lang="syntax">
                <pre>${escapeHtml(data.syntax)}</pre>
            </div>
        </div>`;
    }
    
    // Key Points
    if (data.keyPoints && data.keyPoints.length > 0) {
        html += `<div class="lesson-points">
            <h4>📌 Key Concepts</h4>
            <ul>${data.keyPoints.map(p => `<li>${escapeHtml(p)}</li>`).join('')}</ul>
        </div>`;
    }
    
    // Code Example (old format)
    if (data.codeExample) {
        html += `<div class="lesson-code">
            <div class="lesson-code-title">💻 ${escapeHtml(data.codeExample.title || 'Example')}</div>
            <div class="code-block" data-lang="${escapeHtml(data.codeExample.language || 'code')}">
                <pre>${escapeHtml(data.codeExample.code)}</pre>
            </div>`;
        
        if (data.codeExample.explanation) {
            html += `<div class="code-explanation">${escapeHtml(data.codeExample.explanation)}</div>`;
        }
        
        if (data.codeExample.output) {
            html += `<div class="code-explanation" style="margin-top: 8px; padding: 8px 12px; background: #f0fdf4; border-left: 3px solid #22c55e; border-radius: 4px;">
                <strong style="color: #166534;">Output:</strong> ${escapeHtml(data.codeExample.output)}
            </div>`;
        }
        
        html += `</div>`;
    }
    
    // Teach Me More button
    html += `<div class="lesson-actions">
        <button class="teach-more-btn" onclick="loadDetailedContent(${lessonId})">
            <i class="bi bi-book"></i> Teach Me More
        </button>
    </div>`;
    
    html += `<div id="detailed-content-${lessonId}"></div>`;
    
    return html;
}

async function loadDetailedContent(lessonId) {
    const btn = event.target.closest('.teach-more-btn');
    const detailedDiv = document.getElementById(`detailed-content-${lessonId}`);
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Loading...';
    
    try {
        const response = await fetch(`<?= base_url('/student/lessons/') ?>${lessonId}/content?mode=detailed`);
        const data = await response.json();
        
        if (data.success) {
            detailedDiv.innerHTML = renderDetailedContent(data.data.content);
            btn.style.display = 'none';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-book"></i> Teach Me More';
            alert('Failed to load detailed content');
        }
    } catch (error) {
        console.error('Error:', error);
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-book"></i> Teach Me More';
        alert('Error loading detailed content');
    }
}

function renderDetailedContent(data) {
    let html = '<div class="detailed-concepts">';
    
    // Introduction (if present)
    if (data.introduction) {
        html += `<div class="lesson-summary" style="background: #fef3c7; border-left-color: #f59e0b; margin-bottom: 24px;">
            <strong style="display: block; margin-bottom: 8px; color: #92400e;">📚 Comprehensive Overview</strong>
            ${escapeHtml(data.introduction)}
        </div>`;
    }
    
    // Technical Details / Main Concepts
    if (data.technicalDetails && data.technicalDetails.length > 0) {
        html += '<h4 style="font-size: 1rem; font-weight: 700; color: #111; margin: 24px 0 16px;">🔧 Technical Deep Dive</h4>';
        data.technicalDetails.forEach(tech => {
            html += `<div class="concept-card">
                <h5>${escapeHtml(tech.concept)}</h5>
                <p>${escapeHtml(tech.explanation)}</p>`;
            if (tech.example) {
                html += `<pre style="margin: 12px 0 0;">${escapeHtml(tech.example)}</pre>`;
            }
            if (tech.notes) {
                html += `<p style="margin: 12px 0 0; padding: 8px; background: #fffbeb; border-radius: 4px; font-size: 0.85rem;">
                    <strong>Note:</strong> ${escapeHtml(tech.notes)}
                </p>`;
            }
            html += `</div>`;
        });
    } else if (data.mainConcepts && data.mainConcepts.length > 0) {
        // Fallback for old format
        data.mainConcepts.forEach(concept => {
            html += `<div class="concept-card">
                <h5>${escapeHtml(concept.concept)}</h5>
                <p>${escapeHtml(concept.explanation)}</p>`;
            if (concept.example) {
                html += `<pre>${escapeHtml(concept.example)}</pre>`;
            }
            html += `</div>`;
        });
    }
    
    // Multiple Code Examples
    if (data.examples && data.examples.length > 0) {
        html += '<h4 style="font-size: 1rem; font-weight: 700; color: #111; margin: 24px 0 16px;">💻 Complete Examples</h4>';
        data.examples.forEach((ex, idx) => {
            html += `<div class="lesson-code" style="margin-bottom: 24px;">
                <div class="lesson-code-title">${idx + 1}. ${escapeHtml(ex.title)}</div>`;
            if (ex.description) {
                html += `<p style="margin: 8px 0; color: #666; font-size: 0.9rem;">${escapeHtml(ex.description)}</p>`;
            }
            html += `<div class="code-block" data-lang="${escapeHtml(ex.language)}">
                    <pre>${escapeHtml(ex.code)}</pre>
                </div>`;
            if (ex.output) {
                html += `<div class="code-explanation" style="margin-top: 8px; padding: 8px 12px; background: #f0fdf4; border-left: 3px solid #22c55e; border-radius: 4px;">
                    <strong style="color: #166534;">Result:</strong> ${escapeHtml(ex.output)}
                </div>`;
            }
            html += `</div>`;
        });
    } else if (data.codeExamples && data.codeExamples.length > 0) {
        // Fallback for old format
        data.codeExamples.forEach(ex => {
            html += `<div class="lesson-code">
                <div class="lesson-code-title">💻 ${escapeHtml(ex.title)}</div>
                <div class="code-block" data-lang="${escapeHtml(ex.language)}">
                    <pre>${escapeHtml(ex.code)}</pre>
                </div>
                ${ex.explanation ? `<div class="code-explanation">${escapeHtml(ex.explanation)}</div>` : ''}
            </div>`;
        });
    }
    
    // Best Practices
    if (data.bestPractices && data.bestPractices.length > 0) {
        html += `<div class="tips-section">
            <h4>✨ Best Practices</h4>`;
        
        if (typeof data.bestPractices[0] === 'object') {
            // New detailed format
            data.bestPractices.forEach(bp => {
                html += `<div style="margin-bottom: 16px; padding: 12px; background: #f0fdf4; border-left: 3px solid #22c55e; border-radius: 6px;">
                    <strong style="color: #166534; display: block; margin-bottom: 4px;">${escapeHtml(bp.practice)}</strong>
                    <p style="margin: 4px 0; font-size: 0.88rem; color: #065f46;">${escapeHtml(bp.reason)}</p>`;
                if (bp.example) {
                    html += `<pre style="margin: 8px 0 0; font-size: 0.8rem; background: #fff;">${escapeHtml(bp.example)}</pre>`;
                }
                html += `</div>`;
            });
        } else {
            // Old simple format
            html += `<ul class="tips-list">${data.bestPractices.map(t => `<li>${escapeHtml(t)}</li>`).join('')}</ul>`;
        }
        
        html += `</div>`;
    }
    
    // Common Mistakes
    if (data.commonMistakes && data.commonMistakes.length > 0) {
        html += `<div class="tips-section">
            <h4>⚠️ Common Mistakes to Avoid</h4>`;
        
        if (typeof data.commonMistakes[0] === 'object') {
            // New detailed format
            data.commonMistakes.forEach(cm => {
                html += `<div style="margin-bottom: 16px; padding: 12px; background: #fef2f2; border-left: 3px solid #ef4444; border-radius: 6px;">
                    <strong style="color: #991b1b; display: block; margin-bottom: 4px;">${escapeHtml(cm.mistake)}</strong>
                    <p style="margin: 4px 0; font-size: 0.88rem; color: #7f1d1d;"><strong>Problem:</strong> ${escapeHtml(cm.problem)}</p>
                    <p style="margin: 4px 0; font-size: 0.88rem; color: #065f46;"><strong>Solution:</strong> ${escapeHtml(cm.solution)}</p>`;
                if (cm.code) {
                    html += `<pre style="margin: 8px 0 0; font-size: 0.8rem; background: #fff;">${escapeHtml(cm.code)}</pre>`;
                }
                html += `</div>`;
            });
        } else {
            // Old simple format
            html += `<ul class="mistakes-list">${data.commonMistakes.map(m => `<li>${escapeHtml(m)}</li>`).join('')}</ul>`;
        }
        
        html += `</div>`;
    }
    
    // Related Topics / Next Steps
    if (data.relatedTopics && data.relatedTopics.length > 0) {
        html += `<div class="lesson-summary" style="margin-top: 20px; background: #ede9fe; border-left-color: #8b5cf6;">
            <strong style="display: block; margin-bottom: 8px; color: #5b21b6;">🎯 What to Learn Next</strong>
            <ul style="margin: 8px 0 0; padding-left: 20px;">
                ${data.relatedTopics.map(t => `<li style="margin: 4px 0;">${escapeHtml(t)}</li>`).join('')}
            </ul>
        </div>`;
    } else if (data.nextSteps) {
        // Fallback for old format
        html += `<div class="lesson-summary" style="margin-top: 20px;">
            <strong>🎯 Next Steps:</strong> ${escapeHtml(data.nextSteps)}
        </div>`;
    }
    
    html += '</div>';
    return html;
}

function handleToggle(event, lessonId, currentStatus) {
    event.preventDefault();
    const form = event.target;
    const btn = form.querySelector('button');
    const check = document.querySelector(`[data-lesson-id="${lessonId}"] .lesson-check`);
    
    // Optimistic UI update
    btn.disabled = true;
    const newStatus = currentStatus === 1 ? 0 : 1;
    
    if (newStatus === 1) {
        check.className = 'bi bi-check-circle-fill lesson-check';
        check.style.color = '#111';
        btn.textContent = 'Undone';
    } else {
        check.className = 'bi bi-circle lesson-check';
        check.style.color = '#ccc';
        btn.textContent = 'Done';
    }
    
    // Submit form
    fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
    })
    .then(() => {
        setTimeout(() => location.reload(), 500);
    })
    .catch(() => {
        location.reload();
    });
    
    return false;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
</script>
