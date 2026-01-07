<?php

namespace Controllers\Mail;

use Core\View;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

/**
 * AliasController
 * Handles email alias management for subscriber owners
 */
class AliasController extends BaseController
{
    private $db;
    private $subscriberId;

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        
        // Get subscriber ID from session
        $userId = Auth::id();
        $mailbox = $this->db->fetch(
            "SELECT subscriber_id FROM mail_mailboxes WHERE user_id = ? AND role_type = 'subscriber_owner'",
            [$userId]
        );
        
        if (!$mailbox) {
            $this->error('Access denied. Subscriber owner access required.');
            return;
        }
        
        $this->subscriberId = $mailbox['subscriber_id'];
    }

    /**
     * List all aliases
     */
    public function index()
    {
        // Check plan limits
        $plan = $this->db->fetch(
            "SELECT sp.max_aliases, 
                    (SELECT COUNT(*) FROM mail_aliases WHERE subscriber_id = ?) as aliases_count
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId, $this->subscriberId]
        );

        $aliases = $this->db->fetchAll(
            "SELECT a.*, 
                    d.domain_name,
                    m.email as destination_email
             FROM mail_aliases a
             JOIN mail_domains d ON a.domain_id = d.id
             LEFT JOIN mail_mailboxes m ON a.destination_mailbox_id = m.id
             WHERE a.subscriber_id = ?
             ORDER BY a.created_at DESC",
            [$this->subscriberId]
        );

        View::render('mail/subscriber/aliases', [
            'aliases' => $aliases,
            'plan' => $plan,
            'subscriberId' => $this->subscriberId
        ]);
    }

    /**
     * Show add alias form
     */
    public function create()
    {
        // Get verified domains
        $domains = $this->db->fetchAll(
            "SELECT * FROM mail_domains WHERE subscriber_id = ? AND is_verified = 1 AND is_active = 1",
            [$this->subscriberId]
        );

        // Get mailboxes
        $mailboxes = $this->db->fetchAll(
            "SELECT m.*, d.domain_name 
             FROM mail_mailboxes m
             JOIN mail_domains d ON m.domain_id = d.id
             WHERE m.subscriber_id = ?
             ORDER BY m.email",
            [$this->subscriberId]
        );

        View::render('mail/subscriber/add-alias', [
            'domains' => $domains,
            'mailboxes' => $mailboxes,
            'subscriberId' => $this->subscriberId
        ]);
    }

    /**
     * Store new alias
     */
    public function store()
    {
        $aliasName = trim($_POST['alias_name'] ?? '');
        $domainId = intval($_POST['domain_id'] ?? 0);
        $destinationType = $_POST['destination_type'] ?? 'mailbox';
        $destinationMailboxId = intval($_POST['destination_mailbox_id'] ?? 0);
        $destinationEmail = trim($_POST['destination_email'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Check plan limits
        $plan = $this->db->fetch(
            "SELECT sp.max_aliases, 
                    (SELECT COUNT(*) FROM mail_aliases WHERE subscriber_id = ?) as aliases_count
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId, $this->subscriberId]
        );

        if ($plan['aliases_count'] >= $plan['max_aliases']) {
            $this->error('Alias limit reached for your plan. Please upgrade to add more aliases.');
            return;
        }

        // Validate input
        if (empty($aliasName) || empty($domainId)) {
            $this->error('Alias name and domain are required');
            return;
        }

        // Verify domain ownership
        $domain = $this->db->fetch(
            "SELECT * FROM mail_domains WHERE id = ? AND subscriber_id = ?",
            [$domainId, $this->subscriberId]
        );

        if (!$domain) {
            $this->error('Invalid domain');
            return;
        }

        $fullAlias = $aliasName . '@' . $domain['domain_name'];

        // Check if alias already exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_aliases WHERE alias_email = ?",
            [$fullAlias]
        );

        if ($existing) {
            $this->error('Alias already exists');
            return;
        }

        // Check if mailbox exists with this email
        $existingMailbox = $this->db->fetch(
            "SELECT id FROM mail_mailboxes WHERE email = ?",
            [$fullAlias]
        );

        if ($existingMailbox) {
            $this->error('A mailbox already exists with this email address');
            return;
        }

        // Determine destination
        $destination = '';
        if ($destinationType === 'mailbox' && $destinationMailboxId > 0) {
            // Verify mailbox ownership
            $mailbox = $this->db->fetch(
                "SELECT email FROM mail_mailboxes WHERE id = ? AND subscriber_id = ?",
                [$destinationMailboxId, $this->subscriberId]
            );
            if ($mailbox) {
                $destination = $mailbox['email'];
            }
        } elseif ($destinationType === 'external' && !empty($destinationEmail)) {
            $destination = $destinationEmail;
        }

        if (empty($destination)) {
            $this->error('Valid destination is required');
            return;
        }

        // Insert alias
        $this->db->query(
            "INSERT INTO mail_aliases (subscriber_id, domain_id, alias_email, destination_type, 
                                       destination_mailbox_id, destination_email, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [
                $this->subscriberId,
                $domainId,
                $fullAlias,
                $destinationType,
                $destinationType === 'mailbox' ? $destinationMailboxId : null,
                $destination,
                $isActive
            ]
        );

        $this->success('Alias created successfully');
        $this->redirect('/projects/mail/subscriber/aliases');
    }

    /**
     * Toggle alias status
     */
    public function toggleStatus($aliasId)
    {
        $alias = $this->db->fetch(
            "SELECT * FROM mail_aliases WHERE id = ? AND subscriber_id = ?",
            [$aliasId, $this->subscriberId]
        );

        if (!$alias) {
            return $this->json(['success' => false, 'message' => 'Alias not found']);
        }

        $newStatus = $alias['is_active'] ? 0 : 1;
        $this->db->query(
            "UPDATE mail_aliases SET is_active = ? WHERE id = ?",
            [$newStatus, $aliasId]
        );

        return $this->json([
            'success' => true,
            'message' => 'Alias ' . ($newStatus ? 'activated' : 'deactivated') . ' successfully',
            'is_active' => $newStatus
        ]);
    }

    /**
     * Delete alias
     */
    public function delete($aliasId)
    {
        $alias = $this->db->fetch(
            "SELECT * FROM mail_aliases WHERE id = ? AND subscriber_id = ?",
            [$aliasId, $this->subscriberId]
        );

        if (!$alias) {
            return $this->json(['success' => false, 'message' => 'Alias not found']);
        }

        $this->db->query("DELETE FROM mail_aliases WHERE id = ?", [$aliasId]);

        return $this->json(['success' => true, 'message' => 'Alias deleted successfully']);
    }
}
