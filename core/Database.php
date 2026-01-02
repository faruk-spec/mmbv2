<?php
/**
 * Database Connection Manager
 * 
 * @package MMB\Core
 */

namespace Core;

use PDO;
use PDOException;

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private array $config = [];
    
    /**
     * Private constructor for singleton pattern
     */
    private function __construct()
    {
        $this->config = require BASE_PATH . '/config/database.php';
        $this->connect();
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Establish database connection
     */
    private function connect(): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->config['host'],
                $this->config['port'],
                $this->config['database']
            );
            
            $this->connection = new PDO(
                $dsn,
                $this->config['username'],
                $this->config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ]
            );
        } catch (PDOException $e) {
            Logger::error('Database connection failed: ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed');
        }
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }
    
    /**
     * Execute a prepared statement
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            Logger::error('Query failed: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Fetch a single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    /**
     * Fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch a single column value
     */
    public function fetchColumn(string $sql, array $params = [], int $columnNumber = 0): mixed
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetchColumn($columnNumber);
        return $result !== false ? $result : null;
    }
    
    /**
     * Insert a row and return the ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, array_values($data));
        
        return (int) $this->connection->lastInsertId();
    }
    
    /**
     * Update rows
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = implode(' = ?, ', array_keys($data)) . ' = ?';
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        
        $stmt = $this->query($sql, array_merge(array_values($data), $whereParams));
        return $stmt->rowCount();
    }
    
    /**
     * Delete rows
     */
    public function delete(string $table, string $where, array $params = []): int
    {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->connection->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->connection->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->connection->rollBack();
    }
    
    /**
     * Connect to a specific project database
     */
    public static function projectConnection(string $projectName): Database
    {
        // Validate project name to prevent path traversal
        if (!preg_match('/^[a-z0-9_-]+$/i', $projectName)) {
            throw new \InvalidArgumentException('Invalid project name');
        }
        
        $config = null;
        
        // Try dynamic config first (from admin panel)
        $dynamicConfigPath = BASE_PATH . '/config/projects_db.php';
        if (file_exists($dynamicConfigPath)) {
            $dynamicConfig = require $dynamicConfigPath;
            if (isset($dynamicConfig[$projectName]) && !empty($dynamicConfig[$projectName]['username'])) {
                $config = $dynamicConfig[$projectName];
            }
        }
        
        // Fall back to project config
        if ($config === null) {
            $projectPath = BASE_PATH . "/projects/{$projectName}/config.php";
            
            // Verify path is within projects directory
            $realPath = realpath($projectPath);
            $projectsDir = realpath(BASE_PATH . '/projects');
            
            if ($realPath === false || strpos($realPath, $projectsDir) !== 0) {
                throw new \InvalidArgumentException('Project not found');
            }
            
            $projectConfig = require $projectPath;
            $config = $projectConfig['database'];
        }
        
        // Final fallback to main database config if credentials missing
        if (empty($config['username'])) {
            $mainConfig = require BASE_PATH . '/config/database.php';
            $config = array_merge($config, [
                'username' => $mainConfig['username'],
                'password' => $mainConfig['password'],
            ]);
        }
        
        $db = new self();
        $db->config = $config;
        
        try {
            $db->connect();
        } catch (\RuntimeException $e) {
            // Provide more helpful error message
            throw new \RuntimeException(
                "Failed to connect to {$projectName} database. " .
                "Please configure the database in Admin Panel > Projects > Database Setup. " .
                "Error: " . $e->getMessage()
            );
        }
        
        return $db;
    }
}
