/**
 * The Code Munk — Push Notification Manager
 * Firebase Cloud Messaging (FCM) integration
 *
 * Features:
 *  - Permission request with UI prompt
 *  - FCM token registration & renewal
 *  - Foreground notification toast (in-app)
 *  - Topics: admin, student, global
 *  - Notification bell icon with badge
 */

(function (window) {
    'use strict';

    var BASE = ((document.querySelector('meta[name="app-base"]') || {}).content || '').replace(/\/$/, '');

    /* ── Firebase Config ─────────────────────────────────── */
    var FIREBASE_CONFIG = {
        apiKey:            "AIzaSyDaozu-DoEjwRiCS5pnShpEXjVg1QmQ42k",
        authDomain:        "thecodemunk-21705.firebaseapp.com",
        projectId:         "thecodemunk-21705",
        storageBucket:     "thecodemunk-21705.firebasestorage.app",
        messagingSenderId: "3908839386",
        appId:             "1:3908839386:web:4d52365fbb1c960512c2e3",
        measurementId:     "G-00QY7JNB84"
    };

    /* VAPID public key — Firebase Console → Project Settings → Cloud Messaging → Web Push certificates */
    var VAPID_KEY = "BEbRdn4zmXK7pZlUzwndSt9yC6Rheq-7kP6m59GCkFvsiu8W7Nl8-6JEiwWfVLWHy-sG8rgy34eAmvYim9wSPJI";

    var TCMNotif = {
        app:       null,
        messaging: null,
        token:     null,
        role:      null,   // 'admin' | 'student'
        userId:    null,

        /* ── Init ──────────────────────────────────────── */
        init: function (role, userId) {
            this.role   = role;
            this.userId = userId;

            if (!('serviceWorker' in navigator) || !('Notification' in window)) {
                console.log('[TCM Notif] Push not supported in this browser.');
                return;
            }

            // Bell is handled by notification-panel.php — just load Firebase
            this._loadFirebase();
        },

        /* ── Load Firebase SDK dynamically ─────────────── */
        _loadFirebase: function () {
            var self = this;

            if (window.firebase && window.firebase.messaging) {
                self._setup();
                return;
            }

            /* Load compat SDK (works without bundler) */
            var scripts = [
                'https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js',
                'https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js'
            ];

            var loaded = 0;
            scripts.forEach(function (src) {
                var s    = document.createElement('script');
                s.src    = src;
                s.async  = true;
                s.onload = function () {
                    loaded++;
                    if (loaded === scripts.length) self._setup();
                };
                document.head.appendChild(s);
            });
        },

        /* ── Setup Firebase Messaging ───────────────────── */
        _setup: function () {
            var self = this;

            try {
                if (!firebase.apps.length) {
                    self.app = firebase.initializeApp(FIREBASE_CONFIG);
                } else {
                    self.app = firebase.apps[0];
                }
                self.messaging = firebase.messaging();
            } catch (e) {
                console.error('[TCM Notif] Firebase init error:', e);
                return;
            }

            /* Register Firebase Service Worker — also handles PWA caching */
            navigator.serviceWorker
                .register(BASE + '/firebase-messaging-sw.js', { scope: BASE + '/' })
                .then(function (reg) {
                    console.log('[TCM] SW registered. scope:', reg.scope);
                    self._checkPermission();
                })
                .catch(function (err) {
                    console.error('[TCM] SW registration failed:', err);
                });

            /* Handle foreground messages */
            self.messaging.onMessage(function (payload) {
                console.log('[TCM Notif] Foreground message:', payload);
                var n = payload.notification || payload.data || {};
                self._showToast(n.title || 'The Code Munk', n.body || '', n.icon || '', (payload.data || {}).click_url || '');
                // Update the panel badge — trigger a re-poll
                self._updateBadge(true);
                // If the notification panel pollBadge function is available, call it
                if (typeof window._tcmNotifPoll === 'function') {
                    window._tcmNotifPoll();
                }
            });
        },

        /* ── Permission check & request ─────────────────── */
        _checkPermission: function () {
            var perm = Notification.permission;

            if (perm === 'granted') {
                this._getToken();
            } else if (perm === 'default') {
                /* Show our custom prompt banner after 3s */
                var self = this;
                setTimeout(function () { self._showPermissionBanner(); }, 3000);
            }
            /* If denied — do nothing, user already chose */
        },

        _requestPermission: function () {
            var self = this;
            Notification.requestPermission().then(function (perm) {
                if (perm === 'granted') {
                    self._getToken();
                    self._hideBanner();
                    self._showToast('Notifications enabled!', 'You\'ll get updates for leads, payments and new content.', '', '');
                } else {
                    self._hideBanner();
                }
            });
        },

        /* ── Get FCM Token ──────────────────────────────── */
        _getToken: function () {
            var self = this;

            self.messaging.getToken({ vapidKey: VAPID_KEY })
                .then(function (token) {
                    if (!token) return;
                    self.token = token;
                    self._saveToken(token);
                })
                .catch(function (err) {
                    console.error('[TCM Notif] getToken error:', err);
                });
        },

        /* ── Save token to server ───────────────────────── */
        _saveToken: function (token) {
            var saved = localStorage.getItem('tcm_fcm_token');
            if (saved === token) return; /* Already saved */

            fetch(BASE + '/api/notifications/token', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({
                    token:  token,
                    role:   this.role,
                    userId: this.userId
                })
            })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                if (d.success) {
                    localStorage.setItem('tcm_fcm_token', token);
                    console.log('[TCM Notif] Token saved.');
                }
            })
            .catch(function (e) { console.error('[TCM Notif] Save token error:', e); });
        },

        /* ── Permission Banner ──────────────────────────── */
        _showPermissionBanner: function () {
            if (document.getElementById('tcm-notif-banner')) return;

            var self = this;
            var el   = document.createElement('div');
            el.id    = 'tcm-notif-banner';
            el.innerHTML = [
                '<div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">',
                '  <div style="width:40px;height:40px;background:rgba(255,255,255,.15);border-radius:50%;',
                '              display:grid;place-items:center;font-size:1.2rem;flex-shrink:0;">🔔</div>',
                '  <div style="flex:1;min-width:180px;">',
                '    <div style="font-weight:700;font-size:.9rem;margin-bottom:3px;">Stay updated with TCM</div>',
                '    <div style="font-size:.78rem;opacity:.75;">Get instant alerts for new leads, payments, blog posts and more.</div>',
                '  </div>',
                '  <div style="display:flex;gap:8px;flex-shrink:0;">',
                '    <button id="tcm-notif-allow" style="padding:9px 18px;background:#fff;color:#111;',
                '            border:none;border-radius:9px;font-weight:700;font-size:.82rem;cursor:pointer;">',
                '      Enable Notifications',
                '    </button>',
                '    <button id="tcm-notif-dismiss" style="padding:9px 12px;background:rgba(255,255,255,.12);',
                '            color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:9px;',
                '            font-size:.82rem;cursor:pointer;">Later</button>',
                '  </div>',
                '</div>'
            ].join('');

            el.style.cssText = [
                'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);',
                'background:#111;color:#fff;',
                'padding:16px 20px;border-radius:16px;',
                'box-shadow:0 8px 40px rgba(0,0,0,.25);',
                'z-index:99999;max-width:600px;width:calc(100% - 32px);',
                'animation:tcmBannerIn .35s cubic-bezier(.34,1.56,.64,1) both;',
                'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;'
            ].join('');

            var style = document.createElement('style');
            style.textContent = '@keyframes tcmBannerIn{from{opacity:0;transform:translateX(-50%) translateY(24px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}';
            document.head.appendChild(style);
            document.body.appendChild(el);

            document.getElementById('tcm-notif-allow').onclick   = function () { self._requestPermission(); };
            document.getElementById('tcm-notif-dismiss').onclick  = function () { self._hideBanner(); };
        },

        _hideBanner: function () {
            var b = document.getElementById('tcm-notif-banner');
            if (b) {
                b.style.animation = 'tcmBannerOut .25s ease forwards';
                b.style.cssText  += ';animation:tcmBannerOut .25s ease forwards;';
                setTimeout(function () { if (b.parentNode) b.parentNode.removeChild(b); }, 300);
            }
        },

        /* ── Foreground Toast ───────────────────────────── */
        _showToast: function (title, body, icon, url) {
            var el = document.createElement('div');
            el.id  = 'tcm-toast-' + Date.now();

            // Use TCM SVG logo as default icon
            var defaultIcon = BASE + '/assets/icons/icon-96.svg';
            var ico = (icon && icon.indexOf('http') === 0)
                ? '<img src="' + icon + '" style="width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;">'
                : '<div style="width:36px;height:36px;border-radius:9px;overflow:hidden;flex-shrink:0;">'
                  + '<img src="' + defaultIcon + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.parentNode.innerHTML=\'📡\'">'
                  + '</div>';

            el.innerHTML = [
                '<div style="display:flex;align-items:flex-start;gap:12px;">',
                ico,
                '<div style="flex:1;min-width:0;">',
                '  <div style="font-weight:700;font-size:.88rem;color:#111;margin-bottom:3px;">' + this._esc(title) + '</div>',
                '  <div style="font-size:.78rem;color:#555;line-height:1.5;">' + this._esc(body) + '</div>',
                '</div>',
                '<button onclick="this.closest(\'#' + el.id + '\') ? this.closest(\'[id^=tcm-toast]\').remove() : null" ',
                '  style="background:none;border:none;color:#bbb;cursor:pointer;padding:0;font-size:.9rem;flex-shrink:0;line-height:1;">✕</button>',
                '</div>'
            ].join('');

            el.style.cssText = [
                'position:fixed;top:76px;right:20px;',
                'background:#fff;border:1px solid #e5e5e5;',
                'border-radius:14px;padding:14px 16px;',
                'box-shadow:0 8px 32px rgba(0,0,0,.12);',
                'z-index:99998;max-width:340px;width:calc(100% - 40px);',
                'animation:tcmToastIn .3s cubic-bezier(.34,1.56,.64,1) both;',
                'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;',
                'cursor:' + (url ? 'pointer' : 'default') + ';'
            ].join('');

            var toastStyle = document.createElement('style');
            toastStyle.textContent = '@keyframes tcmToastIn{from{opacity:0;transform:translateX(24px)}to{opacity:1;transform:translateX(0)}}';
            document.head.appendChild(toastStyle);

            if (url) {
                el.onclick = function () { window.location.href = url; };
            }

            document.body.appendChild(el);

            /* Auto remove after 6s */
            setTimeout(function () { if (el.parentNode) el.parentNode.removeChild(el); }, 6000);
        },

        /* ── Notification Bell ──────────────────────────── */
        _injectBell: function () {
            var self = this;

            var bell = document.createElement('button');
            bell.id  = 'tcm-notif-bell';
            bell.title = 'Notifications';
            bell.innerHTML = '<i class="bi bi-bell-fill"></i><span id="tcm-bell-badge" style="display:none;position:absolute;top:-3px;right:-3px;width:8px;height:8px;background:#dc2626;border-radius:50%;border:1.5px solid #fff;"></span>';
            bell.style.cssText = [
                'position:relative;background:none;border:none;cursor:pointer;',
                'display:flex;align-items:center;justify-content:center;',
                'width:34px;height:34px;border-radius:8px;font-size:.95rem;color:#888;',
                'transition:background .15s,color .15s;'
            ].join('');
            bell.onmouseover = function () { this.style.background='#f5f5f5';this.style.color='#111'; };
            bell.onmouseout  = function () { this.style.background='none';this.style.color='#888'; };
            bell.onclick     = function () { self._requestPermission(); };

            /* Inject into topbar right */
            var topbarRight = document.querySelector('.tcm-topbar-right');
            if (topbarRight) {
                topbarRight.insertBefore(bell, topbarRight.firstChild);
            }
        },

        _updateBadge: function (show) {
            // Update the notification panel badge (tcmNotifBadge)
            var badge = document.getElementById('tcmNotifBadge');
            if (badge) {
                if (show) {
                    var cur = parseInt(badge.textContent || '0', 10);
                    badge.textContent = cur > 0 ? cur + 1 : 1;
                    badge.style.display = '';
                }
            }
        },

        /* ── Utility ────────────────────────────────────── */
        _esc: function (str) {
            return String(str || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
        }
    };

    window.TCMNotifications = TCMNotif;

}(window));


