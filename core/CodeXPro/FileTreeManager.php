<?php

/**
 * FileTreeManager - Multi-file project management
 * 
 * Manages file tree structure for CodeXPro projects
 */
class FileTreeManager
{
    /**
     * Create file tree structure
     */
    public static function createTree($files)
    {
        $tree = [];
        
        foreach ($files as $path => $content) {
            $parts = explode('/', $path);
            $current = &$tree;
            
            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    // It's a file
                    $current[$part] = [
                        'type' => 'file',
                        'path' => $path,
                        'name' => $part,
                        'ext' => pathinfo($part, PATHINFO_EXTENSION),
                        'size' => strlen($content)
                    ];
                } else {
                    // It's a folder
                    if (!isset($current[$part])) {
                        $current[$part] = [
                            'type' => 'folder',
                            'name' => $part,
                            'children' => []
                        ];
                    }
                    $current = &$current[$part]['children'];
                }
            }
        }
        
        return $tree;
    }
    
    /**
     * Get file icon based on extension
     */
    public static function getFileIcon($extension)
    {
        $icons = [
            'html' => '📄',
            'css' => '🎨',
            'js' => '⚙️',
            'json' => '📋',
            'md' => '📝',
            'txt' => '📝',
            'jpg' => '🖼️',
            'jpeg' => '🖼️',
            'png' => '🖼️',
            'gif' => '🖼️',
            'svg' => '🖼️',
            'pdf' => '📕',
            'zip' => '📦',
            'folder' => '📁'
        ];
        
        return $icons[$extension] ?? '📄';
    }
    
    /**
     * Render file tree as HTML
     */
    public static function renderTree($tree, $level = 0)
    {
        $html = '';
        
        foreach ($tree as $item) {
            $indent = str_repeat('    ', $level);
            
            if ($item['type'] === 'folder') {
                $html .= $indent . '<div class="folder" data-path="' . htmlspecialchars($item['name']) . '">';
                $html .= '<span class="folder-name">' . self::getFileIcon('folder') . ' ' . htmlspecialchars($item['name']) . '</span>';
                $html .= '<div class="folder-contents">';
                $html .= self::renderTree($item['children'], $level + 1);
                $html .= '</div></div>';
            } else {
                $html .= $indent . '<div class="file" data-path="' . htmlspecialchars($item['path']) . '">';
                $html .= '<span class="file-name">' . self::getFileIcon($item['ext']) . ' ' . htmlspecialchars($item['name']) . '</span>';
                $html .= '<span class="file-size">' . self::formatFileSize($item['size']) . '</span>';
                $html .= '</div>';
            }
        }
        
        return $html;
    }
    
    /**
     * Format file size
     */
    public static function formatFileSize($bytes)
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        } elseif ($bytes < 1048576) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return round($bytes / 1048576, 2) . ' MB';
        }
    }
    
    /**
     * Create new file in project
     */
    public static function createFile($projectId, $path, $content = '')
    {
        // Database-agnostic: project database configured in admin panel
        $db = self::getProjectDatabase();
        
        $stmt = $db->prepare("
            INSERT INTO project_files (project_id, file_path, file_content, created_at, updated_at)
            VALUES (?, ?, ?, NOW(), NOW())
        ");
        
        return $stmt->execute([$projectId, $path, $content]);
    }
    
    /**
     * Create new folder in project
     */
    public static function createFolder($projectId, $path)
    {
        $db = self::getProjectDatabase();
        
        $stmt = $db->prepare("
            INSERT INTO project_folders (project_id, folder_path, created_at)
            VALUES (?, ?, NOW())
        ");
        
        return $stmt->execute([$projectId, $path]);
    }
    
    /**
     * Rename file or folder
     */
    public static function rename($projectId, $oldPath, $newPath, $type = 'file')
    {
        $db = self::getProjectDatabase();
        
        if ($type === 'file') {
            $stmt = $db->prepare("
                UPDATE project_files
                SET file_path = ?, updated_at = NOW()
                WHERE project_id = ? AND file_path = ?
            ");
            return $stmt->execute([$newPath, $projectId, $oldPath]);
        } else {
            // Update folder and all children
            $stmt = $db->prepare("
                UPDATE project_files
                SET file_path = REPLACE(file_path, ?, ?), updated_at = NOW()
                WHERE project_id = ? AND file_path LIKE ?
            ");
            return $stmt->execute([$oldPath, $newPath, $projectId, $oldPath . '%']);
        }
    }
    
    /**
     * Delete file or folder
     */
    public static function delete($projectId, $path, $type = 'file')
    {
        $db = self::getProjectDatabase();
        
        if ($type === 'file') {
            $stmt = $db->prepare("
                DELETE FROM project_files
                WHERE project_id = ? AND file_path = ?
            ");
            return $stmt->execute([$projectId, $path]);
        } else {
            // Delete folder and all children
            $stmt = $db->prepare("
                DELETE FROM project_files
                WHERE project_id = ? AND file_path LIKE ?
            ");
            return $stmt->execute([$projectId, $path . '%']);
        }
    }
    
    /**
     * Get project files
     */
    public static function getProjectFiles($projectId)
    {
        $db = self::getProjectDatabase();
        
        $stmt = $db->prepare("
            SELECT file_path, file_content
            FROM project_files
            WHERE project_id = ?
            ORDER BY file_path
        ");
        
        $stmt->execute([$projectId]);
        $files = [];
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $files[$row['file_path']] = $row['file_content'];
        }
        
        return $files;
    }
    
    /**
     * Get project database connection (database-agnostic)
     */
    private static function getProjectDatabase()
    {
        // Read database configuration from project config file
        $config = require __DIR__ . '/../../projects/codexpro/config.php';
        
        return new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass']
        );
    }
    
    /**
     * Search files in project
     */
    public static function searchFiles($projectId, $query)
    {
        $db = self::getProjectDatabase();
        
        $stmt = $db->prepare("
            SELECT file_path, file_content
            FROM project_files
            WHERE project_id = ? 
            AND (file_path LIKE ? OR file_content LIKE ?)
        ");
        
        $searchTerm = '%' . $query . '%';
        $stmt->execute([$projectId, $searchTerm, $searchTerm]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
