<?php
/**
 * LinkShortner Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\View;

class LinkShortnerAdminController extends BaseController
{
    public function overview(): void
    {
        $db = Database::projectConnection('linkshortner');

        $stats = [
            'total_links'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM linkshortner_links"),
            'total_clicks'  => (int) ($db->fetchColumn("SELECT SUM(total_clicks) FROM linkshortner_links") ?: 0),
            'total_users'   => (int) $db->fetchColumn("SELECT COUNT(DISTINCT user_id) FROM linkshortner_links"),
            'links_today'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM linkshortner_links WHERE DATE(created_at) = CURDATE()"),
            'active_links'  => (int) $db->fetchColumn("SELECT COUNT(*) FROM linkshortner_links WHERE status = 'active'"),
        ];

        $recentLinks = $db->fetchAll("SELECT * FROM linkshortner_links ORDER BY created_at DESC LIMIT 15");
        $topLinks    = $db->fetchAll("SELECT * FROM linkshortner_links ORDER BY total_clicks DESC LIMIT 10");

        $dailyClicks = $db->fetchAll(
            "SELECT DATE(clicked_at) as day, COUNT(*) as cnt FROM linkshortner_clicks WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC"
        );

        View::render('admin/linkshortner/overview', [
            'title'       => 'LinkShortner Overview',
            'stats'       => $stats,
            'recentLinks' => $recentLinks,
            'topLinks'    => $topLinks,
            'dailyClicks' => $dailyClicks,
        ]);
    }

    public function links(): void
    {
        $db    = Database::projectConnection('linkshortner');
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 30;
        $offset = ($page - 1) * $limit;

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM linkshortner_links");
        $links = $db->fetchAll("SELECT * FROM linkshortner_links ORDER BY created_at DESC LIMIT ? OFFSET ?", [$limit, $offset]);

        View::render('admin/linkshortner/links', [
            'title'      => 'All Links',
            'links'      => $links,
            'total'      => $total,
            'page'       => $page,
            'totalPages' => (int) ceil($total / $limit),
        ]);
    }

    public function deleteLink(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/projects/linkshortner/links');
            exit;
        }

        $db = Database::projectConnection('linkshortner');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $db->query("DELETE FROM linkshortner_links WHERE id = ?", [$id]);
        }

        $_SESSION['success'] = 'Link deleted.';
        header('Location: /admin/projects/linkshortner/links');
        exit;
    }

    public function analytics(): void
    {
        $db = Database::projectConnection('linkshortner');

        $deviceStats = $db->fetchAll(
            "SELECT device, COUNT(*) as cnt FROM linkshortner_clicks GROUP BY device ORDER BY cnt DESC"
        );

        $countryStats = $db->fetchAll(
            "SELECT country, COUNT(*) as cnt FROM linkshortner_clicks WHERE country IS NOT NULL GROUP BY country ORDER BY cnt DESC LIMIT 15"
        );

        $dailyClicks = $db->fetchAll(
            "SELECT DATE(clicked_at) as day, COUNT(*) as cnt FROM linkshortner_clicks WHERE clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC"
        );

        View::render('admin/linkshortner/analytics', [
            'title'        => 'Analytics',
            'deviceStats'  => $deviceStats,
            'countryStats' => $countryStats,
            'dailyClicks'  => $dailyClicks,
        ]);
    }

    public function users(): void
    {
        $db    = Database::projectConnection('linkshortner');
        $users = $db->fetchAll(
            "SELECT user_id, COUNT(*) as link_count, SUM(total_clicks) as total_clicks, MAX(created_at) as last_link_at FROM linkshortner_links GROUP BY user_id ORDER BY link_count DESC"
        );

        View::render('admin/linkshortner/users', [
            'title' => 'Users',
            'users' => $users,
        ]);
    }

    public function settings(): void
    {
        View::render('admin/linkshortner/settings', [
            'title' => 'Settings',
        ]);
    }
}
