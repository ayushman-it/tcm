<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;
use TCM\Core\Upload;
use TCM\Models\Portfolio;

final class PortfolioController extends Controller
{
    // ── Portfolio edit page ───────────────────────────────────────────
    public function index(): void
    {
        $user = Auth::require('student');
        $uid  = (int) $user['id'];
        $this->ensureBannerColumn();
        $profile = Database::first('SELECT * FROM student_profiles WHERE user_id = ?', [$uid]) ?? [];

        $this->view('student/portfolio/index', [
            'title'        => 'My Portfolio',
            'user'         => $user,
            'profile'      => $profile,
            'projects'     => Portfolio::projects($uid),
            'skills'       => Portfolio::skills($uid),
            'achievements' => Portfolio::achievements($uid),
            'certificates' => Portfolio::certificates($uid),
            'strength'     => Portfolio::strength($uid),
        ], 'student');
    }

    // ── Public shareable portfolio page (no auth) ─────────────────────
    public function publicView(array $params): void
    {
        $userId = (int) $params['id'];
        $user   = Database::first(
            "SELECT id, name, avatar FROM users WHERE id = ? AND role = 'student'",
            [$userId]
        );
        if ($user === null) {
            http_response_code(404);
            $this->view('errors/404', [], 'public');
            return;
        }
        $this->ensureBannerColumn();
        $profile = Database::first('SELECT * FROM student_profiles WHERE user_id = ?', [$userId]) ?? [];

        // Peer cookies
        $cookieData = ['total' => 0, 'avg' => 0, 'reviews' => []];
        try {
            $cookieExists = (bool) Database::scalar(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='peer_cookies'"
            );
            if ($cookieExists) {
                $row = Database::first(
                    "SELECT COUNT(*) AS cnt, COALESCE(SUM(cookies),0) AS total FROM peer_cookies WHERE to_user_id = ?",
                    [$userId]
                );
                $cookieData['total']   = (int)($row['total'] ?? 0);
                $cookieData['avg']     = ($row['cnt'] ?? 0) > 0 ? round($row['total'] / $row['cnt'], 1) : 0;
                $cookieData['reviews'] = Database::all(
                    "SELECT pc.cookies, pc.review, pc.created_at, u.name AS from_name, u.avatar AS from_avatar
                     FROM peer_cookies pc JOIN users u ON u.id = pc.from_user_id
                     WHERE pc.to_user_id = ? AND pc.review IS NOT NULL AND pc.review != ''
                     ORDER BY pc.created_at DESC LIMIT 6",
                    [$userId]
                );
            }
        } catch (\Throwable $e) {}

        $this->view('student/portfolio/public', [
            'title'        => $user['name'] . ' — Portfolio · TCM',
            'owner'        => $user,
            'profile'      => $profile,
            'projects'     => Portfolio::projects($userId),
            'skills'       => Portfolio::skills($userId),
            'achievements' => Portfolio::achievements($userId),
            'certificates' => Portfolio::certificates($userId),
            'cookieData'   => $cookieData,
        ], 'public');
    }

