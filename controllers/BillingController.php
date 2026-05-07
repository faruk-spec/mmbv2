<?php

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;

class BillingController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
        $this->ensureBillingTable();
    }

    private function ensureBillingTable(): void
    {
        try {
            $this->db->query("CREATE TABLE IF NOT EXISTS `user_billing_details` (
              `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT UNSIGNED NOT NULL,
              `full_name` VARCHAR(100) NOT NULL DEFAULT '',
              `email` VARCHAR(255) NOT NULL DEFAULT '',
              `phone` VARCHAR(20) NOT NULL DEFAULT '',
              `address_line1` VARCHAR(255) NOT NULL DEFAULT '',
              `address_line2` VARCHAR(255) NULL DEFAULT NULL,
              `city` VARCHAR(100) NOT NULL DEFAULT '',
              `state` VARCHAR(100) NOT NULL DEFAULT '',
              `postal_code` VARCHAR(20) NOT NULL DEFAULT '',
              `country` VARCHAR(100) NOT NULL DEFAULT '',
              `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
              `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
              UNIQUE KEY `uniq_user` (`user_id`),
              FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        } catch (\Throwable $e) {}
    }

    public function getBillingDetails(int $userId): ?array
    {
        try {
            return $this->db->fetch("SELECT * FROM user_billing_details WHERE user_id = ?", [$userId]) ?: null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function isComplete(?array $billing): bool
    {
        if (!$billing) return false;
        return !empty($billing['full_name']) && !empty($billing['email'])
            && !empty($billing['phone']) && !empty($billing['address_line1'])
            && !empty($billing['city']) && !empty($billing['state'])
            && !empty($billing['postal_code']) && !empty($billing['country']);
    }

    public function show(): void
    {
        $user = Auth::user();
        $billing = $this->getBillingDetails(Auth::id());

        if (!$billing) {
            $billing = [
                'full_name' => $user['name'] ?? '',
                'email' => $user['email'] ?? '',
                'phone' => $user['phone'] ?? '',
                'address_line1' => '',
                'address_line2' => '',
                'city' => '',
                'state' => '',
                'postal_code' => '',
                'country' => '',
            ];
        }

        $next = $_GET['next'] ?? $_SESSION['billing_next'] ?? '/plans';
        $_SESSION['billing_next'] = $next;

        $this->view('dashboard/billing-details', [
            'title' => 'Billing Details',
            'billing' => $billing,
            'next' => $next,
        ]);
    }

    public function save(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/billing-details');
            return;
        }

        $data = [
            'user_id' => Auth::id(),
            'full_name' => Security::sanitize(trim($this->input('full_name', ''))),
            'email' => trim($this->input('billing_email', '')),
            'phone' => Security::sanitize(trim($this->input('billing_phone', ''))),
            'address_line1' => Security::sanitize(trim($this->input('address_line1', ''))),
            'address_line2' => Security::sanitize(trim($this->input('address_line2', ''))) ?: null,
            'city' => Security::sanitize(trim($this->input('city', ''))),
            'state' => Security::sanitize(trim($this->input('state', ''))),
            'postal_code' => Security::sanitize(trim($this->input('postal_code', ''))),
            'country' => Security::sanitize(trim($this->input('country', ''))),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $required = ['full_name', 'email', 'phone', 'address_line1', 'city', 'state', 'postal_code', 'country'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $this->flash('error', 'Please fill in all required fields.');
                $this->redirect('/billing-details');
                return;
            }
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Please enter a valid email address.');
            $this->redirect('/billing-details');
            return;
        }

        try {
            $existing = $this->db->fetch("SELECT id FROM user_billing_details WHERE user_id = ?", [Auth::id()]);
            if ($existing) {
                unset($data['user_id']);
                $this->db->update('user_billing_details', $data, 'user_id = ?', [Auth::id()]);
            } else {
                $data['created_at'] = date('Y-m-d H:i:s');
                $this->db->insert('user_billing_details', $data);
            }
        } catch (\Throwable $e) {
            Logger::error('BillingController save: ' . $e->getMessage());
            $this->flash('error', 'Failed to save billing details. Please try again.');
            $this->redirect('/billing-details');
            return;
        }

        $next = $_SESSION['billing_next'] ?? '/plans';
        unset($_SESSION['billing_next']);
        $this->flash('success', 'Billing details saved.');
        $this->redirect($next);
    }
}
