<?php

declare(strict_types=1);

namespace TCM\Controllers\Student;

use TCM\Core\Auth;
use TCM\Core\Controller;
use TCM\Core\Database;
use TCM\Core\Request;
use TCM\Models\Portfolio;

/**
 * Student Community — browse fellow learners, view profiles.
 */
final class CommunityController extends Controller
{
    /**
     * Browse all students (cookie-card grid).
     * GET /student/community
     */
    public function browse(): void
    {
        $me = Auth::require('student');

        $search = Request::string('q');
        $filter = Request::string('filter'); // 'all' | 'beginner' | 'intermediate' | 'advanced'

        $sql = "SELECT u.id, u.name, u.avatar,
                       sp.headline, sp.location, sp.experience_level,
                       sp.github_url, sp.linkedin_url, sp.goal,
                       (SELECT COUNT(*) FROM portfolio_projects pp WHERE pp.user_id = u.id) AS project_count,
                       (SELECT COUNT(*) FROM portfolio_skills  ps WHERE ps.user_id = u.id) AS skill_count,
                       (SELECT COUNT(*) FROM certificates       c  WHERE c.user_id  = u.id) AS cert_count
                FROM users u
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                WHERE u.role = 'student'
                  AND u.status = 'active'
                  AND u.id != ?
                  AND u.onboarded = 1";

        $params = [(int) $me['id']];

        if ($search !== '') {
            $sql .= " AND (u.name LIKE ? OR sp.headline LIKE ? OR sp.location LIKE ?)";
            $like = '%' . $search . '%';
            $params[] = $like;
            $params[] = $like;
            $params[] = $like;
        }

        if (in_array($filter, ['beginner', 'intermediate', 'advanced'], true)) {
            $sql .= " AND sp.experience_level = ?";
            $params[] = $filter;
        }

        $sql .= " ORDER BY u.created_at DESC LIMIT 60";

        $students = Database::all($sql, $params);

        $this->view('student/community/browse', [
            'title'    => 'Community',
            'me'       => $me,
            'students' => $students,
            'search'   => $search,
            'filter'   => $filter,
        ], 'student');
    }

    /**
     * View another student's public profile card (modal data).
     * GET /student/community/{id}
     */
    public function profile(array $params): void
    {
        $me      = Auth::require('student');
        $userId  = (int) $params['id'];

        if ($userId === (int) $me['id']) {
            redirect('/student/portfolio');
        }

        $student = Database::first(
            "SELECT u.id, u.name, u.avatar, u.created_at,
                    sp.headline, sp.bio, sp.location, sp.experience_level,
                    sp.goal, sp.github_url, sp.linkedin_url, sp.website_url, sp.twitter_url,
                    sp.college, sp.graduation_year, sp.banner
             FROM users u
             LEFT JOIN student_profiles sp ON sp.user_id = u.id
             WHERE u.id = ? AND u.role = 'student' AND u.status = 'active'",
            [$userId]
        );

        if ($student === null) {
            flash('error', 'Student not found.');
            redirect('/student/community');
        }

        $projects     = Portfolio::projects($userId);
        $skills       = Portfolio::skills($userId);
        $achievements = Portfolio::achievements($userId);
        $certificates = Portfolio::certificates($userId);

        // Peer cookies & reviews
        $cookieData = ['total' => 0, 'avg' => 0, 'reviews' => []];
        try {
            $cookieExists = (bool) \TCM\Core\Database::scalar(
                "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='peer_cookies'"
            );
            if ($cookieExists) {
                $row = \TCM\Core\Database::first(
                    "SELECT COUNT(*) AS cnt, COALESCE(SUM(cookies),0) AS total FROM peer_cookies WHERE to_user_id = ?",
                    [$userId]
                );
                $cookieData['total'] = (int)($row['total'] ?? 0);
                $cookieData['avg']   = $row['cnt'] > 0 ? round($row['total'] / $row['cnt'], 1) : 0;
                $cookieData['reviews'] = \TCM\Core\Database::all(
                    "SELECT pc.cookies, pc.review, pc.created_at, u.name AS from_name, u.avatar AS from_avatar
                     FROM peer_cookies pc JOIN users u ON u.id = pc.from_user_id
                     WHERE pc.to_user_id = ? AND pc.review IS NOT NULL AND pc.review != ''
                     ORDER BY pc.created_at DESC LIMIT 6",
                    [$userId]
                );
            }
        } catch (\Throwable $e) {}

        $this->view('student/community/profile', [
            'title'        => $student['name'],
            'me'           => $me,
            'student'      => $student,
            'projects'     => $projects,
            'skills'       => $skills,
            'achievements' => $achievements,
            'certificates' => $certificates,
            'cookieData'   => $cookieData,
        ], 'student');
    }
}
