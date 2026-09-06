<?php

declare(strict_types=1);

/**
 * Global helper functions used across the application.
 */

if (!function_exists('config')) {
    /**
     * Read a value from the config array using dot notation, e.g. config('app.name').
     */
    function config(?string $key = null, mixed $default = null): mixed
    {
        if (!isset($GLOBALS['__TCM_CONFIG']) || !is_array($GLOBALS['__TCM_CONFIG'])) {
            require dirname(__DIR__, 2) . '/config/config.php';
        }
        $config = $GLOBALS['__TCM_CONFIG'] ?? [];
        if ($key === null) {
            return $config;
        }
        $segments = explode('.', $key);
        $value = $config;
        foreach ($segments as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default;
            }
        }
        return $value;
    }
}

if (!function_exists('e')) {
    /**
     * Escape a string for safe HTML output.
     */
    function e(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('base_url')) {
    /**
     * Build an absolute-ish URL respecting the configured base path.
     */
    function base_url(string $path = ''): string
    {
        $base = (string) config('app.base_path', '');
        $path = '/' . ltrim($path, '/');
        return $base . ($path === '/' ? '' : $path);
    }
}

if (!function_exists('redirect')) {
    /**
     * Send a redirect header and stop execution.
     */
    function redirect(string $path): never
    {
        $location = str_starts_with($path, 'http') ? $path : base_url($path);
        header('Location: ' . $location);
        exit;
    }
}

if (!function_exists('old')) {
    /**
     * Retrieve previously submitted form input flashed to the session.
     */
    function old(string $key, string $default = ''): string
    {
        return (string) ($_SESSION['_old'][$key] ?? $default);
    }
}

if (!function_exists('flash')) {
    /**
     * Set or get a one-time flash message.
     */
    function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}

if (!function_exists('slugify')) {
    /**
     * Create a URL-friendly slug from a string.
     */
    function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text) ?? '';
        $text = trim($text, '-');
        $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text) ?: $text;
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text) ?? '';
        return $text === '' ? 'item-' . substr(md5((string) microtime(true)), 0, 8) : $text;
    }
}

if (!function_exists('money')) {
    /**
     * Format an amount as INR currency.
     */
    function money(float|int|string $amount): string
    {
        return '₹' . number_format((float) $amount, 0);
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Render a hidden CSRF input field for forms.
     */
    function csrf_field(): string
    {
        $token = \TCM\Core\Csrf::token();
        return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
    }
}

if (!function_exists('tcm_avatar')) {
    /**
     * Return an <img> tag if avatar exists, else a styled <div> with initials.
     * Drop-in replacement wherever we show user avatars.
     *
     * @param string|null $avatarFile  e.g. "avatar_123.jpg"  (filename only, no path)
     * @param string      $name        User's display name
     * @param string      $class       CSS classes on the wrapper
     * @param string      $style       Inline styles on the wrapper
     * @param string      $size        '56px' — used for font-size calculation
     */
    function tcm_avatar(
        ?string $avatarFile,
        string  $name,
        string  $class  = '',
        string  $style  = '',
        string  $imgAlt = ''
    ): string {
        // If the user uploaded a real photo, just show it
        if (!empty($avatarFile)) {
            $src = base_url('/uploads/' . $avatarFile);
            return '<img src="' . e($src) . '" alt="' . e($imgAlt ?: $name) . '"
                         style="width:100%;height:100%;object-fit:cover;display:block;">';
        }

        // Generate a deterministic gradient based on name
        $initial = strtoupper(substr(trim($name) ?: '?', 0, 1));
        $hash    = abs(crc32($name));

        // 12 tasteful dark-ish gradient pairs
        $palettes = [
            ['#1e3a5f','#2563eb'], // navy → blue
            ['#1a1a2e','#7c3aed'], // dark navy → purple
            ['#1e3a1e','#16a34a'], // dark green → green
            ['#3b1c1c','#dc2626'], // dark red → red
            ['#1c2b3b','#0891b2'], // dark slate → cyan
            ['#2d1b69','#a855f7'], // indigo → violet
            ['#1a2e1a','#22c55e'], // forest → emerald
            ['#2a1a00','#f59e0b'], // dark brown → amber
            ['#1c1c1c','#6b7280'], // charcoal → grey
            ['#1e1e3a','#3b82f6'], // dark blue → bright blue
            ['#2d0a3a','#ec4899'], // deep purple → pink
            ['#003a2e','#14b8a6'], // dark teal → teal
        ];

        [$from, $to] = $palettes[$hash % count($palettes)];

        return '<div style="
            width:100%;height:100%;
            background:linear-gradient(135deg,' . $from . ' 0%,' . $to . ' 100%);
            display:flex;align-items:center;justify-content:center;
            color:#fff;font-weight:800;font-size:inherit;
            letter-spacing:-.5px;user-select:none;
            ">' . e($initial) . '</div>';
    }
}
