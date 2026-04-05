<?php
/**
 * CardX Admin Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;

class IDCardAdminController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }

    // ------------------------------------------------------------------ //
    //  Lazy-load model (project may not always be bootstrapped)            //
    // ------------------------------------------------------------------ //

    private function model(): \Projects\IDCard\Models\IDCardModel
    {
        static $model = null;
        if (!$model) {
            $projectPath = BASE_PATH . '/projects/idcard';
            if (!defined('PROJECT_PATH')) {
                define('PROJECT_PATH', $projectPath);
            }
            require_once $projectPath . '/models/IDCardModel.php';
            $model = new \Projects\IDCard\Models\IDCardModel();
        }
        return $model;
    }

    // ------------------------------------------------------------------ //
    //  Overview                                                            //
    // ------------------------------------------------------------------ //

    public function overview(): void
    {
        $model  = $this->model();
        $stats  = [
            'total'      => $model->countAll(),
            'today'      => $model->countToday(),
            'this_month' => $model->countThisMonth(),
        ];
        $activeUsers = $model->countActiveUsers();
        $recent      = $model->getRecentAll(10);
        $bulkStats   = [
            'total'     => $model->countAllBulkJobs(),
            'today'     => $model->countBulkJobsToday(),
            'cards_sum' => $model->sumBulkCardsGenerated(),
        ];

        $this->view('admin/projects/idcard/overview', [
            'title'       => 'CardX Admin — Overview',
            'stats'       => $stats,
            'activeUsers' => $activeUsers,
            'recent'      => $recent,
            'dbConnected' => true,
            'bulkStats'   => $bulkStats,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  All cards                                                           //
    // ------------------------------------------------------------------ //

    public function cards(): void
    {
        $model   = $this->model();
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;

        $filters = [
            'template'  => trim($_GET['template']   ?? ''),
            'search'    => trim($_GET['search']     ?? ''),
            'date_from' => trim($_GET['date_from']  ?? ''),
            'date_to'   => trim($_GET['date_to']    ?? ''),
        ];

        foreach (['date_from', 'date_to'] as $k) {
            if ($filters[$k] !== '') {
                $d = \DateTime::createFromFormat('Y-m-d', $filters[$k]);
                if (!$d || $d->format('Y-m-d') !== $filters[$k]) {
                    $filters[$k] = '';
                }
            }
        }

        $total = 0;
        $cards = [];
        try {
            $total = $model->countSearch($filters);
            $cards = $model->searchAdmin($filters, $perPage, $offset);
        } catch (\Exception $e) {
            Logger::error('IDCardAdmin cards query: ' . $e->getMessage());
        }

        $pages = max(1, (int) ceil($total / $perPage));

        // Load template list from config for the filter dropdown
        $projectConfig = [];
        $cfgPath = BASE_PATH . '/projects/idcard/config.php';
        if (file_exists($cfgPath)) {
            $projectConfig = require $cfgPath;
        }

        $this->view('admin/projects/idcard/cards', [
            'title'     => 'CardX Admin — All Cards',
            'cards'     => $cards,
            'total'     => $total,
            'page'      => $page,
            'perPage'   => $perPage,
            'pages'     => $pages,
            'filters'   => $filters,
            'templates' => $projectConfig['templates'] ?? [],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Delete card (admin)                                                 //
    // ------------------------------------------------------------------ //

    public function deleteCard(): void
    {
        if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->json(['success' => false, 'message' => 'Invalid token']);
            return;
        }

        $id    = (int) ($_POST['id'] ?? 0);
        $model = $this->model();

        try {
            $this->db->query("DELETE FROM idcard_cards WHERE id = ?", [$id]);
            ActivityLogger::log(Auth::id(), 'admin_idcard_deleted', ['card_id' => $id]);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            Logger::error('IDCardAdmin deleteCard: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Database error']);
        }
    }

    // ------------------------------------------------------------------ //
    //  Settings                                                            //
    // ------------------------------------------------------------------ //

    public function settings(): void
    {
        $model = $this->model();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
                $this->redirectWithError('/admin/projects/idcard/settings', 'Invalid token');
                return;
            }

            $cfg = [
                'max_cards_per_user'  => max(1, (int) ($_POST['max_cards_per_user']  ?? 200)),
                'allowed_templates'   => $_POST['allowed_templates']  ?? [],
                'ai_enabled'          => !empty($_POST['ai_enabled']),
                'bulk_enabled'        => !empty($_POST['bulk_enabled']),
                'max_bulk_rows'       => max(1, min(1000, (int) ($_POST['max_bulk_rows'] ?? 200))),
            ];

            // Validate allowed_templates entries
            $cfgPath = BASE_PATH . '/projects/idcard/config.php';
            $projectConfig = file_exists($cfgPath) ? require $cfgPath : [];
            $validKeys = array_keys($projectConfig['templates'] ?? []);
            $cfg['allowed_templates'] = array_values(
                array_filter($cfg['allowed_templates'], fn($k) => in_array($k, $validKeys, true))
            );

            $model->setSetting('admin_config', $cfg);
            ActivityLogger::log(Auth::id(), 'admin_idcard_settings_updated');
            header('Location: /admin/projects/idcard/settings?saved=1');
            exit;
        }

        $saved  = $model->getSetting('admin_config', []);
        $defaults = [
            'max_cards_per_user'  => 200,
            'allowed_templates'   => [],
            'ai_enabled'          => true,
            'bulk_enabled'        => true,
            'max_bulk_rows'       => 200,
        ];
        $settings = array_merge($defaults, is_array($saved) ? $saved : []);

        $cfgPath = BASE_PATH . '/projects/idcard/config.php';
        $projectConfig = file_exists($cfgPath) ? require $cfgPath : [];

        $this->view('admin/projects/idcard/settings', [
            'title'          => 'CardX Admin — Settings',
            'settings'       => $settings,
            'templates'      => $projectConfig['templates'] ?? [],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  AI Integration Settings                                            //
    // ------------------------------------------------------------------ //

    public function aiSettings(): void
    {
        $model = $this->model();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Security::validateCsrfToken($_POST['_csrf_token'] ?? '')) {
                $this->redirectWithError('/admin/projects/idcard/ai-settings', 'Invalid token');
                return;
            }

            $cfg = [
                'idcard_ai_enabled'       => !empty($_POST['idcard_ai_enabled']) ? '1' : '0',
                'idcard_openai_api_key'   => trim($_POST['idcard_openai_api_key'] ?? ''),
                'idcard_openai_model'     => trim($_POST['idcard_openai_model'] ?? 'gpt-4o-mini'),
                'idcard_ai_daily_limit'   => max(0, (int) ($_POST['idcard_ai_daily_limit'] ?? 0)),
                'idcard_pro_templates'    => !empty($_POST['idcard_pro_templates']) ? '1' : '0',
                'idcard_pro_styles'       => !empty($_POST['idcard_pro_styles']) ? '1' : '0',
            ];

            if (empty($cfg['idcard_openai_model'])) {
                $cfg['idcard_openai_model'] = 'gpt-4o-mini';
            }

            foreach ($cfg as $key => $value) {
                $model->setSetting($key, $value);
            }

            ActivityLogger::log(Auth::id(), 'admin_idcard_ai_settings_updated');
            header('Location: /admin/projects/idcard/ai-settings?saved=1');
            exit;
        }

        $keys = [
            'idcard_ai_enabled', 'idcard_openai_api_key', 'idcard_openai_model',
            'idcard_ai_daily_limit', 'idcard_pro_templates', 'idcard_pro_styles',
        ];
        $settings = [];
        foreach ($keys as $k) {
            $settings[$k] = $model->getSetting($k, null);
        }
        $defaults = [
            'idcard_ai_enabled'     => '1',
            'idcard_openai_api_key' => '',
            'idcard_openai_model'   => 'gpt-4o-mini',
            'idcard_ai_daily_limit' => '0',
            'idcard_pro_templates'  => '0',
            'idcard_pro_styles'     => '0',
        ];
        foreach ($defaults as $k => $v) {
            if ($settings[$k] === null) {
                $settings[$k] = $v;
            }
        }

        $this->view('admin/projects/idcard/ai_settings', [
            'title'    => 'CardX Admin — AI Integration',
            'settings' => $settings,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Bulk jobs                                                          //
    // ------------------------------------------------------------------ //

    public function bulkJobs(): void
    {
        $model    = $this->model();
        $page     = max(1, (int) ($_GET['page'] ?? 1));
        $perPage  = 30;
        $offset   = ($page - 1) * $perPage;

        $total = $model->countAllBulkJobs();
        $jobs  = $model->getAllBulkJobs($perPage, $offset);
        $pages = max(1, (int) ceil($total / $perPage));

        $stats = [
            'total'     => $total,
            'today'     => $model->countBulkJobsToday(),
            'cards_sum' => $model->sumBulkCardsGenerated(),
        ];

        $this->view('admin/projects/idcard/bulk_jobs', [
            'title'   => 'CardX Admin — Bulk Jobs',
            'jobs'    => $jobs,
            'total'   => $total,
            'page'    => $page,
            'perPage' => $perPage,
            'pages'   => $pages,
            'stats'   => $stats,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    private function redirectWithError(string $url, string $msg): void
    {
        header('Location: ' . $url . '?error=' . urlencode($msg));
        exit;
    }
}
