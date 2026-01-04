<?php
/**
 * Auto Responder Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class AutoResponderController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->view('projects/mail/auto-responder', [
            'pageTitle' => 'Auto Responder'
        ]);
    }
    
    public function save()
    {
        $this->flash('success', 'Auto responder saved successfully');
        $this->redirect('/projects/mail/auto-responder');
    }
    
    public function toggle()
    {
        $this->flash('success', 'Auto responder toggled');
        $this->redirect('/projects/mail/auto-responder');
    }
}
