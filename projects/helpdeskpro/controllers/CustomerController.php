<?php
/**
 * Helpdesk Pro Customer Controller
 *
 * @package MMB\Projects\HelpdeskPro\Controllers
 */

namespace Projects\HelpdeskPro\Controllers;

use Core\Auth;
use Projects\HelpdeskPro\Models\HelpdeskModel;

class CustomerController
{
    private HelpdeskModel $model;

    public function __construct()
    {
        $this->model = new HelpdeskModel();
    }

    public function index(): void
    {
        $this->render('customers/index', [
            'title'     => 'Customers',
            'customers' => $this->model->getCustomers(100),
            'isAgent'   => $this->isAgent(),
        ]);
    }

    public function view(int $userId): void
    {
        $customer = $this->model->getCustomerById($userId);
        if (!$customer) {
            http_response_code(404);
            echo 'Customer not found.';
            return;
        }

        $this->render('customers/view', [
            'title'        => 'Customer: ' . htmlspecialchars($customer['name']),
            'customer'     => $customer,
            'tickets'      => $this->model->getCustomerTickets($userId),
            'liveSessions' => $this->model->getCustomerLiveSessions($userId),
            'isAgent'      => $this->isAgent(),
        ]);
    }

    private function isAgent(): bool
    {
        return Auth::hasRole('admin') || Auth::hasRole('super_admin') || Auth::hasRole('support');
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/' . $view . '.php';
    }
}
