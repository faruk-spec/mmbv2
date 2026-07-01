<?php
/**
 * Deployment Dashboard Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;

class DeploymentController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    /**
     * Main deployment dashboard
     */
    public function index(): void
    {
        $title = 'Deployment Dashboard';
        \Core\View::render('admin/deployment/index', array_merge(compact('title'), $this->getGitHubSettings()));
    }

    /**
     * GitHub section
     */
    public function github(): void
    {
        $title = 'GitHub Repository';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'github'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Branches section
     */
    public function branches(): void
    {
        $title = 'Branch Manager';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'branches'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Deploy section
     */
    public function deploy(): void
    {
        $title = 'Deployment Center';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'deploy'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * History section
     */
    public function history(): void
    {
        $title = 'Deployment History';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'history'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Version control section
     */
    public function versions(): void
    {
        $title = 'Version Control';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'versions'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Logs section
     */
    public function logs(): void
    {
        $title = 'Deployment Logs';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'logs'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Server section
     */
    public function server(): void
    {
        $title = 'Server Management';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'server'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Settings section
     */
    public function settings(): void
    {
        $title = 'Deployment Settings';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'settings'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Subdomain section
     */
    public function subdomain(): void
    {
        $title = 'Subdomain Access';
        \Core\View::render('admin/deployment/index', array_merge(
            ['title' => $title, 'activeTab' => 'subdomain'],
            $this->getGitHubSettings()
        ));
    }

    /**
     * Save GitHub Personal Access Token and repo info
     */
    public function saveGitHubToken(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $token = trim($this->input('github_token', ''));
        $repo  = trim($this->input('github_repo', ''));

        if (!$repo) {
            $this->jsonError('Repository name is required.');
            return;
        }

        try {
            $db = Database::getInstance();
            // Only update the token if a new one was provided (empty means keep existing)
            if ($token !== '') {
                $this->upsertSetting($db, 'github_token', $token);
            }
            $this->upsertSetting($db, 'github_repo', $repo);

            Logger::activity(Auth::id(), 'github_token_saved', ['repo' => $repo]);

            $this->json(['success' => true, 'message' => 'GitHub settings saved.']);
        } catch (\Exception $e) {
            Logger::error('GitHub token save error: ' . $e->getMessage());
            $this->jsonError('Failed to save GitHub settings.');
        }
    }

    /**
     * Test GitHub API connection and return repo data
     */
    public function gitHubApiData(): void
    {
        ['github_token' => $token, 'github_repo' => $repo] = $this->getGitHubSettings();

        if (!$token || !$repo) {
            $this->json(['success' => false, 'message' => 'GitHub token or repository not configured.']);
            return;
        }

        $data = $this->fetchGitHubApi("https://api.github.com/repos/{$repo}", $token);
        if (!$data) {
            $this->json(['success' => false, 'message' => 'Failed to connect to GitHub API. Check your token and repo name.']);
            return;
        }

        $releases = $this->fetchGitHubApi("https://api.github.com/repos/{$repo}/releases?per_page=5", $token) ?: [];
        $workflows = $this->fetchGitHubApi("https://api.github.com/repos/{$repo}/actions/runs?per_page=5", $token);
        $workflowRuns = $workflows['workflow_runs'] ?? [];

        $this->json([
            'success'      => true,
            'repo'         => [
                'name'          => $data['full_name'] ?? $repo,
                'description'   => $data['description'] ?? '',
                'stars'         => $data['stargazers_count'] ?? 0,
                'forks'         => $data['forks_count'] ?? 0,
                'open_issues'   => $data['open_issues_count'] ?? 0,
                'default_branch'=> $data['default_branch'] ?? 'main',
                'visibility'    => $data['visibility'] ?? 'unknown',
                'html_url'      => $data['html_url'] ?? '',
                'updated_at'    => $data['updated_at'] ?? '',
            ],
            'releases'     => array_map(fn($r) => [
                'tag'        => $r['tag_name'] ?? '',
                'name'       => $r['name'] ?? '',
                'published'  => $r['published_at'] ?? '',
                'url'        => $r['html_url'] ?? '',
            ], array_slice($releases, 0, 5)),
            'workflow_runs'=> array_map(fn($w) => [
                'name'       => $w['name'] ?? '',
                'status'     => $w['status'] ?? '',
                'conclusion' => $w['conclusion'] ?? '',
                'created_at' => $w['created_at'] ?? '',
                'url'        => $w['html_url'] ?? '',
            ], array_slice($workflowRuns, 0, 5)),
        ]);
    }

    /**
     * Run git pull on the server
     */
    public function gitPull(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        if (!function_exists('exec')) {
            $this->jsonError('Command execution is not available on this server.', 503);
            return;
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : getcwd();
        if (!is_dir($basePath . '/.git')) {
            $this->jsonError('Git repository not found on server path.', 422);
            return;
        }
        $gitBinary = $this->resolveBinary('git', ['/usr/bin/git', '/usr/local/bin/git']);
        if ($gitBinary === null) {
            $this->jsonError('Git binary not found on server.', 503);
            return;
        }
        $output   = [];
        $code     = 0;
        exec(escapeshellarg($gitBinary) . ' -C ' . escapeshellarg($basePath) . ' pull 2>&1', $output, $code);

        $outputStr = implode("\n", $output);
        Logger::activity(Auth::id(), 'git_pull', ['exit_code' => $code, 'output' => substr($outputStr, 0, 500)]);

        $this->json([
            'success' => $code === 0,
            'output'  => $outputStr,
            'message' => $code === 0 ? 'Git pull completed successfully.' : 'Git pull failed (exit code ' . $code . ').',
        ]);
    }

    /**
     * Clear application cache
     */
    public function clearCache(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $basePath  = defined('BASE_PATH') ? BASE_PATH : getcwd();
        $cacheDir  = $basePath . '/storage/cache';
        $cleared   = 0;

        if (is_dir($cacheDir)) {
            foreach (new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            ) as $file) {
                if ($file->isFile() && $file->getFilename() !== '.gitkeep') {
                    unlink($file->getRealPath());
                    $cleared++;
                }
            }
        }

        // Also clear PHP opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        Logger::activity(Auth::id(), 'cache_cleared', ['files_removed' => $cleared]);

        $this->json([
            'success' => true,
            'message' => "Cache cleared successfully. {$cleared} file(s) removed.",
        ]);
    }

    /**
     * Run composer install
     */
    public function composerInstall(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        if (!function_exists('exec')) {
            $this->jsonError('Command execution is not available on this server.', 503);
            return;
        }

        $basePath = defined('BASE_PATH') ? BASE_PATH : getcwd();
        if (!is_file($basePath . '/composer.json')) {
            $this->jsonError('composer.json not found on server path.', 422);
            return;
        }
        $composerBinary = $this->resolveBinary('composer', [
            '/usr/bin/composer',
            '/usr/local/bin/composer',
            $basePath . '/composer.phar',
        ]);
        if ($composerBinary === null) {
            $this->jsonError('Composer binary not found on server.', 503);
            return;
        }
        $output   = [];
        $code     = 0;
        exec(
            'cd ' . escapeshellarg($basePath) .
            ' && ' . escapeshellarg($composerBinary) . ' install --no-dev --optimize-autoloader 2>&1',
            $output,
            $code
        );

        $outputStr = implode("\n", $output);
        Logger::activity(Auth::id(), 'composer_install', ['exit_code' => $code, 'output' => substr($outputStr, 0, 500)]);

        $this->json([
            'success' => $code === 0,
            'output'  => $outputStr,
            'message' => $code === 0 ? 'Composer install completed successfully.' : 'Composer install failed (exit code ' . $code . ').',
        ]);
    }

    /**
     * Save subdomain URL setting
     */
    public function saveSubdomain(): void
    {
        if (!$this->validateCsrf()) {
            $this->jsonError('Invalid request.');
            return;
        }

        $subdomainUrl = trim($this->input('admin_subdomain_url', ''));

        // Basic URL validation
        if ($subdomainUrl && !filter_var($subdomainUrl, FILTER_VALIDATE_URL)) {
            $this->jsonError('Invalid URL format.');
            return;
        }

        try {
            $db = Database::getInstance();
            $this->upsertSetting($db, 'admin_subdomain_url', $subdomainUrl);

            Logger::activity(Auth::id(), 'subdomain_url_saved', ['url' => $subdomainUrl]);

            $this->json(['success' => true, 'message' => 'Subdomain URL saved.']);
        } catch (\Exception $e) {
            Logger::error('Subdomain save error: ' . $e->getMessage());
            $this->jsonError('Failed to save subdomain URL.');
        }
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    /**
     * Load GitHub token, repo and subdomain URL from settings
     */
    private function getGitHubSettings(): array
    {
        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT `key`, `value` FROM settings WHERE `key` IN ('github_token','github_repo','admin_subdomain_url')"
            );
            $map  = [];
            foreach ($rows as $r) {
                $map[$r['key']] = $r['value'];
            }
        } catch (\Exception $e) {
            $map = [];
        }

        return [
            'github_token'        => $map['github_token'] ?? '',
            'github_repo'         => $map['github_repo'] ?? '',
            'admin_subdomain_url' => $map['admin_subdomain_url'] ?? '',
        ];
    }

    /**
     * Call the GitHub API with a PAT token
     */
    private function fetchGitHubApi(string $url, string $token): ?array
    {
        if (!function_exists('curl_init')) {
            return null;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_USERAGENT      => (defined('APP_NAME') ? APP_NAME : 'MMB') . '-DeploymentDashboard/' . (defined('APP_VERSION') ? APP_VERSION : '1.0'),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $token,
                'Accept: application/vnd.github+json',
                'X-GitHub-Api-Version: 2022-11-28',
            ],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($body === false || $code !== 200 || !$body) {
            return null;
        }

        return json_decode($body, true) ?: null;
    }

    /**
     * INSERT or UPDATE a setting row
     */
    private function upsertSetting(Database $db, string $key, string $value): void
    {
        $existing = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
        if ($existing) {
            $db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
        } else {
            $db->insert('settings', ['key' => $key, 'value' => $value, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        }
    }

    /**
     * Resolve command binary path from known candidates or PATH lookup.
     */
    private function resolveBinary(string $command, array $candidates = []): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && $candidate !== '' && is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }
        }

        if (function_exists('exec')) {
            $lookup = [];
            $code = 0;
            exec('command -v ' . escapeshellarg($command) . ' 2>/dev/null', $lookup, $code);
            if ($code === 0) {
                $resolved = trim($lookup[0] ?? '');
                if ($resolved !== '' && is_file($resolved) && is_executable($resolved)) {
                    return $resolved;
                }
            }
        }

        return null;
    }

    /**
     * Return JSON success response
     */
    private function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Return JSON error response
     */
    private function jsonError(string $message, int $status = 400): void
    {
        $this->json(['success' => false, 'message' => $message], $status);
    }
}
