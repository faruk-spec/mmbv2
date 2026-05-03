<?php
/**
 * Admin Routes
 * 
 * @package MMB\Routes
 */

// Admin dashboard
$router->get('/admin', 'Admin\\DashboardController@index', ['auth', 'admin']);
$router->get('/admin/dashboard', 'Admin\\DashboardController@index', ['auth', 'admin']);

// Admin Profile
$router->get('/admin/profile', 'Admin\\AdminProfileController@index', ['auth', 'admin']);
$router->post('/admin/profile/update', 'Admin\\AdminProfileController@update', ['auth', 'admin']);
$router->post('/admin/profile/change-password', 'Admin\\AdminProfileController@changePassword', ['auth', 'admin']);
$router->get('/admin/api/live-stats', 'Admin\\DashboardController@liveStats', ['auth', 'admin']);

// Pages CMS
$router->get('/admin/pages', 'Admin\\PagesController@index', ['auth', 'admin']);
$router->get('/admin/pages/create', 'Admin\\PagesController@create', ['auth', 'admin']);
$router->post('/admin/pages/create', 'Admin\\PagesController@store', ['auth', 'admin']);
$router->get('/admin/pages/{id}/edit', 'Admin\\PagesController@edit', ['auth', 'admin']);
$router->post('/admin/pages/{id}/update', 'Admin\\PagesController@update', ['auth', 'admin']);
$router->post('/admin/pages/{id}/delete', 'Admin\\PagesController@delete', ['auth', 'admin']);
$router->post('/admin/pages/{id}/toggle', 'Admin\\PagesController@toggleStatus', ['auth', 'admin']);

// Platform Plans (Universal multi-app plans)
$router->get('/admin/platform-plans', 'Admin\\PlatformPlansController@index', ['auth', 'admin']);
$router->get('/admin/platform-plans/create', 'Admin\\PlatformPlansController@createForm', ['auth', 'admin']);
$router->post('/admin/platform-plans/create', 'Admin\\PlatformPlansController@create', ['auth', 'admin']);
$router->get('/admin/platform-plans/{id}/edit', 'Admin\\PlatformPlansController@editForm', ['auth', 'admin']);
$router->post('/admin/platform-plans/{id}/update', 'Admin\\PlatformPlansController@update', ['auth', 'admin']);
$router->post('/admin/platform-plans/{id}/delete', 'Admin\\PlatformPlansController@delete', ['auth', 'admin']);
$router->post('/admin/platform-plans/assign-user', 'Admin\\PlatformPlansController@assignUser', ['auth', 'admin']);
$router->post('/admin/platform-plans/revoke-user', 'Admin\\PlatformPlansController@revokeUser', ['auth', 'admin']);

// QR Code Admin Management
$router->get('/admin/qr', 'Admin\\QRAdminController@index', ['auth', 'admin']);
$router->post('/admin/qr/{id}/block', 'Admin\\QRAdminController@blockQR', ['auth', 'admin']);
$router->post('/admin/qr/{id}/unblock', 'Admin\\QRAdminController@unblockQR', ['auth', 'admin']);
$router->get('/admin/qr/analytics', 'Admin\\QRAdminController@analytics', ['auth', 'admin']);
$router->get('/admin/qr/blocked-links', 'Admin\\QRAdminController@blockedLinks', ['auth', 'admin']);
$router->post('/admin/qr/blocked-links/add', 'Admin\\QRAdminController@blockLink', ['auth', 'admin']);
$router->post('/admin/qr/blocked-links/{id}/remove', 'Admin\\QRAdminController@unblockLink', ['auth', 'admin']);
$router->get('/admin/qr/storage', 'Admin\\QRAdminController@storage', ['auth', 'admin']);
$router->get('/admin/qr/plans', 'Admin\\QRAdminController@plans', ['auth', 'admin']);
$router->post('/admin/qr/plans/create', 'Admin\\QRAdminController@createPlan', ['auth', 'admin']);
$router->post('/admin/qr/plans/{id}/update', 'Admin\\QRAdminController@updatePlan', ['auth', 'admin']);
$router->post('/admin/qr/plans/{id}/toggle-feature', 'Admin\\QRAdminController@togglePlanFeature', ['auth', 'admin']);
$router->post('/admin/qr/plans/{id}/delete', 'Admin\\QRAdminController@deletePlan', ['auth', 'admin']);
$router->get('/admin/qr/abuse-reports', 'Admin\\QRAdminController@abuseReports', ['auth', 'admin']);
$router->post('/admin/qr/abuse-reports/{id}/resolve', 'Admin\\QRAdminController@resolveAbuse', ['auth', 'admin']);
$router->get('/admin/qr/roles', 'Admin\\QRAdminController@roles', ['auth', 'admin']);
$router->get('/admin/qr/roles/user-features/{id}', 'Admin\\QRAdminController@getUserFeaturesApi', ['auth', 'admin']);
$router->post('/admin/qr/roles/set-role-feature', 'Admin\\QRAdminController@setRoleFeature', ['auth', 'admin']);
$router->post('/admin/qr/roles/set-user-feature', 'Admin\\QRAdminController@setUserFeature', ['auth', 'admin']);
$router->post('/admin/qr/roles/remove-user-features', 'Admin\\QRAdminController@removeUserFeatures', ['auth', 'admin']);
$router->post('/admin/qr/roles/assign-plan', 'Admin\\QRAdminController@assignUserPlan', ['auth', 'admin']);
$router->post('/admin/qr/roles/set-use-plan', 'Admin\\QRAdminController@setUsePlanSettings', ['auth', 'admin']);

// QR API Keys management
$router->get('/admin/qr/api-keys', 'Admin\\QRAdminController@qrApiKeys', ['auth', 'admin']);
$router->post('/admin/qr/api-keys/revoke', 'Admin\\QRAdminController@revokeQrApiKey', ['auth', 'admin']);
$router->post('/admin/qr/api-keys/generate', 'Admin\\QRAdminController@adminGenerateApiKey', ['auth', 'admin']);

