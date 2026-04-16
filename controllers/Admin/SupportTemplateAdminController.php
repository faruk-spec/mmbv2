<?php
/**
 * Support Template Admin Controller
 *
 * Manages Template Groups, Categories, and provides the drag-and-drop
 * Form Builder UI page for admins.
 *
 * Routes (registered in routes/admin.php):
 *   GET  /admin/support/groups
 *   POST /admin/support/groups/create
 *   POST /admin/support/groups/{id}/update
 *   POST /admin/support/groups/{id}/delete
 *   GET  /admin/support/groups/{group_id}/categories
 *   POST /admin/support/groups/{group_id}/categories/create
 *   POST /admin/support/categories/{id}/update
 *   POST /admin/support/categories/{id}/delete
 *   GET  /admin/support/builder/{category_id}
 *
 * @package Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Models\SupportTemplateModel;

class SupportTemplateAdminController extends BaseController
{
    private SupportTemplateModel $model;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->model = new SupportTemplateModel();
    }

    // =========================================================================
    // Template Groups
    // =========================================================================

    /** GET /admin/support/groups */
    public function groups(): void
    {
        $groups     = $this->model->getAllGroups();
        $categories = $this->model->getAllCategories();

        $this->view('admin/support/groups', [
            'title'      => 'Support Template Groups',
            'groups'     => $groups,
            'categories' => $categories,
        ]);
    }

    /** POST /admin/support/groups/create */
    public function createGroup(): void
    {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('error', 'Group name is required.');
            $this->redirect('/admin/support/groups');
            return;
        }

        $this->model->createGroup([
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
            'icon'        => trim($_POST['icon'] ?? 'users'),
            'color'       => trim($_POST['color'] ?? '#00f0ff'),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        $this->flash('success', "Group '{$name}' created.");
        $this->redirect('/admin/support/groups');
    }

    /** POST /admin/support/groups/{id}/update */
    public function updateGroup(int $id): void
    {
        $this->validateCsrf();

        $this->model->updateGroup($id, [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon'        => trim($_POST['icon'] ?? 'users'),
            'color'       => trim($_POST['color'] ?? '#00f0ff'),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ]);

        $this->flash('success', 'Group updated.');
        $this->redirect('/admin/support/groups');
    }

    /** POST /admin/support/groups/{id}/delete */
    public function deleteGroup(int $id): void
    {
        $this->validateCsrf();
        $this->model->deleteGroup($id);
        $this->flash('success', 'Group and its categories deleted.');
        $this->redirect('/admin/support/groups');
    }

    // =========================================================================
    // Categories
    // =========================================================================

    /** GET /admin/support/groups/{group_id}/categories */
    public function categories(int $group_id): void
    {
        $group      = $this->model->getGroupById($group_id);
        if (!$group) {
            $this->flash('error', 'Group not found.');
            $this->redirect('/admin/support/groups');
            return;
        }
        $categories = $this->model->getCategoriesByGroup($group_id);

        $this->view('admin/support/categories', [
            'title'      => "Categories — {$group['name']}",
            'group'      => $group,
            'categories' => $categories,
        ]);
    }

    /** POST /admin/support/groups/{group_id}/categories/create */
    public function createCategory(int $group_id): void
    {
        $this->validateCsrf();

        $name = trim($_POST['name'] ?? '');
        if ($name === '') {
            $this->flash('error', 'Category name is required.');
            $this->redirect('/admin/support/groups/' . $group_id . '/categories');
            return;
        }

        $this->model->createCategory([
            'group_id'    => $group_id,
            'name'        => $name,
            'description' => trim($_POST['description'] ?? ''),
            'icon'        => trim($_POST['icon'] ?? 'tag'),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
        ]);

        $this->flash('success', "Category '{$name}' created.");
        $this->redirect('/admin/support/groups/' . $group_id . '/categories');
    }

    /** POST /admin/support/categories/{id}/update */
    public function updateCategory(int $id): void
    {
        $this->validateCsrf();

        $cat = $this->model->getCategoryById($id);
        $backUrl = $cat ? '/admin/support/groups/' . $cat['group_id'] . '/categories' : '/admin/support/groups';

        $this->model->updateCategory($id, [
            'name'        => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'icon'        => trim($_POST['icon'] ?? 'tag'),
            'sort_order'  => (int) ($_POST['sort_order'] ?? 0),
            'is_active'   => !empty($_POST['is_active']) ? 1 : 0,
        ]);

        $this->flash('success', 'Category updated.');
        $this->redirect($backUrl);
    }

    /** POST /admin/support/categories/{id}/delete */
    public function deleteCategory(int $id): void
    {
        $this->validateCsrf();

        $cat = $this->model->getCategoryById($id);
        $backUrl = $cat ? '/admin/support/groups/' . $cat['group_id'] . '/categories' : '/admin/support/groups';

        $this->model->deleteCategory($id);
        $this->flash('success', 'Category deleted.');
        $this->redirect($backUrl);
    }

    // =========================================================================
    // Form Builder
    // =========================================================================

    /** GET /admin/support/builder/{category_id} */
    public function builder(int $category_id): void
    {
        $category = $this->model->getCategoryById($category_id);
        if (!$category) {
            $this->flash('error', 'Category not found.');
            $this->redirect('/admin/support/groups');
            return;
        }

        $template = $this->model->getActiveTemplate($category_id);
        $history  = $this->model->getTemplateHistory($category_id);

        $this->view('admin/support/builder', [
            'title'      => "Form Builder — {$category['name']}",
            'category'   => $category,
            'template'   => $template,
            'history'    => $history,
        ]);
    }
}
