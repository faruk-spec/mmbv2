<?php
/**
 * WhatsApp API Automation - Web Routes
 * 
 * @package MMB\Projects\WhatsApp
 */

use Core\Router;

// Load the controllers
require_once BASE_PATH . '/projects/whatsapp/controllers/DashboardController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/SessionController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/MessageController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/ContactController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/SettingsController.php';
require_once BASE_PATH . '/projects/whatsapp/controllers/ApiDocsController.php';

$router = new Router(false);

// Dashboard routes
$router->get('/projects/whatsapp', 'Projects\WhatsApp\Controllers\DashboardController@index');
$router->get('/projects/whatsapp/dashboard', 'Projects\WhatsApp\Controllers\DashboardController@index');

// Session management routes
$router->get('/projects/whatsapp/sessions', 'Projects\WhatsApp\Controllers\SessionController@index');
$router->post('/projects/whatsapp/sessions/create', 'Projects\WhatsApp\Controllers\SessionController@create');
$router->post('/projects/whatsapp/sessions/disconnect', 'Projects\WhatsApp\Controllers\SessionController@disconnect');
$router->get('/projects/whatsapp/sessions/qr', 'Projects\WhatsApp\Controllers\SessionController@getQRCode');
$router->get('/projects/whatsapp/sessions/status', 'Projects\WhatsApp\Controllers\SessionController@status');

// Message routes
$router->get('/projects/whatsapp/messages', 'Projects\WhatsApp\Controllers\MessageController@index');
$router->post('/projects/whatsapp/messages/send', 'Projects\WhatsApp\Controllers\MessageController@send');
$router->get('/projects/whatsapp/messages/history', 'Projects\WhatsApp\Controllers\MessageController@history');

// Contact routes
$router->get('/projects/whatsapp/contacts', 'Projects\WhatsApp\Controllers\ContactController@index');
$router->get('/projects/whatsapp/contacts/sync', 'Projects\WhatsApp\Controllers\ContactController@sync');

// Settings routes
$router->get('/projects/whatsapp/settings', 'Projects\WhatsApp\Controllers\SettingsController@index');
$router->post('/projects/whatsapp/settings/update', 'Projects\WhatsApp\Controllers\SettingsController@update');

// API documentation
$router->get('/projects/whatsapp/api-docs', 'Projects\WhatsApp\Controllers\ApiDocsController@index');

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
