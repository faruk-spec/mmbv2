<?php

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;

class ProjectDatabaseController extends BaseController
{
    private $configPath;
    private $projects = ['codexpro', 'imgtxt', 'proshare'];
    
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->configPath = BASE_PATH . '/config/projects_db.php';
    }
    
    /**
     * Show database setup overview
     */
    public function index()
    {
        $config = $this->loadConfig();
        
        $data = [
            'title' => 'Project Database Setup',
            'projects' => [],
        ];
        
        foreach ($this->projects as $project) {
            $projectConfig = $config[$project] ?? [];
            $data['projects'][$project] = [
                'name' => ucfirst($project),
                'config' => $projectConfig,
                'status' => $projectConfig['status'] ?? 'unconfigured',
                'last_tested' => $projectConfig['last_tested'] ?? null,
            ];
        }
        
        $this->view('admin/projects/database-setup', $data, 'admin');
    }
    
    /**
     * Show configuration form for specific project
     */
    public function configure($project)
    {
        if (!in_array($project, $this->projects)) {
            $this->redirect('/admin/projects/database-setup');
            return;
        }
        
        $config = $this->loadConfig();
        $projectConfig = $config[$project] ?? [];
        
        // Get schema file path
        $schemaPath = BASE_PATH . "/projects/{$project}/schema.sql";
        $hasSchema = file_exists($schemaPath);
        
        $data = [
            'title' => ucfirst($project) . ' Database Configuration',
            'project' => $project,
            'project_name' => ucfirst($project),
            'config' => $projectConfig,
            'has_schema' => $hasSchema,
            'schema_path' => $hasSchema ? $schemaPath : null,
        ];
        
        $this->view('admin/projects/database-configure', $data, 'admin');
    }
    
    /**
     * Test database connection
     */
    public function testConnection()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        $project = $_POST['project'] ?? '';
        $host = $_POST['host'] ?? 'localhost';
        $port = $_POST['port'] ?? '3306';
        $database = $_POST['database'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (!in_array($project, $this->projects)) {
            $this->json(['success' => false, 'message' => 'Invalid project'], 400);
            return;
        }
        
        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new \PDO($dsn, $username, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            ]);
            
            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($database));
            $dbExists = $stmt->fetch() !== false;
            
            if (!$dbExists) {
                $this->json([
                    'success' => true,
                    'message' => 'Connection successful! Database does not exist yet.',
                    'database_exists' => false,
                    'can_create' => true,
                ]);
                return;
            }
            
            // Connect to specific database and check tables
            $pdo = new \PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password);
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $this->json([
                'success' => true,
                'message' => 'Connection successful!',
                'database_exists' => true,
                'table_count' => count($tables),
                'tables' => $tables,
            ]);
            
        } catch (\PDOException $e) {
            $this->json([
                'success' => false,
                'message' => 'Connection failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Save database configuration
     */
    public function saveConfiguration()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/projects/database-setup');
            return;
        }
        
        $project = $_POST['project'] ?? '';
        if (!in_array($project, $this->projects)) {
            $_SESSION['error'] = 'Invalid project';
            $this->redirect('/admin/projects/database-setup');
            return;
        }
        
        $config = $this->loadConfig();
        
        $config[$project] = [
            'host' => $_POST['host'] ?? 'localhost',
            'port' => $_POST['port'] ?? '3306',
            'database' => $_POST['database'] ?? $project,
            'username' => $_POST['username'] ?? '',
            'password' => $_POST['password'] ?? '',
            'status' => 'active',
            'last_tested' => date('Y-m-d H:i:s'),
        ];
        
        if ($this->saveConfig($config)) {
            $_SESSION['success'] = 'Configuration saved successfully for ' . ucfirst($project);
        } else {
            // More detailed error message
            $dir = dirname($this->configPath);
            $dirWritable = is_writable($dir);
            $fileExists = file_exists($this->configPath);
            $fileWritable = $fileExists ? is_writable($this->configPath) : 'N/A';
            
            $_SESSION['error'] = 'Failed to save configuration. Please ensure the config directory is writable. ' .
                                "(Dir writable: " . ($dirWritable ? 'Yes' : 'No') . ", " .
                                "File exists: " . ($fileExists ? 'Yes' : 'No') . ", " .
                                "File writable: " . $fileWritable . ")";
        }
        
        $this->redirect('/admin/projects/database-setup');
    }
    
    /**
     * Import SQL schema
     */
    public function importSchema()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'message' => 'Invalid request method'], 405);
            return;
        }
        
        $project = $_POST['project'] ?? '';
        if (!in_array($project, $this->projects)) {
            $this->json(['success' => false, 'message' => 'Invalid project'], 400);
            return;
        }
        
        // Get SQL content (either from file upload or textarea)
        $sqlContent = '';
        
        if (!empty($_FILES['sql_file']['tmp_name'])) {
            $sqlContent = file_get_contents($_FILES['sql_file']['tmp_name']);
        } elseif (!empty($_POST['sql_content'])) {
            $sqlContent = $_POST['sql_content'];
        }
        
        if (empty($sqlContent)) {
            $this->json(['success' => false, 'message' => 'No SQL content provided'], 400);
            return;
        }
        
        // Get database config
        $config = $this->loadConfig();
        $projectConfig = $config[$project] ?? [];
        
        if (empty($projectConfig['username'])) {
            $this->json(['success' => false, 'message' => 'Database not configured. Please configure first.'], 400);
            return;
        }
        
        try {
            // Connect to MySQL server
            $pdo = new \PDO(
                "mysql:host={$projectConfig['host']};port={$projectConfig['port']};charset=utf8mb4",
                $projectConfig['username'],
                $projectConfig['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$projectConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Select database
            $pdo->exec("USE `{$projectConfig['database']}`");
            
            // Execute SQL statements
            $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
            $executed = 0;
            
            foreach ($statements as $statement) {
                if (!empty($statement)) {
                    $pdo->exec($statement);
                    $executed++;
                }
            }
            
            // Check tables created
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            
            $this->json([
                'success' => true,
                'message' => "Schema imported successfully! {$executed} statements executed, " . count($tables) . " tables created.",
                'tables' => $tables,
            ]);
            
        } catch (\PDOException $e) {
            $this->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Load configuration from file
     */
    private function loadConfig(): array
    {
        if (!file_exists($this->configPath)) {
            return [
                'codexpro' => ['status' => 'unconfigured'],
                'imgtxt' => ['status' => 'unconfigured'],
                'proshare' => ['status' => 'unconfigured'],
            ];
        }
        
        return require $this->configPath;
    }
    
    /**
     * Save configuration to file
     */
    private function saveConfig(array $config): bool
    {
        try {
            // Check if directory is writable
            $dir = dirname($this->configPath);
            if (!is_writable($dir)) {
                // Try to make it writable
                @chmod($dir, 0755);
            }
            
            // Check if file exists and is writable
            if (file_exists($this->configPath) && !is_writable($this->configPath)) {
                @chmod($this->configPath, 0644);
            }
            
            $content = "<?php\n/**\n * Dynamic Project Database Configuration\n * \n * Last Updated: " . date('Y-m-d H:i:s') . "\n */\n\nreturn " . var_export($config, true) . ";\n";
            
            $result = file_put_contents($this->configPath, $content);
            
            if ($result === false) {
                error_log("Failed to write config file: {$this->configPath}");
                return false;
            }
            
            // Set proper permissions after writing
            @chmod($this->configPath, 0644);
            
            return true;
            
        } catch (\Exception $e) {
            error_log("Exception saving config: " . $e->getMessage());
            return false;
        }
    }
}
