<?php
/**
 * ProShare API Controller
 * 
 * API endpoints for ProShare file sharing
 * Part of Phase 11: API Development
 * 
 * @package MMB\Core\API
 */

namespace Core\API;

use Core\Database;
use Core\Logger;

class ProShareApiController extends ApiController
{
    /**
     * Route request to appropriate method
     */
    protected function route(): void
    {
        $path = $_GET['path'] ?? '';
        
        switch ($this->requestMethod) {
            case 'GET':
                if (preg_match('/^files\/([a-z0-9]+)$/', $path, $matches)) {
                    $this->getFile($matches[1]);
                } elseif ($path === 'files') {
                    $this->listFiles();
                } elseif ($path === 'stats') {
                    $this->getStats();
                } else {
                    $this->respondNotFound();
                }
                break;
                
            case 'POST':
                if ($path === 'upload') {
                    $this->uploadFile();
                } elseif (preg_match('/^files\/([a-z0-9]+)\/share$/', $path, $matches)) {
                    $this->shareFile($matches[1]);
                } else {
                    $this->respondNotFound();
                }
                break;
                
            case 'DELETE':
                if (preg_match('/^files\/([a-z0-9]+)$/', $path, $matches)) {
                    $this->deleteFile($matches[1]);
                } else {
                    $this->respondNotFound();
                }
                break;
                
            default:
                $this->respondError('Method not allowed', 405);
        }
    }
    
