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
        $totalSubmissions = 0;
        $recentForms      = [];

        try {
            $totalForms = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_forms"
            );
            $activeForms = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_forms WHERE status = 'active'"
            );
            $totalSubmissions = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_submissions"
            );
            $recentForms = $this->db->fetchAll(
                "SELECT * FROM formx_forms ORDER BY created_at DESC LIMIT 5"
            );
        } catch (\Exception $e) {
            // tables may not exist yet — show empty state
        }

        View::render('projects/formx/dashboard', [
            'title'            => 'FormX Dashboard',
            'totalForms'       => $totalForms,
            'activeForms'      => $activeForms,
            'totalSubmissions' => $totalSubmissions,
            'recentForms'      => $recentForms,
        ]);
    }
}
