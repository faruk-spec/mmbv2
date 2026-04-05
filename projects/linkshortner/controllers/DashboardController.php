<?php
/**
 * LinkShortner Dashboard Controller
 *
 * @package MMB\Projects\LinkShortner\Controllers
 */

namespace Projects\LinkShortner\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class DashboardController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');

        $stats = [
            'total_links'   => (int) $db->fetchColumn("SELECT COUNT(*) FROM short_links WHERE user_id = ?", [$user['id']]),
            'total_clicks'  => (int) ($db->fetchColumn("SELECT SUM(total_clicks) FROM short_links WHERE user_id = ?", [$user['id']]) ?: 0),
            'active_links'  => (int) $db->fetchColumn("SELECT COUNT(*) FROM short_links WHERE user_id = ? AND status = 'active'", [$user['id']]),
            'links_today'   => (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM short_links WHERE user_id = ? AND DATE(created_at) = CURDATE()",
                [$user['id']]
            ),
        ];

        $recentLinks = $db->fetchAll(
            "SELECT * FROM short_links WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
            [$user['id']]
        );

        $topLinks = $db->fetchAll(
            "SELECT * FROM short_links WHERE user_id = ? ORDER BY total_clicks DESC LIMIT 5",
            [$user['id']]
        );

        View::render('projects/linkshortner/dashboard', [
            'title'       => 'Dashboard',
            'subtitle'    => 'URL Shortener & Analytics',
            'stats'       => $stats,
            'recentLinks' => $recentLinks,
            'topLinks'    => $topLinks,
        ]);
    }
}
