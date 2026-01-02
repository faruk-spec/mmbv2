<?php
/**
 * CodeXPro Dashboard Controller
 * 
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;

class DashboardController
{
    /**
     * Show dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('codexpro');
        
        // Get user's recent projects
        $projects = $db->fetchAll(
            "SELECT * FROM projects WHERE user_id = ? ORDER BY updated_at DESC LIMIT 6",
            [$user['id']]
        );
        
        // Get user's recent snippets
        $snippets = $db->fetchAll(
            "SELECT * FROM snippets WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$user['id']]
        );
        
        // Get stats
        $stats = [
            'total_projects' => $db->fetchColumn("SELECT COUNT(*) FROM projects WHERE user_id = ?", [$user['id']]),
            'total_snippets' => $db->fetchColumn("SELECT COUNT(*) FROM snippets WHERE user_id = ?", [$user['id']]),
            'total_templates' => $db->fetchColumn("SELECT COUNT(*) FROM templates", []),
            'recent_edits' => $db->fetchColumn("SELECT COUNT(*) FROM projects WHERE user_id = ? AND updated_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)", [$user['id']]) ?: 0,
        ];
        
        View::render('projects/codexpro/dashboard', [
            'recentProjects' => $projects,
            'recentSnippets' => $snippets,
            'stats' => $stats,
        ]);
    }
}
