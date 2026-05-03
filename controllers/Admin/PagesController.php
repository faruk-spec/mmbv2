<?php
namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\ActivityLogger;

class PagesController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('pages');
    }

    public function index(): void
    {
        $db = Database::getInstance();

        // Auto-seed default Privacy and Policy pages if missing.
        $this->seedLegalPages($db);

        $pages = $db->fetchAll("SELECT * FROM pages ORDER BY sort_order ASC, created_at DESC");

        $this->view('admin/pages/index', [
            'title' => 'Pages',
            'pages' => $pages,
        ]);
    }

    public function create(): void
    {
        $this->view('admin/pages/create', ['title' => 'Create Page']);
    }

    public function store(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages/create');
            return;
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'slug'  => 'required|max:200',
        ]);

        if (!empty($errors)) {
            $this->redirect('/admin/pages/create');
            return;
        }

        try {
            $db = Database::getInstance();
            $slug = $this->sanitizeSlug($this->input('slug'));

            $existing = $db->fetch("SELECT id FROM pages WHERE slug = ?", [$slug]);
            if ($existing) {
                $this->flash('error', 'A page with that slug already exists.');
                $this->redirect('/admin/pages/create');
                return;
            }

            $id = $db->insert('pages', [
                'title'            => Security::sanitize($this->input('title')),
                'slug'             => $slug,
                'content'          => $this->input('content', ''),
                'meta_title'       => Security::sanitize($this->input('meta_title', '')),
                'meta_description' => Security::sanitize($this->input('meta_description', '')),
                'show_navbar'      => $this->input('show_navbar') ? 1 : 0,
                'show_footer'      => $this->input('show_footer') ? 1 : 0,
                'status'           => in_array($this->input('status'), ['published','draft']) ? $this->input('status') : 'draft',
                'sort_order'       => (int)$this->input('sort_order', 0),
                'created_by'       => Auth::id(),
            ]);

            ActivityLogger::log(Auth::id(), 'page_created', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => $id, 'entity_name' => $this->input('title'), 'new_values' => ['title' => $this->input('title')]]);
            $this->flash('success', 'Page created successfully.');
            $this->redirect('/admin/pages');
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to create page: ' . $e->getMessage());
            $this->redirect('/admin/pages/create');
        }
    }

    public function edit(string $id): void
    {
        $db = Database::getInstance();
        $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);

        if (!$page) {
            $this->flash('error', 'Page not found.');
            $this->redirect('/admin/pages');
            return;
        }

        $this->view('admin/pages/edit', ['title' => 'Edit Page', 'page' => $page]);
    }

    public function update(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        $errors = $this->validate([
            'title' => 'required|max:255',
            'slug'  => 'required|max:200',
        ]);

        if (!empty($errors)) {
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        try {
            $db = Database::getInstance();
            $slug = $this->sanitizeSlug($this->input('slug'));

            $existing = $db->fetch("SELECT id FROM pages WHERE slug = ? AND id != ?", [$slug, (int)$id]);
            if ($existing) {
                $this->flash('error', 'A page with that slug already exists.');
                $this->redirect('/admin/pages/' . $id . '/edit');
                return;
            }

            $db->update('pages', [
                'title'            => Security::sanitize($this->input('title')),
                'slug'             => $slug,
                'content'          => $this->input('content', ''),
                'meta_title'       => Security::sanitize($this->input('meta_title', '')),
                'meta_description' => Security::sanitize($this->input('meta_description', '')),
                'show_navbar'      => $this->input('show_navbar') ? 1 : 0,
                'show_footer'      => $this->input('show_footer') ? 1 : 0,
                'status'           => in_array($this->input('status'), ['published','draft']) ? $this->input('status') : 'draft',
                'sort_order'       => (int)$this->input('sort_order', 0),
            ], 'id = ?', [(int)$id]);

            ActivityLogger::log(Auth::id(), 'page_updated', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $this->input('title')]);
            $this->flash('success', 'Page updated successfully.');
            $this->redirect('/admin/pages');
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to update page: ' . $e->getMessage());
            $this->redirect('/admin/pages/' . $id . '/edit');
        }
    }

    public function delete(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages');
            return;
        }

        try {
            $db = Database::getInstance();
            $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);
            if ($page) {
                $db->delete('pages', 'id = ?', [(int)$id]);
                ActivityLogger::log(Auth::id(), 'page_deleted', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $page['title']]);
                $this->flash('success', 'Page deleted.');
            }
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to delete page: ' . $e->getMessage());
        }

        $this->redirect('/admin/pages');
    }

    public function toggleStatus(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/pages');
            return;
        }

        try {
            $db = Database::getInstance();
            $page = $db->fetch("SELECT * FROM pages WHERE id = ?", [(int)$id]);
            if (!$page) {
                $this->flash('error', 'Page not found.');
                $this->redirect('/admin/pages');
                return;
            }
            $newStatus = $page['status'] === 'published' ? 'draft' : 'published';
            $db->update('pages', ['status' => $newStatus], 'id = ?', [(int)$id]);
            ActivityLogger::log(Auth::id(), 'page_status_changed', ['module' => 'pages', 'resource_type' => 'page', 'resource_id' => (int)$id, 'entity_name' => $page['title'] ?? '', 'new_values' => ['status' => $newStatus]]);
            $this->flash('success', 'Page status changed to ' . $newStatus . '.');
        } catch (\Exception $e) {
            $this->flash('error', $e->getMessage());
        }

        $this->redirect('/admin/pages');
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9\-_\/]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    /**
     * Seed default Privacy Policy and Terms of Service pages if they do not yet exist.
     * This runs only when the admin visits the Pages list — it is idempotent.
     */
    private function seedLegalPages(Database $db): void
    {
        $legalPages = [
            [
                'slug'             => 'privacy',
                'title'            => 'Privacy Policy',
                'meta_title'       => 'Privacy Policy',
                'meta_description' => 'How we collect, use, and protect your personal information.',
                'sort_order'       => 90,
                'content'          => $this->getDefaultPrivacyContent(),
            ],
            [
                'slug'             => 'policy',
                'title'            => 'Terms of Service',
                'meta_title'       => 'Terms of Service',
                'meta_description' => 'Please read these terms carefully before using our services.',
                'sort_order'       => 91,
                'content'          => $this->getDefaultTermsContent(),
            ],
        ];

        foreach ($legalPages as $page) {
            try {
                $existing = $db->fetch("SELECT id FROM pages WHERE slug = ?", [$page['slug']]);
                if (!$existing) {
                    $db->insert('pages', [
                        'title'            => $page['title'],
                        'slug'             => $page['slug'],
                        'content'          => $page['content'],
                        'meta_title'       => $page['meta_title'],
                        'meta_description' => $page['meta_description'],
                        'show_navbar'      => 1,
                        'show_footer'      => 1,
                        'status'           => 'published',
                        'sort_order'       => $page['sort_order'],
                        'created_by'       => Auth::id(),
                    ]);
                }
            } catch (\Exception $e) {
                // Non-critical — silently skip if table is unavailable.
            }
        }
    }

    private function getDefaultPrivacyContent(): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Our Service';
        $domain  = defined('APP_URL')  ? APP_URL  : 'https://example.com';
        $year    = date('Y');
        return <<<HTML
