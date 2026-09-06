<div class="tcm-page-head">
    <div><h2>Students</h2><p>All registered learners on the platform.</p></div>
</div>

<div class="tcm-card">
    <form method="get" style="margin-bottom:16px;max-width:400px;">
        <input class="tcm-input" name="q" placeholder="Search by name, email or Student ID..."
               value="<?= e($_GET['q'] ?? '') ?>">
    </form>
    <div style="overflow-x:auto;">
    <table class="tcm-table">
        <thead>
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Referral ID</th>
                <th>Status</th>
                <th>Joined</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($students as $s): ?>
            <tr>
                <td style="font-family:monospace;font-size:.78rem;color:#555;">
                    <?= e($s['student_id'] ?? '—') ?>
                </td>
                <td style="font-weight:600;"><?= e($s['name']) ?></td>
                <td class="muted" style="font-size:.82rem;"><?= e($s['email']) ?></td>
                <td class="muted" style="font-size:.82rem;"><?= e($s['phone'] ?? '—') ?></td>
                <td style="font-family:monospace;font-size:.75rem;color:#888;">
                    <?= e($s['referral_id'] ?? '—') ?>
                </td>
                <td><span class="tcm-badge <?= $s['status'] === 'active' ? 'green' : 'red' ?>"><?= e($s['status']) ?></span></td>
                <td class="muted" style="font-size:.8rem;"><?= e(date('d M Y', strtotime($s['created_at']))) ?></td>
                <td><a class="tcm-btn sm" href="<?= base_url('/admin/students/' . $s['id']) ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if ($students === []): ?><tr><td colspan="8" class="muted">No students found.</td></tr><?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
