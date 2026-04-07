<?php
namespace Controllers;

use Core\Database;
use Core\View;

class PagesController extends BaseController
{
    public function show(string $slug): void
    {
        try {
            $db = Database::getInstance();
            $page = $db->fetch(
                "SELECT * FROM pages WHERE slug = ? AND status = 'published'",
                [$slug]
            );

            if (!$page) {
                http_response_code(404);
                View::render('errors/404');
                return;
            }

            View::extend('main');
            View::render('pages/show', [
                'title'       => $page['meta_title'] ?: $page['title'],
                'page'        => $page,
                'show_navbar' => (bool)$page['show_navbar'],
                'show_footer' => (bool)$page['show_footer'],
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            View::render('errors/500');
        }
    }
}