<h1>Privacy Policy</h1>
<p><em>Last updated: {$year}</em></p>

<p>This Privacy Policy describes how {$appName} ("{$appName}", "we", "us", or "our") collects, uses, and shares information about you when you use our website and services located at {$domain} (the "Service").</p>

<h2>1. Information We Collect</h2>
<h3>1.1 Information You Provide</h3>
<ul>
  <li><strong>Account Information:</strong> When you register for an account, we collect your name, email address, and password.</li>
  <li><strong>Payment Information:</strong> When you subscribe to a paid plan, payment data (card number, billing address) is processed directly by our payment processor and is not stored on our servers.</li>
  <li><strong>Content:</strong> Any content you create or upload through the Service (e.g. resumes, QR codes, forms).</li>
  <li><strong>Communications:</strong> If you contact us for support, we retain that correspondence.</li>
</ul>

<h3>1.2 Information Collected Automatically</h3>
<ul>
  <li><strong>Log Data:</strong> IP address, browser type, operating system, referring URLs, and pages visited.</li>
  <li><strong>Cookies &amp; Local Storage:</strong> Session cookies (required for authentication), preference cookies (optional), and analytics identifiers.</li>
  <li><strong>Usage Data:</strong> Feature usage patterns, conversion events, and error reports used to improve the Service.</li>
