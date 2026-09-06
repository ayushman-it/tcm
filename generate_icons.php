<?php
/**
 * TCM PWA Icon Generator
 * Generates PNG icons for all required PWA sizes using GD.
 * Run once: http://localhost/tcm/tcm-2.0/generate_icons.php
 * Self-deletes after running.
 */
$dir = __DIR__ . '/assets/icons';
if (!is_dir($dir)) mkdir($dir, 0775, true);

$sizes = [72, 96, 128, 144, 152, 192, 384, 512];
$log   = [];

foreach ($sizes as $size) {
    $img = imagecreatetruecolor($size, $size);

    // Background: #111111
    $bg  = imagecolorallocate($img, 17, 17, 17);
    imagefill($img, 0, 0, $bg);

    // Rounded corners via arc trick
    $r = (int)($size * 0.22);
    $corner = imagecolorallocate($img, 255, 255, 255); // use white then make transparent

    // Draw white "TCM" text centred
    $white = imagecolorallocate($img, 255, 255, 255);

    // Use built-in font — scale by size
    $fontSize = max(2, min(5, (int)($size / 40)));
    $text = 'TCM';
    $tw   = imagefontwidth($fontSize)  * strlen($text);
    $th   = imagefontheight($fontSize);
    $tx   = (int)(($size - $tw) / 2);
    $ty   = (int)(($size - $th) / 2);
    imagestring($img, $fontSize, $tx, $ty, $text, $white);

    // Add code symbol above text
    $codeText = '</>';
    $ctw = imagefontwidth(max(1,$fontSize-1)) * strlen($codeText);
    $ctx = (int)(($size - $ctw) / 2);
    $cty = (int)($ty - imagefontheight(max(1,$fontSize-1)) - 2);
    if ($cty > 2) {
        $accent = imagecolorallocate($img, 160, 160, 255);
        imagestring($img, max(1,$fontSize-1), $ctx, $cty, $codeText, $accent);
    }

    $path = "$dir/icon-$size.png";
    imagepng($img, $path);
    imagedestroy($img);
    $log[] = "✅ icon-$size.png";
}

unlink(__FILE__);
$log[] = '🗑️ generate_icons.php deleted';
foreach ($log as $l) echo $l . PHP_EOL;
?><!DOCTYPE html><html><body>
<h2 style="font-family:sans-serif">✅ Icons generated!</h2>
<ul style="font-family:monospace">
<?php foreach ($log as $l): ?><li><?= htmlspecialchars($l) ?></li><?php endforeach; ?>
</ul>
<p><a href="/">← Home</a></p>
</body></html>
