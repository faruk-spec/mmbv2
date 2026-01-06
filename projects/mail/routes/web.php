<?php
/**
 * Mail Server Project Routes
 * 
 * @package MMB\Projects\Mail
 */

use Core\Router;

$router = new Router();
$baseUrl = '/projects/mail';

// Dashboard / Webmail Interface
$router->get($baseUrl, 'Controllers\\Mail\\DashboardController@index');
$router->get($baseUrl . '/dashboard', 'Controllers\\Mail\\DashboardController@index');

// New subscription / upgrade routes
$router->get($baseUrl . '/subscribe', 'Controllers\\Mail\\SubscriberController@subscribe');
$router->post($baseUrl . '/subscribe', 'Controllers\\Mail\\SubscriberController@processSubscription');
$router->get($baseUrl . '/subscriber/upgrade', 'Controllers\\Mail\\SubscriberController@showUpgrade');

// ============================================
// SUBSCRIBER OWNER ROUTES (Manage their subscription)
// ============================================
$router->get($baseUrl . '/subscriber/dashboard', 'Controllers\\Mail\\SubscriberController@dashboard');
$router->get($baseUrl . '/subscriber/users', 'Controllers\\Mail\\SubscriberController@manageUsers');
$router->get($baseUrl . '/subscriber/users/add', 'Controllers\\Mail\\SubscriberController@addUser');
$router->post($baseUrl . '/subscriber/users/add', 'Controllers\\Mail\\SubscriberController@addUser');
$router->post($baseUrl . '/subscriber/users/assign-role', 'Controllers\\Mail\\SubscriberController@assignRole');
$router->post($baseUrl . '/subscriber/users/delete', 'Controllers\\Mail\\SubscriberController@deleteUser');
$router->get($baseUrl . '/subscriber/users/{id}/edit', 'Controllers\\Mail\\SubscriberController@editUser');
$router->post($baseUrl . '/subscriber/users/{id}/edit', 'Controllers\\Mail\\SubscriberController@updateUser');
$router->post($baseUrl . '/subscriber/users/{id}/suspend', 'Controllers\\Mail\\SubscriberController@suspendUser');
$router->post($baseUrl . '/subscriber/users/{id}/activate', 'Controllers\\Mail\\SubscriberController@activateUser');

// Subscriber billing and subscription management
$router->get($baseUrl . '/subscriber/subscription', 'Controllers\\Mail\\SubscriberController@subscription');
$router->get($baseUrl . '/subscriber/billing', 'Controllers\\Mail\\SubscriberController@billing');
$router->post($baseUrl . '/subscriber/upgrade', 'Controllers\\Mail\\SubscriberController@upgradePlan');
$router->post($baseUrl . '/subscriber/downgrade', 'Controllers\\Mail\\SubscriberController@downgradePlan');

// Subscriber domain management
$router->get($baseUrl . '/subscriber/domains', 'Controllers\\Mail\\DomainController@index');
$router->get($baseUrl . '/subscriber/domains/add', 'Controllers\\Mail\\DomainController@create');
$router->post($baseUrl . '/subscriber/domains/store', 'Controllers\\Mail\\DomainController@store');
$router->get($baseUrl . '/subscriber/domains/{id}/dns', 'Controllers\\Mail\\DomainController@dnsRecords');
$router->post($baseUrl . '/subscriber/domains/{id}/verify', 'Controllers\\Mail\\DomainController@verify');
$router->delete($baseUrl . '/subscriber/domains/{id}/delete', 'Controllers\\Mail\\DomainController@delete');

// Subscriber alias management
$router->get($baseUrl . '/subscriber/aliases', 'Controllers\\Mail\\AliasController@index');
$router->get($baseUrl . '/subscriber/aliases/add', 'Controllers\\Mail\\AliasController@create');
$router->post($baseUrl . '/subscriber/aliases/store', 'Controllers\\Mail\\AliasController@store');
$router->post($baseUrl . '/subscriber/aliases/{id}/toggle', 'Controllers\\Mail\\AliasController@toggleStatus');

// ============================================
// WEBMAIL INTERFACE ROUTES
// ============================================
$router->get($baseUrl . '/webmail', 'Controllers\\Mail\\WebmailController@inbox');
$router->get($baseUrl . '/webmail/inbox', 'Controllers\\Mail\\WebmailController@inbox');
$router->get($baseUrl . '/webmail/view/{messageId}', 'Controllers\\Mail\\WebmailController@viewEmail');
$router->get($baseUrl . '/webmail/compose', 'Controllers\\Mail\\WebmailController@compose');
$router->post($baseUrl . '/webmail/send', 'Controllers\\Mail\\WebmailController@send');

