<?php
/**
 * In-app Notification Bell + Dropdown Panel
 * Include in both admin.php and student.php layouts inside .tcm-topbar-right
 * $me is set in student layout, $admin is set in admin layout — handle both.
 */
$_notifUser = $me ?? $admin ?? [];
$userId = (int) ($_notifUser['id'] ?? 0);
?>

<!-- ── Notification Bell ───────────────────────────────── -->
<div class="tcm-notif-wrap" id="tcmNotifWrap">

    <button class="tcm-notif-btn" id="tcmNotifBtn" title="Notifications" aria-label="Notifications">
        <i class="bi bi-bell"></i>
        <span class="tcm-notif-badge" id="tcmNotifBadge" style="display:none;">0</span>
    </button>

    <!-- Dropdown Panel -->
    <div class="tcm-notif-panel" id="tcmNotifPanel" role="dialog" aria-label="Notifications">

        <div class="tcm-notif-head">
            <span class="tcm-notif-head-title">Notifications</span>
            <button class="tcm-notif-read-all" id="tcmNotifReadAll" title="Mark all as read">
                <i class="bi bi-check2-all"></i> Mark all read
            </button>
        </div>

        <div class="tcm-notif-list" id="tcmNotifList">
            <div class="tcm-notif-empty">
                <i class="bi bi-bell-slash"></i>
                <span>No notifications yet</span>
            </div>
        </div>

    </div>
</div>

<style>
/* ── Bell button ─────────────────────────────────────── */
.tcm-notif-wrap {
    position: relative;
}
.tcm-notif-btn {
    position: relative;
    background: none;
    border: none;
    cursor: pointer;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .95rem;
    color: #888;
    transition: background .15s, color .15s;
    flex-shrink: 0;
}
.tcm-notif-btn:hover,
.tcm-notif-btn.active { background: #f0f0f0; color: #111; }

.tcm-notif-badge {
    position: absolute;
    top: 3px; right: 3px;
    min-width: 16px; height: 16px;
    padding: 0 4px;
    background: #dc2626;
    color: #fff;
    border-radius: 999px;
    font-size: .6rem;
    font-weight: 700;
    line-height: 16px;
    text-align: center;
    border: 1.5px solid #fff;
    pointer-events: none;
}

/* ── Dropdown panel ──────────────────────────────────── */
.tcm-notif-panel {
    display: none;
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 340px;
    max-height: 480px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 14px;
    box-shadow: 0 12px 40px rgba(0,0,0,.12);
    z-index: 9999;
    overflow: hidden;
    animation: tcmPanelIn .2s cubic-bezier(.34,1.56,.64,1) both;
}
@keyframes tcmPanelIn {
    from { opacity:0; transform:translateY(-8px) scale(.97); }
    to   { opacity:1; transform:translateY(0) scale(1); }
}
.tcm-notif-panel.open { display: flex; flex-direction: column; }

/* Header */
.tcm-notif-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 16px 10px;
    border-bottom: 1px solid #f0f0f0;
    flex-shrink: 0;
}
.tcm-notif-head-title {
    font-size: .88rem;
    font-weight: 700;
    color: #111;
}
.tcm-notif-read-all {
    background: none;
    border: none;
    cursor: pointer;
    font-size: .75rem;
    color: #888;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    border-radius: 6px;
    transition: background .12s, color .12s;
}
.tcm-notif-read-all:hover { background: #f5f5f5; color: #111; }

/* List */
.tcm-notif-list {
    overflow-y: auto;
    flex: 1;
    padding: 6px 0;
    scrollbar-width: thin;
    scrollbar-color: #e0e0e0 transparent;
}

/* Empty state */
.tcm-notif-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 40px 20px;
    color: #aaa;
    font-size: .82rem;
}
.tcm-notif-empty i { font-size: 1.8rem; }

