<?php
/**
 * Helpdesk Pro Template Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Core\Security;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class TemplateController
{
    private HelpdeskModel $model;

    public function __construct()
    {
        $this->model = new HelpdeskModel();
    }

    public function index(): void
    {
        $this->render('templates/index', [
            'title'      => 'Templates',
            'categories' => $this->model->getTemplateCategories(),
            'isAgent'    => $this->isAgent(),
        ]);
    }

    public function createCategory(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $this->flash('error', 'Category name is required.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $this->model->createTemplateCategory(
            mb_substr($name, 0, 100),
            mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000),
            mb_substr(trim((string) ($_POST['icon'] ?? 'folder')), 0, 50) ?: 'folder'
        );

        $this->flash('success', 'Category created.');
        $this->redirect('/projects/helpdeskpro/templates');
    }

    public function updateCategory(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $name = trim((string) ($_POST['name'] ?? ''));
        if ($name === '') {
            $this->flash('error', 'Category name is required.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $this->model->updateTemplateCategory(
            $id,
            mb_substr($name, 0, 100),
            mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000),
            mb_substr(trim((string) ($_POST['icon'] ?? 'folder')), 0, 50) ?: 'folder'
        );

        $this->flash('success', 'Category updated.');
        $this->redirect('/projects/helpdeskpro/templates');
    }

    public function deleteCategory(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $this->model->deleteTemplateCategory($id);
        $this->flash('success', 'Category deleted.');
        $this->redirect('/projects/helpdeskpro/templates');
    }

    public function viewCategory(int $id): void
    {
        $category = $this->model->getTemplateCategoryById($id);
        if (!$category) {
            http_response_code(404);
            echo 'Category not found.';
            return;
        }

        $this->render('templates/category', [
            'title'          => 'Category: ' . htmlspecialchars($category['name']),
            'category'       => $category,
            'subcategories'  => $this->model->getTemplateSubcategories($id),
            'isAgent'        => $this->isAgent(),
        ]);
    }

    public function createSubcategory(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($categoryId <= 0 || $name === '') {
            $this->flash('error', 'Category and name are required.');
            $this->redirect('/projects/helpdeskpro/templates/category/' . $categoryId);
        }

        $this->model->createTemplateSubcategory(
            $categoryId,
            mb_substr($name, 0, 100),
            mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000)
        );

        $this->flash('success', 'Subcategory created.');
        $this->redirect('/projects/helpdeskpro/templates/category/' . $categoryId);
    }

    public function deleteSubcategory(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $sub = $this->model->getTemplateSubcategoryById($id);
        $categoryId = $sub ? (int) $sub['category_id'] : 0;
        $this->model->deleteTemplateSubcategory($id);
        $this->flash('success', 'Subcategory deleted.');
        $this->redirect($categoryId > 0 ? '/projects/helpdeskpro/templates/category/' . $categoryId : '/projects/helpdeskpro/templates');
    }

    public function viewSubcategory(int $id): void
    {
        $sub = $this->model->getTemplateSubcategoryById($id);
        if (!$sub) {
            http_response_code(404);
            echo 'Subcategory not found.';
            return;
        }

        $category = $this->model->getTemplateCategoryById((int) $sub['category_id']);

        $this->render('templates/subcategory', [
            'title'       => 'Subcategory: ' . htmlspecialchars($sub['name']),
            'subcategory' => $sub,
            'category'    => $category,
            'items'       => $this->model->getTemplateItems($id),
            'isAgent'     => $this->isAgent(),
        ]);
    }

    public function createItem(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $subcategoryId = (int) ($_POST['subcategory_id'] ?? 0);
        $name = trim((string) ($_POST['name'] ?? ''));
        if ($subcategoryId <= 0 || $name === '') {
            $this->flash('error', 'Subcategory and name are required.');
            $this->redirect('/projects/helpdeskpro/templates/subcategory/' . $subcategoryId);
        }

        $this->model->createTemplateItem(
            $subcategoryId,
            mb_substr($name, 0, 150),
            mb_substr(trim((string) ($_POST['description'] ?? '')), 0, 1000),
            trim((string) ($_POST['default_priority'] ?? 'medium'))
        );

        $this->flash('success', 'Item created.');
        $this->redirect('/projects/helpdeskpro/templates/subcategory/' . $subcategoryId);
    }

    public function deleteItem(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $item = $this->model->getTemplateItemById($id);
        $subId = $item ? (int) $item['subcategory_id'] : 0;
        $this->model->deleteTemplateItem($id);
        $this->flash('success', 'Item deleted.');
        $this->redirect($subId > 0 ? '/projects/helpdeskpro/templates/subcategory/' . $subId : '/projects/helpdeskpro/templates');
    }

    public function viewItem(int $id): void
    {
        $item = $this->model->getTemplateItemById($id);
        if (!$item) {
            http_response_code(404);
            echo 'Item not found.';
            return;
        }

        $sub      = $this->model->getTemplateSubcategoryById((int) $item['subcategory_id']);
        $category = $sub ? $this->model->getTemplateCategoryById((int) $sub['category_id']) : null;

        $this->render('templates/item', [
            'title'       => 'Item: ' . htmlspecialchars($item['name']),
            'item'        => $item,
            'subcategory' => $sub,
            'category'    => $category,
            'fields'      => $this->model->getTemplateFields($id),
            'isAgent'     => $this->isAgent(),
        ]);
    }

    public function createField(): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $label  = trim((string) ($_POST['field_label'] ?? ''));
        if ($itemId <= 0 || $label === '') {
            $this->flash('error', 'Item and field label are required.');
            $this->redirect('/projects/helpdeskpro/templates/item/' . $itemId);
        }

        $this->model->createTemplateField(
            $itemId,
            mb_substr($label, 0, 120),
            trim((string) ($_POST['field_type'] ?? 'text')),
            mb_substr(trim((string) ($_POST['field_options'] ?? '')), 0, 2000),
            !empty($_POST['is_required'])
        );

        $this->flash('success', 'Field added.');
        $this->redirect('/projects/helpdeskpro/templates/item/' . $itemId);
    }

    public function deleteField(int $id): void
    {
        if (!$this->isAgent()) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/projects/helpdeskpro/templates');
        }

        $itemId = (int) ($_POST['item_id'] ?? 0);
        $this->model->deleteTemplateField($id);
        $this->flash('success', 'Field deleted.');
        $this->redirect($itemId > 0 ? '/projects/helpdeskpro/templates/item/' . $itemId : '/projects/helpdeskpro/templates');
    }

    private function isAgent(): bool
    {
        return Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::hasRole('support');
    }

    private function flash(string $type, string $message): void
    {
        $_SESSION['_flash'][$type] = $message;
    }

    private function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/' . $view . '.php';
    }
}
