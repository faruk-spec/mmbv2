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
$router->get($baseUrl, 'Mail\\DashboardController@index');
$router->get($baseUrl . '/dashboard', 'Mail\\DashboardController@index');

// ============================================
// SUBSCRIBER OWNER ROUTES (Manage their subscription)
// ============================================
$router->get($baseUrl . '/subscriber/dashboard', 'Mail\\SubscriberController@dashboard');
$router->get($baseUrl . '/subscriber/users', 'Mail\\SubscriberController@manageUsers');
$router->get($baseUrl . '/subscriber/users/add', 'Mail\\SubscriberController@addUser');
$router->post($baseUrl . '/subscriber/users/add', 'Mail\\SubscriberController@addUser');
$router->post($baseUrl . '/subscriber/users/assign-role', 'Mail\\SubscriberController@assignRole');
$router->post($baseUrl . '/subscriber/users/delete', 'Mail\\SubscriberController@deleteUser');
$router->get($baseUrl . '/subscriber/users/{id}/edit', 'Mail\\SubscriberController@editUser');
$router->post($baseUrl . '/subscriber/users/{id}/edit', 'Mail\\SubscriberController@updateUser');
$router->post($baseUrl . '/subscriber/users/{id}/suspend', 'Mail\\SubscriberController@suspendUser');
$router->post($baseUrl . '/subscriber/users/{id}/activate', 'Mail\\SubscriberController@activateUser');

// Subscriber billing and subscription management
$router->get($baseUrl . '/subscriber/subscription', 'Mail\\SubscriberController@subscription');
$router->get($baseUrl . '/subscriber/billing', 'Mail\\SubscriberController@billing');
$router->post($baseUrl . '/subscriber/upgrade', 'Mail\\SubscriberController@upgradePlan');
$router->post($baseUrl . '/subscriber/downgrade', 'Mail\\SubscriberController@downgradePlan');

// Subscriber domain management
$router->get($baseUrl . '/subscriber/domains', 'Mail\\DomainController@index');
$router->get($baseUrl . '/subscriber/domains/add', 'Mail\\DomainController@create');
$router->post($baseUrl . '/subscriber/domains/store', 'Mail\\DomainController@store');
$router->get($baseUrl . '/subscriber/domains/{id}/dns', 'Mail\\DomainController@dnsRecords');
$router->post($baseUrl . '/subscriber/domains/{id}/verify', 'Mail\\DomainController@verify');
$router->delete($baseUrl . '/subscriber/domains/{id}/delete', 'Mail\\DomainController@delete');

// Subscriber alias management
$router->get($baseUrl . '/subscriber/aliases', 'Mail\\AliasController@index');
$router->get($baseUrl . '/subscriber/aliases/add', 'Mail\\AliasController@create');
$router->post($baseUrl . '/subscriber/aliases/store', 'Mail\\AliasController@store');
$router->post($baseUrl . '/subscriber/aliases/{id}/toggle', 'Mail\\AliasController@toggleStatus');

// ============================================
// WEBMAIL INTERFACE ROUTES
// ============================================
$router->get($baseUrl . '/webmail', 'Mail\\WebmailController@inbox');
$router->get($baseUrl . '/webmail/inbox', 'Mail\\WebmailController@inbox');
$router->get($baseUrl . '/webmail/view/{messageId}', 'Mail\\WebmailController@viewEmail');
$router->get($baseUrl . '/webmail/compose', 'Mail\\WebmailController@compose');
$router->post($baseUrl . '/webmail/send', 'Mail\\WebmailController@send');

// Webmail actions
$router->post($baseUrl . '/webmail/move-to-folder', 'Mail\\WebmailController@moveToFolder');
$router->post($baseUrl . '/webmail/toggle-read', 'Mail\\WebmailController@toggleRead');
$router->post($baseUrl . '/webmail/toggle-star', 'Mail\\WebmailController@toggleStar');
$router->post($baseUrl . '/webmail/delete', 'Mail\\WebmailController@delete');
$router->post($baseUrl . '/webmail/bulk-action', 'Mail\\WebmailController@bulkAction');
$router->get($baseUrl . '/webmail/attachment/{attachmentId}', 'Mail\\WebmailController@downloadAttachment');
$router->delete($baseUrl . '/subscriber/aliases/{id}/delete', 'Mail\\AliasController@delete');

// ============================================
// MAILBOX MANAGEMENT
// ============================================
$router->get($baseUrl . '/mailbox/inbox', 'Mail\\MailboxController@inbox');
$router->get($baseUrl . '/mailbox/sent', 'Mail\\MailboxController@sent');
$router->get($baseUrl . '/mailbox/drafts', 'Mail\\MailboxController@drafts');
$router->get($baseUrl . '/mailbox/trash', 'Mail\\MailboxController@trash');
$router->get($baseUrl . '/mailbox/spam', 'Mail\\MailboxController@spam');
$router->get($baseUrl . '/mailbox/folder/{id}', 'Mail\\MailboxController@folder');

// Email Operations
$router->get($baseUrl . '/email/compose', 'Mail\\EmailController@compose');
$router->post($baseUrl . '/email/send', 'Mail\\EmailController@send');
$router->get($baseUrl . '/email/read/{id}', 'Mail\\EmailController@read');
$router->post($baseUrl . '/email/reply/{id}', 'Mail\\EmailController@reply');
$router->post($baseUrl . '/email/forward/{id}', 'Mail\\EmailController@forward');
$router->post($baseUrl . '/email/delete', 'Mail\\EmailController@delete');
$router->post($baseUrl . '/email/move', 'Mail\\EmailController@move');
$router->post($baseUrl . '/email/mark-read', 'Mail\\EmailController@markRead');
$router->post($baseUrl . '/email/mark-spam', 'Mail\\EmailController@markSpam');
$router->post($baseUrl . '/email/star', 'Mail\\EmailController@star');

