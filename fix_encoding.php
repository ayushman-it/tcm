<?php
/**
 * Fix broken UTF-8 encoding + dummy page links
 * Visit: http://localhost/tcm/tcm-2.0/fix_encoding.php
 * Self-deletes after running.
 */
$log = [];

// ── Helper ─────────────────────────────────────────────────────────
function fixFile(string $file, array $replacements): array {
    if (!file_exists($file)) return [['warn', "$file not found"]];
    $c = file_get_contents($file);
    $changes = 0;
    foreach ($replacements as [$from, $to]) {
        $new = str_replace($from, $to, $c);
        if ($new !== $c) { $changes++; $c = $new; }
    }
    file_put_contents($file, $c);
    return [['ok', basename($file) . " — $changes replacements made"]];
}

// ── Broken UTF-8 byte sequences ─────────────────────────────────────
$rupee  = "\xe2\x82\xb9";   // â‚¹ = ₹
$arrow  = "\xe2\x86\x92";   // â†' = →
$mdash  = "\xe2\x80\x94";   // â€" = —
$ndash  = "\xe2\x80\x93";   // â€" = –
$times  = "\xc3\x97";       // Ã× = ×
$rsquo  = "\xe2\x80\x99";   // â€™ = '
$lsquo  = "\xe2\x80\x98";   // â€˜ = '
$rdquo  = "\xe2\x80\x9d";   // â€ = "
$ldquo  = "\xe2\x80\x9c";   // â€œ = "
$copy   = "\xc2\xa9";       // Â© = ©

$common = [
    [$rupee, '&#8377;'],
    [$arrow, '&rarr;'],
    [$mdash, '&mdash;'],
    [$ndash, '&ndash;'],
    [$times, '&times;'],
    [$rsquo, "'"],
    [$lsquo, "'"],
    [$rdquo, '"'],
    [$ldquo, '"'],
    [$copy,  '&copy;'],
    // double-encoded versions
    ['â‚¹',  '&#8377;'],
    ['â†"',  '&rarr;'],
    ['â€"',  '&mdash;'],
    ['â€"',  '&ndash;'],
    ['Ã—',   '&times;'],
    ['Â©',   '&copy;'],
];

$base = __DIR__;

// Fix all HTML files
$htmlFiles = [
    "$base/index.html",
    "$base/programs.html",
    "$base/insights.html",
    "$base/contact.html",
    "$base/community.html",
    "$base/event-details.html",
    "$base/course-details.html",
];

foreach ($htmlFiles as $f) {
    $rows = fixFile($f, $common);
    foreach ($rows as $r) $log[] = $r;
}

// ── Fix dummy links → real pages ────────────────────────────────────
// event-details.html → /student/events  (for logged-in), /programs.html (for public)
// course-details.html → /student/courses

$linkFixes = [
    'index.html' => [
        // event links → programs page (public facing)
        ['href="event-details.html"',  'href="programs.html"'],
        // course links → programs page
        ['href="course-details.html"', 'href="programs.html"'],
    ],
    'programs.html' => [
        ['href="event-details.html"',  'href="programs.html#events"'],
        ['href="course-details.html"', 'href="programs.html#courses"'],
    ],
    'event-details.html' => [
        // keep the enroll buttons pointing to student events
        // no change needed — this page is static showcase
    ],
    'course-details.html' => [
        // Enroll Now buttons → login/register
        ['href="#" class="cd-enroll-btn"', 'href="/auth/login" class="cd-enroll-btn"'],
    ],
];

foreach ($linkFixes as $fname => $replacements) {
    if (empty($replacements)) continue;
    $rows = fixFile("$base/$fname", $replacements);
    foreach ($rows as $r) $log[] = $r;
}

// ── Self-delete ─────────────────────────────────────────────────────
unlink(__FILE__);
$log[] = ['ok', 'fix_encoding.php deleted'];

?><!DOCTYPE html>
<html>
<head><title>Fix Complete</title>
<style>
body{font-family:-apple-system,sans-serif;background:#f5f5f5;padding:32px}
.card{background:#fff;border:1px solid #e5e5e5;border-radius:14px;max-width:560px;padding:24px}
h2{margin-bottom:16px;font-size:1.1rem}
ul{list-style:none;padding:0}
li{padding:7px 0;border-bottom:1px solid #f5f5f5;font-size:.88rem}
a{display:inline-block;margin-top:18px;padding:10px 20px;background:#111;color:#fff;border-radius:8px;text-decoration:none;font-weight:600}
.ok{color:#16a34a}.warn{color:#d97706}.bad{color:#dc2626}
</style>
</head>
<body>
<div class="card">
<h2>&#9889; Encoding + Link Fixes Complete</h2>
<ul>
<?php foreach ($log as [$t, $m]): ?>
<li class="<?= $t ?>">
    <?= $t==='ok'?'&#10003;':($t==='warn'?'&#9888;':'&#10007;') ?> <?= htmlspecialchars($m) ?>
</li>
<?php endforeach; ?>
</ul>
<a href="/">&#8592; View Homepage</a>
</div>
</body>
</html>
