<?php
/**
 * Folder Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class FolderController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->json(['folders' => []]);
    }
    
    public function create()
    {
        $this->flash('success', 'Folder created');
        $this->json(['success' => true]);
    }
    
    public function edit($id)
    {
        $this->flash('success', 'Folder updated');
        $this->json(['success' => true]);
    }
    
    public function delete($id)
    {
        $this->flash('success', 'Folder deleted');
        $this->json(['success' => true]);
    }
}