// Folders
$router->get($baseUrl . '/folders', 'Mail\\FolderController@index');
$router->post($baseUrl . '/folders/create', 'Mail\\FolderController@create');
$router->post($baseUrl . '/folders/{id}/edit', 'Mail\\FolderController@edit');
$router->post($baseUrl . '/folders/{id}/delete', 'Mail\\FolderController@delete');

// Contacts
$router->get($baseUrl . '/contacts', 'Mail\\ContactController@index');
$router->get($baseUrl . '/contacts/create', 'Mail\\ContactController@create');
$router->post($baseUrl . '/contacts/create', 'Mail\\ContactController@store');
$router->get($baseUrl . '/contacts/{id}/edit', 'Mail\\ContactController@edit');
$router->post($baseUrl . '/contacts/{id}/edit', 'Mail\\ContactController@update');
$router->post($baseUrl . '/contacts/{id}/delete', 'Mail\\ContactController@delete');

// Templates
$router->get($baseUrl . '/templates', 'Mail\\TemplateController@index');
$router->get($baseUrl . '/templates/create', 'Mail\\TemplateController@create');
$router->post($baseUrl . '/templates/create', 'Mail\\TemplateController@store');
$router->get($baseUrl . '/templates/{id}/edit', 'Mail\\TemplateController@edit');
$router->post($baseUrl . '/templates/{id}/edit', 'Mail\\TemplateController@update');
$router->post($baseUrl . '/templates/{id}/delete', 'Mail\\TemplateController@delete');

// Domain Management (User)
$router->get($baseUrl . '/domains', 'Mail\\DomainController@index');
$router->get($baseUrl . '/domains/add', 'Mail\\DomainController@add');
$router->post($baseUrl . '/domains/add', 'Mail\\DomainController@store');
$router->get($baseUrl . '/domains/{id}/verify', 'Mail\\DomainController@verify');
$router->get($baseUrl . '/domains/{id}/dns', 'Mail\\DomainController@dnsRecords');
$router->post($baseUrl . '/domains/{id}/check-verification', 'Mail\\DomainController@checkVerification');
$router->get($baseUrl . '/domains/{id}/settings', 'Mail\\DomainController@settings');
$router->post($baseUrl . '/domains/{id}/settings', 'Mail\\DomainController@updateSettings');

// Mailbox Accounts Management (User)
$router->get($baseUrl . '/accounts', 'Mail\\AccountController@index');
$router->get($baseUrl . '/accounts/create', 'Mail\\AccountController@create');
$router->post($baseUrl . '/accounts/create', 'Mail\\AccountController@store');
$router->get($baseUrl . '/accounts/{id}/edit', 'Mail\\AccountController@edit');
$router->post($baseUrl . '/accounts/{id}/edit', 'Mail\\AccountController@update');
$router->post($baseUrl . '/accounts/{id}/delete', 'Mail\\AccountController@delete');

// Aliases
$router->get($baseUrl . '/aliases', 'Mail\\AliasController@index');
$router->post($baseUrl . '/aliases/create', 'Mail\\AliasController@store');
$router->post($baseUrl . '/aliases/{id}/delete', 'Mail\\AliasController@delete');

// Filters & Auto-responders
$router->get($baseUrl . '/filters', 'Mail\\FilterController@index');
$router->get($baseUrl . '/filters/create', 'Mail\\FilterController@create');
$router->post($baseUrl . '/filters/create', 'Mail\\FilterController@store');
$router->get($baseUrl . '/filters/{id}/edit', 'Mail\\FilterController@edit');
$router->post($baseUrl . '/filters/{id}/edit', 'Mail\\FilterController@update');
$router->post($baseUrl . '/filters/{id}/delete', 'Mail\\FilterController@delete');
$router->post($baseUrl . '/filters/{id}/toggle', 'Mail\\FilterController@toggle');

// Auto-responder
$router->get($baseUrl . '/auto-responder', 'Mail\\AutoResponderController@index');
$router->post($baseUrl . '/auto-responder/save', 'Mail\\AutoResponderController@save');
$router->post($baseUrl . '/auto-responder/toggle', 'Mail\\AutoResponderController@toggle');

// Settings
$router->get($baseUrl . '/settings', 'Mail\\SettingsController@index');
$router->post($baseUrl . '/settings/save', 'Mail\\SettingsController@save');

// Search
$router->get($baseUrl . '/search', 'Mail\\SearchController@index');
$router->post($baseUrl . '/search', 'Mail\\SearchController@search');

// API endpoints for AJAX operations
$router->get($baseUrl . '/api/emails', 'Mail\\ApiController@getEmails');
$router->get($baseUrl . '/api/email/{id}', 'Mail\\ApiController@getEmail');
$router->post($baseUrl . '/api/email/draft', 'Mail\\ApiController@saveDraft');
$router->get($baseUrl . '/api/folders', 'Mail\\ApiController@getFolders');
$router->get($baseUrl . '/api/contacts/search', 'Mail\\ApiController@searchContacts');
$router->post($baseUrl . '/api/attachment/upload', 'Mail\\ApiController@uploadAttachment');
$router->get($baseUrl . '/api/attachment/download/{id}', 'Mail\\ApiController@downloadAttachment');

// Run the router
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