// User management
$router->get('/admin/users', 'Admin\\UserController@index', ['auth', 'admin']);
$router->get('/admin/users/create', 'Admin\\UserController@create', ['auth', 'admin']);
$router->post('/admin/users/create', 'Admin\\UserController@store', ['auth', 'admin']);
$router->get('/admin/users/{id}/edit', 'Admin\\UserController@edit', ['auth', 'admin']);
$router->post('/admin/users/{id}/edit', 'Admin\\UserController@update', ['auth', 'admin']);
$router->post('/admin/users/{id}/delete', 'Admin\\UserController@delete', ['auth', 'admin']);
$router->post('/admin/users/{id}/toggle', 'Admin\\UserController@toggle', ['auth', 'admin']);

// Admin User Access — granular admin panel permissions
$router->get('/admin/admin-access', 'Admin\\AdminUserAccessController@index', ['auth', 'admin']);
$router->get('/admin/admin-access/{userId}/edit', 'Admin\\AdminUserAccessController@editForm', ['auth', 'admin']);
$router->post('/admin/admin-access/{userId}/save', 'Admin\\AdminUserAccessController@save', ['auth', 'admin']);

// User Roles
$router->get('/admin/roles', 'Admin\\RoleController@index', ['auth', 'admin']);
$router->get('/admin/roles/create', 'Admin\\RoleController@createForm', ['auth', 'admin']);
$router->post('/admin/roles/create', 'Admin\\RoleController@create', ['auth', 'admin']);
$router->get('/admin/roles/{id}/edit', 'Admin\\RoleController@editForm', ['auth', 'admin']);
$router->post('/admin/roles/{id}/update', 'Admin\\RoleController@update', ['auth', 'admin']);
$router->post('/admin/roles/{id}/delete', 'Admin\\RoleController@delete', ['auth', 'admin']);

// Project management
$router->get('/admin/projects', 'Admin\\ProjectController@index', ['auth', 'admin']);
$router->get('/admin/projects/{name}', 'Admin\\ProjectController@show', ['auth', 'admin']);
$router->post('/admin/projects/{name}/toggle', 'Admin\\ProjectController@toggle', ['auth', 'admin']);
$router->get('/admin/projects/{name}/settings', 'Admin\\ProjectController@settings', ['auth', 'admin']);
$router->post('/admin/projects/{name}/settings', 'Admin\\ProjectController@updateSettings', ['auth', 'admin']);

// ConvertX admin routes
$router->get('/admin/projects/convertx', 'Admin\\ConvertXAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/convertx/jobs', 'Admin\\ConvertXAdminController@jobs', ['auth', 'admin']);
$router->post('/admin/projects/convertx/jobs/cancel', 'Admin\\ConvertXAdminController@cancelJob', ['auth', 'admin']);
$router->post('/admin/projects/convertx/jobs/delete', 'Admin\\ConvertXAdminController@deleteJob', ['auth', 'admin']);
$router->get('/admin/projects/convertx/users', 'Admin\\ConvertXAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/convertx/api-keys', 'Admin\\ConvertXAdminController@apiKeys', ['auth', 'admin']);
$router->post('/admin/projects/convertx/api-keys/revoke', 'Admin\\ConvertXAdminController@revokeApiKey', ['auth', 'admin']);
$router->post('/admin/projects/convertx/api-keys/generate', 'Admin\\ConvertXAdminController@generateApiKeyForUser', ['auth', 'admin']);
$router->get('/admin/projects/convertx/settings', 'Admin\\ConvertXAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/convertx/settings', 'Admin\\ConvertXAdminController@updateSettings', ['auth', 'admin']);
$router->post('/admin/projects/convertx/settings/create-provider', 'Admin\\ConvertXAdminController@createProvider', ['auth', 'admin']);
$router->post('/admin/projects/convertx/settings/edit-provider', 'Admin\\ConvertXAdminController@editProvider', ['auth', 'admin']);
$router->post('/admin/projects/convertx/settings/delete-provider', 'Admin\\ConvertXAdminController@deleteProvider', ['auth', 'admin']);
$router->post('/admin/projects/convertx/settings/test-provider', 'Admin\\ConvertXAdminController@testProvider', ['auth', 'admin']);
$router->get('/admin/projects/convertx/image-tools-settings', 'Admin\\ConvertXAdminController@imageToolsSettings', ['auth', 'admin']);
$router->post('/admin/projects/convertx/image-tools-settings', 'Admin\\ConvertXAdminController@imageToolsSettings', ['auth', 'admin']);
$router->get('/admin/projects/convertx/upload-limits', 'Admin\\ConvertXAdminController@uploadLimits', ['auth', 'admin']);
$router->post('/admin/projects/convertx/upload-limits', 'Admin\\ConvertXAdminController@uploadLimits', ['auth', 'admin']);
$router->get('/admin/projects/convertx/storage', 'Admin\\ConvertXAdminController@storage', ['auth', 'admin']);
$router->get('/admin/projects/convertx/plans', 'Admin\\ConvertXAdminController@plans', ['auth', 'admin']);
$router->post('/admin/projects/convertx/plans/create', 'Admin\\ConvertXAdminController@createPlan', ['auth', 'admin']);
$router->post('/admin/projects/convertx/plans/update', 'Admin\\ConvertXAdminController@updatePlan', ['auth', 'admin']);
$router->post('/admin/projects/convertx/plans/delete', 'Admin\\ConvertXAdminController@deletePlan', ['auth', 'admin']);
$router->get('/admin/projects/convertx/roles', 'Admin\\ConvertXAdminController@roles', ['auth', 'admin']);
$router->get('/admin/projects/convertx/roles/user-features/{id}', 'Admin\\ConvertXAdminController@getUserFeaturesApi', ['auth', 'admin']);
$router->post('/admin/projects/convertx/roles/set-user-feature', 'Admin\\ConvertXAdminController@setUserFeature', ['auth', 'admin']);
$router->post('/admin/projects/convertx/roles/remove-user-features', 'Admin\\ConvertXAdminController@removeUserFeatures', ['auth', 'admin']);
$router->get('/admin/projects/convertx/schema', 'Admin\\ConvertXAdminController@schema', ['auth', 'admin']);

