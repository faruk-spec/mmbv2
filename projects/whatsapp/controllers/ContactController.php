<?php
/**
 * WhatsApp Contact Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;

class ContactController
{
    private $db;
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
        $this->db = Database::getInstance();
    }
    
    /**
     * Display contacts page
     */
    public function index()
    {
        $sessions = $this->getUserSessions();
        $contacts = $this->getUserContacts();
        
        View::render('whatsapp/contacts', [
            'user' => $this->user,
            'sessions' => $sessions,
            'contacts' => $contacts,
            'pageTitle' => 'WhatsApp Contacts'
        ]);
    }
    
    /**
     * Sync contacts from WhatsApp
     */
    public function sync()
    {
        header('Content-Type: application/json');
        
        try {
            $sessionId = $_POST['session_id'] ?? null;
            
            if (!$sessionId) {
                throw new \Exception('Session ID required');
            }
            
            // Verify session ownership
            $session = $this->db->fetch("
                SELECT id, status FROM whatsapp_sessions 
                WHERE id = ? AND user_id = ?
            ", [$sessionId, $this->user['id']]);
            
            if (!$session) {
                throw new \Exception('Session not found or access denied');
            }
            
            if ($session['status'] !== 'connected') {
                throw new \Exception('Session is not connected');
            }
            
            // In production, this would fetch contacts from WhatsApp Web API
            $contacts = $this->fetchContactsFromWhatsApp($sessionId);
            
            // Save contacts to database
            $syncedCount = $this->saveContacts($sessionId, $contacts);
            
            echo json_encode([
                'success' => true,
                'message' => "Synced $syncedCount contacts",
                'count' => $syncedCount
            ]);
            
        } catch (\Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get user's sessions
     */
    private function getUserSessions()
    {
        return $this->db->fetchAll("
            SELECT * FROM whatsapp_sessions 
            WHERE user_id = ? AND status = 'connected'
            ORDER BY created_at DESC
        ", [$this->user['id']]);
    }
    
    /**
     * Get user's contacts
     */
    private function getUserContacts()
    {
        return $this->db->fetchAll("
            SELECT c.*, s.session_name 
            FROM whatsapp_contacts c
            JOIN whatsapp_sessions s ON c.session_id = s.id
            WHERE s.user_id = ?
            ORDER BY c.name ASC
        ", [$this->user['id']]);
    }
    
    /**
     * Fetch contacts from WhatsApp (placeholder)
     */
    private function fetchContactsFromWhatsApp($sessionId)
    {
        // Placeholder - would fetch from actual WhatsApp Web API
        return [];
    }
    
    /**
     * Save contacts to database
     */
    private function saveContacts($sessionId, $contacts)
    {
        $count = 0;
        
        foreach ($contacts as $contact) {
            $this->db->query("
                INSERT INTO whatsapp_contacts (
                    session_id, phone_number, name, profile_pic, 
                    last_synced, created_at
                ) VALUES (?, ?, ?, ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    name = VALUES(name),
                    profile_pic = VALUES(profile_pic),
                    last_synced = NOW()
            ", [
                $sessionId,
                $contact['phone_number'] ?? '',
                $contact['name'] ?? 'Unknown',
                $contact['profile_pic'] ?? null
            ]);
            
            $count++;
        }
        
        return $count;
    }
}
