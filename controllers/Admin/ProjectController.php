<?php
/**
 * Admin Project Controller
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;

class ProjectController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }
    
    /**
     * List all projects
     */
    public function index(): void
    {
        $projects = require BASE_PATH . '/config/projects.php';
        
        $this->view('admin/projects/index', [
            'title' => 'Project Management',
            'projects' => $projects
        ]);
    }
    
    /**
     * Show project details
     */
    public function show(string $name): void
    {
        $projects = require BASE_PATH . '/config/projects.php';
        
        if (!isset($projects[$name])) {
            $this->flash('error', 'Project not found.');
            $this->redirect('/admin/projects');
            return;
        }
        
        $project = $projects[$name];
        $project['key'] = $name;
        
        $this->view('admin/projects/show', [
            'title' => $project['name'],
            'project' => $project
        ]);
    }
    
    /**
     * Toggle project status
     */
    public function toggle(string $name): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects');
            return;
        }
        
        // Note: In a full implementation, this would update a database or config file
        // For now, we just log the action
        Logger::activity(Auth::id(), 'project_toggled', ['project' => $name]);
        
        $this->flash('success', 'Project status updated.');
        $this->redirect('/admin/projects');
    }
    
    /**
     * Project settings
     */
    public function settings(string $name): void
    {
        $projects = require BASE_PATH . '/config/projects.php';
        
        if (!isset($projects[$name])) {
            $this->flash('error', 'Project not found.');
            $this->redirect('/admin/projects');
            return;
        }
        
        $project = $projects[$name];
        $project['key'] = $name;
        
        $this->view('admin/projects/settings', [
            'title' => $project['name'] . ' Settings',
            'project' => $project
        ]);
    }
    
    /**
     * Update project settings
     */
    public function updateSettings(string $name): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/projects/' . $name . '/settings');
            return;
        }
        
        // Note: In a full implementation, this would update the project configuration
        Logger::activity(Auth::id(), 'project_settings_updated', ['project' => $name]);
        
        $this->flash('success', 'Project settings updated.');
        $this->redirect('/admin/projects/' . $name);
    }
}
