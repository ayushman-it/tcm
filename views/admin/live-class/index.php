<?php
// Encode enrolled data for JS
$enrolledByCourseJson  = json_encode($enrolledByCourse  ?? [], JSON_HEX_TAG);
$enrolledByProgramJson = json_encode($enrolledByProgram ?? [], JSON_HEX_TAG);
$allStudentsJson       = json_encode($allStudents       ?? [], JSON_HEX_TAG);
?>
<style>
/* ── Student chip / pill ── */
.lc-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:#111; color:#fff; border-radius:20px;
    padding:4px 10px 4px 8px; font-size:.75rem; font-weight:600;
}
.lc-chip button { background:none; border:none; color:rgba(255,255,255,.65); cursor:pointer; padding:0; line-height:1; font-size:.75rem; }
.lc-chip button:hover { color:#fff; }
/* Student row in dropdown */
.lc-stu-row {
    display:flex; align-items:center; gap:10px;
    padding:8px 12px; cursor:pointer; transition:background .12s;
    border-bottom:1px solid #f5f5f5;
}
.lc-stu-row:last-child { border-bottom:none; }
.lc-stu-row:hover { background:#f5f5f5; }
.lc-stu-row.checked { background:#f0f4ff; }
.lc-stu-av {
    width:30px; height:30px; border-radius:50%; background:#e5e5e5;
    overflow:hidden; display:grid; place-items:center;
    font-size:.7rem; font-weight:800; flex-shrink:0;
}
.lc-enrolled-list {
    max-height:220px; overflow-y:auto;
    border:1px solid #ececec; border-radius:10px;
    background:#fafafa; margin-top:6px;
}
.lc-enrolled-header {
    font-size:.7rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.07em; color:#888;
    padding:8px 12px 6px; border-bottom:1px solid #ececec;
    background:#fff; position:sticky; top:0;
    display:flex; align-items:center; justify-content:space-between;
}
.lc-search-wrap { position:relative; }
.lc-search-wrap i { position:absolute; right:11px; top:50%; transform:translateY(-50%); color:#bbb; font-size:.8rem; pointer-events:none; }
.lc-dropdown {
    display:none; position:relative; z-index:100;
    background:#fff; border:1px solid #e5e5e5;
    border-radius:10px; margin-top:4px;
    box-shadow:0 6px 24px rgba(0,0,0,.1);
    max-height:200px; overflow-y:auto;
}
</style>

<div class="tcm-page-head">
    <div>
        <h2><i class="bi bi-broadcast" style="color:#6366f1;margin-right:8px;"></i>Live Class Links</h2>
        <p>Send meeting links to enrolled students with instant notifications.</p>
    </div>
</div>

<div class="tcm-grid-2" style="align-items:start;">

<!-- ── Send Form ── -->
<div class="tcm-card">
    <h3 style="margin-bottom:16px;"><i class="bi bi-send-fill" style="color:var(--muted);margin-right:8px;"></i>Send Live Class Link</h3>
    <form method="post" action="<?= base_url('/admin/live-class/send') ?>" id="lcForm">
        <?= csrf_field() ?>

        <!-- Title -->
        <div class="tcm-field">
            <label>Class Title *</label>
            <input class="tcm-input" name="title" required placeholder="e.g. React Hooks — Session 5" maxlength="200">
        </div>

        <!-- Meeting URL -->
        <div class="tcm-field">
            <label>Meeting URL * <span style="font-size:.72rem;color:#aaa;">(Google Meet, Zoom, Teams etc.)</span></label>
            <input class="tcm-input" name="meeting_url" required type="url" placeholder="https://meet.google.com/...">
        </div>

        <!-- Description -->
        <div class="tcm-field">
            <label>Description <span style="font-size:.72rem;color:#aaa;">(optional)</span></label>
            <input class="tcm-input" name="description" placeholder="What will be covered today?" maxlength="500">
        </div>

        <!-- Scheduled time -->
        <div class="tcm-field">
            <label>Scheduled Time <span style="font-size:.72rem;color:#aaa;">(optional)</span></label>
            <input class="tcm-input" name="scheduled_at" type="datetime-local">
        </div>

        <!-- Target selector -->
        <div class="tcm-field">
            <label>Send To</label>
            <select class="tcm-select" name="target" id="lcTarget" onchange="lcToggleTarget(this.value)">
                <option value="all">🌐 All Students</option>
                <option value="course">📚 Course — Enrolled Students</option>
                <option value="program">📦 Program — Enrolled Students</option>
                <option value="specific">👤 Specific Students</option>
            </select>
        </div>

        <!-- Course selector + enrolled list -->
        <div id="lcCourseWrap" style="display:none;">
            <div class="tcm-field" style="margin-bottom:8px;">
                <label>Select Course</label>
                <select class="tcm-select" name="target_id" id="lcCourseSelect" onchange="lcShowEnrolled('course', this.value)">
                    <option value="">— Select a course —</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= e($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="lcCourseEnrolled" style="display:none;">
                <div class="lc-enrolled-list" id="lcCourseList"></div>
            </div>
        </div>

        <!-- Program selector + enrolled list -->
        <div id="lcProgramWrap" style="display:none;">
            <div class="tcm-field" style="margin-bottom:8px;">
                <label>Select Program</label>
                <select class="tcm-select" name="target_id" id="lcProgramSelect" onchange="lcShowEnrolled('program', this.value)">
                    <option value="">— Select a program —</option>
                    <?php foreach ($programs as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= e($p['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div id="lcProgramEnrolled" style="display:none;">
                <div class="lc-enrolled-list" id="lcProgramList"></div>
            </div>
        </div>

        <!-- Specific student picker -->
        <div id="lcSpecificWrap" style="display:none;">
            <div class="tcm-field" style="margin-bottom:6px;">
                <label>Search &amp; Select Students</label>
                <div class="lc-search-wrap">
                    <input class="tcm-input" id="lcStudentSearch" autocomplete="off"
                           placeholder="Type name to search..." style="padding-right:32px;">
                    <i class="bi bi-search"></i>
                </div>
                <div class="lc-dropdown" id="lcStudentDropdown"></div>
            </div>
            <!-- Selected chips -->
            <div id="lcSelectedChips" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:10px;min-height:0;"></div>
            <input type="hidden" name="specific_student_ids" id="lcSpecificIds">
        </div>

        <!-- Submit -->
        <button type="submit" class="tcm-btn primary" style="margin-top:8px;">
            <i class="bi bi-send-fill"></i> Send to Students
        </button>
    </form>
</div>

<!-- ── Sent Links History ── -->
<div class="tcm-card">
    <h3 style="margin-bottom:16px;"><i class="bi bi-clock-history" style="color:var(--muted);margin-right:8px;"></i>Sent Links</h3>
    <?php if (empty($links)): ?>
        <div class="tcm-empty"><i class="bi bi-broadcast"></i><br>No links sent yet.</div>
    <?php else: ?>
        <?php foreach ($links as $lnk): ?>
        <div class="tcm-row">
            <div class="tcm-row-main">
                <div class="tcm-row-title">
                    <i class="bi bi-broadcast" style="color:#6366f1;margin-right:5px;"></i>
                    <?= e($lnk['title']) ?>
                </div>
                <div class="tcm-row-sub">
                    <a href="<?= e($lnk['meeting_url']) ?>" target="_blank" rel="noopener"
                       style="color:#6366f1;font-size:.75rem;word-break:break-all;">
                        <?= e(mb_substr($lnk['meeting_url'], 0, 55)) ?>
                    </a>
                </div>
                <div class="tcm-row-sub" style="margin-top:3px;">
                    <span class="tcm-badge gray" style="font-size:.65rem;"><?= e(ucfirst($lnk['target'])) ?></span>
                    <?php if (!empty($lnk['recipient_count'])): ?>
                        &nbsp;<span class="tcm-badge green" style="font-size:.65rem;"><?= (int)$lnk['recipient_count'] ?> sent</span>
                    <?php endif; ?>
                    <?php if ($lnk['scheduled_at']): ?>
                        &nbsp;<i class="bi bi-clock" style="font-size:.65rem;"></i>
                        <?= e(date('d M, h:i A', strtotime($lnk['scheduled_at']))) ?>
                    <?php endif; ?>
                    &nbsp;·&nbsp; by <?= e($lnk['sent_by_name']) ?>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                <span style="font-size:.68rem;color:#aaa;"><?= e(date('d M', strtotime($lnk['created_at']))) ?></span>
                <form method="post" action="<?= base_url('/admin/live-class/'.$lnk['id'].'/delete') ?>"
                      onsubmit="return confirm('Remove this link?');">
                    <?= csrf_field() ?>
                    <button class="tcm-btn sm danger" style="padding:3px 8px;"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</div><!-- /.tcm-grid-2 -->

<script>
(function () {
    var BASE            = <?= json_encode(base_url('')) ?>;
    var enrolledCourse  = <?= $enrolledByCourseJson ?>;
    var enrolledProgram = <?= $enrolledByProgramJson ?>;
    var allStudents     = <?= $allStudentsJson ?>;

    var selectedMap = {};   // for specific picker
    var searchTimer = null;

    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function avatarHtml(av, name, size) {
        size = size || 30;
        var s = 'width:'+size+'px;height:'+size+'px;border-radius:50%;overflow:hidden;background:#e5e5e5;display:grid;place-items:center;font-size:'+Math.round(size*.28)+'px;font-weight:800;flex-shrink:0;';
        if (av) return '<div style="'+s+'"><img src="'+BASE+'/uploads/'+esc(av)+'" style="width:100%;height:100%;object-fit:cover;"></div>';
        return '<div style="'+s+'">'+esc((name||'?').charAt(0).toUpperCase())+'</div>';
    }

    /* ── Toggle target section ── */
    window.lcToggleTarget = function(val) {
        document.getElementById('lcCourseWrap').style.display   = val === 'course'   ? '' : 'none';
        document.getElementById('lcProgramWrap').style.display  = val === 'program'  ? '' : 'none';
        document.getElementById('lcSpecificWrap').style.display = val === 'specific' ? '' : 'none';
    };

    /* ── Show enrolled students for a course or program ── */
    window.lcShowEnrolled = function(type, id) {
        var listEl   = document.getElementById(type === 'course' ? 'lcCourseList'    : 'lcProgramList');
        var wrapEl   = document.getElementById(type === 'course' ? 'lcCourseEnrolled': 'lcProgramEnrolled');
        var students = (type === 'course' ? enrolledCourse : enrolledProgram)[id] || [];

        if (!id) { wrapEl.style.display = 'none'; return; }

        wrapEl.style.display = '';
        listEl.innerHTML = '<div class="lc-enrolled-header">'
            + '<span><i class="bi bi-people-fill" style="margin-right:5px;"></i>'
            + students.length + ' enrolled student' + (students.length !== 1 ? 's' : '') + '</span>'
            + '</div>';

        if (!students.length) {
            listEl.innerHTML += '<div style="padding:14px;text-align:center;color:#bbb;font-size:.8rem;">No enrolled students yet.</div>';
            return;
        }

        students.forEach(function(s) {
            var row = document.createElement('div');
            row.className = 'lc-stu-row';
            row.style.cursor = 'default';
            row.innerHTML = avatarHtml(s.avatar, s.name, 28)
                + '<span style="font-size:.83rem;font-weight:600;color:#111;">' + esc(s.name) + '</span>';
            listEl.appendChild(row);
        });
    };

    /* ── Specific student picker ── */
    var searchEl   = document.getElementById('lcStudentSearch');
    var dropEl     = document.getElementById('lcStudentDropdown');
    var chipsEl    = document.getElementById('lcSelectedChips');
    var hiddenEl   = document.getElementById('lcSpecificIds');

    function renderChips() {
        chipsEl.innerHTML = '';
        Object.values(selectedMap).forEach(function(s) {
            var chip = document.createElement('div');
            chip.className = 'lc-chip';
            chip.innerHTML = avatarHtml(s.avatar, s.name, 18)
                + '<span>' + esc(s.name) + '</span>'
                + '<button type="button" data-id="'+s.id+'" title="Remove">&#10005;</button>';
            chip.querySelector('button').addEventListener('click', function() {
                delete selectedMap[this.dataset.id];
                renderChips(); syncHidden(); updateDropChecks();
            });
            chipsEl.appendChild(chip);
        });
        hiddenEl.value = Object.keys(selectedMap).join(',');
    }

    function syncHidden() { hiddenEl.value = Object.keys(selectedMap).join(','); }

    function updateDropChecks() {
        dropEl.querySelectorAll('.lc-stu-row').forEach(function(row) {
            var cb = row.querySelector('input[type=checkbox]');
            if (cb) { cb.checked = !!selectedMap[row.dataset.id]; row.classList.toggle('checked', !!selectedMap[row.dataset.id]); }
        });
    }

    function renderDrop(students) {
        dropEl.innerHTML = '';
        if (!students.length) {
            dropEl.innerHTML = '<div style="padding:12px;color:#bbb;font-size:.8rem;text-align:center;">No students found</div>';
            dropEl.style.display = 'block'; return;
        }
        students.forEach(function(s) {
            var row = document.createElement('div');
            row.className = 'lc-stu-row' + (selectedMap[s.id] ? ' checked' : '');
            row.dataset.id = s.id;
            row.innerHTML = avatarHtml(s.avatar, s.name, 28)
                + '<div style="flex:1;min-width:0;">'
                + '<div style="font-size:.83rem;font-weight:600;color:#111;">' + esc(s.name) + '</div>'
                + (s.headline ? '<div style="font-size:.71rem;color:#aaa;">' + esc(s.headline) + '</div>' : '')
                + '</div>'
                + '<input type="checkbox"' + (selectedMap[s.id] ? ' checked' : '') + ' style="width:15px;height:15px;accent-color:#111;flex-shrink:0;">';

            function toggle(ev) {
                if (ev && ev.target && ev.target.type === 'checkbox') return;
                if (selectedMap[s.id]) { delete selectedMap[s.id]; }
                else { selectedMap[s.id] = s; }
                var cb = row.querySelector('input'); cb.checked = !!selectedMap[s.id];
                row.classList.toggle('checked', !!selectedMap[s.id]);
                renderChips(); syncHidden();
            }
            row.addEventListener('click', toggle);
            row.querySelector('input').addEventListener('change', function() {
                if (selectedMap[s.id]) { delete selectedMap[s.id]; } else { selectedMap[s.id] = s; }
                row.classList.toggle('checked', !!selectedMap[s.id]); renderChips(); syncHidden();
            });
            dropEl.appendChild(row);
        });
        dropEl.style.display = 'block';
    }

    searchEl.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim().toLowerCase();
        if (!q) { dropEl.style.display = 'none'; return; }
        searchTimer = setTimeout(function() {
            var results = allStudents.filter(function(s) {
                return s.name.toLowerCase().indexOf(q) !== -1 || (s.headline||'').toLowerCase().indexOf(q) !== -1;
            }).slice(0, 15);
            renderDrop(results);
        }, 200);
    });

    searchEl.addEventListener('focus', function() {
        if (this.value.trim() && dropEl.innerHTML) dropEl.style.display = 'block';
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('#lcStudentSearch') && !e.target.closest('#lcStudentDropdown')) {
            dropEl.style.display = 'none';
        }
    });

    /* Sync hidden on submit */
    document.getElementById('lcForm').addEventListener('submit', function() { syncHidden(); });

}());
</script>
