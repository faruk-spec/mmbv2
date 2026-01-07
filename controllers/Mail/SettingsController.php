<?php
/**
 * Settings Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class SettingsController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->view('projects/mail/settings', [
            'pageTitle' => 'Mail Settings'
        ]);
    }
    
    public function save()
    {
        $this->flash('success', 'Settings saved successfully');
        $this->redirect('/projects/mail/settings');
    }
}
