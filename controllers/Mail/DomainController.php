<?php

namespace Controllers\Mail;

use Core\View;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

/**
 * DomainController
 * Handles domain management operations for subscriber owners
 */
class DomainController extends BaseController
{
    private $db;
    private $subscriberId;

    public function __construct()
    {
        // BaseController doesn't have a constructor, so no need to call parent::__construct()
        
        // Initialize database with error handling
        try {
            $this->db = Database::getInstance();
        } catch (\Throwable $e) {
            error_log('Warning: Database initialization failed in DomainController: ' . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Ensure database and subscriber access
     */
    private function ensureDatabaseAndSubscriber()
    {
        if ($this->db === null) {
            try {
                $this->db = Database::getInstance();
            } catch (\Throwable $e) {
                error_log('Failed to initialize database in DomainController: ' . $e->getMessage());
                throw new \RuntimeException('Database is not available. Please try again later.');
            }
        }
        
        if ($this->subscriberId === null) {
            // Get subscriber ID from session
            $userId = Auth::id();
            $mailbox = $this->db->fetch(
                "SELECT subscriber_id FROM mail_mailboxes WHERE mmb_user_id = ? AND role_type = 'subscriber_owner'",
                [$userId]
            );
            
            if (!$mailbox) {
                throw new \RuntimeException('Access denied. Subscriber owner access required.');
            }
            
            $this->subscriberId = $mailbox['subscriber_id'];
        }
    }

    /**
     * List all domains for subscriber
     */
    public function index()
    {
        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();
        
        $domains = $this->db->fetchAll(
            "SELECT d.*, 
                    COUNT(DISTINCT m.id) as mailboxes_count,
                    COUNT(DISTINCT a.id) as aliases_count
             FROM mail_domains d
             LEFT JOIN mail_mailboxes m ON d.id = m.domain_id
             LEFT JOIN mail_aliases a ON d.id = a.domain_id
             WHERE d.subscriber_id = ?
             GROUP BY d.id
             ORDER BY d.created_at DESC",
            [$this->subscriberId]
        );

        View::render('mail/subscriber/domains', [
            'domains' => $domains,
            'subscriberId' => $this->subscriberId
        ]);
    }

    /**
     * Show add domain form
     */
    public function create()
        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();

    {
        View::render('mail/subscriber/add-domain', [
            'subscriberId' => $this->subscriberId
        ]);
    }

    /**
        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();

     * Store new domain
     */
    public function store()
    {
        $domainName = trim($_POST['domain_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $catchAllEmail = trim($_POST['catch_all_email'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Clean domain name
        $domainName = $this->cleanDomainName($domainName);

        // Validate domain
        if (empty($domainName)) {
            $this->error('Domain name is required');
            return;
        }

        if (!$this->isValidDomain($domainName)) {
            $this->error('Invalid domain name format');
            return;
        }

        // Check if domain already exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_domains WHERE domain_name = ?",
            [$domainName]
        );

        if ($existing) {
            $this->error('Domain already exists in the system');
            return;
        }

        // Insert domain
        $this->db->query(
            "INSERT INTO mail_domains (subscriber_id, domain_name, description, catch_all_email, 
                                       is_active, is_verified, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 0, NOW(), NOW())",
            [$this->subscriberId, $domainName, $description, $catchAllEmail, $isActive]
        );

        $domainId = $this->db->lastInsertId();

        // Generate DNS records
        $this->generateDNSRecords($domainId, $domainName);

        $this->success('Domain added successfully. Please configure DNS records to verify.');
        $this->redirect('/projects/mail/subscriber/domains/' . $domainId . '/dns');
        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();

    }

    /**
     * Show DNS records for domain
     */
    public function dnsRecords($domainId)
    {
        $domain = $this->db->fetch(
            "SELECT * FROM mail_domains WHERE id = ? AND subscriber_id = ?",
            [$domainId, $this->subscriberId]
        );

        if (!$domain) {
            $this->error('Domain not found');
            $this->redirect('/projects/mail/subscriber/domains');
            return;
        }

        $dnsRecords = $this->db->fetchAll(
            "SELECT * FROM mail_dns_records WHERE domain_id = ? ORDER BY record_type, priority",
            [$domainId]
        );

        View::render('mail/subscriber/dns-records', [
            'domain' => $domain,
        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();

            'dnsRecords' => $dnsRecords,
            'subscriberId' => $this->subscriberId
        ]);
    }

    /**
     * Verify domain DNS records
     */
    public function verify($domainId)
    {
        $domain = $this->db->fetch(
            "SELECT * FROM mail_domains WHERE id = ? AND subscriber_id = ?",
            [$domainId, $this->subscriberId]
        );

        if (!$domain) {
            return $this->json(['success' => false, 'message' => 'Domain not found']);
        }

        // Get DNS records
        $dnsRecords = $this->db->fetchAll(
            "SELECT * FROM mail_dns_records WHERE domain_id = ?",
            [$domainId]
        );

        $verified = true;
        $results = [];

        foreach ($dnsRecords as $record) {
            $isValid = $this->verifyDNSRecord($domain['domain_name'], $record);
            $results[] = [
                'type' => $record['record_type'],
                'verified' => $isValid
            ];

            if (!$isValid) {
                $verified = false;
            }

            // Update record status
            $this->db->query(
                "UPDATE mail_dns_records SET is_verified = ?, last_verified_at = NOW() WHERE id = ?",
                [$isValid ? 1 : 0, $record['id']]
            );
        }

        // Update domain verification status
        if ($verified) {
            $this->db->query(
                "UPDATE mail_domains SET is_verified = 1, verified_at = NOW() WHERE id = ?",
                [$domainId]
            );
        }

        // Ensure database and subscriber access
        $this->ensureDatabaseAndSubscriber();

        return $this->json([
            'success' => true,
            'verified' => $verified,
            'results' => $results,
            'message' => $verified ? 'Domain verified successfully!' : 'Some DNS records are not configured correctly'
        ]);
    }

    /**
     * Delete domain
     */
    public function delete($domainId)
    {
        $domain = $this->db->fetch(
            "SELECT * FROM mail_domains WHERE id = ? AND subscriber_id = ?",
            [$domainId, $this->subscriberId]
        );

        if (!$domain) {
            return $this->json(['success' => false, 'message' => 'Domain not found']);
        }

        // Check if domain has mailboxes
        $mailboxCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM mail_mailboxes WHERE domain_id = ?",
            [$domainId]
        )['count'];

        if ($mailboxCount > 0) {
            return $this->json(['success' => false, 'message' => 'Cannot delete domain with active mailboxes']);
        }

        // Delete DNS records
        $this->db->query("DELETE FROM mail_dns_records WHERE domain_id = ?", [$domainId]);

        // Delete domain
        $this->db->query("DELETE FROM mail_domains WHERE id = ?", [$domainId]);

        return $this->json(['success' => true, 'message' => 'Domain deleted successfully']);
    }

    /**
     * Generate DNS records for domain
     */
    private function generateDNSRecords($domainId, $domainName)
    {
        $config = require __DIR__ . '/../config.php';
        $systemDomain = $config['system_domain'] ?? 'mail.yourdomain.com';

        // MX Records
        $this->db->query(
            "INSERT INTO mail_dns_records (domain_id, record_type, record_name, record_value, priority, ttl, created_at)
             VALUES (?, 'MX', '@', ?, 10, 3600, NOW())",
            [$domainId, 'mail.' . $systemDomain]
        );

        $this->db->query(
            "INSERT INTO mail_dns_records (domain_id, record_type, record_name, record_value, priority, ttl, created_at)
             VALUES (?, 'MX', '@', ?, 20, 3600, NOW())",
            [$domainId, 'mail2.' . $systemDomain]
        );

        // SPF Record
        $spfValue = 'v=spf1 include:' . $systemDomain . ' ~all';
        $this->db->query(
            "INSERT INTO mail_dns_records (domain_id, record_type, record_name, record_value, ttl, created_at)
             VALUES (?, 'TXT', '@', ?, 3600, NOW())",
            [$domainId, $spfValue]
        );

        // DKIM Record
        $dkimSelector = 'mail';
        $dkimPublicKey = $this->generateDKIMKey($domainId);
        $dkimValue = 'v=DKIM1; k=rsa; p=' . $dkimPublicKey;
        $this->db->query(
            "INSERT INTO mail_dns_records (domain_id, record_type, record_name, record_value, ttl, created_at)
             VALUES (?, 'TXT', ?, ?, 3600, NOW())",
            [$domainId, $dkimSelector . '._domainkey', $dkimValue]
        );

        // DMARC Record
        $dmarcValue = 'v=DMARC1; p=quarantine; rua=mailto:dmarc@' . $domainName . '; ruf=mailto:dmarc@' . $domainName . '; pct=100';
        $this->db->query(
            "INSERT INTO mail_dns_records (domain_id, record_type, record_name, record_value, ttl, created_at)
             VALUES (?, 'TXT', '_dmarc', ?, 3600, NOW())",
            [$domainId, $dmarcValue]
        );
    }

    /**
     * Generate DKIM key pair
     */
    private function generateDKIMKey($domainId)
    {
        // Generate RSA key pair
        $config = [
            "digest_alg" => "sha256",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];

        $res = openssl_pkey_new($config);
        openssl_pkey_export($res, $privateKey);
        $publicKey = openssl_pkey_get_details($res)['key'];

        // Extract public key for DNS record
        preg_match('/-----BEGIN PUBLIC KEY-----\s*(.+?)\s*-----END PUBLIC KEY-----/s', $publicKey, $matches);
        $publicKeyForDNS = str_replace(["\n", "\r", " "], '', $matches[1] ?? '');

        // Store private key in database
        $this->db->query(
            "UPDATE mail_domains SET dkim_private_key = ?, dkim_public_key = ? WHERE id = ?",
            [$privateKey, $publicKeyForDNS, $domainId]
        );

        return $publicKeyForDNS;
    }

    /**
     * Verify DNS record
     */
    private function verifyDNSRecord($domain, $record)
    {
        try {
            switch ($record['record_type']) {
                case 'MX':
                    $records = dns_get_record($domain, DNS_MX);
                    foreach ($records as $r) {
                        if (strpos($r['target'], $record['record_value']) !== false) {
                            return true;
                        }
                    }
                    break;

                case 'TXT':
                    $records = dns_get_record($record['record_name'] . '.' . $domain, DNS_TXT);
                    foreach ($records as $r) {
                        if (strpos($r['txt'], $record['record_value']) !== false) {
                            return true;
                        }
                    }
                    break;
            }
        } catch (Exception $e) {
            // DNS lookup failed
            return false;
        }

        return false;
    }

    /**
     * Clean domain name
     */
    private function cleanDomainName($domain)
    {
        // Remove http://, https://, www.
        $domain = preg_replace('#^https?://#', '', $domain);
        $domain = preg_replace('#^www\.#', '', $domain);
        $domain = rtrim($domain, '/');
        $domain = strtolower($domain);
        
        return $domain;
    }

    /**
     * Validate domain name
     */
    private function isValidDomain($domain)
    {
        return preg_match('/^([a-z0-9]+(-[a-z0-9]+)*\.)+[a-z]{2,}$/', $domain);
    }
}