    // ── Banner upload ─────────────────────────────────────────────────
    public function uploadBanner(): void
    {
        $user = Auth::require('student');
        $this->ensureBannerColumn();

        if (!Upload::present($_FILES['banner'] ?? null)) {
            flash('error', 'Please select an image file.');
            redirect('/student/portfolio');
        }

        $uploadDir = config('uploads.path') . '/banners';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0775, true); }

        try {
            $path = Upload::store(
                $_FILES['banner'],
                $uploadDir,
                ['jpg', 'jpeg', 'png', 'webp'],
                4 * 1024 * 1024
            );
        } catch (\RuntimeException $e) {
            flash('error', 'Upload failed: ' . $e->getMessage());
            redirect('/student/portfolio');
        }

        Database::run(
            'UPDATE student_profiles SET banner = ? WHERE user_id = ?',
            [$path, (int) $user['id']]
        );

        flash('success', 'Banner updated!');
        redirect('/student/portfolio');
    }

    // ── Remove banner ─────────────────────────────────────────────────
    public function removeBanner(): void
    {
        $user = Auth::require('student');
        $this->ensureBannerColumn();
        Database::run(
            'UPDATE student_profiles SET banner = NULL WHERE user_id = ?',
            [(int) $user['id']]
        );
        flash('success', 'Banner removed.');
        redirect('/student/portfolio');
    }

    // ── Projects ─────────────────────────────────────────────────────
    public function storeProject(): void
    {
        $user = Auth::require('student');
        $this->validate([
            'title'    => 'required|max:180',
            'repo_url' => 'url',
            'live_url' => 'url',
        ], '/student/portfolio');

        Database::insert('portfolio_projects', [
            'user_id'     => (int) $user['id'],
            'title'       => Request::string('title'),
            'description' => Request::string('description'),
            'tech_stack'  => Request::string('tech_stack'),
            'repo_url'    => Request::string('repo_url'),
            'live_url'    => Request::string('live_url'),
            'is_featured' => Request::int('is_featured'),
        ]);
        $this->respond(null, 'Project added.', '/student/portfolio');
    }

    public function deleteProject(array $params): void
    {
        $user = Auth::require('student');
        Database::delete('portfolio_projects', ['id' => (int) $params['id'], 'user_id' => (int) $user['id']]);
        $this->respond(null, 'Project removed.', '/student/portfolio');
    }

    // ── Skills ───────────────────────────────────────────────────────
    public function storeSkill(): void
    {
        $user = Auth::require('student');
        $this->validate(['name' => 'required|max:80'], '/student/portfolio');
        Database::insert('portfolio_skills', [
            'user_id' => (int) $user['id'],
            'name'    => Request::string('name'),
            'level'   => min(100, max(0, Request::int('level', 50))),
        ]);
        $this->respond(null, 'Skill added.', '/student/portfolio');
    }

    public function deleteSkill(array $params): void
    {
        $user = Auth::require('student');
        Database::delete('portfolio_skills', ['id' => (int) $params['id'], 'user_id' => (int) $user['id']]);
        $this->respond(null, 'Skill removed.', '/student/portfolio');
    }

    // ── Achievements ─────────────────────────────────────────────────
    public function storeAchievement(): void
    {
        $user = Auth::require('student');
        $this->validate(['title' => 'required|max:180', 'url' => 'url'], '/student/portfolio');
        Database::insert('portfolio_achievements', [
            'user_id'     => (int) $user['id'],
            'title'       => Request::string('title'),
            'issuer'      => Request::string('issuer'),
            'description' => Request::string('description'),
            'url'         => Request::string('url'),
            'achieved_on' => Request::string('achieved_on') ?: null,
        ]);
        $this->respond(null, 'Achievement added.', '/student/portfolio');
    }

    public function deleteAchievement(array $params): void
    {
        $user = Auth::require('student');
        Database::delete('portfolio_achievements', ['id' => (int) $params['id'], 'user_id' => (int) $user['id']]);
        $this->respond(null, 'Achievement removed.', '/student/portfolio');
    }

    // ── Private helpers ───────────────────────────────────────────────

    /**
     * Dynamic OG image — returns an SVG branded card for this student.
     * GET /portfolio/{id}/og-image
     * Used as og:image fallback when no avatar/banner uploaded.
     */
    public function ogImage(array $params): void
    {
        $userId = (int) $params['id'];
        $user   = Database::first(
            "SELECT id, name, avatar FROM users WHERE id = ? AND role = 'student'",
            [$userId]
        );

        $name     = $user ? htmlspecialchars($user['name'] ?? 'Developer', ENT_XML1) : 'Developer';
        $profile  = Database::first('SELECT headline, experience_level FROM student_profiles WHERE user_id = ?', [$userId]) ?? [];
        $headline = htmlspecialchars($profile['headline'] ?? 'Developer at The Code Munk', ENT_XML1);
        $level    = ucfirst($profile['experience_level'] ?? '');

        // Get counts
        $pCount = (int) Database::scalar('SELECT COUNT(*) FROM portfolio_projects WHERE user_id = ?', [$userId]);
        $sCount = (int) Database::scalar('SELECT COUNT(*) FROM portfolio_skills  WHERE user_id = ?', [$userId]);
        $cCount = (int) Database::scalar('SELECT COUNT(*) FROM certificates      WHERE user_id = ?', [$userId]);

        // Initial letter for avatar placeholder
        $initial = strtoupper(substr($user['name'] ?? 'D', 0, 1));

        header('Content-Type: image/svg+xml');
        header('Cache-Control: public, max-age=86400'); // cache 24h
        echo <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="630" viewBox="0 0 1200 630">
  <defs>
    <radialGradient id="bg" cx="75%" cy="25%" r="70%">
      <stop offset="0%" stop-color="#1a1a1a"/>
      <stop offset="100%" stop-color="#0a0a0a"/>
    </radialGradient>
    <pattern id="dots" x="0" y="0" width="22" height="22" patternUnits="userSpaceOnUse">
      <circle cx="1" cy="1" r="1" fill="rgba(255,255,255,0.045)"/>
    </pattern>
    <clipPath id="avatarClip"><circle cx="940" cy="295" r="130"/></clipPath>
  </defs>

  <!-- Background -->
  <rect width="1200" height="630" fill="url(#bg)"/>
  <rect width="1200" height="630" fill="url(#dots)"/>
  <ellipse cx="900" cy="100" rx="400" ry="250" fill="rgba(255,255,255,0.025)"/>

  <!-- Left content -->
  <!-- TCM brand -->
  <rect x="80" y="72" width="48" height="48" rx="12" fill="#fff"/>
  <text x="104" y="105" font-family="system-ui,sans-serif" font-size="20" font-weight="900" fill="#111" text-anchor="middle">T</text>
  <text x="142" y="95" font-family="system-ui,sans-serif" font-size="15" font-weight="700" fill="rgba(255,255,255,0.45)" letter-spacing="1.5">THE CODE MUNK</text>
  <text x="142" y="113" font-family="system-ui,sans-serif" font-size="12" fill="rgba(255,255,255,0.25)">thecodemunk.in</text>

  <!-- Divider line -->
  <line x1="80" y1="148" x2="680" y2="148" stroke="rgba(255,255,255,0.08)" stroke-width="1"/>

  <!-- Name -->
  <text x="80" y="220" font-family="system-ui,sans-serif" font-size="52" font-weight="900" fill="#ffffff" letter-spacing="-1.5">{$name}</text>

  <!-- Headline -->
  <text x="80" y="265" font-family="system-ui,sans-serif" font-size="20" font-weight="400" fill="rgba(255,255,255,0.5)">{$headline}</text>

  <!-- Level badge -->
  {$this->ogLevelBadge($level)}

  <!-- Stats row -->
  <rect x="80" y="380" width="130" height="64" rx="12" fill="rgba(255,255,255,0.07)"/>
  <text x="145" y="407" font-family="system-ui,sans-serif" font-size="26" font-weight="900" fill="#fff" text-anchor="middle">{$pCount}</text>
  <text x="145" y="431" font-family="system-ui,sans-serif" font-size="12" font-weight="600" fill="rgba(255,255,255,0.4)" text-anchor="middle">PROJECTS</text>

  <rect x="224" y="380" width="120" height="64" rx="12" fill="rgba(255,255,255,0.07)"/>
  <text x="284" y="407" font-family="system-ui,sans-serif" font-size="26" font-weight="900" fill="#fff" text-anchor="middle">{$sCount}</text>
  <text x="284" y="431" font-family="system-ui,sans-serif" font-size="12" font-weight="600" fill="rgba(255,255,255,0.4)" text-anchor="middle">SKILLS</text>

  <rect x="358" y="380" width="140" height="64" rx="12" fill="rgba(255,255,255,0.07)"/>
  <text x="428" y="407" font-family="system-ui,sans-serif" font-size="26" font-weight="900" fill="#fff" text-anchor="middle">{$cCount}</text>
  <text x="428" y="431" font-family="system-ui,sans-serif" font-size="12" font-weight="600" fill="rgba(255,255,255,0.4)" text-anchor="middle">CERTS</text>

  <!-- CTA -->
  <rect x="80" y="498" width="300" height="52" rx="14" fill="#ffffff"/>
  <text x="230" y="530" font-family="system-ui,sans-serif" font-size="16" font-weight="800" fill="#111" text-anchor="middle">View Portfolio →</text>

  <!-- Right: Avatar circle -->
  <circle cx="940" cy="295" r="148" fill="rgba(255,255,255,0.04)" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
  <circle cx="940" cy="295" r="130" fill="rgba(255,255,255,0.08)"/>
  <text x="940" y="330" font-family="system-ui,sans-serif" font-size="96" font-weight="900" fill="rgba(255,255,255,0.2)" text-anchor="middle">{$initial}</text>

  <!-- Bottom bar -->
  <rect x="0" y="618" width="1200" height="12" fill="rgba(255,255,255,0.05)"/>
</svg>
SVG;
        exit;
    }

    /** Helper: render level badge SVG snippet */
    private function ogLevelBadge(string $level): string
    {
        if (!$level) return '';
        $emoji = match($level) {
            'Beginner'     => '🌱',
            'Intermediate' => '⚡',
            'Advanced'     => '🔥',
            default        => ''
        };
        $text = $emoji ? "$emoji $level" : $level;
        return <<<SVG
  <rect x="80" y="295" width="160" height="34" rx="17" fill="rgba(255,255,255,0.1)" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
  <text x="160" y="317" font-family="system-ui,sans-serif" font-size="13" font-weight="700" fill="rgba(255,255,255,0.7)" text-anchor="middle">{$text}</text>
SVG;
    }
    private function ensureBannerColumn(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        $exists = (bool) Database::scalar(
            "SELECT COUNT(*) FROM information_schema.columns
             WHERE table_schema = DATABASE()
               AND table_name   = 'student_profiles'
               AND column_name  = 'banner'"
        );
        if (!$exists) {
            Database::run(
                'ALTER TABLE student_profiles ADD COLUMN banner VARCHAR(255) DEFAULT NULL'
            );
        }
    }
}
