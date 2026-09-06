/**
 * The Code Munk — PWA Service Worker
 * Handles: install, activate, fetch (cache-first for assets, network-first for pages)
 * Version bump this string to force cache refresh on deploy
 */
const CACHE_VERSION = 'tcm-v1';
const STATIC_CACHE  = CACHE_VERSION + '-static';
const PAGE_CACHE    = CACHE_VERSION + '-pages';

// Core assets to pre-cache on install
const PRECACHE_ASSETS = [
    '/',
    '/index.html',
    '/assets/style.css',
    '/assets/hero-effects.css',
    '/assets/skeleton.css',
    '/assets/auth.css',
    '/assets/tcm-app.js',
    '/assets/skeleton.js',
    '/programs.html',
    '/insights.html',
    '/contact.html',
    'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
];

// ── Install ──────────────────────────────────────────
self.addEventListener('install', function (e) {
    e.waitUntil(
        caches.open(STATIC_CACHE).then(function (cache) {
            return cache.addAll(PRECACHE_ASSETS.map(function (url) {
                return new Request(url, { mode: 'no-cors' });
            }));
        }).then(function () {
            return self.skipWaiting();
        })
    );
});

// ── Activate — clean old caches ──────────────────────
self.addEventListener('activate', function (e) {
    e.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(
                keys.filter(function (k) {
                    return k.startsWith('tcm-') && k !== STATIC_CACHE && k !== PAGE_CACHE;
                }).map(function (k) {
                    return caches.delete(k);
                })
            );
        }).then(function () {
            return self.clients.claim();
        })
    );
});

// ── Fetch strategy ───────────────────────────────────
self.addEventListener('fetch', function (e) {
    var req = e.request;
    var url = new URL(req.url);

    // Skip: non-GET, cross-origin (except CDN), chrome-extension etc.
    if (req.method !== 'GET') return;
    if (url.protocol !== 'https:' && url.hostname !== 'localhost' && !url.hostname.includes('127.0.0.1')) return;

    // API calls — always network, never cache
    if (url.pathname.startsWith('/api/') || url.pathname.startsWith('/auth/') ||
        url.pathname.startsWith('/admin') || url.pathname.startsWith('/student')) {
        return; // Let browser handle natively
    }

    // Static assets (.css, .js, .png, .jpg, .svg, .woff2) — cache first
    if (/\.(css|js|png|jpg|jpeg|gif|svg|ico|woff2?|ttf|webp)$/i.test(url.pathname)) {
        e.respondWith(
            caches.match(req).then(function (cached) {
                return cached || fetch(req).then(function (res) {
                    if (res && res.status === 200) {
                        var clone = res.clone();
                        caches.open(STATIC_CACHE).then(function (c) { c.put(req, clone); });
                    }
                    return res;
                });
            })
        );
        return;
    }

    // HTML pages — network first, fall back to cache, then offline page
    if (req.headers.get('Accept') && req.headers.get('Accept').includes('text/html')) {
        e.respondWith(
            fetch(req).then(function (res) {
                if (res && res.status === 200) {
                    var clone = res.clone();
                    caches.open(PAGE_CACHE).then(function (c) { c.put(req, clone); });
                }
                return res;
            }).catch(function () {
                return caches.match(req).then(function (cached) {
                    return cached || caches.match('/');
                });
            })
        );
        return;
    }
});
