<?php
/**
 * Deployment Dashboard Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;

class DeploymentController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
    }

    /**
     * Main deployment dashboard
     */
    public function index(): void
    {
        $title = 'Deployment Dashboard';
        \Core\View::render('admin/deployment/index', compact('title'));
    }

    /**
     * GitHub section
     */
    public function github(): void
    {
        $title = 'GitHub Repository';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'github']);
    }

    /**
     * Branches section
     */
    public function branches(): void
    {
        $title = 'Branch Manager';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'branches']);
    }

    /**
     * Deploy section
     */
    public function deploy(): void
    {
        $title = 'Deployment Center';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'deploy']);
    }

    /**
     * History section
     */
    public function history(): void
    {
        $title = 'Deployment History';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'history']);
    }

    /**
     * Version control section
     */
    public function versions(): void
    {
        $title = 'Version Control';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'versions']);
    }

    /**
     * Logs section
     */
    public function logs(): void
    {
        $title = 'Deployment Logs';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'logs']);
    }

    /**
     * Server section
     */
    public function server(): void
    {
        $title = 'Server Management';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'server']);
    }

    /**
     * Settings section
     */
    public function settings(): void
    {
        $title = 'Deployment Settings';
        \Core\View::render('admin/deployment/index', ['title' => $title, 'activeTab' => 'settings']);
    }
}
