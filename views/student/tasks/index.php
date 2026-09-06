<?php
// Daily Tasks View
$total = $stats['total'];
$completed = $stats['completed'];
$pending = $stats['pending'];
$completionRate = $total > 0 ? round(($completed / $total) * 100) : 0;
?>
<style>
.tasks-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px; }
.tasks-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px; margin-bottom: 20px; }
.task-stat-card {
    background: #fff; border: 1px solid #ececec; border-radius: 12px; padding: 14px 16px;
    display: flex; flex-direction: column; gap: 4px;
}
.task-stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: #aaa; }
.task-stat-value { font-size: 1.5rem; font-weight: 800; color: #111; }
.task-list { display: flex; flex-direction: column; gap: 12px; }
.task-card {
    background: #fff; border: 1px solid #ececec; border-radius: 14px; padding: 16px 18px;
    display: flex; gap: 14px; align-items: start; transition: all 0.2s;
}
.task-card:hover { border-color: #bbb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
.task-card.completed { opacity: 0.6; background: #f9f9f9; }
.task-checkbox {
    width: 24px; height: 24px; border: 2px solid #ddd; border-radius: 50%;
    display: grid; place-items: center; cursor: pointer; transition: all 0.2s;
    flex-shrink: 0; margin-top: 2px;
}
.task-checkbox:hover { border-color: #111; }
.task-checkbox.checked { background: #22c55e; border-color: #22c55e; color: #fff; }
.task-content { flex: 1; min-width: 0; }
.task-header { display: flex; align-items: start; gap: 10px; margin-bottom: 6px; }
.task-title { font-size: 0.95rem; font-weight: 700; color: #111; flex: 1; }
.task-difficulty {
    padding: 3px 8px; border-radius: 12px; font-size: 0.65rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: 0.05em;
}
.task-difficulty.easy { background: #dcfce7; color: #166534; }
.task-difficulty.medium { background: #fef3c7; color: #92400e; }
.task-difficulty.hard { background: #fee2e2; color: #991b1b; }
.task-description { font-size: 0.88rem; color: #555; line-height: 1.6; margin-bottom: 8px; white-space: pre-wrap; }
.task-meta {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    font-size: 0.72rem; color: #888;
}
.task-course { 
    display: inline-flex; align-items: center; gap: 4px;
    background: #f5f5f5; padding: 4px 10px; border-radius: 12px;
    font-weight: 600; color: #555;
}
.task-time { display: flex; align-items: center; gap: 4px; }
.task-motivation {
    margin-top: 8px; padding: 8px 12px; background: #fef3c7; border-left: 3px solid #fbbf24;
    border-radius: 6px; font-size: 0.78rem; color: #92400e; font-weight: 600;
}
.empty-state {
    padding: 60px 20px; text-align: center; color: #aaa;
    background: #fff; border: 1px solid #ececec; border-radius: 14px;
}
.empty-state i { font-size: 3rem; opacity: 0.3; margin-bottom: 16px; }
</style>

<div class="tasks-header">
    <div>
        <h2 style="font-size: 1.25rem; margin: 0;">📋 My Daily Tasks</h2>
        <p style="margin: 4px 0 0; font-size: 0.82rem; color: #888;">
            AI-generated personalized learning tasks for this week
        </p>
    </div>
    <?php if ($total > 0): ?>
    <a href="<?= base_url('/student/tasks/generate') ?>" class="tcm-btn">
        <i class="bi bi-arrow-clockwise"></i> Regenerate Tasks
    </a>
    <?php endif; ?>
</div>

<!-- Stats -->
<?php if ($total > 0): ?>
<div class="tasks-stats">
    <div class="task-stat-card" style="border-color: #d1fae5;">
        <div class="task-stat-label">Total Tasks</div>
        <div class="task-stat-value"><?= $total ?></div>
    </div>
    <div class="task-stat-card" style="border-color: #bbf7d0;">
        <div class="task-stat-label">Completed</div>
        <div class="task-stat-value" style="color: #16a34a;"><?= $completed ?></div>
    </div>
    <div class="task-stat-card" style="border-color: #fed7aa;">
        <div class="task-stat-label">Pending</div>
        <div class="task-stat-value" style="color: #ea580c;"><?= $pending ?></div>
    </div>
    <div class="task-stat-card" style="border-color: #e0e7ff;">
        <div class="task-stat-label">Progress</div>
        <div class="task-stat-value" style="color: #4f46e5;"><?= $completionRate ?>%</div>
    </div>
</div>
<?php endif; ?>

<!-- Task List -->
<div class="task-list">
    <?php if (empty($tasks)): ?>
    <div class="empty-state">
        <i class="bi bi-calendar-check"></i>
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #111; margin-bottom: 8px;">
            No Tasks Yet
        </h3>
        <p style="font-size: 0.88rem; margin-bottom: 20px;">
            Your personalized tasks will be generated every Friday at noon.<br>
            Or you can generate them manually now!
        </p>
        <a href="<?= base_url('/student/tasks/generate') ?>" class="tcm-btn primary">
            <i class="bi bi-stars"></i> Generate My Tasks
        </a>
    </div>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
        <div class="task-card <?= $task['status'] === 'completed' ? 'completed' : '' ?>" data-task-id="<?= $task['id'] ?>">
            <div class="task-checkbox <?= $task['status'] === 'completed' ? 'checked' : '' ?>" 
                 onclick="toggleTask(<?= $task['id'] ?>, this)">
                <?php if ($task['status'] === 'completed'): ?>
                <i class="bi bi-check-lg"></i>
                <?php endif; ?>
            </div>
            <div class="task-content">
                <div class="task-header">
                    <div class="task-title"><?= e($task['title']) ?></div>
                    <span class="task-difficulty <?= strtolower($task['difficulty']) ?>">
                        <?= e($task['difficulty']) ?>
                    </span>
                </div>
                <div class="task-description"><?= nl2br(e($task['description'])) ?></div>
                
                <!-- Action Buttons -->
                <?php if ($task['status'] !== 'completed'): ?>
                <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
                    <a href="<?= base_url('/student/tasks/' . $task['id']) ?>" 
                       class="tcm-btn" style="font-size: 0.75rem; padding: 6px 12px;">
                        <i class="bi bi-play-circle"></i> Start Learning
                    </a>
                    <button onclick="markInProgress(<?= $task['id'] ?>)" 
                            class="tcm-btn" style="font-size: 0.75rem; padding: 6px 12px; background: #fbbf24; border-color: #fbbf24;">
                        <i class="bi bi-hourglass-split"></i> Working on it
                    </button>
                    <button onclick="skipTask(<?= $task['id'] ?>)" 
                            class="tcm-btn" style="font-size: 0.75rem; padding: 6px 12px; background: #94a3b8; border-color: #94a3b8;">
                        <i class="bi bi-skip-forward"></i> Skip
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="task-meta">
                    <?php if ($task['course_title']): ?>
                    <span class="task-course">
                        <i class="bi bi-book"></i> <?= e($task['course_title']) ?>
                    </span>
                    <?php endif; ?>
                    <span class="task-time">
                        <i class="bi bi-clock"></i> ~<?= $task['estimated_time'] ?> min
                    </span>
                    <?php if ($task['status'] === 'in_progress'): ?>
                    <span style="color: #fbbf24; font-weight: 600;">
                        <i class="bi bi-hourglass-split"></i> In Progress
                    </span>
                    <?php elseif ($task['completed_at']): ?>
                    <span style="color: #16a34a;">
                        <i class="bi bi-check-circle-fill"></i> Completed
                    </span>
                    <?php elseif ($task['status'] === 'skipped'): ?>
                    <span style="color: #94a3b8;">
                        <i class="bi bi-skip-forward-fill"></i> Skipped
                    </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($task['motivation']) && $task['status'] !== 'completed'): ?>
                <div class="task-motivation">
                    💡 <?= e($task['motivation']) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function toggleTask(taskId, checkbox) {
    const isCompleted = checkbox.classList.contains('checked');
    const newStatus = isCompleted ? 'pending' : 'completed';
    
    updateTaskStatus(taskId, newStatus, checkbox);
}

function markInProgress(taskId) {
    if (confirm('Mark this task as "Working on it"?')) {
        updateTaskStatus(taskId, 'in_progress');
    }
}

function skipTask(taskId) {
    if (confirm('Skip this task? You can come back to it later.')) {
        updateTaskStatus(taskId, 'skipped');
    }
}

function updateTaskStatus(taskId, status, checkbox = null) {
    // Optimistic UI update for checkbox
    if (checkbox) {
        const card = checkbox.closest('.task-card');
        if (status === 'completed') {
            checkbox.classList.add('checked');
            checkbox.innerHTML = '<i class="bi bi-check-lg"></i>';
            card.classList.add('completed');
        } else {
            checkbox.classList.remove('checked');
            checkbox.innerHTML = '';
            card.classList.remove('completed');
        }
    }
    
    // Send to server
    fetch('<?= base_url('/student/tasks/') ?>' + taskId + '/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ status: status })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Show success message
            if (status === 'completed') {
                showToast('✅ Great job! Task completed!', 'success');
            } else if (status === 'in_progress') {
                showToast('⏳ Keep going! You got this!', 'info');
            } else if (status === 'skipped') {
                showToast('⏭️ Task skipped. No worries!', 'info');
            }
            // Reload to update stats
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(() => {
        // Revert on error
        location.reload();
    });
}

function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        background: ${type === 'success' ? '#22c55e' : '#3b82f6'};
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
