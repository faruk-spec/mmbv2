<?php
/**
 * Admin User Access Controller
 *
 * Allows super_admin / admin to grant or revoke granular admin panel
 * permissions for individual users.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Logger;
use Core\Security;
use Core\ActivityLogger;

class AdminUserAccessController extends BaseController
{
    /**
     * All grantable admin panel permissions grouped by section.
     *
     * Structure mirrors the admin sidebar navigation exactly.
     * Keys use dot-notation: 'section' or 'section.sub_feature'.
     *
     * Each entry: 'key' => ['label', 'icon', 'description', 'group', 'parent'?]
     * - parent: the key of the parent permission (optional, for sub-items)
     */
    public const PERMISSIONS = [
        // ── Dashboard ─────────────────────────────────────────────────────
        'dashboard' => [
            'label'       => 'Dashboard',
            'icon'        => 'fas fa-tachometer-alt',
            'description' => 'View the main admin dashboard and overview stats',
            'group'       => 'Dashboard',
        ],

        // ── QR Code Admin ─────────────────────────────────────────────────
        'qr' => [
            'label'       => 'QR Code Admin',
            'icon'        => 'fas fa-qrcode',
            'description' => 'Access QR code administration section',
            'group'       => 'Projects',
        ],
        'qr.analytics' => [
            'label'       => 'Analytics',
            'icon'        => 'fas fa-chart-bar',
            'description' => 'View QR code analytics and scan statistics',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.blocked_links' => [
            'label'       => 'Blocked Links',
            'icon'        => 'fas fa-ban',
            'description' => 'View and manage blocked QR destination links',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.storage' => [
            'label'       => 'Storage',
            'icon'        => 'fas fa-hdd',
            'description' => 'Monitor QR code storage usage',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.plans' => [
            'label'       => 'Plans',
            'icon'        => 'fas fa-tags',
            'description' => 'Manage QR subscription plans',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.abuse_reports' => [
            'label'       => 'Abuse Reports',
            'icon'        => 'fas fa-flag',
            'description' => 'Review and handle QR abuse reports',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.roles' => [
            'label'       => 'Role Management',
            'icon'        => 'fas fa-users-cog',
            'description' => 'Manage QR user roles and permissions',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],
        'qr.api_keys' => [
            'label'       => 'API Keys',
            'icon'        => 'fas fa-key',
            'description' => 'Manage QR API keys',
            'group'       => 'Projects',
            'parent'      => 'qr',
        ],

        // ── Platform Billing ──────────────────────────────────────────────
        'platform_plans' => [
            'label'       => 'Platform Billing',
            'icon'        => 'fas fa-layer-group',
            'description' => 'Access platform billing and plans section',
            'group'       => 'Platform Billing',
        ],
        'platform_plans.list' => [
            'label'       => 'Plans List',
            'icon'        => 'fas fa-list',
            'description' => 'View all platform subscription plans',
            'group'       => 'Platform Billing',
            'parent'      => 'platform_plans',
        ],
        'platform_plans.create' => [
            'label'       => 'Create Plan',
            'icon'        => 'fas fa-plus',
            'description' => 'Create new platform subscription plans',
            'group'       => 'Platform Billing',
            'parent'      => 'platform_plans',
        ],

        // ── Projects ─────────────────────────────────────────────────────
        'projects' => [
            'label'       => 'Projects',
            'icon'        => 'fas fa-th',
            'description' => 'Access projects management section',
            'group'       => 'Projects',
        ],
        'projects.list' => [
            'label'       => 'Projects List',
            'icon'        => 'fas fa-th-large',
            'description' => 'View all projects overview',
            'group'       => 'Projects',
            'parent'      => 'projects',
        ],
        'projects.database_setup' => [
            'label'       => 'Database Setup',
            'icon'        => 'fas fa-database',
            'description' => 'Run project database migrations and setup',
            'group'       => 'Projects',
            'parent'      => 'projects',
        ],

        // ConvertX
        'convertx' => [
            'label'       => 'ConvertX',
            'icon'        => 'fas fa-file-export',
            'description' => 'Access ConvertX admin panel',
            'group'       => 'ConvertX',
        ],
        'convertx.jobs' => [
            'label'       => 'Jobs',
            'icon'        => 'fas fa-tasks',
            'description' => 'View and manage conversion jobs',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],
        'convertx.users' => [
            'label'       => 'Users',
            'icon'        => 'fas fa-users',
            'description' => 'Manage ConvertX users',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],
        'convertx.api_keys' => [
            'label'       => 'API Keys',
            'icon'        => 'fas fa-key',
            'description' => 'Manage ConvertX API keys',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],
        'convertx.settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Configure ConvertX settings',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],
        'convertx.storage' => [
            'label'       => 'Storage',
            'icon'        => 'fas fa-hdd',
            'description' => 'Monitor ConvertX storage usage',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],
        'convertx.plans' => [
            'label'       => 'Plans',
            'icon'        => 'fas fa-tags',
            'description' => 'Manage ConvertX subscription plans',
            'group'       => 'ConvertX',
            'parent'      => 'convertx',
        ],

        // CodeXPro
        'codexpro' => [
            'label'       => 'CodeXPro',
            'icon'        => 'fas fa-code',
            'description' => 'Access CodeXPro admin panel',
            'group'       => 'CodeXPro',
        ],
        'codexpro.settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Configure CodeXPro settings',
            'group'       => 'CodeXPro',
            'parent'      => 'codexpro',
        ],
        'codexpro.users' => [
            'label'       => 'Users',
            'icon'        => 'fas fa-users',
            'description' => 'Manage CodeXPro users',
            'group'       => 'CodeXPro',
            'parent'      => 'codexpro',
        ],
        'codexpro.templates' => [
            'label'       => 'Templates',
            'icon'        => 'fas fa-file-code',
            'description' => 'Manage code templates',
            'group'       => 'CodeXPro',
            'parent'      => 'codexpro',
        ],

        // ProShare
        'proshare' => [
            'label'       => 'ProShare',
            'icon'        => 'fas fa-share-alt',
            'description' => 'Access ProShare admin panel',
            'group'       => 'ProShare',
        ],
        'proshare.settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Configure ProShare settings',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.user_dashboard' => [
            'label'       => 'User Dashboard',
            'icon'        => 'fas fa-user-circle',
            'description' => 'View user ProShare dashboard data',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.user_files' => [
            'label'       => 'User Files',
            'icon'        => 'fas fa-folder',
            'description' => 'Browse user files',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.user_activity' => [
            'label'       => 'User Activity',
            'icon'        => 'fas fa-history',
            'description' => 'View user activity in ProShare',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.user_logs' => [
            'label'       => 'User Logs',
            'icon'        => 'fas fa-file-alt',
            'description' => 'View ProShare user action logs',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.sessions' => [
            'label'       => 'Sessions',
            'icon'        => 'fas fa-clock',
            'description' => 'View and manage ProShare sessions',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.files' => [
            'label'       => 'Files Admin',
            'icon'        => 'fas fa-folder-open',
            'description' => 'Manage all ProShare files',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.file_activity' => [
            'label'       => 'File Activity',
            'icon'        => 'fas fa-exchange-alt',
            'description' => 'View file access and modification logs',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.texts' => [
            'label'       => 'Texts',
            'icon'        => 'fas fa-font',
            'description' => 'Manage ProShare text entries',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.security' => [
            'label'       => 'Security',
            'icon'        => 'fas fa-shield-alt',
            'description' => 'ProShare security settings',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.server_health' => [
            'label'       => 'Server Health',
            'icon'        => 'fas fa-heartbeat',
            'description' => 'Monitor ProShare server health',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.storage' => [
            'label'       => 'Storage',
            'icon'        => 'fas fa-hdd',
            'description' => 'Monitor ProShare storage',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.audit_trail' => [
            'label'       => 'Audit Trail',
            'icon'        => 'fas fa-scroll',
            'description' => 'View ProShare audit trail',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.notifications' => [
            'label'       => 'Notifications & Alerts',
            'icon'        => 'fas fa-bell',
            'description' => 'Manage ProShare notifications',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],
        'proshare.analytics' => [
            'label'       => 'Analytics',
            'icon'        => 'fas fa-chart-bar',
            'description' => 'View ProShare analytics',
            'group'       => 'ProShare',
            'parent'      => 'proshare',
        ],

        // BillX
        'billx' => [
            'label'       => 'BillX',
            'icon'        => 'fas fa-file-invoice',
            'description' => 'Access BillX admin panel',
            'group'       => 'BillX',
        ],
        'billx.bills' => [
            'label'       => 'All Bills',
            'icon'        => 'fas fa-list',
            'description' => 'View and manage all bills',
            'group'       => 'BillX',
            'parent'      => 'billx',
        ],
        'billx.export' => [
            'label'       => 'Export CSV',
            'icon'        => 'fas fa-file-csv',
            'description' => 'Export bills as CSV',
            'group'       => 'BillX',
            'parent'      => 'billx',
        ],
        'billx.activity_logs' => [
            'label'       => 'Activity Logs',
            'icon'        => 'fas fa-history',
            'description' => 'View BillX activity logs',
            'group'       => 'BillX',
            'parent'      => 'billx',
        ],
        'billx.settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Configure BillX settings',
            'group'       => 'BillX',
            'parent'      => 'billx',
        ],

        // WhatsApp
        'whatsapp' => [
            'label'       => 'WhatsApp API',
            'icon'        => 'fab fa-whatsapp',
            'description' => 'Access WhatsApp API admin panel',
            'group'       => 'WhatsApp',
        ],
        'whatsapp.overview' => [
            'label'       => 'Overview',
            'icon'        => 'fas fa-chart-line',
            'description' => 'WhatsApp overview and stats',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.sessions' => [
            'label'       => 'Sessions',
            'icon'        => 'fas fa-mobile-alt',
            'description' => 'Manage WhatsApp sessions',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.messages' => [
            'label'       => 'Messages',
            'icon'        => 'fas fa-comments',
            'description' => 'View WhatsApp messages',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.users' => [
            'label'       => 'Users',
            'icon'        => 'fas fa-users',
            'description' => 'Manage WhatsApp users',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.api_logs' => [
            'label'       => 'API Logs',
            'icon'        => 'fas fa-file-alt',
            'description' => 'View WhatsApp API call logs',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.subscription_plans' => [
            'label'       => 'Subscription Plans',
            'icon'        => 'fas fa-tags',
            'description' => 'Manage WhatsApp subscription plans',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.user_subscriptions' => [
            'label'       => 'User Subscriptions',
            'icon'        => 'fas fa-crown',
            'description' => 'View user subscriptions',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],
        'whatsapp.assign_subscription' => [
            'label'       => 'Assign Subscription',
            'icon'        => 'fas fa-user-plus',
            'description' => 'Assign subscriptions to users',
            'group'       => 'WhatsApp',
            'parent'      => 'whatsapp',
        ],

        // ── Management ────────────────────────────────────────────────────
        'users' => [
            'label'       => 'Users',
            'icon'        => 'fas fa-users',
            'description' => 'View and manage all users',
            'group'       => 'Management',
        ],
        'users.create' => [
            'label'       => 'Create User',
            'icon'        => 'fas fa-user-plus',
            'description' => 'Create new user accounts',
            'group'       => 'Management',
            'parent'      => 'users',
        ],
        'users.edit' => [
            'label'       => 'Edit Users',
            'icon'        => 'fas fa-user-edit',
            'description' => 'Edit existing user accounts',
            'group'       => 'Management',
            'parent'      => 'users',
        ],
        'users.delete' => [
            'label'       => 'Delete Users',
            'icon'        => 'fas fa-user-minus',
            'description' => 'Delete user accounts',
            'group'       => 'Management',
            'parent'      => 'users',
        ],
        'admin_access' => [
            'label'       => 'Admin Users Access',
            'icon'        => 'fas fa-user-shield',
            'description' => 'Manage admin panel permissions for users',
            'group'       => 'Management',
        ],

        // ── Security ──────────────────────────────────────────────────────
        'security' => [
            'label'       => 'Security Center',
            'icon'        => 'fas fa-shield-alt',
            'description' => 'View security overview and alerts',
            'group'       => 'Security',
        ],
        'security.blocked_ips' => [
            'label'       => 'Blocked IPs',
            'icon'        => 'fas fa-ban',
            'description' => 'Manage blocked IP addresses',
            'group'       => 'Security',
            'parent'      => 'security',
        ],
        'security.failed_logins' => [
            'label'       => 'Failed Logins',
            'icon'        => 'fas fa-exclamation-triangle',
            'description' => 'View failed login attempts',
            'group'       => 'Security',
            'parent'      => 'security',
        ],
        'oauth' => [
            'label'       => 'OAuth & SSO',
            'icon'        => 'fas fa-key',
            'description' => 'Manage OAuth providers and SSO settings',
            'group'       => 'Security',
        ],
        'oauth.connections' => [
            'label'       => 'OAuth Connections',
            'icon'        => 'fas fa-link',
            'description' => 'View active OAuth connections',
            'group'       => 'Security',
            'parent'      => 'oauth',
        ],
        'sessions' => [
            'label'       => 'Session Management',
            'icon'        => 'fas fa-clock',
            'description' => 'View and terminate active sessions',
            'group'       => 'Security',
        ],
        'sessions.login_history' => [
            'label'       => 'Login History',
            'icon'        => 'fas fa-history',
            'description' => 'View complete login history',
            'group'       => 'Security',
            'parent'      => 'sessions',
        ],
        '2fa' => [
            'label'       => '2FA Management',
            'icon'        => 'fas fa-mobile-alt',
            'description' => 'View and manage user 2FA settings',
            'group'       => 'Security',
        ],

        // ── Logs ─────────────────────────────────────────────────────────
        'logs' => [
            'label'       => 'Activity Logs',
            'icon'        => 'fas fa-file-alt',
            'description' => 'Access system and user activity logs',
            'group'       => 'Logs',
        ],
        'logs.activity' => [
            'label'       => 'User Activity',
            'icon'        => 'fas fa-user-clock',
            'description' => 'View user activity logs',
            'group'       => 'Logs',
            'parent'      => 'logs',
        ],
        'logs.system' => [
            'label'       => 'System Logs',
            'icon'        => 'fas fa-server',
            'description' => 'View system-level logs',
            'group'       => 'Logs',
            'parent'      => 'logs',
        ],
        'audit' => [
            'label'       => 'Audit Explorer',
            'icon'        => 'fas fa-search',
            'description' => 'Query and explore the full audit trail with SQL',
            'group'       => 'Logs',
        ],

        // ── API Management ────────────────────────────────────────────────
        'api' => [
            'label'       => 'API Management',
            'icon'        => 'fas fa-plug',
            'description' => 'Access API management section',
            'group'       => 'Advanced Features',
        ],
        'api.keys' => [
            'label'       => 'API Keys',
            'icon'        => 'fas fa-key',
            'description' => 'Manage API keys',
            'group'       => 'Advanced Features',
            'parent'      => 'api',
        ],
        'api.logs' => [
            'label'       => 'API Logs',
            'icon'        => 'fas fa-file-alt',
            'description' => 'View API request logs',
            'group'       => 'Advanced Features',
            'parent'      => 'api',
        ],
        'api.rate_limits' => [
            'label'       => 'Rate Limits',
            'icon'        => 'fas fa-tachometer-alt',
            'description' => 'Configure API rate limiting',
            'group'       => 'Advanced Features',
            'parent'      => 'api',
        ],
        'api.documentation' => [
            'label'       => 'API Documentation',
            'icon'        => 'fas fa-book',
            'description' => 'View API documentation',
            'group'       => 'Advanced Features',
            'parent'      => 'api',
        ],

        // WebSocket
        'websocket' => [
            'label'       => 'WebSocket',
            'icon'        => 'fas fa-bolt',
            'description' => 'Manage WebSocket server',
            'group'       => 'Advanced Features',
        ],
        'websocket.connections' => [
            'label'       => 'Connections',
            'icon'        => 'fas fa-network-wired',
            'description' => 'View active WebSocket connections',
            'group'       => 'Advanced Features',
            'parent'      => 'websocket',
        ],
        'websocket.rooms' => [
            'label'       => 'Rooms',
            'icon'        => 'fas fa-door-open',
            'description' => 'Manage WebSocket rooms',
            'group'       => 'Advanced Features',
            'parent'      => 'websocket',
        ],
        'websocket.settings' => [
            'label'       => 'WebSocket Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Configure WebSocket settings',
            'group'       => 'Advanced Features',
            'parent'      => 'websocket',
        ],

        // Analytics
        'analytics' => [
            'label'       => 'Analytics',
            'icon'        => 'fas fa-chart-bar',
            'description' => 'Access platform analytics',
            'group'       => 'Advanced Features',
        ],
        'analytics.events' => [
            'label'       => 'Events',
            'icon'        => 'fas fa-calendar-check',
            'description' => 'View analytics events',
            'group'       => 'Advanced Features',
            'parent'      => 'analytics',
        ],
        'analytics.reports' => [
            'label'       => 'Reports',
            'icon'        => 'fas fa-file-chart-bar',
            'description' => 'View and generate analytics reports',
            'group'       => 'Advanced Features',
            'parent'      => 'analytics',
        ],
        'analytics.export' => [
            'label'       => 'Export Analytics',
            'icon'        => 'fas fa-download',
            'description' => 'Export analytics data',
            'group'       => 'Advanced Features',
            'parent'      => 'analytics',
        ],

        // Email & Notifications
        'email' => [
            'label'       => 'Email & Notifications',
            'icon'        => 'fas fa-envelope',
            'description' => 'Manage email and notification settings',
            'group'       => 'Advanced Features',
        ],
        'email.queue' => [
            'label'       => 'Email Queue',
            'icon'        => 'fas fa-inbox',
            'description' => 'View and manage the email send queue',
            'group'       => 'Advanced Features',
            'parent'      => 'email',
        ],
        'email.templates' => [
            'label'       => 'Email Templates',
            'icon'        => 'fas fa-file-alt',
            'description' => 'Edit email templates',
            'group'       => 'Advanced Features',
            'parent'      => 'email',
        ],
        'notifications' => [
            'label'       => 'Notifications',
            'icon'        => 'fas fa-bell',
            'description' => 'View and manage notifications',
            'group'       => 'Advanced Features',
            'parent'      => 'email',
        ],
        'notifications.preferences' => [
            'label'       => 'Notification Preferences',
            'icon'        => 'fas fa-sliders-h',
            'description' => 'Configure notification preferences',
            'group'       => 'Advanced Features',
            'parent'      => 'email',
        ],

        // Performance
        'performance' => [
            'label'       => 'Performance',
            'icon'        => 'fas fa-tachometer-alt',
            'description' => 'Access performance and cache tools',
            'group'       => 'Advanced Features',
        ],
        'performance.cache' => [
            'label'       => 'Cache Management',
            'icon'        => 'fas fa-memory',
            'description' => 'View and clear application cache',
            'group'       => 'Advanced Features',
            'parent'      => 'performance',
        ],
        'performance.assets' => [
            'label'       => 'Asset Optimization',
            'icon'        => 'fas fa-compress',
            'description' => 'Manage static asset optimization',
            'group'       => 'Advanced Features',
            'parent'      => 'performance',
        ],
        'performance.database' => [
            'label'       => 'Database Performance',
            'icon'        => 'fas fa-database',
            'description' => 'Monitor database performance',
            'group'       => 'Advanced Features',
            'parent'      => 'performance',
        ],
        'performance.monitoring' => [
            'label'       => 'Monitoring',
            'icon'        => 'fas fa-heartbeat',
            'description' => 'Real-time performance monitoring',
            'group'       => 'Advanced Features',
            'parent'      => 'performance',
        ],

        // ── System Settings ───────────────────────────────────────────────
        'settings' => [
            'label'       => 'Settings',
            'icon'        => 'fas fa-cog',
            'description' => 'Access system settings',
            'group'       => 'System',
        ],
        'settings.session' => [
            'label'       => 'Session Settings',
            'icon'        => 'fas fa-clock',
            'description' => 'Configure session timeout and security',
            'group'       => 'System',
            'parent'      => 'settings',
        ],
        'settings.home_content' => [
            'label'       => 'Home Content',
            'icon'        => 'fas fa-home',
            'description' => 'Edit homepage sections and content',
            'group'       => 'System',
            'parent'      => 'settings',
        ],
        'settings.maintenance' => [
            'label'       => 'Maintenance Mode',
            'icon'        => 'fas fa-tools',
            'description' => 'Toggle maintenance mode',
            'group'       => 'System',
            'parent'      => 'settings',
        ],
        'settings.features' => [
            'label'       => 'Feature Flags',
            'icon'        => 'fas fa-toggle-on',
            'description' => 'Enable or disable platform features',
            'group'       => 'System',
            'parent'      => 'settings',
        ],
        'settings.timezone' => [
            'label'       => 'Timezone',
            'icon'        => 'fas fa-globe',
            'description' => 'Configure system timezone',
            'group'       => 'System',
            'parent'      => 'settings',
        ],
        'navbar' => [
            'label'       => 'Navbar & Branding',
            'icon'        => 'fas fa-paint-brush',
            'description' => 'Customize the site navbar and branding',
            'group'       => 'System',
        ],
    ];

    public function __construct()
    {
        $this->requireAuth();
        $this->requireRoleAdmin();
    }

    /**
     * List all users with their current permission summary.
     */
    public function index(): void
    {
        $db = Database::getInstance();

        $users = $db->fetchAll(
            "SELECT u.id, u.name, u.email, u.role, u.status,
                    COUNT(p.id) AS perm_count
             FROM users u
             LEFT JOIN admin_user_permissions p ON p.user_id = u.id
             GROUP BY u.id
             ORDER BY u.name"
        );

        $this->view('admin/admin-access/index', [
            'title' => 'Admin Users Access',
            'users' => $users,
        ]);
    }

    /**
     * Edit permissions for a single user (GET).
     */
    public function editForm(string $userId): void
    {
        $db = Database::getInstance();

        $user = $db->fetch("SELECT id, name, email, role FROM users WHERE id = ?", [(int) $userId]);
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/admin-access');
            return;
        }

        $granted = $db->fetchAll(
            "SELECT permission_key FROM admin_user_permissions WHERE user_id = ?",
            [(int) $userId]
        );
        $grantedKeys = array_column($granted, 'permission_key');

        $this->view('admin/admin-access/edit', [
            'title'       => 'Admin Access — ' . $user['name'],
            'targetUser'  => $user,
            'permissions' => self::PERMISSIONS,
            'grantedKeys' => $grantedKeys,
        ]);
    }

    /**
     * Save permissions for a user (POST).
     */
    public function save(string $userId): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request token.');
            $this->redirect('/admin/admin-access/' . $userId . '/edit');
            return;
        }

        $db   = Database::getInstance();
        $user = $db->fetch("SELECT id, name FROM users WHERE id = ?", [(int) $userId]);
        if (!$user) {
            $this->flash('error', 'User not found.');
            $this->redirect('/admin/admin-access');
            return;
        }

        // Collect submitted permissions
        $submitted   = isset($_POST['permissions']) ? (array)$_POST['permissions'] : [];
        $allowedKeys = array_keys(self::PERMISSIONS);
        $newKeys     = array_values(array_filter($submitted, fn($k) => in_array($k, $allowedKeys, true)));

        // Old keys for audit log
        $oldRows = $db->fetchAll(
            "SELECT permission_key FROM admin_user_permissions WHERE user_id = ?",
            [(int) $userId]
        );
        $oldKeys = array_column($oldRows, 'permission_key');

        try {
            $db->beginTransaction();

            // Delete all existing permissions for this user, then batch re-insert
            $db->query(
                "DELETE FROM admin_user_permissions WHERE user_id = ?",
                [(int) $userId]
            );

            if (!empty($newKeys)) {
                $now         = date('Y-m-d H:i:s');
                $grantedBy   = Auth::id();
                $placeholders = implode(', ', array_fill(0, count($newKeys), '(?, ?, ?, ?)'));
                $params       = [];
                foreach ($newKeys as $key) {
                    array_push($params, (int) $userId, $key, $grantedBy, $now);
                }
                $db->query(
                    "INSERT INTO admin_user_permissions (user_id, permission_key, granted_by, created_at)
                     VALUES {$placeholders}",
                    $params
                );
            }

            $db->commit();

            ActivityLogger::logUpdate(
                Auth::id(),
                'users',
                'admin_permissions',
                (int) $userId,
                ['permissions' => $oldKeys],
                ['permissions' => $newKeys]
            );

            $this->flash('success', 'Permissions updated for ' . $user['name'] . '.');
        } catch (\Exception $e) {
            try { $db->rollback(); } catch (\Throwable $_) {}
            Logger::error('AdminUserAccessController::save — ' . $e->getMessage());
            $this->flash('error', 'Failed to save permissions: ' . $e->getMessage());
        }

        $this->redirect('/admin/admin-access/' . $userId . '/edit');
    }
}