    /**
     * Upload file
     */
    private function uploadFile(): void
    {
        // Check permission
        if (!ApiAuth::hasPermission('proshare:upload')) {
            $this->respondForbidden('Missing permission: proshare:upload');
            return;
        }
        
        // Validate required fields
        if (!$this->validateRequired(['file_content', 'filename'])) {
            return;
        }
        
        try {
            $userId = ApiAuth::getUserId();
            $fileContent = $this->requestData['file_content'];
            $filename = $this->requestData['filename'];
            $expiresIn = $this->requestData['expires_in'] ?? 7; // Days
            
            // Decode base64 if needed
            if (base64_encode(base64_decode($fileContent, true)) === $fileContent) {
                $fileContent = base64_decode($fileContent);
            }
            
            // Generate short code
            $shortCode = $this->generateShortCode();
            
            // Save file
            $filePath = $this->saveFile($shortCode, $fileContent);
            
            // Store in database
            $db = Database::projectConnection('proshare');
            
            $fileId = $db->insert('files', [
                'user_id' => $userId,
                'short_code' => $shortCode,
                'original_name' => $filename,
                'file_path' => $filePath,
                'file_size' => strlen($fileContent),
                'mime_type' => $this->requestData['mime_type'] ?? 'application/octet-stream',
                'expires_at' => date('Y-m-d H:i:s', strtotime("+{$expiresIn} days")),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            $shareUrl = (APP_URL ?? 'http://localhost') . "/proshare/download/{$shortCode}";
            
            $this->respondCreated([
                'id' => $fileId,
                'short_code' => $shortCode,
                'filename' => $filename,
                'share_url' => $shareUrl,
                'expires_at' => date('Y-m-d H:i:s', strtotime("+{$expiresIn} days"))
            ], 'File uploaded successfully');
            
        } catch (\Exception $e) {
            Logger::error('API upload error: ' . $e->getMessage());
            $this->respondError('Upload failed');
        }
    }
    
    /**
     * Get file info
     */
    private function getFile(string $shortCode): void
    {
        if (!ApiAuth::hasPermission('proshare:read')) {
            $this->respondForbidden('Missing permission: proshare:read');
            return;
        }
        
        try {
            $db = Database::projectConnection('proshare');
            
            $file = $db->fetch(
                "SELECT * FROM files WHERE short_code = ?",
                [$shortCode]
            );
            
            if (!$file) {
                $this->respondNotFound('File not found');
                return;
            }
            
            // Check ownership or public access
            $userId = ApiAuth::getUserId();
            if ($file['user_id'] != $userId && !$file['is_public']) {
                $this->respondForbidden('Access denied');
                return;
            }
            
            $this->respondSuccess([
                'id' => $file['id'],
                'short_code' => $file['short_code'],
                'filename' => $file['original_name'],
                'size' => $file['file_size'],
                'mime_type' => $file['mime_type'],
                'downloads' => $file['downloads'] ?? 0,
                'share_url' => (APP_URL ?? 'http://localhost') . "/proshare/download/{$shortCode}",
                'created_at' => $file['created_at'],
                'expires_at' => $file['expires_at']
            ]);
            
        } catch (\Exception $e) {
            Logger::error('API get file error: ' . $e->getMessage());
            $this->respondError('Failed to retrieve file');
        }
    }
    
    /**
     * List user files
     */
    private function listFiles(): void
    {
        if (!ApiAuth::hasPermission('proshare:read')) {
            $this->respondForbidden('Missing permission: proshare:read');
            return;
        }
        
        try {
            $userId = ApiAuth::getUserId();
            $page = (int)($this->requestData['page'] ?? 1);
            $perPage = min((int)($this->requestData['per_page'] ?? 20), 100);
            
            $db = Database::projectConnection('proshare');
            
            $files = $db->fetchAll(
                "SELECT * FROM files WHERE user_id = ? ORDER BY created_at DESC",
                [$userId]
            );
            
            $result = $this->paginate($files, $page, $perPage);
            
            // Format files
            $result['items'] = array_map(function($file) {
                return [
                    'id' => $file['id'],
                    'short_code' => $file['short_code'],
                    'filename' => $file['original_name'],
                    'size' => $file['file_size'],
                    'downloads' => $file['downloads'] ?? 0,
                    'share_url' => (APP_URL ?? 'http://localhost') . "/proshare/download/{$file['short_code']}",
                    'created_at' => $file['created_at'],
                    'expires_at' => $file['expires_at']
                ];
            }, $result['items']);
            
            $this->respondSuccess($result);
            
        } catch (\Exception $e) {
            Logger::error('API list files error: ' . $e->getMessage());
            $this->respondError('Failed to list files');
        }
    }
    
    /**
     * Delete file
     */
    private function deleteFile(string $shortCode): void
    {
        if (!ApiAuth::hasPermission('proshare:delete')) {
            $this->respondForbidden('Missing permission: proshare:delete');
            return;
        }
        
        try {
            $userId = ApiAuth::getUserId();
            $db = Database::projectConnection('proshare');
            
            $file = $db->fetch(
                "SELECT * FROM files WHERE short_code = ? AND user_id = ?",
                [$shortCode, $userId]
            );
            
            if (!$file) {
                $this->respondNotFound('File not found or access denied');
                return;
            }
            
            // Delete file from filesystem
            if (file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            
            // Delete from database
            $db->delete('files', 'id = ?', [$file['id']]);
            
            $this->respondSuccess(null, 'File deleted successfully');
            
        } catch (\Exception $e) {
            Logger::error('API delete file error: ' . $e->getMessage());
            $this->respondError('Failed to delete file');
        }
    }
    
    /**
     * Get usage statistics
     */
    private function getStats(): void
    {
        if (!ApiAuth::hasPermission('proshare:read')) {
            $this->respondForbidden('Missing permission: proshare:read');
            return;
        }
        
        try {
            $userId = ApiAuth::getUserId();
            $db = Database::projectConnection('proshare');
            
            $stats = [
                'total_files' => $db->fetch(
                    "SELECT COUNT(*) as count FROM files WHERE user_id = ?",
                    [$userId]
                )['count'] ?? 0,
                'total_downloads' => $db->fetch(
                    "SELECT SUM(downloads) as total FROM files WHERE user_id = ?",
                    [$userId]
                )['total'] ?? 0,
                'storage_used' => $db->fetch(
                    "SELECT SUM(file_size) as total FROM files WHERE user_id = ?",
                    [$userId]
                )['total'] ?? 0
            ];
            
            $this->respondSuccess($stats);
            
        } catch (\Exception $e) {
            Logger::error('API get stats error: ' . $e->getMessage());
            $this->respondError('Failed to get statistics');
        }
    }
    
    /**
     * Generate short code
     */
    private function generateShortCode(): string
    {
        return substr(bin2hex(random_bytes(4)), 0, 8);
    }
    
    /**
     * Save file to storage
     */
    private function saveFile(string $shortCode, string $content): string
    {
        $uploadDir = BASE_PATH . '/storage/uploads/proshare';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filePath = $uploadDir . '/' . $shortCode;
        file_put_contents($filePath, $content);
        
        return $filePath;
    }
    
    /**
     * Share file (generate public link)
     */
    private function shareFile(string $shortCode): void
    {
        if (!ApiAuth::hasPermission('proshare:write')) {
            $this->respondForbidden('Missing permission: proshare:write');
            return;
        }
        
        try {
            $userId = ApiAuth::getUserId();
            $db = Database::projectConnection('proshare');
            
            $file = $db->fetch(
                "SELECT * FROM files WHERE short_code = ? AND user_id = ?",
                [$shortCode, $userId]
            );
            
            if (!$file) {
                $this->respondNotFound('File not found or access denied');
                return;
            }
            
            // Update file to be public
            $db->update('files', [
                'is_public' => 1
            ], 'id = ?', [$file['id']]);
            
            $shareUrl = (APP_URL ?? 'http://localhost') . "/proshare/download/{$shortCode}";
            
            $this->respondSuccess([
                'share_url' => $shareUrl,
                'qr_code_url' => (APP_URL ?? 'http://localhost') . "/api/v1/proshare/files/{$shortCode}/qr"
            ], 'File is now publicly shareable');
            
        } catch (\Exception $e) {
            Logger::error('API share file error: ' . $e->getMessage());
            $this->respondError('Failed to share file');
        }
    }
}
