<?php
/**
 * Search Controller
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;

class SearchController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    public function index()
    {
        $this->view('projects/mail/search', [
            'pageTitle' => 'Search Mail'
        ]);
    }
    
    public function search()
    {
        $query = $_POST['query'] ?? '';
        $this->json(['results' => []]);
    }
}