/* ══════════════════════════════════════════════
   PWA Install Prompt — "Add to Home Screen"
   Shows after 30s on mobile if not installed
   ══════════════════════════════════════════════ */
(function () {
    var deferredPrompt = null;
    var BASE = ((document.querySelector('meta[name="app-base"]') || {}).content || '').replace(/\/$/, '');

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;

        // Only show on mobile, only once per week
        var lastShown = parseInt(localStorage.getItem('tcm_install_shown') || '0', 10);
        if (Date.now() - lastShown < 7 * 24 * 3600 * 1000) return;

        setTimeout(function () {
            if (!deferredPrompt) return;
            showInstallBanner();
        }, 30000); // show after 30s
    });

    function showInstallBanner() {
        if (document.getElementById('tcm-install-banner')) return;

        var el = document.createElement('div');
        el.id  = 'tcm-install-banner';
        el.innerHTML = [
            '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">',
            '  <img src="' + BASE + '/assets/icons/icon-96.svg" style="width:44px;height:44px;border-radius:11px;flex-shrink:0;">',
            '  <div style="flex:1;min-width:150px;">',
            '    <div style="font-weight:800;font-size:.88rem;margin-bottom:2px;">Install The Code Munk</div>',
            '    <div style="font-size:.75rem;opacity:.7;">Add to your home screen for quick access</div>',
            '  </div>',
            '  <div style="display:flex;gap:8px;flex-shrink:0;">',
            '    <button id="tcm-install-yes" style="padding:8px 16px;background:#fff;color:#111;border:none;border-radius:8px;font-weight:700;font-size:.8rem;cursor:pointer;">Install</button>',
            '    <button id="tcm-install-no"  style="padding:8px 10px;background:rgba(255,255,255,.12);color:#fff;border:1px solid rgba(255,255,255,.2);border-radius:8px;font-size:.8rem;cursor:pointer;">Later</button>',
            '  </div>',
            '</div>'
        ].join('');

        el.style.cssText = [
            'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);',
            'background:#111;color:#fff;padding:14px 18px;border-radius:16px;',
            'box-shadow:0 8px 40px rgba(0,0,0,.3);z-index:99999;',
            'max-width:520px;width:calc(100% - 32px);',
            'font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;',
            'animation:tcmBannerIn .35s cubic-bezier(.34,1.56,.64,1) both;'
        ].join('');

        document.body.appendChild(el);
        localStorage.setItem('tcm_install_shown', Date.now().toString());

        document.getElementById('tcm-install-yes').addEventListener('click', function () {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(function () { deferredPrompt = null; });
            }
            el.remove();
        });

        document.getElementById('tcm-install-no').addEventListener('click', function () {
            el.style.animation = 'none';
            el.style.opacity = '0';
            el.style.transition = 'opacity .25s';
            setTimeout(function () { el.remove(); }, 280);
        });
    }

    // Track install completion
    window.addEventListener('appinstalled', function () {
        localStorage.setItem('tcm_installed', '1');
        console.log('[TCM PWA] App installed.');
    });
}());
