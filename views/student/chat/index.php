<?php
$BASE = base_url('');
$meId   = (int) $me['id'];
$meName = $me['name'];
?>
<style>
/* ── Chat Layout ─────────────────────────── */
.cht-wrap { display:flex; gap:14px; height:calc(100vh - 130px); min-height:520px; }
.cht-sidebar {
    width:300px; flex-shrink:0;
    display:flex; flex-direction:column; gap:10px;
    overflow-y:auto;
}
.cht-main {
    flex:1; min-width:0;
    display:flex; flex-direction:column;
    background:#fff; border:1px solid #ececec;
    border-radius:16px; overflow:hidden;
}
/* Sidebar sections */
.cht-section { background:#fff; border:1px solid #ececec; border-radius:14px; overflow:hidden; }
.cht-section-head {
    padding:12px 14px 10px;
    font-size:.72rem; font-weight:800;
    text-transform:uppercase; letter-spacing:.08em; color:#aaa;
    border-bottom:1px solid #f5f5f5;
    display:flex; align-items:center; justify-content:space-between;
}
.cht-group-item {
    display:flex; align-items:center; gap:10px;
    padding:10px 14px; cursor:pointer;
    border-bottom:1px solid #f9f9f9;
    transition:background .12s;
}
.cht-group-item:hover, .cht-group-item.active { background:#f5f5f5; }
.cht-group-item:last-child { border-bottom:none; }
.cht-group-icon {
    width:38px; height:38px; border-radius:10px;
    background:#111; color:#fff;
    display:grid; place-items:center;
    font-size:.85rem; flex-shrink:0;
}
.cht-group-icon.help { background:#6366f1; }
.cht-group-name { font-size:.84rem; font-weight:700; color:#111; }
.cht-group-preview { font-size:.72rem; color:#aaa; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:160px; }
/* Request items */
.cht-req-item { padding:12px 14px; border-bottom:1px solid #f9f9f9; }
.cht-req-item:last-child { border-bottom:none; }
.cht-req-name { font-size:.83rem; font-weight:700; color:#111; margin-bottom:3px; }
.cht-req-msg { font-size:.75rem; color:#777; margin-bottom:8px; line-height:1.4; }
.cht-req-actions { display:flex; gap:6px; }
/* Chat header */
.cht-header {
    padding:14px 18px; border-bottom:1px solid #f0f0f0;
    display:flex; align-items:center; gap:12px;
    background:#fff;
}
.cht-header-icon {
    width:40px; height:40px; border-radius:11px;
    background:#111; color:#fff;
    display:grid; place-items:center; font-size:.95rem; flex-shrink:0;
}
.cht-header-name { font-size:.92rem; font-weight:800; color:#111; }
.cht-header-sub  { font-size:.72rem; color:#aaa; }
/* Messages area */
.cht-messages {
    flex:1; overflow-y:auto; padding:16px 18px;
    display:flex; flex-direction:column; gap:10px;
    background:#fafafa; scroll-behavior:smooth;
}
.cht-msg { display:flex; gap:8px; align-items:flex-end; max-width:75%; }
.cht-msg.mine { align-self:flex-end; flex-direction:row-reverse; }
.cht-msg-avatar {
    width:28px; height:28px; border-radius:50%; flex-shrink:0;
    background:#e5e5e5; overflow:hidden;
    display:grid; place-items:center; font-size:.7rem; font-weight:700;
}
.cht-bubble {
    padding:9px 13px; border-radius:14px;
    background:#fff; border:1px solid #e5e5e5;
    font-size:.84rem; color:#111; line-height:1.55; max-width:100%; word-break:break-word;
}
.cht-msg.mine .cht-bubble { background:#111; color:#fff; border-color:#111; border-radius:14px 14px 2px 14px; }
.cht-msg:not(.mine) .cht-bubble { border-radius:14px 14px 14px 2px; }
.cht-msg-sender { font-size:.65rem; color:#aaa; margin-bottom:3px; font-weight:600; }
.cht-msg-time { font-size:.62rem; color:#bbb; margin-top:4px; text-align:right; }
/* File messages */
.cht-file-msg {
    display:flex; align-items:center; gap:8px;
    padding:8px 12px; background:#f5f5f5;
    border-radius:10px; text-decoration:none;
    color:#111; font-size:.8rem; font-weight:600; border:1px solid #ececec;
}
.cht-file-msg:hover { background:#eee; }
.cht-img-msg { max-width:220px; border-radius:10px; cursor:pointer; display:block; }
/* Input bar */
.cht-input-bar {
    padding:12px 16px; border-top:1px solid #f0f0f0;
    display:flex; align-items:center; gap:8px; background:#fff;
}
.cht-input {
    flex:1; border:1px solid #e5e5e5; border-radius:10px;
    padding:9px 14px; font-size:.86rem; outline:none;
    font-family:inherit; resize:none; line-height:1.4;
    max-height:120px; overflow-y:auto; background:#f9f9f9;
    transition:border-color .15s;
}
.cht-input:focus { border-color:#aaa; background:#fff; }
.cht-send-btn {
    width:38px; height:38px; border-radius:10px;
    background:#111; color:#fff; border:none;
    display:grid; place-items:center; font-size:.9rem;
    cursor:pointer; flex-shrink:0; transition:background .15s;
}
.cht-send-btn:hover { background:#333; }
.cht-attach-btn {
    width:34px; height:34px; border-radius:8px;
    background:#f5f5f5; color:#888; border:1px solid #e5e5e5;
    display:grid; place-items:center; font-size:.85rem;
    cursor:pointer; flex-shrink:0; transition:background .15s;
}
.cht-attach-btn:hover { background:#ececec; color:#111; }
/* Empty states */
.cht-empty-main {
    flex:1; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    color:#bbb; gap:12px;
}
.cht-empty-main i { font-size:2.5rem; }
/* New group modal */
.cht-modal-backdrop {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.35); z-index:9000;
    align-items:center; justify-content:center;
}
.cht-modal-backdrop.open { display:flex; }
.cht-modal {
    background:#fff; border-radius:18px;
    padding:24px; width:100%; max-width:440px;
    margin:16px; box-shadow:0 20px 60px rgba(0,0,0,.15);
}
.cht-modal h3 { font-size:1rem; font-weight:800; color:#111; margin-bottom:16px; }
@media(max-width:700px){
    .cht-wrap { flex-direction:column; height:auto; }
    .cht-sidebar { width:100%; max-height:260px; overflow-y:auto; flex-direction:row; flex-wrap:wrap; }
    .cht-section { flex:1; min-width:200px; }
    .cht-main { min-height:420px; }
}
</style>

<div class="tcm-page-head" style="margin-bottom:14px;">
    <div>
        <h2><i class="bi bi-chat-dots-fill" style="color:#888;margin-right:8px;"></i>Chat &amp; Help</h2>
        <p>Help requests, group chats and peer connections.</p>
    </div>
    <button class="tcm-btn primary" onclick="document.getElementById('newGroupModal').classList.add('open')">
        <i class="bi bi-plus-lg"></i> New Group
    </button>
</div>

<!-- Incoming requests banner -->
<?php if (!empty($incoming)): ?>
<div style="background:#fef9c3;border:1.5px solid #fde047;border-radius:14px;padding:14px 18px;margin-bottom:14px;">
    <div style="font-size:.85rem;font-weight:700;color:#854d0e;margin-bottom:10px;">
        <i class="bi bi-hand-index-fill"></i> <?= count($incoming) ?> Help Request<?= count($incoming)>1?'s':'' ?> Waiting
    </div>
    <?php foreach ($incoming as $req): ?>
    <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #fde68a;flex-wrap:wrap;">
        <div style="width:36px;height:36px;border-radius:50%;overflow:hidden;flex-shrink:0;background:#f0f0f0;display:grid;place-items:center;">
            <?= tcm_avatar($req['from_avatar']??null,$req['from_name']) ?>
        </div>
        <div style="flex:1;min-width:0;">
            <div style="font-size:.83rem;font-weight:700;color:#111;"><?= e($req['from_name']) ?></div>
            <div style="font-size:.75rem;color:#777;"><?= e(mb_substr($req['message']??'',0,80)) ?></div>
        </div>
        <div style="display:flex;gap:6px;flex-shrink:0;">
            <form method="post" action="<?= base_url('/student/help/requests/'.$req['id'].'/accept') ?>">
                <?= csrf_field() ?>
                <button class="tcm-btn primary sm"><i class="bi bi-check2"></i> Accept</button>
            </form>
            <form method="post" action="<?= base_url('/student/help/requests/'.$req['id'].'/decline') ?>">
                <?= csrf_field() ?>
                <button class="tcm-btn sm danger"><i class="bi bi-x"></i> Decline</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="cht-wrap">

<!-- ── Sidebar ── -->
<div class="cht-sidebar">
    <!-- My Groups -->
    <div class="cht-section">
        <div class="cht-section-head">
            <span><i class="bi bi-chat-square-dots" style="margin-right:5px;"></i>My Groups</span>
            <span style="font-size:.7rem;background:#f0f0f0;padding:2px 7px;border-radius:20px;color:#888;"><?= count($groups) ?></span>
        </div>
        <?php if (empty($groups)): ?>
            <div style="padding:20px;text-align:center;color:#bbb;font-size:.8rem;">
                <i class="bi bi-chat" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                No chats yet.<br>Accept a help request or create a group.
            </div>
        <?php else: ?>
            <?php foreach ($groups as $g): ?>
            <div class="cht-group-item" data-gid="<?= (int)$g['id'] ?>" onclick="openGroup(<?= (int)$g['id'] ?>, <?= json_encode(e($g['name'])) ?>, '<?= $g['type'] ?>')">
                <div class="cht-group-icon <?= $g['type']==='help'?'help':'' ?>">
                    <i class="bi <?= $g['type']==='help'?'bi-hand-index-fill':'bi-people-fill' ?>"></i>
                </div>
                <div style="flex:1;min-width:0;">
                    <div class="cht-group-name"><?= e($g['name']) ?></div>
                    <div class="cht-group-preview">
                        <?= $g['last_message'] ? e(mb_substr($g['last_message'],0,40)) : $g['member_count'].' members' ?>
                    </div>
                </div>
                <?php if ($g['last_at']): ?>
                <div style="font-size:.62rem;color:#bbb;flex-shrink:0;"><?= date('d M',strtotime($g['last_at'])) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Pending sent requests -->
    <?php if (!empty($outgoing)): ?>
    <div class="cht-section">
        <div class="cht-section-head"><span><i class="bi bi-send" style="margin-right:5px;"></i>Sent Requests</span></div>
        <?php foreach ($outgoing as $r): ?>
        <div class="cht-req-item">
            <div class="cht-req-name"><?= e($r['to_name']) ?></div>
            <div class="cht-req-msg"><?= e(mb_substr($r['message']??'',0,60)) ?></div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:5px;flex-wrap:wrap;">
                <span class="tcm-badge <?= $r['status']==='accepted'?'green':($r['status']==='resolved'?'gray':'amber') ?>"><?= e($r['status']) ?></span>
                <?php if ($r['status'] === 'accepted'): ?>
                <button onclick="document.getElementById('cookieModal<?= $r['id'] ?>').style.display='flex'"
                        class="tcm-btn primary sm" style="font-size:.7rem;padding:3px 10px;">
                    🍪 Give Cookie
                </button>
                <?php endif; ?>
            </div>
        </div>
        <!-- Cookie modal for each accepted request -->
        <?php if ($r['status'] === 'accepted'): ?>
        <div id="cookieModal<?= $r['id'] ?>" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9000;align-items:center;justify-content:center;">
            <div style="background:#fff;border-radius:18px;padding:26px;width:100%;max-width:400px;margin:16px;box-shadow:0 20px 60px rgba(0,0,0,.15);">
                <h3 style="font-size:1rem;font-weight:800;color:#111;margin-bottom:6px;">🍪 Give a Cookie to <?= e($r['to_name']) ?></h3>
                <p style="font-size:.82rem;color:#888;margin-bottom:16px;">Rate their help and write a short review. It'll show on their portfolio!</p>
                <form method="post" action="<?= base_url('/student/help/requests/'.$r['id'].'/cookie') ?>">
                    <?= csrf_field() ?>
                    <div class="tcm-field" style="margin-bottom:12px;">
                        <label style="font-size:.8rem;font-weight:700;">Cookies 🍪</label>
                        <div style="display:flex;gap:8px;margin-top:6px;" id="cookieRating<?= $r['id'] ?>">
                            <?php for ($i=1;$i<=5;$i++): ?>
                            <label style="cursor:pointer;font-size:1.4rem;opacity:.35;transition:opacity .1s;" title="<?= $i ?> cookie<?= $i>1?'s':'' ?>">
                                <input type="radio" name="cookies" value="<?= $i ?>" style="display:none;"
                                       onchange="highlightCookies(this,<?= $i ?>,'<?= $r['id'] ?>')">
                                🍪
                            </label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <div class="tcm-field">
                        <label>Review <span style="font-size:.72rem;color:#aaa;">(optional, max 300 chars)</span></label>
                        <textarea class="tcm-textarea" name="review" rows="2" maxlength="300"
                            placeholder="Great help with my React project!"></textarea>
                    </div>
                    <div style="display:flex;gap:8px;margin-top:8px;">
                        <button type="submit" class="tcm-btn primary"><i class="bi bi-check2"></i> Submit</button>
                        <button type="button" class="tcm-btn" onclick="document.getElementById('cookieModal<?= $r['id'] ?>').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        function highlightCookies(el, val, rid) {
            var labels = document.querySelectorAll('#cookieRating'+rid+' label');
            labels.forEach(function(l,i){ l.style.opacity = i < val ? '1' : '.35'; });
        }
        document.getElementById('cookieModal<?= $r['id'] ?>').addEventListener('click', function(e){ if(e.target===this) this.style.display='none'; });
        </script>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ── Main Chat Area ── -->
<div class="cht-main" id="chatMain">
    <div class="cht-empty-main" id="chatEmpty">
        <i class="bi bi-chat-dots"></i>
        <div style="font-size:.9rem;font-weight:700;color:#888;">Select a group to start chatting</div>
        <div style="font-size:.78rem;color:#bbb;">Or send a help request from the Community page</div>
        <a href="<?= base_url('/student/community') ?>" class="tcm-btn sm" style="margin-top:4px;">
            <i class="bi bi-people-fill"></i> Browse Community
        </a>
    </div>

    <!-- Chat header (hidden until group selected) -->
    <div class="cht-header" id="chatHeader" style="display:none;">
        <div class="cht-header-icon" id="chatHeaderIcon"><i class="bi bi-people-fill"></i></div>
        <div>
            <div class="cht-header-name" id="chatHeaderName">Group</div>
            <div class="cht-header-sub" id="chatHeaderSub">Loading...</div>
        </div>
        <div style="margin-left:auto;display:flex;gap:6px;">
            <button class="tcm-btn sm" onclick="leaveCurrentGroup()" title="Leave group" style="color:#dc2626;">
                <i class="bi bi-box-arrow-right"></i> Leave
            </button>
        </div>
    </div>

    <!-- Messages -->
    <div class="cht-messages" id="chatMessages" style="display:none;"></div>

    <!-- Input bar -->
    <div class="cht-input-bar" id="chatInputBar" style="display:none;">
        <label class="cht-attach-btn" title="Attach file (max 5MB)">
            <i class="bi bi-paperclip"></i>
            <input type="file" id="chatFileInput" style="display:none;" accept="image/*,.pdf,.doc,.docx,.txt,.zip">
        </label>
        <div style="flex:1;position:relative;">
            <div class="cht-input" id="chatInput" contenteditable="true" data-placeholder="Type a message..." style="min-height:38px;"></div>
        </div>
        <button class="cht-send-btn" onclick="sendMessage()" title="Send">
            <i class="bi bi-send-fill"></i>
        </button>
    </div>
</div>

</div><!-- /.cht-wrap -->

<!-- ── New Group Modal ── -->
<div class="cht-modal-backdrop" id="newGroupModal">
    <div class="cht-modal">
        <h3><i class="bi bi-people-fill" style="margin-right:8px;"></i>Create Group Chat</h3>
        <form method="post" action="<?= base_url('/student/groups/create') ?>" id="createGroupForm">
            <?= csrf_field() ?>

            <!-- Group name -->
            <div class="tcm-field" style="margin-bottom:14px;">
                <label>Group Name *</label>
                <input class="tcm-input" name="name" id="grpName" required
                       placeholder="e.g. React Study Group" maxlength="100">
            </div>

            <!-- Member search -->
            <div class="tcm-field" style="margin-bottom:6px;">
                <label>Add Members</label>
                <div style="position:relative;">
                    <input class="tcm-input" id="memberSearch" autocomplete="off"
                           placeholder="Search by name..." style="padding-right:34px;">
                    <i class="bi bi-search" style="position:absolute;right:11px;top:50%;transform:translateY(-50%);color:#bbb;font-size:.8rem;pointer-events:none;"></i>
                </div>

                <!-- Dropdown results -->
                <div id="memberDropdown" style="
                    display:none; position:relative; z-index:100;
                    background:#fff; border:1px solid #e5e5e5;
                    border-radius:10px; margin-top:4px;
                    box-shadow:0 6px 24px rgba(0,0,0,.1);
                    max-height:200px; overflow-y:auto;">
                </div>
            </div>

            <!-- Selected members chips -->
            <div id="selectedMembers" style="display:flex;flex-wrap:wrap;gap:6px;min-height:0;margin-bottom:12px;"></div>

            <!-- Hidden input carrying comma-separated IDs -->
            <input type="hidden" name="member_ids" id="memberIdsInput">

            <div style="display:flex;gap:8px;margin-top:4px;">
                <button type="submit" class="tcm-btn primary"><i class="bi bi-plus-lg"></i> Create Group</button>
                <button type="button" class="tcm-btn" id="cancelGroupModal">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.grp-member-row {
    display:flex; align-items:center; gap:10px;
    padding:8px 12px; cursor:pointer; transition:background .12s;
    border-bottom:1px solid #f5f5f5;
}
.grp-member-row:last-child { border-bottom:none; }
.grp-member-row:hover { background:#f5f5f5; }
.grp-member-row.checked { background:#f0f4ff; }
.grp-member-av {
    width:30px; height:30px; border-radius:50%;
    background:#e5e5e5; overflow:hidden;
    display:grid; place-items:center;
    font-size:.72rem; font-weight:700; flex-shrink:0;
}
.grp-member-name { font-size:.84rem; font-weight:600; color:#111; flex:1; }
.grp-member-hl   { font-size:.72rem; color:#aaa; }
.grp-chip {
    display:inline-flex; align-items:center; gap:5px;
    background:#111; color:#fff;
    border-radius:20px; padding:4px 10px 4px 8px;
    font-size:.75rem; font-weight:600;
}
.grp-chip button {
    background:none; border:none; color:rgba(255,255,255,.7);
    cursor:pointer; padding:0; line-height:1; font-size:.75rem;
}
.grp-chip button:hover { color:#fff; }
</style>

<script>
(function(){
    var BASE = <?= json_encode(base_url('')) ?>;
    var selectedMap = {}; // id → {id, name, avatar}

    var searchInput  = document.getElementById('memberSearch');
    var dropdown     = document.getElementById('memberDropdown');
    var chipsWrap    = document.getElementById('selectedMembers');
    var hiddenInput  = document.getElementById('memberIdsInput');
    var searchTimer  = null;

    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

    function renderChips() {
        chipsWrap.innerHTML = '';
        Object.values(selectedMap).forEach(function(m) {
            var chip = document.createElement('div');
            chip.className = 'grp-chip';
            var av = m.avatar
                ? '<img src="' + BASE + '/uploads/' + esc(m.avatar) + '" style="width:18px;height:18px;border-radius:50%;object-fit:cover;">'
                : '<span style="width:18px;height:18px;border-radius:50%;background:rgba(255,255,255,.2);display:inline-flex;align-items:center;justify-content:center;font-size:.6rem;">' + esc(m.name.charAt(0).toUpperCase()) + '</span>';
            chip.innerHTML = av + '<span>' + esc(m.name) + '</span>'
                + '<button type="button" data-id="' + m.id + '" title="Remove">&#10005;</button>';
            chip.querySelector('button').addEventListener('click', function() {
                delete selectedMap[this.dataset.id];
                renderChips();
                syncHidden();
                updateDropdownChecks();
            });
            chipsWrap.appendChild(chip);
        });
        hiddenInput.style.display = Object.keys(selectedMap).length ? '' : 'none';
    }

    function syncHidden() {
        hiddenInput.value = Object.keys(selectedMap).join(',');
    }

    function updateDropdownChecks() {
        dropdown.querySelectorAll('.grp-member-row').forEach(function(row) {
            var id = row.dataset.id;
            var cb = row.querySelector('input[type=checkbox]');
            if (cb) {
                cb.checked = !!selectedMap[id];
                row.classList.toggle('checked', !!selectedMap[id]);
            }
        });
    }

    function renderDropdown(students) {
        dropdown.innerHTML = '';
        if (!students.length) {
            dropdown.innerHTML = '<div style="padding:12px 14px;color:#bbb;font-size:.8rem;text-align:center;">No students found</div>';
            dropdown.style.display = 'block';
            return;
        }
        students.forEach(function(s) {
            var row = document.createElement('div');
            row.className = 'grp-member-row' + (selectedMap[s.id] ? ' checked' : '');
            row.dataset.id = s.id;

            var avHtml = s.avatar
                ? '<img src="' + BASE + '/uploads/' + esc(s.avatar) + '" style="width:100%;height:100%;object-fit:cover;">'
                : '<div style="width:100%;height:100%;display:grid;place-items:center;font-size:.7rem;font-weight:800;">' + esc((s.name||'?').charAt(0).toUpperCase()) + '</div>';

            row.innerHTML = '<div class="grp-member-av">' + avHtml + '</div>'
                + '<div style="flex:1;min-width:0;">'
                + '<div class="grp-member-name">' + esc(s.name) + '</div>'
                + (s.headline ? '<div class="grp-member-hl">' + esc(s.headline) + '</div>' : '')
                + '</div>'
                + '<input type="checkbox"' + (selectedMap[s.id] ? ' checked' : '') + ' style="width:16px;height:16px;accent-color:#111;cursor:pointer;flex-shrink:0;">';

            row.addEventListener('click', function(e) {
                if (e.target.type === 'checkbox') return; // handled separately
                toggle(s);
                var cb = row.querySelector('input[type=checkbox]');
                cb.checked = !!selectedMap[s.id];
                row.classList.toggle('checked', !!selectedMap[s.id]);
            });
            row.querySelector('input[type=checkbox]').addEventListener('change', function() {
                toggle(s);
                row.classList.toggle('checked', !!selectedMap[s.id]);
            });

            dropdown.appendChild(row);
        });
        dropdown.style.display = 'block';
    }

    function toggle(s) {
        if (selectedMap[s.id]) {
            delete selectedMap[s.id];
        } else {
            selectedMap[s.id] = s;
        }
        renderChips();
        syncHidden();
    }

    /* search */
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        var q = this.value.trim();
        if (q.length < 1) { dropdown.style.display = 'none'; return; }
        searchTimer = setTimeout(function() {
            fetch(BASE + '/api/student/search?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
            .then(function(r){ return r.json(); })
            .then(function(res) {
                if (res.success) renderDropdown(res.data.students || []);
            })
            .catch(function(){});
        }, 280);
    });

    /* close dropdown when clicking outside */
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#memberSearch') && !e.target.closest('#memberDropdown')) {
            dropdown.style.display = 'none';
        }
    });

    /* re-open dropdown on focus if query present */
    searchInput.addEventListener('focus', function() {
        if (this.value.trim().length >= 1 && dropdown.innerHTML) {
            dropdown.style.display = 'block';
        }
    });

    /* cancel button */
    document.getElementById('cancelGroupModal').addEventListener('click', function() {
        document.getElementById('newGroupModal').classList.remove('open');
    });

    /* reset on form submit */
    document.getElementById('createGroupForm').addEventListener('submit', function() {
        syncHidden();
    });

}());
</script>

<script>
(function () {
    var BASE       = <?= json_encode($BASE) ?>;
    var ME_ID      = <?= $meId ?>;
    var ME_NAME    = <?= json_encode($meName) ?>;
    var ME_AVATAR  = <?= json_encode($me['avatar'] ?? null) ?>;

    var currentGroupId   = null;
    var lastMessageId    = 0;
    var pollTimer        = null;
    var pendingFile      = null;

    /* ── helpers ── */
    function esc(s){ return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function relTime(ts){
        if(!ts) return '';
        var d = new Date(ts.replace(' ','T'));
        var now = Date.now();
        var diff = Math.floor((now - d.getTime())/1000);
        if(diff<60) return 'just now';
        if(diff<3600) return Math.floor(diff/60)+'m';
        if(diff<86400) return Math.floor(diff/3600)+'h';
        return d.toLocaleDateString('en-IN',{day:'numeric',month:'short'});
    }

    /* ── open a group ── */
    window.openGroup = function(groupId, groupName, groupType) {
        currentGroupId = groupId;
        lastMessageId  = 0;
        clearInterval(pollTimer);

        // Update sidebar active state
        document.querySelectorAll('.cht-group-item').forEach(function(el){ el.classList.remove('active'); });
        // find the clicked item by groupId data attribute
        var clickedItem = document.querySelector('.cht-group-item[data-gid="' + groupId + '"]');
        if (clickedItem) clickedItem.classList.add('active');

        // Show chat UI
        document.getElementById('chatEmpty').style.display     = 'none';
        document.getElementById('chatHeader').style.display    = 'flex';
        document.getElementById('chatMessages').style.display  = 'flex';
        document.getElementById('chatInputBar').style.display  = 'flex';

        // Set header
        document.getElementById('chatHeaderName').textContent = groupName;
        document.getElementById('chatHeaderSub').textContent  = 'Loading messages...';
        var icon = document.getElementById('chatHeaderIcon');
        icon.innerHTML = groupType==='help' ? '<i class="bi bi-hand-index-fill"></i>' : '<i class="bi bi-people-fill"></i>';
        icon.style.background = groupType==='help' ? '#6366f1' : '#111';

        // Clear messages and fetch
        document.getElementById('chatMessages').innerHTML = '<div style="text-align:center;padding:20px;color:#bbb;font-size:.8rem;">Loading...</div>';
        fetchMessages(false);

        // Poll every 4s
        pollTimer = setInterval(function(){ fetchMessages(true); }, 4000);
    };

    /* ── fetch messages ── */
    function fetchMessages(isPolling) {
        if (!currentGroupId) return;
        var url = BASE + '/api/student/chat/groups/' + currentGroupId + '/messages';
        if (isPolling && lastMessageId > 0) url += '?since=' + lastMessageId;

        fetch(url, { headers:{'Accept':'application/json'}, credentials:'same-origin' })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (!res.success) return;
            var msgs = res.data.messages || [];
            if (!isPolling) {
                renderAll(msgs);
            } else {
                msgs.forEach(function(m){ appendMessage(m); });
            }
            document.getElementById('chatHeaderSub').textContent = msgs.length ? '' : 'No messages yet — say hello!';
        })
        .catch(function(){});
    }

    function renderAll(msgs) {
        var container = document.getElementById('chatMessages');
        container.innerHTML = '';
        if (!msgs.length) {
            container.innerHTML = '<div style="text-align:center;padding:40px 20px;color:#bbb;font-size:.82rem;"><i class="bi bi-chat" style="font-size:2rem;display:block;margin-bottom:10px;"></i>No messages yet — say hello!</div>';
            return;
        }
        msgs.forEach(function(m){ appendMessage(m); });
        container.scrollTop = container.scrollHeight;
    }

    function appendMessage(m) {
        if (!m || !m.id) return;
        if (lastMessageId >= m.id) return;
        lastMessageId = Math.max(lastMessageId, m.id);

        var container = document.getElementById('chatMessages');
        var isMine = (m.user_id == ME_ID);
        var div = document.createElement('div');
        div.className = 'cht-msg' + (isMine ? ' mine' : '');
        div.dataset.id = m.id;

        // Avatar
        var avHtml = m.sender_avatar
            ? '<img src="' + BASE + '/uploads/' + esc(m.sender_avatar) + '" style="width:100%;height:100%;object-fit:cover;">'
            : '<div style="width:100%;height:100%;display:grid;place-items:center;font-size:.7rem;font-weight:800;">' + esc((m.sender_name||'?').charAt(0).toUpperCase()) + '</div>';

        var bodyHtml = '';
        if (m.file_type === 'image' && m.file_path) {
            bodyHtml = '<a href="' + BASE + '/uploads/chat/' + esc(m.file_path) + '" target="_blank">'
                + '<img src="' + BASE + '/uploads/chat/' + esc(m.file_path) + '" class="cht-img-msg" alt="Image"></a>';
            if (m.body) bodyHtml += '<div>' + esc(m.body) + '</div>';
        } else if (m.file_path) {
            bodyHtml = '<a href="' + BASE + '/uploads/chat/' + esc(m.file_path) + '" target="_blank" class="cht-file-msg">'
                + '<i class="bi bi-file-earmark"></i>' + esc(m.file_name || 'Download') + '</a>';
            if (m.body) bodyHtml += '<div style="margin-top:4px;">' + esc(m.body) + '</div>';
        } else {
            bodyHtml = esc(m.body||'');
        }

        div.innerHTML = '<div class="cht-msg-avatar">' + avHtml + '</div>'
            + '<div>'
            + (!isMine ? '<div class="cht-msg-sender">' + esc(m.sender_name) + '</div>' : '')
            + '<div class="cht-bubble">' + bodyHtml + '</div>'
            + '<div class="cht-msg-time">' + relTime(m.created_at) + '</div>'
            + '</div>';

        // Remove skeleton if first real message
        var skeleton = container.querySelector('[data-skeleton]');
        if (skeleton) skeleton.remove();
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
    }

    /* ── send message ── */
    window.sendMessage = function() {
        if (!currentGroupId) return;
        var input = document.getElementById('chatInput');
        var body  = (input.innerText || input.textContent || '').trim();

        if (!body && !pendingFile) return;

        var formData = new FormData();
        formData.append('body', body);
        if (pendingFile) formData.append('file', pendingFile);

        // Optimistic clear
        input.innerText = '';
        clearPendingFile();

        fetch(BASE + '/api/student/chat/groups/' + currentGroupId + '/messages', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            body: formData
        })
        .then(function(r){ return r.json(); })
        .then(function(res) {
            if (res.success && res.data && res.data.message) {
                appendMessage(res.data.message);
            }
        })
        .catch(function(){});
    };

    /* ── file attachment ── */
    document.getElementById('chatFileInput').addEventListener('change', function() {
        var f = this.files[0];
        if (!f) return;
        if (f.size > 5*1024*1024) { alert('File too large. Max 5MB.'); this.value=''; return; }
        pendingFile = f;
        showFilePreview(f);
        this.value = '';
    });

    function showFilePreview(f) {
        var existing = document.getElementById('cht-file-preview');
        if (existing) existing.remove();
        var bar = document.getElementById('chatInputBar');
        var preview = document.createElement('div');
        preview.id = 'cht-file-preview';
        preview.style.cssText = 'display:flex;align-items:center;gap:8px;padding:6px 14px;background:#f0f0f0;border-top:1px solid #e5e5e5;font-size:.78rem;color:#555;';
        preview.innerHTML = '<i class="bi bi-paperclip"></i><span>' + esc(f.name) + '</span>'
            + '<button onclick="clearPendingFile()" style="background:none;border:none;color:#dc2626;cursor:pointer;margin-left:auto;"><i class="bi bi-x"></i></button>';
        bar.parentNode.insertBefore(preview, bar);
    }

    window.clearPendingFile = function() {
        pendingFile = null;
        var p = document.getElementById('cht-file-preview');
        if (p) p.remove();
    };

    /* ── Enter to send (Shift+Enter for newline) ── */
    document.getElementById('chatInput').addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });

    /* ── leave group ── */
    window.leaveCurrentGroup = function() {
        if (!currentGroupId) return;
        if (!confirm('Leave this group?')) return;
        fetch(BASE + '/student/groups/' + currentGroupId + '/leave', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: 'csrf_handled=1'
        }).then(function(){ window.location.reload(); });
    };

    /* ── close modal on backdrop click ── */
    document.getElementById('newGroupModal').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('open');
    });

}());
</script>