/* Item */
.tcm-notif-item {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 16px;
    cursor: pointer;
    transition: background .12s;
    border-bottom: 1px solid #f8f8f8;
    text-decoration: none;
    color: inherit;
}
.tcm-notif-item:last-child { border-bottom: none; }
.tcm-notif-item:hover { background: #fafafa; }
.tcm-notif-item.unread { background: #f0f7ff; }
.tcm-notif-item.unread:hover { background: #e8f2ff; }

.tcm-notif-icon {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
    margin-top: 1px;
}
.tcm-notif-item.unread .tcm-notif-icon { background: #dbeafe; }

.tcm-notif-body { flex: 1; min-width: 0; }
.tcm-notif-title {
    font-size: .82rem;
    font-weight: 600;
    color: #111;
    line-height: 1.4;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.tcm-notif-text {
    font-size: .75rem;
    color: #666;
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.tcm-notif-time {
    font-size: .68rem;
    color: #aaa;
    margin-top: 4px;
}
.tcm-notif-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    background: #3b82f6;
    flex-shrink: 0;
    margin-top: 6px;
}

/* Loading skeleton */
.tcm-notif-skeleton {
    padding: 10px 16px;
    display: flex;
    gap: 10px;
    align-items: flex-start;
}
.tcm-sk-circle {
    width: 34px; height: 34px; border-radius: 50%;
    background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: tcmSkAnim 1.2s infinite;
    flex-shrink: 0;
}
.tcm-sk-lines { flex: 1; display: flex; flex-direction: column; gap: 6px; }
.tcm-sk-line {
    height: 10px; border-radius: 4px;
    background: linear-gradient(90deg,#f0f0f0 25%,#e0e0e0 50%,#f0f0f0 75%);
    background-size: 200% 100%;
    animation: tcmSkAnim 1.2s infinite;
}
.tcm-sk-line.short { width: 55%; }
@keyframes tcmSkAnim {
    from { background-position: 200% 0; }
    to   { background-position: -200% 0; }
}

@media (max-width: 420px) {
    .tcm-notif-panel { width: calc(100vw - 24px); right: -8px; }
}
</style>

<script>
(function () {
    var BASE = (document.querySelector('meta[name="app-base"]') || {}).content || '';
    var userId = <?= $userId ?>;
    var panel   = document.getElementById('tcmNotifPanel');
    var btn     = document.getElementById('tcmNotifBtn');
    var list    = document.getElementById('tcmNotifList');
    var badge   = document.getElementById('tcmNotifBadge');
    var readAll = document.getElementById('tcmNotifReadAll');

    var loaded = false;

    /* ── Toggle panel ───────────────────────────── */
    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        var isOpen = panel.classList.contains('open');
        panel.classList.toggle('open', !isOpen);
        btn.classList.toggle('active', !isOpen);
        if (!isOpen && !loaded) { fetchNotifs(); }
        // Request push notification permission on first open
        if (!isOpen && typeof window.TCMNotifications !== 'undefined' && window.TCMNotifications._requestPermission) {
            if (window.Notification && window.Notification.permission === 'default') {
                window.TCMNotifications._requestPermission();
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (!document.getElementById('tcmNotifWrap').contains(e.target)) {
            panel.classList.remove('open');
            btn.classList.remove('active');
        }
    });

    /* ── Mark all read ──────────────────────────── */
    readAll.addEventListener('click', function () {
        fetch(BASE + '/api/notifications/read-all', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        }).then(function () {
            document.querySelectorAll('.tcm-notif-item.unread').forEach(function (el) {
                el.classList.remove('unread');
                var dot = el.querySelector('.tcm-notif-dot');
                if (dot) dot.remove();
            });
            badge.style.display = 'none';
        });
    });

    /* ── Fetch notifications ────────────────────── */
    function fetchNotifs() {
        list.innerHTML = skeleton();
        fetch(BASE + '/api/notifications', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            loaded = true;
            if (!res.success) { showEmpty(); return; }
            var items = (res.data || {}).notifications || [];
            var unread = (res.data || {}).unread_count || 0;
            renderItems(items);
            updateBadge(unread);
        })
        .catch(function () { showEmpty(); });
    }

    /* ── Render list ────────────────────────────── */
    function renderItems(items) {
        if (!items.length) { showEmpty(); return; }
        list.innerHTML = items.map(function (n) {
            return notifItem(n);
        }).join('');

        list.querySelectorAll('.tcm-notif-item').forEach(function (el) {
            el.addEventListener('click', function () {
                var id = el.getAttribute('data-id');
                var url = el.getAttribute('data-url');
                if (el.classList.contains('unread')) {
                    fetch(BASE + '/api/notifications/' + id + '/read', {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        credentials: 'same-origin'
                    });
                    el.classList.remove('unread');
                    var dot = el.querySelector('.tcm-notif-dot');
                    if (dot) dot.remove();
                    var cur = parseInt(badge.textContent || '0', 10);
                    updateBadge(Math.max(0, cur - 1));
                }
                if (url && url !== '#') {
                    window.location.href = url;
                }
            });
        });
    }

    function notifItem(n) {
        var isUnread = n.is_read == 0;
        var timeAgo  = relTime(n.created_at);
        var url      = n.click_url || '#';
        return '<div class="tcm-notif-item' + (isUnread ? ' unread' : '') + '" data-id="' + esc(n.id) + '" data-url="' + esc(url) + '">' +
            '<div class="tcm-notif-icon">' + esc(n.icon || '🔔') + '</div>' +
            '<div class="tcm-notif-body">' +
                '<div class="tcm-notif-title">' + esc(n.title) + '</div>' +
                (n.body ? '<div class="tcm-notif-text">' + esc(n.body) + '</div>' : '') +
                '<div class="tcm-notif-time">' + esc(timeAgo) + '</div>' +
            '</div>' +
            (isUnread ? '<div class="tcm-notif-dot"></div>' : '') +
        '</div>';
    }

    function showEmpty() {
        list.innerHTML = '<div class="tcm-notif-empty"><i class="bi bi-bell-slash"></i><span>No notifications yet</span></div>';
    }

    function skeleton() {
        var s = '';
        for (var i = 0; i < 3; i++) {
            s += '<div class="tcm-notif-skeleton"><div class="tcm-sk-circle"></div><div class="tcm-sk-lines"><div class="tcm-sk-line"></div><div class="tcm-sk-line short"></div></div></div>';
        }
        return s;
    }

    /* ── Badge ──────────────────────────────────── */
    function updateBadge(count) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = '';
        } else {
            badge.style.display = 'none';
        }
    }

    /* ── Relative time ──────────────────────────── */
    function relTime(ts) {
        if (!ts) return '';
        var now  = Date.now();
        var then = new Date(ts.replace(' ', 'T')).getTime();
        var diff = Math.floor((now - then) / 1000);
        if (diff < 60)    return 'just now';
        if (diff < 3600)  return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        return Math.floor(diff / 86400) + 'd ago';
    }

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    /* ── Poll unread count every 60s (background) ── */
    function pollBadge() {
        fetch(BASE + '/api/notifications', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            if (res.success) {
                updateBadge((res.data || {}).unread_count || 0);
                if (loaded) {
                    var items = (res.data || {}).notifications || [];
                    renderItems(items);
                }
            }
        })
        .catch(function () {});
    }

    /* Initial badge load after 1s, then poll every 15s */
    setTimeout(pollBadge, 1000);
    setInterval(pollBadge, 15000);

    /* Expose poll function for external callers (e.g. Firebase foreground msg) */
    window._tcmNotifPoll = pollBadge;

}());
</script>
