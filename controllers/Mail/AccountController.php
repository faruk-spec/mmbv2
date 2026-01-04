<?php
/**
 * Account Controller
 * Handles mailbox account management
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class AccountController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->view('projects/mail/accounts/index', [
            'pageTitle' => 'Mail Accounts'
        ]);
    }
    
    public function create()
    {
        $this->view('projects/mail/accounts/create', [
            'pageTitle' => 'Create Account'
        ]);
    }
    
    public function store()
    {
        $this->flash('success', 'Account created successfully');
        $this->redirect('/projects/mail/accounts');
    }
    
    public function edit($id)
    {
        $this->view('projects/mail/accounts/edit', [
            'pageTitle' => 'Edit Account',
            'id' => $id
        ]);
    }
    
    public function update($id)
    {
        $this->flash('success', 'Account updated successfully');
        $this->redirect('/projects/mail/accounts');
    }
    
    public function delete($id)
    {
        $this->flash('success', 'Account deleted successfully');
        $this->redirect('/projects/mail/accounts');
    }
}
