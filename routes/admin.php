<?php
/**
 * Admin Routes
 * 
 * @package MMB\Routes
 */

// Admin dashboard
$router->get('/admin', 'Admin\\DashboardController@index', ['auth', 'admin']);
$router->get('/admin/dashboard', 'Admin\\DashboardController@index', ['auth', 'admin']);

// User management
$router->get('/admin/users', 'Admin\\UserController@index', ['auth', 'admin']);
$router->get('/admin/users/create', 'Admin\\UserController@create', ['auth', 'admin']);
$router->post('/admin/users/create', 'Admin\\UserController@store', ['auth', 'admin']);
$router->get('/admin/users/{id}/edit', 'Admin\\UserController@edit', ['auth', 'admin']);
$router->post('/admin/users/{id}/edit', 'Admin\\UserController@update', ['auth', 'admin']);
$router->post('/admin/users/{id}/delete', 'Admin\\UserController@delete', ['auth', 'admin']);
$router->post('/admin/users/{id}/toggle', 'Admin\\UserController@toggle', ['auth', 'admin']);

// Project management
$router->get('/admin/projects', 'Admin\\ProjectController@index', ['auth', 'admin']);
$router->get('/admin/projects/{name}', 'Admin\\ProjectController@show', ['auth', 'admin']);
$router->post('/admin/projects/{name}/toggle', 'Admin\\ProjectController@toggle', ['auth', 'admin']);
$router->get('/admin/projects/{name}/settings', 'Admin\\ProjectController@settings', ['auth', 'admin']);
$router->post('/admin/projects/{name}/settings', 'Admin\\ProjectController@updateSettings', ['auth', 'admin']);

// CodeXPro admin routes
$router->get('/admin/projects/codexpro', 'Admin\\CodeXProAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/settings', 'Admin\\CodeXProAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/settings', 'Admin\\CodeXProAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/users', 'Admin\\CodeXProAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/codexpro/templates', 'Admin\\CodeXProAdminController@templates', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/delete', 'Admin\\CodeXProAdminController@deleteTemplate', ['auth', 'admin']);
$router->post('/admin/projects/codexpro/templates/toggle', 'Admin\\CodeXProAdminController@toggleTemplate', ['auth', 'admin']);

// ImgTxt admin routes
$router->get('/admin/projects/imgtxt', 'Admin\\ImgTxtAdminController@overview', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/settings', 'Admin\\ImgTxtAdminController@settings', ['auth', 'admin']);
$router->post('/admin/projects/imgtxt/settings', 'Admin\\ImgTxtAdminController@settings', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/jobs', 'Admin\\ImgTxtAdminController@jobs', ['auth', 'admin']);
$router->post('/admin/projects/imgtxt/jobs/retry', 'Admin\\ImgTxtAdminController@retryJob', ['auth', 'admin']);
$router->post('/admin/projects/imgtxt/jobs/delete', 'Admin\\ImgTxtAdminController@deleteJob', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/languages', 'Admin\\ImgTxtAdminController@languages', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/users', 'Admin\\ImgTxtAdminController@users', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/statistics', 'Admin\\ImgTxtAdminController@statistics', ['auth', 'admin']);
$router->get('/admin/projects/imgtxt/activity', 'Admin\\ImgTxtAdminController@activity', ['auth', 'admin']);

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

// Activity logs
$router->get('/admin/logs', 'Admin\\LogController@index', ['auth', 'admin']);
$router->get('/admin/logs/activity', 'Admin\\LogController@activity', ['auth', 'admin']);
$router->get('/admin/logs/system', 'Admin\\LogController@system', ['auth', 'admin']);

// Navbar customization
$router->get('/admin/navbar', 'Admin\\NavbarController@index', ['auth', 'admin']);
$router->post('/admin/navbar/update', 'Admin\\NavbarController@update', ['auth', 'admin']);
$router->get('/admin/navbar/reset', 'Admin\\NavbarController@reset', ['auth', 'admin']);
$router->post('/admin/navbar/reset', 'Admin\\NavbarController@reset', ['auth', 'admin']);

// Settings
$router->get('/admin/settings', 'Admin\\SettingsController@index', ['auth', 'admin']);
$router->post('/admin/settings', 'Admin\\SettingsController@update', ['auth', 'admin']);
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
$router->post('/admin/email/queue/process', 'Admin\\EmailController@processQueue', ['auth', 'admin']);
$router->post('/admin/email/queue/delete-failed', 'Admin\\EmailController@deleteFailed', ['auth', 'admin']);

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