// Webmail actions
$router->post($baseUrl . '/webmail/move-to-folder', 'Controllers\\Mail\\WebmailController@moveToFolder');
$router->post($baseUrl . '/webmail/toggle-read', 'Controllers\\Mail\\WebmailController@toggleRead');
$router->post($baseUrl . '/webmail/toggle-star', 'Controllers\\Mail\\WebmailController@toggleStar');
$router->post($baseUrl . '/webmail/delete', 'Controllers\\Mail\\WebmailController@delete');
$router->post($baseUrl . '/webmail/bulk-action', 'Controllers\\Mail\\WebmailController@bulkAction');
$router->get($baseUrl . '/webmail/attachment/{attachmentId}', 'Controllers\\Mail\\WebmailController@downloadAttachment');
$router->delete($baseUrl . '/subscriber/aliases/{id}/delete', 'Controllers\\Mail\\AliasController@delete');

// ============================================
// MAILBOX MANAGEMENT
// ============================================
$router->get($baseUrl . '/mailbox/inbox', 'Controllers\\Mail\\MailboxController@inbox');
$router->get($baseUrl . '/mailbox/sent', 'Controllers\\Mail\\MailboxController@sent');
$router->get($baseUrl . '/mailbox/drafts', 'Controllers\\Mail\\MailboxController@drafts');
$router->get($baseUrl . '/mailbox/trash', 'Controllers\\Mail\\MailboxController@trash');
$router->get($baseUrl . '/mailbox/spam', 'Controllers\\Mail\\MailboxController@spam');
$router->get($baseUrl . '/mailbox/folder/{id}', 'Controllers\\Mail\\MailboxController@folder');

// Email Operations
$router->get($baseUrl . '/email/compose', 'Controllers\\Mail\\EmailController@compose');
$router->post($baseUrl . '/email/send', 'Controllers\\Mail\\EmailController@send');
$router->get($baseUrl . '/email/read/{id}', 'Controllers\\Mail\\EmailController@read');
$router->post($baseUrl . '/email/reply/{id}', 'Controllers\\Mail\\EmailController@reply');
$router->post($baseUrl . '/email/forward/{id}', 'Controllers\\Mail\\EmailController@forward');
$router->post($baseUrl . '/email/delete', 'Controllers\\Mail\\EmailController@delete');
$router->post($baseUrl . '/email/move', 'Controllers\\Mail\\EmailController@move');
$router->post($baseUrl . '/email/mark-read', 'Controllers\\Mail\\EmailController@markRead');
$router->post($baseUrl . '/email/mark-spam', 'Controllers\\Mail\\EmailController@markSpam');
$router->post($baseUrl . '/email/star', 'Controllers\\Mail\\EmailController@star');

// Folders
$router->get($baseUrl . '/folders', 'Controllers\\Mail\\FolderController@index');
$router->post($baseUrl . '/folders/create', 'Controllers\\Mail\\FolderController@create');
$router->post($baseUrl . '/folders/{id}/edit', 'Controllers\\Mail\\FolderController@edit');
$router->post($baseUrl . '/folders/{id}/delete', 'Controllers\\Mail\\FolderController@delete');

// Contacts
$router->get($baseUrl . '/contacts', 'Controllers\\Mail\\ContactController@index');
$router->get($baseUrl . '/contacts/create', 'Controllers\\Mail\\ContactController@create');
$router->post($baseUrl . '/contacts/create', 'Controllers\\Mail\\ContactController@store');
$router->get($baseUrl . '/contacts/{id}/edit', 'Controllers\\Mail\\ContactController@edit');
$router->post($baseUrl . '/contacts/{id}/edit', 'Controllers\\Mail\\ContactController@update');
$router->post($baseUrl . '/contacts/{id}/delete', 'Controllers\\Mail\\ContactController@delete');

// Templates
$router->get($baseUrl . '/templates', 'Controllers\\Mail\\TemplateController@index');
$router->get($baseUrl . '/templates/create', 'Controllers\\Mail\\TemplateController@create');
$router->post($baseUrl . '/templates/create', 'Controllers\\Mail\\TemplateController@store');
$router->get($baseUrl . '/templates/{id}/edit', 'Controllers\\Mail\\TemplateController@edit');
$router->post($baseUrl . '/templates/{id}/edit', 'Controllers\\Mail\\TemplateController@update');
$router->post($baseUrl . '/templates/{id}/delete', 'Controllers\\Mail\\TemplateController@delete');