</ul>

<h2>2. How We Use Your Information</h2>
<ul>
  <li>To provide, operate, and maintain the Service.</li>
  <li>To process payments and manage subscriptions.</li>
  <li>To send transactional emails (account confirmation, password reset, receipts).</li>
  <li>To send marketing communications <strong>only if you have opted in</strong>.</li>
  <li>To detect, prevent, and respond to fraud, abuse, or security incidents.</li>
  <li>To comply with legal obligations.</li>
</ul>

<h2>3. Sharing Your Information</h2>
<p>We do <strong>not</strong> sell, rent, or trade your personal information. We may share it with:</p>
<ul>
  <li><strong>Service Providers:</strong> Third-party vendors (e.g. payment processors, email delivery, hosting) who process data on our behalf under confidentiality agreements.</li>
  <li><strong>Legal Requirements:</strong> If required by law, court order, or government authority.</li>
  <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets, with prior notice to you.</li>
</ul>

<h2>4. Data Retention</h2>
<p>We retain your personal data for as long as your account is active or as needed to provide the Service, comply with legal obligations, resolve disputes, and enforce agreements. You may request deletion at any time (see Section 6).</p>

<h2>5. Security</h2>
<p>We implement industry-standard technical and organisational measures (TLS encryption, hashed passwords, access controls) to protect your data. However, no method of transmission over the Internet is 100% secure and we cannot guarantee absolute security.</p>

<h2>6. Your Rights</h2>
<p>Depending on your jurisdiction, you may have the right to:</p>
<ul>
  <li><strong>Access</strong> the personal data we hold about you.</li>
  <li><strong>Correct</strong> inaccurate or incomplete data.</li>
  <li><strong>Delete</strong> your account and associated data.</li>
  <li><strong>Portability</strong> — receive your data in a machine-readable format.</li>
  <li><strong>Object</strong> to or restrict certain processing activities.</li>
  <li><strong>Withdraw consent</strong> for marketing emails at any time via the unsubscribe link.</li>
</ul>
<p>To exercise these rights, contact us at the address below.</p>

<h2>7. Cookies</h2>
<p>You can control cookies through your browser settings. Disabling cookies may affect the functionality of the Service (e.g. you will not be able to stay logged in).</p>

<h2>8. Children's Privacy</h2>
<p>The Service is not directed to children under 13 (or 16 in the EU). We do not knowingly collect personal data from children. If you believe we have inadvertently collected such data, please contact us immediately.</p>

<h2>9. Changes to This Policy</h2>
<p>We may update this Privacy Policy from time to time. We will notify you of material changes by posting the new policy on this page and, where appropriate, by email. Your continued use of the Service after the effective date constitutes acceptance of the revised policy.</p>

