<?php
/**
 * FormX Dashboard Controller
 *
 * @package MMB\Projects\FormX\Controllers
 */

namespace Projects\FormX\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;

class DashboardController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        $userId = Auth::id();

        $totalForms       = 0;
        $activeForms      = 0;
        $draftForms       = 0;
        $totalSubmissions = 0;
        $recentForms      = [];

        try {
            $totalForms = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_forms WHERE user_id = ?", [$userId]
            );
            $activeForms = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_forms WHERE user_id = ? AND status = 'active'", [$userId]
            );
            $draftForms = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_forms WHERE user_id = ? AND status = 'draft'", [$userId]
            );
            $totalSubmissions = (int) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(submissions_count),0) FROM formx_forms WHERE user_id = ?", [$userId]
            );
            $recentForms = $this->db->fetchAll(
                "SELECT * FROM formx_forms WHERE user_id = ? ORDER BY updated_at DESC LIMIT 6",
                [$userId]
            );

            // Daily submissions across all user forms – last 30 days
            $dailyChart = $this->db->fetchAll(
                "SELECT DATE(fs.created_at) AS day, COUNT(*) AS cnt
                 FROM formx_submissions fs
                 JOIN formx_forms ff ON ff.id = fs.form_id
                 WHERE ff.user_id = ?
                   AND fs.created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
                 GROUP BY DATE(fs.created_at) ORDER BY day ASC",
                [$userId]
            );

            // This month vs last 30 days
            $thisMonth = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_submissions fs
                 JOIN formx_forms ff ON ff.id = fs.form_id
                 WHERE ff.user_id = ?
                   AND YEAR(fs.created_at)=YEAR(NOW()) AND MONTH(fs.created_at)=MONTH(NOW())",
                [$userId]
            );
        } catch (\Exception $e) {
            // tables may not exist yet — show empty state
            $dailyChart = [];
            $thisMonth  = 0;
        }

        View::render('projects/formx/dashboard', [
            'title'            => 'FormX – Form Builder',
            'totalForms'       => $totalForms,
            'activeForms'      => $activeForms,
            'draftForms'       => $draftForms,
            'totalSubmissions' => $totalSubmissions,
            'recentForms'      => $recentForms,
            'dailyChart'       => $dailyChart,
            'thisMonth'        => $thisMonth,
            'activePage'       => 'dashboard',
        ]);
    }
}
