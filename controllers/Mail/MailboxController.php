<?php
/**
 * Mailbox Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class MailboxController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function inbox()
    {
        $this->view('projects/mail/mailbox/inbox', [
            'pageTitle' => 'Inbox'
        ]);
    }
    
    public function sent()
    {
        $this->view('projects/mail/mailbox/sent', [
            'pageTitle' => 'Sent'
        ]);
    }
    
    public function drafts()
    {
        $this->view('projects/mail/mailbox/drafts', [
            'pageTitle' => 'Drafts'
        ]);
    }
    
    public function trash()
    {
        $this->view('projects/mail/mailbox/trash', [
            'pageTitle' => 'Trash'
        ]);
    }
    
    public function spam()
    {
        $this->view('projects/mail/mailbox/spam', [
            'pageTitle' => 'Spam'
        ]);
    }
    
    public function folder($id)
    {
        $this->view('projects/mail/mailbox/folder', [
            'pageTitle' => 'Folder',
            'id' => $id
        ]);
    }
}
