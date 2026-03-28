<?php
/**
 * ResumeX Dashboard Controller
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Projects\ResumeX\Models\ResumeModel;

class DashboardController
{
    private ResumeModel $resumeModel;

    public function __construct()
    {
        $this->resumeModel = new ResumeModel();
    }

    /**
     * Show the ResumeX dashboard with saved resumes
     */
    public function index(): void
    {
        $user   = Auth::user();
        $userId = Auth::id();

        $resumes = [];
        $stats   = ['total' => 0, 'templates_used' => 0, 'last_updated' => null];

        if ($userId) {
            try {
                $resumes = $this->resumeModel->getAll($userId);
                $stats['total'] = count($resumes);

                // Count unique templates used
                $templatesUsed = array_unique(array_column($resumes, 'template'));
                $stats['templates_used'] = count($templatesUsed);

                // Last updated resume
                if (!empty($resumes)) {
                    $stats['last_updated'] = $resumes[0]['updated_at'];
                }
            } catch (\Exception $e) {
                \Core\Logger::error('ResumeX DashboardController::index error: ' . $e->getMessage());
            }
        }

        // Parse theme settings for display
        foreach ($resumes as &$resume) {
            $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true);
            $resume['theme_name']   = $themeSettings['name'] ?? ucfirst(str_replace('-', ' ', $resume['template'] ?? 'Default'));
            $resume['primaryColor'] = $themeSettings['primaryColor'] ?? '#00f0ff';
        }
        unset($resume);

        $allThemes = $this->resumeModel->getAllThemePresets();

        // Load relevant admin feature flags
        $featureSettings = $this->loadFeatureSettings();

        View::render('projects/resumex/dashboard', [
            'title'           => 'ResumeX - Dashboard',
            'user'            => $user,
            'resumes'         => $resumes,
            'stats'           => $stats,
            'allThemes'       => $allThemes,
            'featureSettings' => $featureSettings,
        ]);
    }

    /**
     * Load the admin feature flags relevant to the user-facing dashboard.
     */
    private function loadFeatureSettings(): array
    {
        $defaults = [
            'resumex_public_resumes'  => '1',
            'resumex_linkedin_import' => '1',
            'resumex_custom_domain'   => '0',
            'resumex_max_resumes_free' => '3',
            'resumex_max_resumes_pro'  => '0',
        ];

        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT `key`, `value` FROM settings WHERE `key` IN ('resumex_public_resumes','resumex_linkedin_import','resumex_custom_domain','resumex_max_resumes_free','resumex_max_resumes_pro')"
            );
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
            }
        } catch (\Exception $e) {
            // Use defaults on error
        }

        return $defaults;
    }
}