<h2>10. Contact Us</h2>
<p>If you have questions about this Privacy Policy, please contact us at <a href="{$domain}/support">our support page</a>.</p>
HTML;
    }

    private function getDefaultTermsContent(): string
    {
        $appName = defined('APP_NAME') ? APP_NAME : 'Our Service';
        $domain  = defined('APP_URL')  ? APP_URL  : 'https://example.com';
        $year    = date('Y');
        return <<<HTML
<h1>Terms of Service</h1>
<p><em>Last updated: {$year}</em></p>

<p>Please read these Terms of Service ("Terms") carefully before using {$appName} ("Service", "we", "us") operated at {$domain}. By accessing or using the Service, you agree to be bound by these Terms.</p>

<h2>1. Acceptance of Terms</h2>
<p>By creating an account or using the Service, you represent that you are at least 18 years old (or the age of majority in your jurisdiction), and that you agree to these Terms and our Privacy Policy. If you do not agree, do not use the Service.</p>

<h2>2. Account Responsibilities</h2>
<ul>
  <li>You are responsible for maintaining the confidentiality of your login credentials.</li>
  <li>You are responsible for all activity that occurs under your account.</li>
  <li>You must notify us immediately of any unauthorised use of your account.</li>
  <li>You may not share your account with others or create multiple accounts to circumvent usage limits.</li>
</ul>

<h2>3. Acceptable Use</h2>
<p>You agree <strong>not</strong> to use the Service to:</p>
<ul>
  <li>Violate any applicable laws or regulations.</li>
  <li>Infringe on intellectual property rights of any third party.</li>
  <li>Distribute malware, spam, or phishing content.</li>
  <li>Attempt to gain unauthorised access to any part of the Service or its infrastructure.</li>
  <li>Scrape or harvest data from the Service without our express written consent.</li>
  <li>Engage in any activity that unreasonably burdens our infrastructure.</li>
</ul>

<h2>4. Subscription Plans &amp; Payments</h2>
<ul>
  <li><strong>Free Tier:</strong> Available with limited features at no cost.</li>
  <li><strong>Paid Plans:</strong> Subscription fees are charged in advance on a monthly or annual basis. All fees are non-refundable except where required by law.</li>
  <li><strong>Price Changes:</strong> We reserve the right to change pricing with at least 30 days' notice. Continued use after the effective date constitutes acceptance of the new pricing.</li>
  <li><strong>Cancellation:</strong> You may cancel at any time from your account settings. Access continues until the end of the current billing period.</li>
  <li><strong>Taxes:</strong> Prices are exclusive of applicable taxes (VAT, GST, etc.), which may be added at checkout based on your location.</li>
</ul>

<h2>5. Intellectual Property</h2>
<p>The Service and its original content (excluding user-generated content) are and will remain the exclusive property of {$appName} and its licensors. You retain all rights to content you create using the Service. By uploading content, you grant us a non-exclusive, worldwide licence to host, display, and process that content solely to provide the Service.</p>

<h2>6. Termination</h2>
<p>We may suspend or terminate your account immediately, without prior notice, if you breach these Terms. Upon termination, your right to use the Service will cease. Provisions that by their nature should survive termination will survive.</p>

<h2>7. Disclaimers</h2>
<p>The Service is provided on an "AS IS" and "AS AVAILABLE" basis without any warranty of any kind. We do not warrant that the Service will be uninterrupted, error-free, or free from viruses or other harmful components.</p>

<h2>8. Limitation of Liability</h2>
<p>To the fullest extent permitted by law, {$appName} shall not be liable for any indirect, incidental, special, consequential, or punitive damages, including loss of profits, data, or goodwill, arising out of or in connection with your use of the Service, even if we have been advised of the possibility of such damages. Our total aggregate liability to you for any claims arising under these Terms shall not exceed the amount you paid us in the twelve months preceding the claim.</p>

<h2>9. Governing Law</h2>
<p>These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which {$appName} is registered, without regard to conflict-of-law principles.</p>

<h2>10. Changes to Terms</h2>
<p>We reserve the right to modify these Terms at any time. We will provide notice of significant changes by posting the updated Terms on this page and updating the "Last updated" date. Continued use of the Service after changes become effective constitutes your acceptance of the revised Terms.</p>

<h2>11. Contact Us</h2>
<p>If you have any questions about these Terms, please contact us at <a href="{$domain}/support">our support page</a>.</p>
HTML;
    }
}
