/**
 * The Code Munk — Combined Service Worker
 * Handles: Firebase push notifications + PWA caching
 *
 * ONE service worker, ONE scope — no conflicts.
 */

// ── Firebase ──────────────────────────────────────────────
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.12.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey:            "AIzaSyDaozu-DoEjwRiCS5pnShpEXjVg1QmQ42k",
    authDomain:        "thecodemunk-21705.firebaseapp.com",
    projectId:         "thecodemunk-21705",
    storageBucket:     "thecodemunk-21705.firebasestorage.app",
    messagingSenderId: "3908839386",
    appId:             "1:3908839386:web:4d52365fbb1c960512c2e3",
    measurementId:     "G-00QY7JNB84"
});

const messaging = firebase.messaging();

// ── Helpers ───────────────────────────────────────────────
function getBase() {
    try {
        const url  = new URL(self.registration.scope);
        return url.pathname.replace(/\/$/, '');
    } catch (e) { return ''; }
}

// ── PWA Cache ─────────────────────────────────────────────
const CACHE = 'tcm-v2';
const PRECACHE = [
    '/',
    '/index.html',
    '/programs.html',
    '/insights.html',
    '/contact.html',
    '/assets/style.css',
    '/assets/hero-effects.css',
    '/assets/skeleton.css',
    '/assets/auth.css',
    '/assets/tcm-app.js',
    '/assets/skeleton.js',
    '/assets/icons/icon-192.svg',
    '/assets/icons/icon-96.svg',
];

self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(CACHE).then(function (c) {
            return Promise.allSettled(PRECACHE.map(function (url) {
                return c.add(new Request(url, { mode: 'no-cors' })).catch(function(){});
            }));
        }).then(function () { return self.skipWaiting(); })
    );
});

self.addEventListener('activate', function (e) {
    e.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (k) {
                return k !== CACHE;
            }).map(function (k) { return caches.delete(k); }));
        }).then(function () { return self.clients.claim(); })
    );
});

self.addEventListener('fetch', function (e) {
    var req = e.request;
    if (req.method !== 'GET') return;

    var url = new URL(req.url);

    // Never cache: auth, admin, student, api, push-related
    if (/\/(auth|admin|student|api)\//i.test(url.pathname) ||
        url.pathname.startsWith('/api/') ||
        url.pathname.startsWith('/auth/') ||
        url.pathname.startsWith('/admin') ||
        url.pathname.startsWith('/student')) {
        return;
    }

    // Static assets → cache first
    if (/\.(css|js|svg|png|jpg|jpeg|gif|ico|woff2?|ttf|webp)$/i.test(url.pathname)) {
        e.respondWith(
            caches.match(req).then(function (cached) {
                return cached || fetch(req).then(function (res) {
                    if (res && res.status === 200) {
                        caches.open(CACHE).then(function (c) { c.put(req, res.clone()); });
                    }
                    return res;
                }).catch(function () { return cached; });
            })
        );
        return;
    }

    // HTML pages → network first, cache fallback
    if (req.headers.get('Accept') && req.headers.get('Accept').includes('text/html')) {
        e.respondWith(
            fetch(req).then(function (res) {
                if (res && res.status === 200) {
                    caches.open(CACHE).then(function (c) { c.put(req, res.clone()); });
                }
                return res;
            }).catch(function () {
                return caches.match(req).then(function (c) { return c || caches.match('/'); });
            })
        );
    }
});

// ── Firebase: Background Push ─────────────────────────────
messaging.onBackgroundMessage(function (payload) {
    const base = getBase();
    const data = payload.data || {};
    const notif = payload.notification || {};

    const title = notif.title || data.title || 'The Code Munk';
    const body  = notif.body  || data.body  || 'You have a new notification.';
    const icon  = notif.icon  || data.icon  || base + '/assets/icons/icon-192.svg';
    const badge = base + '/assets/icons/icon-96.svg';
    const url   = data.click_url || base + '/student';
    const tag   = data.tag || 'tcm-push';

    self.registration.showNotification(title, {
        body:    body,
        icon:    icon,
        badge:   badge,
        tag:     tag,
        data:    { url: url },
        vibrate: [200, 100, 200],
        requireInteraction: false,
        actions: [
            { action: 'open',    title: '👁️ View' },
            { action: 'dismiss', title: '✕ Dismiss' }
        ]
    });
});

// ── Notification click ────────────────────────────────────
self.addEventListener('notificationclick', function (event) {
    event.notification.close();
    if (event.action === 'dismiss') return;

    const base = getBase();
    const url  = (event.notification.data && event.notification.data.url)
        ? event.notification.data.url
        : base + '/student';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clients) {
            for (var i = 0; i < clients.length; i++) {
                if (clients[i].url.includes(url.split('?')[0]) && 'focus' in clients[i]) {
                    return clients[i].focus();
                }
            }
            return self.clients.openWindow ? self.clients.openWindow(url) : null;
        })
    );
});
