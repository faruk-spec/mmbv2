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
$router->get('/projects/whatsapp', 'WhatsApp\Controllers\DashboardController@index');
$router->get('/projects/whatsapp/dashboard', 'WhatsApp\Controllers\DashboardController@index');

// Session management routes
$router->get('/projects/whatsapp/sessions', 'WhatsApp\Controllers\SessionController@index');
$router->post('/projects/whatsapp/sessions/create', 'WhatsApp\Controllers\SessionController@create');
$router->post('/projects/whatsapp/sessions/disconnect', 'WhatsApp\Controllers\SessionController@disconnect');
$router->get('/projects/whatsapp/sessions/qr', 'WhatsApp\Controllers\SessionController@getQRCode');
$router->get('/projects/whatsapp/sessions/status', 'WhatsApp\Controllers\SessionController@status');

// Message routes
$router->get('/projects/whatsapp/messages', 'WhatsApp\Controllers\MessageController@index');
$router->post('/projects/whatsapp/messages/send', 'WhatsApp\Controllers\MessageController@send');
$router->get('/projects/whatsapp/messages/history', 'WhatsApp\Controllers\MessageController@history');

// Contact routes
$router->get('/projects/whatsapp/contacts', 'WhatsApp\Controllers\ContactController@index');
$router->get('/projects/whatsapp/contacts/sync', 'WhatsApp\Controllers\ContactController@sync');

// Settings routes
$router->get('/projects/whatsapp/settings', 'WhatsApp\Controllers\SettingsController@index');
$router->post('/projects/whatsapp/settings/update', 'WhatsApp\Controllers\SettingsController@update');

// API documentation
$router->get('/projects/whatsapp/api-docs', 'WhatsApp\Controllers\ApiDocsController@index');

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
