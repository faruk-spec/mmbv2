<?php
/**
 * Filter Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class FilterController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->view('projects/mail/filters/index', [
            'pageTitle' => 'Mail Filters'
        ]);
    }
    
    public function create()
    {
        $this->view('projects/mail/filters/create', [
            'pageTitle' => 'Create Filter'
        ]);
    }
    
    public function store()
    {
        $this->flash('success', 'Filter created');
        $this->redirect('/projects/mail/filters');
    }
    
    public function edit($id)
    {
        $this->view('projects/mail/filters/edit', [
            'pageTitle' => 'Edit Filter',
            'id' => $id
        ]);
    }
    
    public function update($id)
    {
        $this->flash('success', 'Filter updated');
        $this->redirect('/projects/mail/filters');
    }
    
    public function delete($id)
    {
        $this->flash('success', 'Filter deleted');
        $this->redirect('/projects/mail/filters');
    }
    
    public function toggle($id)
    {
        $this->json(['success' => true]);
    }
}