// CodeXPro admin routes
$router->get('/admin/projects/codexpro', 'Admin\\CodeXProAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/settings', 'Admin\\CodeXProAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/settings', 'Admin\\CodeXProAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/users', 'Admin\\CodeXProAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/templates', 'Admin\\CodeXProAdminController@templates', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/delete', 'Admin\\CodeXProAdminController@deleteTemplate', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/toggle', 'Admin\\CodeXProAdminController@toggleTemplate', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/create', 'Admin\\CodeXProAdminController@createTemplate', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/templates/{id}/edit', 'Admin\\CodeXProAdminController@editTemplate', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/{id}/update', 'Admin\\CodeXProAdminController@updateTemplate', ['auth', 'admin']);

// ProShare admin routes
$router->get('/admin/projects/proshare', 'Admin\\ProShareAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/proshare/settings', 'Admin\\ProShareAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/proshare/settings', 'Admin\\ProShareAdminController@settings', ['auth', 'admin']);

// User Dashboard Features
$router->get('/admin/projects/proshare/user-dashboard', 'Admin\\ProShareAdminController@userDashboard', ['auth', 'admin']);
$router->get('/admin/projects/proshare/user-files', 'Admin\\ProShareAdminController@userFiles', ['auth', 'admin']);
$router->get('/admin/projects/proshare/user-activity', 'Admin\\ProShareAdminController@userActivity', ['auth', 'admin']);

// File Management
$router->get('/admin/projects/proshare/files', 'Admin\\ProShareAdminController@files', ['auth', 'admin']);
$router->post('/admin/projects/proshare/files/delete', 'Admin\\ProShareAdminController@deleteFile', ['auth', 'admin']);
$router->post('/admin/projects/proshare/files/expire', 'Admin\\ProShareAdminController@expireFile', ['auth', 'admin']);
$router->get('/admin/projects/proshare/texts', 'Admin\\ProShareAdminController@texts', ['auth', 'admin']);
$router->post('/admin/projects/proshare/texts/delete', 'Admin\\ProShareAdminController@deleteText', ['auth', 'admin']);

// User Activity Logs
$router->get('/admin/projects/proshare/user-logs', 'Admin\\ProShareAdminController@userLogs', ['auth', 'admin']);
$router->get('/admin/projects/proshare/sessions', 'Admin\\ProShareAdminController@sessions', ['auth', 'admin']);

// File & Folder Activity
$router->get('/admin/projects/proshare/file-activity', 'Admin\\ProShareAdminController@fileActivity', ['auth', 'admin']);

// Security Monitoring
$router->get('/admin/projects/proshare/security', 'Admin\\ProShareAdminController@security', ['auth', 'admin']);
$router->get('/admin/projects/proshare/server-health', 'Admin\\ProShareAdminController@serverHealth', ['auth', 'admin']);

// Storage Monitoring
$router->get('/admin/projects/proshare/storage', 'Admin\\ProShareAdminController@storage', ['auth', 'admin']);

// Audit Trail
$router->get('/admin/projects/proshare/audit-trail', 'Admin\\ProShareAdminController@auditTrail', ['auth', 'admin']);
$router->get('/admin/projects/proshare/audit-trail/export', 'Admin\\ProShareAdminController@exportAuditTrail', ['auth', 'admin']);

// Notifications & Alerts
$router->get('/admin/projects/proshare/notifications', 'Admin\\ProShareAdminController@notifications', ['auth', 'admin']);

// Analytics & Insights
$router->get('/admin/projects/proshare/analytics', 'Admin\\ProShareAdminController@analytics', ['auth', 'admin']);

// Activity Tracker
$router->get('/admin/projects/proshare/track', 'Admin\\ProShareAdminController@track', ['auth', 'admin']);

// BillX admin routes
$router->get('/admin/projects/billx', 'Admin\\BillXAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/billx/bills', 'Admin\\BillXAdminController@bills', ['auth', 'admin']);
$router->get('/admin/projects/billx/bills/export', 'Admin\\BillXAdminController@exportCsv', ['auth', 'admin']);
$router->get('/admin/projects/billx/bills/view/{id}', 'Admin\\BillXAdminController@viewBill', ['auth', 'admin']);
$router->post('/admin/projects/billx/bills/delete', 'Admin\\BillXAdminController@deleteBill', ['auth', 'admin']);
$router->post('/admin/projects/billx/bills/bulk-delete', 'Admin\\BillXAdminController@bulkDelete', ['auth', 'admin']);
$router->get('/admin/projects/billx/settings', 'Admin\\BillXAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/billx/settings', 'Admin\\BillXAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/billx/activity-logs', 'Admin\\BillXAdminController@activityLogs', ['auth', 'admin']);

// Project Database Setup routes
$router->get('/admin/projects/database-setup', 'Admin\\ProjectDatabaseController@index', ['auth', 'admin']);
$router->get('/admin/projects/database-setup/{project}', 'Admin\\ProjectDatabaseController@configure', ['auth', 'admin']);
$router->post('/admin/projects/database-setup/test', 'Admin\\ProjectDatabaseController@testConnection', ['auth', 'admin']);
$router->post('/admin/projects/database-setup/save', 'Admin\\ProjectDatabaseController@saveConfiguration', ['auth', 'admin']);
$router->post('/admin/projects/database-setup/import', 'Admin\\ProjectDatabaseController@importSchema', ['auth', 'admin']);

// Security center
$router->get('/admin/security', 'Admin\\SecurityController@index', ['auth', 'admin']);
$router->get('/admin/security/blocked-ips', 'Admin\\SecurityController@blockedIps', ['auth', 'admin']);
$router->post('/admin/security/block-ip', 'Admin\\SecurityController@blockIp', ['auth', 'admin']);
$router->post('/admin/security/unblock-ip/{id}', 'Admin\\SecurityController@unblockIp', ['auth', 'admin']);
$router->get('/admin/security/failed-logins', 'Admin\\SecurityController@failedLogins', ['auth', 'admin']);
$router->post('/admin/security/auto-block', 'Admin\\SecurityController@autoBlock', ['auth', 'admin']);
$router->get('/admin/security/stats', 'Admin\\SecurityController@getStats', ['auth', 'admin']);
$router->get('/admin/security/settings', 'Admin\\SecurityController@uploadSettings', ['auth', 'admin']);
$router->post('/admin/security/update-settings', 'Admin\\SecurityController@updateUploadSettings', ['auth', 'admin']);

// OAuth Management
$router->get('/admin/oauth', 'Admin\\OAuthController@index', ['auth', 'admin']);
$router->get('/admin/oauth/{id}/edit', 'Admin\\OAuthController@edit', ['auth', 'admin']);
$router->post('/admin/oauth/{id}/edit', 'Admin\\OAuthController@update', ['auth', 'admin']);
$router->post('/admin/oauth/{id}/toggle', 'Admin\\OAuthController@toggle', ['auth', 'admin']);
$router->get('/admin/oauth/connections', 'Admin\\OAuthController@connections', ['auth', 'admin']);
$router->post('/admin/oauth/connections/{id}/revoke', 'Admin\\OAuthController@revokeConnection', ['auth', 'admin']);

// 2FA Management routes
$router->get('/admin/2fa', 'Admin\\TwoFactorController@index', ['auth', 'admin']);
$router->post('/admin/2fa/{userId}/reset', 'Admin\\TwoFactorController@reset', ['auth', 'admin']);
$router->post('/admin/2fa/{userId}/toggle', 'Admin\\TwoFactorController@toggle', ['auth', 'admin']);

// Session Management
$router->get('/admin/sessions', 'Admin\\SessionController@index', ['auth', 'admin']);
$router->post('/admin/sessions/{id}/revoke', 'Admin\\SessionController@revoke', ['auth', 'admin']);
$router->post('/admin/sessions/cleanup', 'Admin\\SessionController@cleanup', ['auth', 'admin']);
$router->get('/admin/sessions/login-history', 'Admin\\SessionController@loginHistory', ['auth', 'admin']);

// Activity logs
$router->get('/admin/logs', 'Admin\\LogController@index', ['auth', 'admin']);
$router->get('/admin/logs/activity', 'Admin\\LogController@activity', ['auth', 'admin']);
$router->get('/admin/logs/activity/export', 'Admin\\LogController@export', ['auth', 'admin']);
$router->get('/admin/logs/activity/api', 'Admin\\LogController@api', ['auth', 'admin']);
$router->get('/admin/logs/system', 'Admin\\LogController@system', ['auth', 'admin']);

// Audit Explorer – accessible to admin, super_admin, and audit_viewer roles
$router->get('/admin/audit', 'Admin\\AuditController@index', ['auth']);
$router->post('/admin/audit/query', 'Admin\\AuditController@query', ['auth']);
$router->post('/admin/audit/sql', 'Admin\\AuditController@rawSql', ['auth']);
$router->get('/admin/audit/export', 'Admin\\AuditController@export', ['auth']);

// Navbar customization
$router->get('/admin/navbar', 'Admin\\NavbarController@index', ['auth', 'admin']);
$router->post('/admin/navbar/update', 'Admin\\NavbarController@update', ['auth', 'admin']);
$router->get('/admin/navbar/reset', 'Admin\\NavbarController@reset', ['auth', 'admin']);
$router->post('/admin/navbar/reset', 'Admin\\NavbarController@reset', ['auth', 'admin']);

// Settings
$router->get('/admin/settings', 'Admin\\SettingsController@index', ['auth', 'admin']);
$router->post('/admin/settings', 'Admin\\SettingsController@update', ['auth', 'admin']);
$router->post('/admin/settings/upload-logo', 'Admin\\SettingsController@uploadLogo', ['auth', 'admin']);
$router->post('/admin/settings/delete-logo', 'Admin\\SettingsController@deleteLogo', ['auth', 'admin']);
$router->get('/admin/settings/session', 'Admin\\SettingsController@session', ['auth', 'admin']);
$router->post('/admin/settings/session', 'Admin\\SettingsController@updateSession', ['auth', 'admin']);
$router->post('/admin/settings/security-policy', 'Admin\\SettingsController@updateSecurityPolicy', ['auth', 'admin']);
$router->post('/admin/settings/force-logout-all', 'Admin\\SettingsController@forceLogoutAll', ['auth', 'admin']);
$router->post('/admin/settings/force-logout-user', 'Admin\\SettingsController@forceLogoutUser', ['auth', 'admin']);

// Theme Management
$router->get('/admin/settings/theme', 'Admin\\ThemeController@index', ['auth', 'admin']);
$router->post('/admin/settings/theme', 'Admin\\ThemeController@update', ['auth', 'admin']);
$router->get('/admin/api/theme', 'Admin\\ThemeController@getThemeApi', ['auth', 'admin']);

// Preloader & Skeleton Settings
$router->get('/admin/settings/preloader', 'Admin\\PreloaderController@index', ['auth', 'admin']);
$router->post('/admin/settings/preloader', 'Admin\\PreloaderController@update', ['auth', 'admin']);

// Captcha Settings
$router->get('/admin/settings/captcha', 'Admin\\CaptchaAdminController@index', ['auth', 'admin']);
$router->post('/admin/settings/captcha', 'Admin\\CaptchaAdminController@update', ['auth', 'admin']);

// ── Tools ─────────────────────────────────────────────────────────────────────
$router->get('/admin/tools/scanner', 'Admin\\ToolsController@scanner', ['auth', 'admin']);
$router->post('/admin/tools/scanner', 'Admin\\ToolsController@scanUrl', ['auth', 'admin']);
$router->get('/admin/settings/maintenance', 'Admin\\SettingsController@maintenance', ['auth', 'admin']);
$router->post('/admin/settings/maintenance', 'Admin\\SettingsController@toggleMaintenance', ['auth', 'admin']);
$router->post('/admin/settings/maintenance/update', 'Admin\\SettingsController@updateMaintenanceSettings', ['auth', 'admin']);
$router->get('/admin/settings/features', 'Admin\\SettingsController@features', ['auth', 'admin']);
$router->post('/admin/settings/features/toggle', 'Admin\\SettingsController@toggleFeature', ['auth', 'admin']);

// Navbar Settings
$router->get('/admin/navbar', 'Admin\\NavbarController@index', ['auth', 'admin']);
$router->post('/admin/navbar', 'Admin\\NavbarController@update', ['auth', 'admin']);

// Home Content Management
$router->get('/admin/home-content', 'Admin\\HomeContentController@index', ['auth', 'admin']);
$router->post('/admin/home-content/hero', 'Admin\\HomeContentController@updateHero', ['auth', 'admin']);
$router->post('/admin/home-content/projects-section', 'Admin\\HomeContentController@updateProjectsSection', ['auth', 'admin']);
$router->post('/admin/home-content/project', 'Admin\\HomeContentController@updateProject', ['auth', 'admin']);
$router->post('/admin/home-content/stat', 'Admin\\HomeContentController@updateStat', ['auth', 'admin']);
$router->get('/admin/home-content/stat/get/{id}', 'Admin\\HomeContentController@getStat', ['auth', 'admin']);
$router->post('/admin/home-content/stat/delete', 'Admin\\HomeContentController@deleteStat', ['auth', 'admin']);
$router->post('/admin/home-content/timeline', 'Admin\\HomeContentController@updateTimeline', ['auth', 'admin']);
$router->get('/admin/home-content/timeline/get/{id}', 'Admin\\HomeContentController@getTimeline', ['auth', 'admin']);
$router->post('/admin/home-content/timeline/delete', 'Admin\\HomeContentController@deleteTimeline', ['auth', 'admin']);
$router->post('/admin/home-content/section', 'Admin\\HomeContentController@updateSection', ['auth', 'admin']);
$router->post('/admin/home-content/hero-slide/add', 'Admin\\HomeContentController@addHeroSlide', ['auth', 'admin']);
$router->post('/admin/home-content/hero-slide/delete', 'Admin\\HomeContentController@deleteHeroSlide', ['auth', 'admin']);

// API Management routes
$router->get('/admin/api/keys', 'Admin\\ApiController@keys', ['auth', 'admin']);
$router->get('/admin/api/logs', 'Admin\\ApiController@logs', ['auth', 'admin']);
$router->get('/admin/api/rate-limits', 'Admin\\ApiController@rateLimits', ['auth', 'admin']);
$router->get('/admin/api/documentation', 'Admin\\ApiController@documentation', ['auth', 'admin']);
$router->post('/admin/api/keys/generate', 'Admin\\ApiController@generateKey', ['auth', 'admin']);
$router->post('/admin/api/keys/revoke', 'Admin\\ApiController@revokeKey', ['auth', 'admin']);

// WebSocket Management routes
$router->get('/admin/websocket/status', 'Admin\\WebSocketController@status', ['auth', 'admin']);
$router->get('/admin/websocket/connections', 'Admin\\WebSocketController@connections', ['auth', 'admin']);
$router->get('/admin/websocket/rooms', 'Admin\\WebSocketController@rooms', ['auth', 'admin']);
$router->get('/admin/websocket/settings', 'Admin\\WebSocketController@settings', ['auth', 'admin']);
$router->post('/admin/websocket/settings', 'Admin\\WebSocketController@updateSettings', ['auth', 'admin']);

// Analytics routes
$router->get('/admin/analytics/overview', 'Admin\\AnalyticsController@overview', ['auth', 'admin']);
$router->get('/admin/analytics/events', 'Admin\\AnalyticsController@events', ['auth', 'admin']);
$router->get('/admin/analytics/reports', 'Admin\\AnalyticsController@reports', ['auth', 'admin']);
$router->get('/admin/analytics/export', 'Admin\\AnalyticsController@export', ['auth', 'admin']);
$router->post('/admin/analytics/export', 'Admin\\AnalyticsController@export', ['auth', 'admin']);

// Email Management routes
$router->get('/admin/email/queue', 'Admin\\EmailController@queue', ['auth', 'admin']);
$router->get('/admin/email/templates', 'Admin\\EmailController@templates', ['auth', 'admin']);
$router->get('/admin/email/templates/view', 'Admin\\EmailController@viewTemplate', ['auth', 'admin']);
$router->get('/admin/email/templates/edit', 'Admin\\EmailController@editTemplate', ['auth', 'admin']);
$router->post('/admin/email/templates/toggle', 'Admin\\EmailController@toggleTemplate', ['auth', 'admin']);
$router->post('/admin/email/templates/update', 'Admin\\EmailController@updateTemplate', ['auth', 'admin']);
$router->post('/admin/email/queue/process', 'Admin\\EmailController@processQueue', ['auth', 'admin']);
$router->post('/admin/email/queue/delete-failed', 'Admin\\EmailController@deleteFailed', ['auth', 'admin']);

// Mail Provider Configuration routes
$router->get('/admin/mail/config', 'Admin\\MailConfigController@index', ['auth', 'admin']);
$router->get('/admin/mail/config/create', 'Admin\\MailConfigController@create', ['auth', 'admin']);
$router->get('/admin/mail/config/edit', 'Admin\\MailConfigController@edit', ['auth', 'admin']);
$router->post('/admin/mail/config/store', 'Admin\\MailConfigController@store', ['auth', 'admin']);
$router->post('/admin/mail/config/update', 'Admin\\MailConfigController@update', ['auth', 'admin']);
$router->post('/admin/mail/config/activate', 'Admin\\MailConfigController@activate', ['auth', 'admin']);
$router->post('/admin/mail/config/delete', 'Admin\\MailConfigController@delete', ['auth', 'admin']);
$router->post('/admin/mail/config/test-smtp', 'Admin\\MailConfigController@testSmtp', ['auth', 'admin']);
$router->post('/admin/mail/config/test-imap', 'Admin\\MailConfigController@testImap', ['auth', 'admin']);
$router->post('/admin/mail/config/send-test', 'Admin\\MailConfigController@sendTestEmail', ['auth', 'admin']);
$router->post('/admin/mail/config/process-queue', 'Admin\\MailConfigController@processQueue', ['auth', 'admin']);
$router->get('/admin/mail/templates', 'Admin\\MailConfigController@templates', ['auth', 'admin']);
$router->get('/admin/mail/templates/edit', 'Admin\\MailConfigController@editTemplate', ['auth', 'admin']);
$router->post('/admin/mail/templates/update', 'Admin\\MailConfigController@updateTemplate', ['auth', 'admin']);
$router->post('/admin/mail/templates/toggle', 'Admin\\MailConfigController@toggleTemplate', ['auth', 'admin']);
$router->post('/admin/mail/templates/set-provider', 'Admin\\MailConfigController@setTemplateProvider', ['auth', 'admin']);
$router->get('/admin/mail/logs', 'Admin\\MailConfigController@logs', ['auth', 'admin']);
// Mail user access management
$router->get('/admin/mail/access', 'Admin\\MailAccessController@index', ['auth', 'admin']);
$router->get('/admin/mail/access/{userId}/edit', 'Admin\\MailAccessController@editForm', ['auth', 'admin']);
$router->post('/admin/mail/access/{userId}/save', 'Admin\\MailAccessController@save', ['auth', 'admin']);
$router->post('/admin/mail/access/{userId}/revoke', 'Admin\\MailAccessController@revoke', ['auth', 'admin']);

// Notification Management routes
$router->get('/admin/notifications/all', 'Admin\\NotificationController@allNotifications', ['auth', 'admin']);
$router->get('/admin/notifications/preferences', 'Admin\\NotificationController@preferences', ['auth', 'admin']);
$router->post('/admin/notifications/send-test', 'Admin\\NotificationController@sendTest', ['auth', 'admin']);
$router->post('/admin/notifications/delete-old', 'Admin\\NotificationController@deleteOld', ['auth', 'admin']);

// Performance Management routes
$router->get('/admin/performance/cache', 'Admin\\PerformanceController@cache', ['auth', 'admin']);
$router->get('/admin/performance/assets', 'Admin\\PerformanceController@assets', ['auth', 'admin']);
$router->get('/admin/performance/database', 'Admin\\PerformanceController@database', ['auth', 'admin']);
$router->get('/admin/performance/monitoring', 'Admin\\PerformanceController@monitoring', ['auth', 'admin']);
$router->post('/admin/performance/cache/clear', 'Admin\\PerformanceController@clearCache', ['auth', 'admin']);
$router->post('/admin/performance/database/optimize', 'Admin\\PerformanceController@optimizeTable', ['auth', 'admin']);
$router->post('/admin/performance/assets/minify', 'Admin\\PerformanceController@minifyAsset', ['auth', 'admin']);

// WhatsApp API admin routes
$router->get('/admin/whatsapp/overview', 'Admin\\WhatsAppAdminController@overview', ['auth', 'admin']);
$router->get('/admin/whatsapp/sessions', 'Admin\\WhatsAppAdminController@sessions', ['auth', 'admin']);
$router->get('/admin/whatsapp/messages', 'Admin\\WhatsAppAdminController@messages', ['auth', 'admin']);
$router->get('/admin/whatsapp/users', 'Admin\\WhatsAppAdminController@userSettings', ['auth', 'admin']);
$router->get('/admin/whatsapp/api-logs', 'Admin\\WhatsAppAdminController@apiLogs', ['auth', 'admin']);
$router->get('/admin/whatsapp/api-keys', 'Admin\\WhatsAppAdminController@whatsappApiKeys', ['auth', 'admin']);
$router->post('/admin/whatsapp/api-keys/generate', 'Admin\\WhatsAppAdminController@generateWhatsAppApiKeyForUser', ['auth', 'admin']);
$router->post('/admin/whatsapp/api-keys/revoke', 'Admin\\WhatsAppAdminController@revokeWhatsAppApiKey', ['auth', 'admin']);
$router->post('/admin/whatsapp/sessions/delete', 'Admin\\WhatsAppAdminController@deleteSession', ['auth', 'admin']);

// WhatsApp Subscription Management
$router->get('/admin/whatsapp/subscription-plans', 'Admin\\WhatsAppSubscriptionController@plans', ['auth', 'admin']);
$router->get('/admin/whatsapp/subscription-plans/create', 'Admin\\WhatsAppSubscriptionController@createPlanForm', ['auth', 'admin']);
$router->post('/admin/whatsapp/subscription-plans/create', 'Admin\\WhatsAppSubscriptionController@createPlan', ['auth', 'admin']);
$router->get('/admin/whatsapp/subscription-plans/edit/{id}', 'Admin\\WhatsAppSubscriptionController@editPlanForm', ['auth', 'admin']);
$router->post('/admin/whatsapp/subscription-plans/update/{id}', 'Admin\\WhatsAppSubscriptionController@updatePlan', ['auth', 'admin']);
$router->post('/admin/whatsapp/subscription-plans/delete/{id}', 'Admin\\WhatsAppSubscriptionController@deletePlan', ['auth', 'admin']);
$router->get('/admin/whatsapp/user-subscriptions', 'Admin\\WhatsAppSubscriptionController@subscriptions', ['auth', 'admin']);
$router->get('/admin/whatsapp/user-subscriptions/assign', 'Admin\\WhatsAppSubscriptionController@assignSubscriptionForm', ['auth', 'admin']);
$router->post('/admin/whatsapp/user-subscriptions/assign', 'Admin\\WhatsAppSubscriptionController@assignSubscription', ['auth', 'admin']);
$router->post('/admin/whatsapp/user-subscriptions/update/{id}', 'Admin\\WhatsAppSubscriptionController@updateSubscription', ['auth', 'admin']);
$router->post('/admin/whatsapp/user-subscriptions/cancel/{id}', 'Admin\\WhatsAppSubscriptionController@cancelSubscription', ['auth', 'admin']);


// ── ResumeX Admin ────────────────────────────────────────────────────────────
$router->get('/admin/projects/resumex', 'Admin\\ResumeXAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/resumex/analytics', 'Admin\\ResumeXAdminController@analytics', ['auth', 'admin']);
$router->get('/admin/projects/resumex/plans', 'Admin\\ResumeXAdminController@plans', ['auth', 'admin']);
$router->post('/admin/projects/resumex/plans', 'Admin\\ResumeXAdminController@plans', ['auth', 'admin']);
$router->get('/admin/projects/resumex/settings', 'Admin\\ResumeXAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/resumex/settings', 'Admin\\ResumeXAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/resumex/templates', 'Admin\\ResumeXAdminController@templates', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/upload', 'Admin\\ResumeXAdminController@uploadTemplate', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/upload-full', 'Admin\\ResumeXAdminController@uploadFullTemplate', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/delete', 'Admin\\ResumeXAdminController@deleteTemplate', ['auth', 'admin']);
$router->get('/admin/projects/resumex/templates/sample-download', 'Admin\\ResumeXAdminController@downloadSample', ['auth', 'admin']);
$router->get('/admin/projects/resumex/templates/sample-full-download', 'Admin\\ResumeXAdminController@downloadSampleFull', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/preview-image', 'Admin\\ResumeXAdminController@uploadPreviewImage', ['auth', 'admin']);
$router->get('/admin/projects/resumex/designer', 'Admin\\ResumeXAdminController@designerNew', ['auth', 'admin']);
$router->get('/admin/projects/resumex/designer/{id}', 'Admin\\ResumeXAdminController@designerEdit', ['auth', 'admin']);
$router->post('/admin/projects/resumex/designer/save', 'Admin\\ResumeXAdminController@designerSave', ['auth', 'admin']);
$router->get('/admin/projects/resumex/resumes', 'Admin\\ResumeXAdminController@resumes', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/toggle-pro', 'Admin\\ResumeXAdminController@toggleTemplatePro', ['auth', 'admin']);
$router->post('/admin/projects/resumex/templates/toggle-builtin-pro', 'Admin\\ResumeXAdminController@toggleBuiltinTemplatePro', ['auth', 'admin']);

// ── FormX – Form Builder ─────────────────────────────────────────────────────
$router->get('/admin/formx',                                         'Admin\\FormXController@index',            ['auth', 'admin']);
$router->get('/admin/formx/overview',                                'Admin\\FormXController@overview',         ['auth', 'admin']);
$router->get('/admin/formx/create',                                  'Admin\\FormXController@create',           ['auth', 'admin']);
$router->post('/admin/formx/save',                                   'Admin\\FormXController@save',             ['auth', 'admin']);
$router->get('/admin/formx/{id}/edit',                               'Admin\\FormXController@edit',             ['auth', 'admin']);
$router->post('/admin/formx/{id}/update',                            'Admin\\FormXController@update',           ['auth', 'admin']);
$router->post('/admin/formx/{id}/delete',                            'Admin\\FormXController@delete',           ['auth', 'admin']);
$router->post('/admin/formx/{id}/duplicate',                         'Admin\\FormXController@duplicate',        ['auth', 'admin']);
$router->post('/admin/formx/{id}/toggle',                            'Admin\\FormXController@toggle',           ['auth', 'admin']);
$router->get('/admin/formx/{id}/submissions',                        'Admin\\FormXController@submissions',      ['auth', 'admin']);
$router->get('/admin/formx/{id}/export',                             'Admin\\FormXController@exportSubmissions', ['auth', 'admin']);
$router->get('/admin/formx/{form_id}/submissions/{submission_id}',   'Admin\\FormXController@submissionDetail', ['auth', 'admin']);
$router->post('/admin/formx/{form_id}/submissions/{submission_id}/delete', 'Admin\\FormXController@deleteSubmission', ['auth', 'admin']);

// ── CardX (ID Card Generator) Admin ─────────────────────────────────────────
$router->get('/admin/projects/idcard', 'Admin\\IDCardAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/idcard/cards', 'Admin\\IDCardAdminController@cards', ['auth', 'admin']);
$router->post('/admin/projects/idcard/cards/delete', 'Admin\\IDCardAdminController@deleteCard', ['auth', 'admin']);
$router->get('/admin/projects/idcard/bulk-jobs', 'Admin\\IDCardAdminController@bulkJobs', ['auth', 'admin']);
$router->get('/admin/projects/idcard/settings', 'Admin\\IDCardAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/idcard/settings', 'Admin\\IDCardAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/idcard/ai-settings', 'Admin\\IDCardAdminController@aiSettings', ['auth', 'admin']);
$router->post('/admin/projects/idcard/ai-settings', 'Admin\\IDCardAdminController@aiSettings', ['auth', 'admin']);

// ── LinkShortner Admin ────────────────────────────────────────────────────────
$router->get('/admin/projects/linkshortner', 'Admin\\LinkShortnerAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/linkshortner/links', 'Admin\\LinkShortnerAdminController@links', ['auth', 'admin']);
$router->post('/admin/projects/linkshortner/links/delete', 'Admin\\LinkShortnerAdminController@deleteLink', ['auth', 'admin']);
$router->get('/admin/projects/linkshortner/analytics', 'Admin\\LinkShortnerAdminController@analytics', ['auth', 'admin']);
$router->get('/admin/projects/linkshortner/users', 'Admin\\LinkShortnerAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/linkshortner/settings', 'Admin\\LinkShortnerAdminController@settings', ['auth', 'admin']);

// ── NoteX Admin ───────────────────────────────────────────────────────────────
$router->get('/admin/projects/notex', 'Admin\\NoteXAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/notex/notes', 'Admin\\NoteXAdminController@notes', ['auth', 'admin']);
$router->post('/admin/projects/notex/notes/delete', 'Admin\\NoteXAdminController@deleteNote', ['auth', 'admin']);
$router->get('/admin/projects/notex/users', 'Admin\\NoteXAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/notex/settings', 'Admin\\NoteXAdminController@settings', ['auth', 'admin']);

// Network Inspector (super_admin + debug mode only)
$router->get("/admin/network-inspector", "Admin\\\\NetworkInspectorController@index", ["auth", "admin"]);
$router->post("/admin/network-inspector/clear", "Admin\\\\NetworkInspectorController@clear", ["auth", "admin"]);

// Support Admin
$router->get('/admin/support', 'Admin\\SupportAdminController@index', ['auth', 'admin']);
$router->get('/admin/support/tickets', 'Admin\\SupportAdminController@tickets', ['auth', 'admin']);
$router->get('/admin/support/tickets/{id}', 'Admin\\SupportAdminController@viewTicket', ['auth', 'admin']);
$router->post('/admin/support/tickets/{id}/reply', 'Admin\\SupportAdminController@replyTicket', ['auth', 'admin']);
$router->post('/admin/support/tickets/{id}/status', 'Admin\\SupportAdminController@updateTicketStatus', ['auth', 'admin']);
$router->post('/admin/support/tickets/{id}/reopen', 'Admin\\SupportAdminController@reopenTicket', ['auth', 'admin']);
$router->get('/admin/support/live-chats', 'Admin\\SupportAdminController@liveChats', ['auth', 'admin']);
$router->get('/admin/support/live-chats/{id}', 'Admin\\SupportAdminController@viewLiveChat', ['auth', 'admin']);
$router->post('/admin/support/live-chats/{id}/reply', 'Admin\\SupportAdminController@replyLiveChat', ['auth', 'admin']);
$router->post('/admin/support/live-chats/{id}/close', 'Admin\\SupportAdminController@closeLiveChat', ['auth', 'admin']);
$router->post('/admin/support/live-chats/{id}/reopen', 'Admin\\SupportAdminController@reopenLiveChat', ['auth', 'admin']);
$router->get('/admin/support/templates', 'Admin\\SupportAdminController@templates', ['auth', 'admin']);
$router->post('/admin/support/templates/category/create', 'Admin\\SupportAdminController@createCategory', ['auth', 'admin']);
$router->post('/admin/support/templates/category/{id}/delete', 'Admin\\SupportAdminController@deleteCategory', ['auth', 'admin']);
$router->post('/admin/support/templates/item/create', 'Admin\\SupportAdminController@createItem', ['auth', 'admin']);
$router->post('/admin/support/templates/item/{id}/delete', 'Admin\\SupportAdminController@deleteItem', ['auth', 'admin']);
$router->get('/admin/support/users', 'Admin\\SupportAdminController@userAccess', ['auth', 'admin']);
$router->post('/admin/support/agents/add', 'Admin\\SupportAdminController@addAgent', ['auth', 'admin']);
$router->post('/admin/support/agents/{id}/remove', 'Admin\\SupportAdminController@removeAgent', ['auth', 'admin']);
$router->get('/admin/support/settings', 'Admin\\SupportAdminController@supportSettings', ['auth', 'admin']);
$router->post('/admin/support/settings', 'Admin\\SupportAdminController@saveSupportSettings', ['auth', 'admin']);

// ── Dynamic Ticket Template Builder ──────────────────────────────────────────
// Groups
$router->get('/admin/support/groups',          'Admin\\SupportTemplateAdminController@groups',       ['auth', 'admin']);
$router->post('/admin/support/groups/create',  'Admin\\SupportTemplateAdminController@createGroup',  ['auth', 'admin']);
$router->post('/admin/support/groups/{id}/update', 'Admin\\SupportTemplateAdminController@updateGroup', ['auth', 'admin']);
$router->post('/admin/support/groups/{id}/delete', 'Admin\\SupportTemplateAdminController@deleteGroup', ['auth', 'admin']);
// Categories (under a group)
$router->get('/admin/support/groups/{group_id}/categories',        'Admin\\SupportTemplateAdminController@categories',      ['auth', 'admin']);
$router->post('/admin/support/groups/{group_id}/categories/create','Admin\\SupportTemplateAdminController@createCategory',  ['auth', 'admin']);
$router->post('/admin/support/categories/{id}/update',             'Admin\\SupportTemplateAdminController@updateCategory',  ['auth', 'admin']);
$router->post('/admin/support/categories/{id}/delete',             'Admin\\SupportTemplateAdminController@deleteCategory',  ['auth', 'admin']);
// Form Builder (drag-and-drop)
$router->get('/admin/support/builder/{category_id}',               'Admin\\SupportTemplateAdminController@builder',         ['auth', 'admin']);
