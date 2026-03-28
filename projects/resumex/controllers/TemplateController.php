<?php
/**
 * ResumeX Template Controller
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\View;
use Projects\ResumeX\Models\ResumeModel;

class TemplateController
{
    private ResumeModel $resumeModel;

    public function __construct()
    {
        $this->resumeModel = new ResumeModel();
    }

    /**
     * Show all template options (requires login)
     */
    public function index(): void
    {
        if (!\Core\Auth::check()) {
            header('Location: /login?redirect=' . urlencode('/projects/resumex/templates'));
            exit;
        }

        $allThemes = $this->resumeModel->getAllThemePresets();

        View::render('projects/resumex/templates', [
            'title'     => 'Resume Templates',
            'user'      => Auth::user(),
            'allThemes' => $allThemes,
            'isAdmin'   => Auth::isAdmin(),
        ]);
    }
}
