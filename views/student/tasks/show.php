<?php
// Task Tutorial View - W3Schools/GeeksforGeeks Style
$intro = $tutorial['introduction'] ?? '';
$prerequisites = $tutorial['prerequisites'] ?? [];
$steps = $tutorial['steps'] ?? [];
$example = $tutorial['example'] ?? [];
$output = $tutorial['output'] ?? '';
$exercises = $tutorial['exercises'] ?? [];
$mistakes = $tutorial['mistakes'] ?? [];
$summary = $tutorial['summary'] ?? '';
?>

<style>
/* Tutorial Layout */
.tutorial-container {
    display: grid;
    grid-template-columns: 250px 1fr;
    gap: 24px;
    align-items: start;
}

/* Sidebar Navigation */
.tutorial-sidebar {
    position: sticky;
    top: 80px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

.tutorial-sidebar h4 {
    font-size: 0.8rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #6b7280;
    margin: 0 0 12px 0;
}

.tutorial-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tutorial-nav li {
    margin-bottom: 4px;
}

.tutorial-nav a {
    display: block;
    padding: 8px 12px;
    font-size: 0.85rem;
    color: #374151;
    text-decoration: none;
    border-radius: 6px;
    transition: all 0.2s;
}

.tutorial-nav a:hover {
    background: #f3f4f6;
    color: #111827;
}

.tutorial-nav a.active {
    background: #4f46e5;
    color: #fff;
    font-weight: 600;
}

/* Main Content */
.tutorial-content {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 32px;
    min-height: 70vh;
}

/* Header */
.tutorial-header {
    margin-bottom: 32px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e5e7eb;
}

.tutorial-breadcrumb {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.85rem;
    color: #6b7280;
    margin-bottom: 12px;
}

.tutorial-breadcrumb a {
    color: #4f46e5;
    text-decoration: none;
}

.tutorial-breadcrumb a:hover {
    text-decoration: underline;
}

.tutorial-header h1 {
    font-size: 1.8rem;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px 0;
}

.task-badges {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.badge {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.badge.difficulty-easy { background: #dcfce7; color: #166534; }
.badge.difficulty-medium { background: #fef3c7; color: #92400e; }
.badge.difficulty-hard { background: #fee2e2; color: #991b1b; }
.badge.time { background: #dbeafe; color: #1e40af; }
.badge.course { background: #f3e8ff; color: #6b21a8; }

/* Section Styles */
.tutorial-section {
    margin-bottom: 48px;
    scroll-margin-top: 80px;
}

.tutorial-section h2 {
    font-size: 1.4rem;
    font-weight: 700;
    color: #111827;
    margin: 0 0 16px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tutorial-section h2 .icon {
    font-size: 1.2rem;
}

.tutorial-section h3 {
    font-size: 1.1rem;
    font-weight: 700;
    color: #374151;
    margin: 24px 0 12px 0;
}

.tutorial-section p {
    font-size: 0.95rem;
    line-height: 1.7;
    color: #374151;
    margin-bottom: 16px;
}

/* Info Box */
.info-box {
    padding: 16px 20px;
    border-radius: 8px;
    margin: 16px 0;
    border-left: 4px solid;
}

.info-box.intro {
    background: #eff6ff;
    border-color: #3b82f6;
}

.info-box.success {
    background: #f0fdf4;
    border-color: #22c55e;
}

.info-box.warning {
    background: #fefce8;
    border-color: #eab308;
}

.info-box.error {
    background: #fef2f2;
    border-color: #ef4444;
}

.info-box h4 {
    font-size: 0.9rem;
    font-weight: 700;
    margin: 0 0 8px 0;
    color: inherit;
}

/* Lists */
.check-list {
    list-style: none;
    padding: 0;
    margin: 16px 0;
}

.check-list li {
    padding: 8px 0 8px 32px;
    position: relative;
    font-size: 0.9rem;
    line-height: 1.6;
    color: #374151;
}

.check-list li::before {
    content: '✓';
    position: absolute;
    left: 0;
    top: 8px;
    width: 20px;
    height: 20px;
    background: #22c55e;
    color: white;
    border-radius: 50%;
    display: grid;
    place-items: center;
    font-size: 0.75rem;
    font-weight: 700;
}

.warning-list li::before {
    content: '!';
    background: #ef4444;
}

/* Code Blocks */
.code-block {
    background: #1e293b;
    color: #e2e8f0;
    border-radius: 8px;
    padding: 20px;
    margin: 16px 0;
    overflow-x: auto;
    position: relative;
}

.code-block::before {
    content: attr(data-language);
    position: absolute;
    top: 8px;
    right: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #94a3b8;
    letter-spacing: 0.05em;
}

.code-block pre {
    margin: 0;
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.6;
}

.code-block code {
    color: inherit;
    background: none;
    padding: 0;
}

/* Inline code */
code {
    background: #f1f5f9;
    color: #c7254e;
    padding: 2px 6px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 0.88rem;
}

/* Step Cards */
.step-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    margin: 16px 0;
}

.step-card h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #4f46e5;
    margin: 0 0 12px 0;
}

.step-card p {
    margin-bottom: 12px;
}

/* Try It Button */
.try-it-section {
    background: #111;
    color: white;
    padding: 24px;
    border-radius: 12px;
    margin: 24px 0;
    text-align: center;
}

.try-it-section h3 {
    color: white;
    margin-bottom: 12px;
}

.try-it-section p {
    color: rgba(255,255,255,0.9);
    margin-bottom: 16px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 2px solid #e5e7eb;
}

.tcm-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.9rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    border: 1px solid;
}

.tcm-btn.primary {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

.tcm-btn.primary:hover {
    background: #4338ca;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
}

.tcm-btn.secondary {
    background: white;
    color: #374151;
    border-color: #d1d5db;
}

.tcm-btn.secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.tcm-btn.success {
    background: #22c55e;
    color: white;
    border-color: #22c55e;
}

.tcm-btn.success:hover {
    background: #16a34a;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .tutorial-container {
        grid-template-columns: 1fr;
    }
    
    .tutorial-sidebar {
        position: static;
        max-height: none;
    }
    
    .tutorial-content {
        padding: 20px;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>

<div class="tutorial-container">
    <!-- Sidebar Navigation -->
    <aside class="tutorial-sidebar">
        <h4>📚 Contents</h4>
        <ul class="tutorial-nav">
            <li><a href="#introduction">Introduction</a></li>
            <li><a href="#prerequisites">Prerequisites</a></li>
            <li><a href="#steps">Step-by-Step Guide</a></li>
            <li><a href="#example">Complete Example</a></li>
            <li><a href="#output">Expected Output</a></li>
            <li><a href="#exercises">Try It Yourself</a></li>
            <li><a href="#mistakes">Common Mistakes</a></li>
            <li><a href="#summary">Summary</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="tutorial-content">
        <!-- Header -->
        <div class="tutorial-header">
            <div class="tutorial-breadcrumb">
                <a href="<?= base_url('/student/tasks') ?>">My Tasks</a>
                <span>/</span>
                <span><?= e($task['title']) ?></span>
            </div>
            <h1><?= e($task['title']) ?></h1>
            <div class="task-badges">
                <span class="badge difficulty-<?= strtolower($task['difficulty']) ?>">
                    <?= e($task['difficulty']) ?>
                </span>
                <span class="badge time">
                    <i class="bi bi-clock"></i> ~<?= $task['estimated_time'] ?> min
                </span>
                <?php if ($task['course_title']): ?>
                <span class="badge course">
                    <i class="bi bi-book"></i> <?= e($task['course_title']) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Introduction -->
        <?php if ($intro): ?>
        <section id="introduction" class="tutorial-section">
            <h2><span class="icon">💡</span> Introduction</h2>
            <div class="info-box intro">
                <p style="margin: 0;"><?= nl2br(e($intro)) ?></p>
            </div>
        </section>
        <?php endif; ?>

        <!-- Prerequisites -->
        <?php if (!empty($prerequisites)): ?>
        <section id="prerequisites" class="tutorial-section">
            <h2><span class="icon">📋</span> Prerequisites</h2>
            <p>Before starting, make sure you have:</p>
            <ul class="check-list">
                <?php foreach ($prerequisites as $prereq): ?>
                <li><?= e($prereq) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- Step-by-Step Guide -->
        <?php if (!empty($steps)): ?>
        <section id="steps" class="tutorial-section">
            <h2><span class="icon">📝</span> Step-by-Step Guide</h2>
            <?php foreach ($steps as $index => $step): ?>
            <div class="step-card">
                <h3><?= e($step['title']) ?></h3>
                <p><?= nl2br(e($step['explanation'])) ?></p>
                <?php if (!empty($step['code'])): ?>
                <div class="code-block" data-language="<?= e($example['language'] ?? 'code') ?>">
                    <pre><code><?= e($step['code']) ?></code></pre>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <!-- Complete Example -->
        <?php if (!empty($example['code'])): ?>
        <section id="example" class="tutorial-section">
            <h2><span class="icon">💻</span> Complete Code Example</h2>
            <p>Here's the complete working code:</p>
            <div class="code-block" data-language="<?= e($example['language'] ?? 'code') ?>">
                <pre><code><?= e($example['code']) ?></code></pre>
            </div>
        </section>
        <?php endif; ?>

        <!-- Expected Output -->
        <?php if ($output): ?>
        <section id="output" class="tutorial-section">
            <h2><span class="icon">✨</span> Expected Output</h2>
            <div class="info-box success">
                <h4>When you run this code, you should see:</h4>
                <p style="margin: 8px 0 0;"><?= nl2br(e($output)) ?></p>
            </div>
        </section>
        <?php endif; ?>

        <!-- Try It Yourself -->
        <?php if (!empty($exercises)): ?>
        <section id="exercises" class="tutorial-section">
            <div class="try-it-section">
                <h3>🚀 Try It Yourself!</h3>
                <p>Now it's your turn! Try these exercises to practice:</p>
            </div>
            <ul class="check-list">
                <?php foreach ($exercises as $exercise): ?>
                <li><?= e($exercise) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- Common Mistakes -->
        <?php if (!empty($mistakes)): ?>
        <section id="mistakes" class="tutorial-section">
            <h2><span class="icon">⚠️</span> Common Mistakes to Avoid</h2>
            <div class="info-box warning">
                <h4>Watch out for these common errors:</h4>
            </div>
            <ul class="check-list warning-list">
                <?php foreach ($mistakes as $mistake): ?>
                <li><?= e($mistake) ?></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <!-- Summary -->
        <?php if ($summary): ?>
        <section id="summary" class="tutorial-section">
            <h2><span class="icon">🎯</span> Summary</h2>
            <div class="info-box success">
                <p style="margin: 0;"><?= nl2br(e($summary)) ?></p>
            </div>
        </section>
        <?php endif; ?>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <?php if ($task['status'] !== 'completed'): ?>
            <button onclick="markComplete()" class="tcm-btn success">
                <i class="bi bi-check-circle"></i> Mark as Completed
            </button>
            <?php endif; ?>
            <?php if ($task['course_id']): ?>
            <a href="<?= base_url('/student/learn/' . $task['course_id']) ?>" class="tcm-btn primary">
                <i class="bi bi-book"></i> Go to Course
            </a>
            <?php endif; ?>
            <a href="<?= base_url('/student/tasks') ?>" class="tcm-btn secondary">
                <i class="bi bi-arrow-left"></i> Back to Tasks
            </a>
        </div>
    </main>
</div>

<script>
// Smooth scroll for navigation
document.querySelectorAll('.tutorial-nav a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        const targetId = this.getAttribute('href').substring(1);
        const targetSection = document.getElementById(targetId);
        
        if (targetSection) {
            targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            
            // Update active state
            document.querySelectorAll('.tutorial-nav a').forEach(a => a.classList.remove('active'));
            this.classList.add('active');
        }
    });
});

// Highlight active section on scroll
const sections = document.querySelectorAll('.tutorial-section');
const navLinks = document.querySelectorAll('.tutorial-nav a');

window.addEventListener('scroll', () => {
    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop;
        if (scrollY >= sectionTop - 100) {
            current = section.getAttribute('id');
        }
    });
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === '#' + current) {
            link.classList.add('active');
        }
    });
});

// Mark task as complete
function markComplete() {
    if (!confirm('Mark this task as completed?')) return;
    
    fetch('<?= base_url('/student/tasks/' . $task['id']) ?>/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: 'completed' })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast('✅ Great job! Task completed!', 'success');
            setTimeout(() => {
                window.location.href = '<?= base_url('/student/tasks') ?>';
            }, 1500);
        }
    })
    .catch(() => {
        showToast('Failed to update task. Please try again.', 'error');
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white; padding: 12px 20px; border-radius: 8px;
        font-weight: 600; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        animation: slideIn 0.3s ease-out;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => toast.remove(), 300);
    }, 2000);
}

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slideOut {
        from { transform: translateX(0); opacity: 1; }
        to { transform: translateX(400px); opacity: 0; }
    }
`;
document.head.appendChild(style);
</script>
