<?php
/**
 * LinkShortner Analytics Controller
 *
 * @package MMB\Projects\LinkShortner\Controllers
 */

namespace Projects\LinkShortner\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class AnalyticsController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');

        $totalClicks = (int) ($db->fetchColumn(
            "SELECT SUM(total_clicks) FROM linkshortner_links WHERE user_id = ?", [$user['id']]
        ) ?: 0);

        $clicksToday = (int) ($db->fetchColumn(
            "SELECT COUNT(*) FROM linkshortner_clicks lc
             JOIN linkshortner_links sl ON sl.id = lc.link_id
             WHERE sl.user_id = ? AND DATE(lc.clicked_at) = CURDATE()",
            [$user['id']]
        ) ?: 0);

        $topLinks = $db->fetchAll(
            "SELECT code, title, original_url, total_clicks, unique_clicks FROM linkshortner_links WHERE user_id = ? ORDER BY total_clicks DESC LIMIT 10",
            [$user['id']]
        );

        $deviceStats = $db->fetchAll(
            "SELECT lc.device, COUNT(*) as cnt FROM linkshortner_clicks lc
             JOIN linkshortner_links sl ON sl.id = lc.link_id
             WHERE sl.user_id = ?
             GROUP BY lc.device ORDER BY cnt DESC",
            [$user['id']]
        );

        $countryStats = $db->fetchAll(
            "SELECT lc.country, COUNT(*) as cnt FROM linkshortner_clicks lc
             JOIN linkshortner_links sl ON sl.id = lc.link_id
             WHERE sl.user_id = ? AND lc.country IS NOT NULL
             GROUP BY lc.country ORDER BY cnt DESC LIMIT 10",
            [$user['id']]
        );

        // Clicks per day – last 30 days
        $dailyClicks = $db->fetchAll(
            "SELECT DATE(lc.clicked_at) as day, COUNT(*) as cnt FROM linkshortner_clicks lc
             JOIN linkshortner_links sl ON sl.id = lc.link_id
             WHERE sl.user_id = ? AND lc.clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY day ORDER BY day ASC",
            [$user['id']]
        );

        View::render('projects/linkshortner/analytics', [
            'title'        => 'Analytics',
            'subtitle'     => 'Click tracking & insights',
            'totalClicks'  => $totalClicks,
            'clicksToday'  => $clicksToday,
            'topLinks'     => $topLinks,
            'deviceStats'  => $deviceStats,
            'countryStats' => $countryStats,
            'dailyClicks'  => $dailyClicks,
        ]);
    }

    public function show(string $code): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');
        $link = $db->fetch("SELECT * FROM linkshortner_links WHERE code = ? AND user_id = ?", [$code, $user['id']]);
        if (!$link) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $deviceStats = $db->fetchAll(
            "SELECT device, COUNT(*) as cnt FROM linkshortner_clicks WHERE link_id = ? GROUP BY device ORDER BY cnt DESC",
            [$link['id']]
        );

        $countryStats = $db->fetchAll(
            "SELECT country, COUNT(*) as cnt FROM linkshortner_clicks WHERE link_id = ? AND country IS NOT NULL GROUP BY country ORDER BY cnt DESC LIMIT 10",
            [$link['id']]
        );

        $referrerStats = $db->fetchAll(
            "SELECT referer, COUNT(*) as cnt FROM linkshortner_clicks WHERE link_id = ? AND referer IS NOT NULL GROUP BY referer ORDER BY cnt DESC LIMIT 10",
            [$link['id']]
        );

        $dailyClicks = $db->fetchAll(
            "SELECT DATE(clicked_at) as day, COUNT(*) as cnt FROM linkshortner_clicks WHERE link_id = ? AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY day ORDER BY day ASC",
            [$link['id']]
        );

        $recentClicks = $db->fetchAll(
            "SELECT * FROM linkshortner_clicks WHERE link_id = ? ORDER BY clicked_at DESC LIMIT 20",
            [$link['id']]
        );

        View::render('projects/linkshortner/analytics-detail', [
            'title'         => 'Link Analytics – ' . ($link['title'] ?? $link['code']),
            'subtitle'      => 'Detailed click stats',
            'link'          => $link,
            'deviceStats'   => $deviceStats,
            'countryStats'  => $countryStats,
            'referrerStats' => $referrerStats,
            'dailyClicks'   => $dailyClicks,
            'recentClicks'  => $recentClicks,
        ]);
    }
}
