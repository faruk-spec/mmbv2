<?php
/**
 * Email Controller
 * Handles email operations
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class EmailController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function compose()
    {
        $this->view('projects/mail/email/compose', [
            'pageTitle' => 'Compose Email'
        ]);
    }
    
    public function send()
    {
        $this->flash('success', 'Email sent successfully');
        $this->redirect('/projects/mail/mailbox/inbox');
    }
    
    public function read($id)
    {
        $this->view('projects/mail/email/read', [
            'pageTitle' => 'Read Email',
            'id' => $id
        ]);
    }
    
    public function reply($id)
    {
        $this->flash('success', 'Reply sent');
        $this->redirect('/projects/mail/mailbox/inbox');
    }
    
    public function forward($id)
    {
        $this->flash('success', 'Email forwarded');
        $this->redirect('/projects/mail/mailbox/inbox');
    }
    
    public function delete()
    {
        $this->flash('success', 'Email deleted');
        $this->json(['success' => true]);
    }
    
    public function move()
    {
        $this->flash('success', 'Email moved');
        $this->json(['success' => true]);
    }
    
    public function markRead()
    {
        $this->json(['success' => true]);
    }
    
    public function markSpam()
    {
        $this->flash('success', 'Marked as spam');
        $this->json(['success' => true]);
    }
    
    public function star()
    {
        $this->json(['success' => true]);
    }
}
