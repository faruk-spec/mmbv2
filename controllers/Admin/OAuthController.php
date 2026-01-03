<?php
/**
 * Admin OAuth Controller
 * Manages OAuth providers and user connections
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;

class OAuthController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * List OAuth providers
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        $providers = $db->fetchAll(
            "SELECT * FROM oauth_providers ORDER BY name ASC",
            []
        );
        
        $this->view('admin/oauth/index', [
            'title' => 'OAuth Providers',
            'providers' => $providers
        ]);
    }
    
    /**
     * Edit OAuth provider
     */
    public function edit(string $id): void
    {
        $db = Database::getInstance();
        $provider = $db->fetch("SELECT * FROM oauth_providers WHERE id = ?", [(int) $id]);
        
        if (!$provider) {
            $this->flash('error', 'Provider not found.');
            $this->redirect('/admin/oauth');
            return;
        }
        
        $this->view('admin/oauth/edit', [
            'title' => 'Edit OAuth Provider',
            'provider' => $provider
        ]);
    }
    
    /**
     * Update OAuth provider
     */
    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/oauth/' . $id . '/edit');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $updateData = [
                'client_id' => $this->input('client_id'),
                'client_secret' => $this->input('client_secret'),
                'redirect_uri' => $this->input('redirect_uri'),
                'scopes' => $this->input('scopes'),
                'is_enabled' => $this->input('is_enabled') === 'on' ? 1 : 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $db->update('oauth_providers', $updateData, 'id = ?', [(int) $id]);
            
            Logger::activity(Auth::id(), 'oauth_provider_updated', ['provider_id' => (int) $id]);
            
            $this->flash('success', 'OAuth provider updated successfully.');
            $this->redirect('/admin/oauth');
            
        } catch (\Exception $e) {
            Logger::error('OAuth provider update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update OAuth provider.');
            $this->redirect('/admin/oauth/' . $id . '/edit');
        }
    }
    
    /**
     * List user OAuth connections
     */
    public function connections(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $connections = $db->fetchAll(
            "SELECT ouc.*, u.name as user_name, u.email as user_email, 
                    op.display_name as provider_name
             FROM oauth_user_connections ouc
             JOIN users u ON ouc.user_id = u.id
             JOIN oauth_providers op ON ouc.provider_id = op.id
             ORDER BY ouc.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM oauth_user_connections",
            []
        );
        
        $this->view('admin/oauth/connections', [
            'title' => 'OAuth Connections',
            'connections' => $connections,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Revoke OAuth connection
     */
    public function revokeConnection(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/oauth/connections');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $connection = $db->fetch(
                "SELECT user_id FROM oauth_user_connections WHERE id = ?",
                [(int) $id]
            );
            
            if ($connection) {
                $db->delete('oauth_user_connections', 'id = ?', [(int) $id]);
                
                Logger::activity(Auth::id(), 'oauth_connection_revoked', [
                    'connection_id' => (int) $id,
                    'user_id' => $connection['user_id']
                ]);
                
                $this->flash('success', 'OAuth connection revoked successfully.');
            }
            
        } catch (\Exception $e) {
            Logger::error('Revoke OAuth connection error: ' . $e->getMessage());
            $this->flash('error', 'Failed to revoke OAuth connection.');
        }
        
        $this->redirect('/admin/oauth/connections');
    }
}