// Domain Management (User)
$router->get($baseUrl . '/domains', 'Controllers\\Mail\\DomainController@index');
$router->get($baseUrl . '/domains/add', 'Controllers\\Mail\\DomainController@add');
$router->post($baseUrl . '/domains/add', 'Controllers\\Mail\\DomainController@store');
$router->get($baseUrl . '/domains/{id}/verify', 'Controllers\\Mail\\DomainController@verify');
$router->get($baseUrl . '/domains/{id}/dns', 'Controllers\\Mail\\DomainController@dnsRecords');
$router->post($baseUrl . '/domains/{id}/check-verification', 'Controllers\\Mail\\DomainController@checkVerification');
$router->get($baseUrl . '/domains/{id}/settings', 'Controllers\\Mail\\DomainController@settings');
$router->post($baseUrl . '/domains/{id}/settings', 'Controllers\\Mail\\DomainController@updateSettings');

// Mailbox Accounts Management (User)
$router->get($baseUrl . '/accounts', 'Controllers\\Mail\\AccountController@index');
$router->get($baseUrl . '/accounts/create', 'Controllers\\Mail\\AccountController@create');
$router->post($baseUrl . '/accounts/create', 'Controllers\\Mail\\AccountController@store');
$router->get($baseUrl . '/accounts/{id}/edit', 'Controllers\\Mail\\AccountController@edit');
$router->post($baseUrl . '/accounts/{id}/edit', 'Controllers\\Mail\\AccountController@update');
$router->post($baseUrl . '/accounts/{id}/delete', 'Controllers\\Mail\\AccountController@delete');

// Aliases
$router->get($baseUrl . '/aliases', 'Controllers\\Mail\\AliasController@index');
$router->post($baseUrl . '/aliases/create', 'Controllers\\Mail\\AliasController@store');
$router->post($baseUrl . '/aliases/{id}/delete', 'Controllers\\Mail\\AliasController@delete');

// Filters & Auto-responders
$router->get($baseUrl . '/filters', 'Controllers\\Mail\\FilterController@index');
$router->get($baseUrl . '/filters/create', 'Controllers\\Mail\\FilterController@create');
$router->post($baseUrl . '/filters/create', 'Controllers\\Mail\\FilterController@store');
$router->get($baseUrl . '/filters/{id}/edit', 'Controllers\\Mail\\FilterController@edit');
$router->post($baseUrl . '/filters/{id}/edit', 'Controllers\\Mail\\FilterController@update');
$router->post($baseUrl . '/filters/{id}/delete', 'Controllers\\Mail\\FilterController@delete');
$router->post($baseUrl . '/filters/{id}/toggle', 'Controllers\\Mail\\FilterController@toggle');

// Auto-responder
$router->get($baseUrl . '/auto-responder', 'Controllers\\Mail\\AutoResponderController@index');
$router->post($baseUrl . '/auto-responder/save', 'Controllers\\Mail\\AutoResponderController@save');
$router->post($baseUrl . '/auto-responder/toggle', 'Controllers\\Mail\\AutoResponderController@toggle');

// Settings
$router->get($baseUrl . '/settings', 'Controllers\\Mail\\SettingsController@index');
$router->post($baseUrl . '/settings/save', 'Controllers\\Mail\\SettingsController@save');

// Search
$router->get($baseUrl . '/search', 'Controllers\\Mail\\SearchController@index');
$router->post($baseUrl . '/search', 'Controllers\\Mail\\SearchController@search');

// API endpoints for AJAX operations
$router->get($baseUrl . '/api/emails', 'Controllers\\Mail\\ApiController@getEmails');
$router->get($baseUrl . '/api/email/{id}', 'Controllers\\Mail\\ApiController@getEmail');
$router->post($baseUrl . '/api/email/draft', 'Controllers\\Mail\\ApiController@saveDraft');
$router->get($baseUrl . '/api/folders', 'Controllers\\Mail\\ApiController@getFolders');
$router->get($baseUrl . '/api/contacts/search', 'Controllers\\Mail\\ApiController@searchContacts');
$router->post($baseUrl . '/api/attachment/upload', 'Controllers\\Mail\\ApiController@uploadAttachment');
$router->get($baseUrl . '/api/attachment/download/{id}', 'Controllers\\Mail\\ApiController@downloadAttachment');

// Run the router
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
