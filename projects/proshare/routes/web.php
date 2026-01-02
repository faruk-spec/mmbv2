<?php
/**
 * ProShare Routes
 * 
 * @package MMB\Projects\ProShare
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/proshare', 'Projects\ProShare\Controllers\DashboardController@index');
$router->get('/projects/proshare/dashboard', 'Projects\ProShare\Controllers\DashboardController@index');

// File Upload & Management (using new enhanced controller)
$router->get('/projects/proshare/upload', 'Projects\ProShare\Controllers\UploadController@index');
$router->post('/projects/proshare/upload', 'Projects\ProShare\Controllers\UploadController@upload');
$router->get('/projects/proshare/files', 'Projects\ProShare\Controllers\DashboardController@myFiles');
$router->delete('/projects/proshare/files/delete/{shortcode}', 'Projects\ProShare\Controllers\FileController@delete');
$router->post('/projects/proshare/files/delete/{shortcode}', 'Projects\ProShare\Controllers\FileController@delete');

// File Download
$router->get('/projects/proshare/download/{shortcode}', 'Projects\ProShare\Controllers\DownloadController@download');
$router->get('/projects/proshare/preview/{shortcode}', 'Projects\ProShare\Controllers\DownloadController@preview');
$router->post('/projects/proshare/verify-password', 'Projects\ProShare\Controllers\DownloadController@verifyPassword');

// Anonymous download (short URL)
$router->get('/s/{shortcode}', 'Projects\ProShare\Controllers\DownloadController@download');

// Text Sharing
$router->get('/projects/proshare/text', 'Projects\ProShare\Controllers\TextShareController@index');
$router->post('/projects/proshare/text/create', 'Projects\ProShare\Controllers\TextShareController@create');
$router->get('/projects/proshare/text/{shortcode}', 'Projects\ProShare\Controllers\TextShareController@view');
$router->post('/projects/proshare/text/verify-password', 'Projects\ProShare\Controllers\TextShareController@verifyPassword');

// Anonymous text view (short URL)
$router->get('/t/{shortcode}', 'Projects\ProShare\Controllers\TextShareController@view');

// Settings
$router->get('/projects/proshare/settings', 'Projects\ProShare\Controllers\SettingsController@index');
$router->post('/projects/proshare/settings', 'Projects\ProShare\Controllers\SettingsController@update');

// Notifications
$router->get('/projects/proshare/notifications', 'Projects\ProShare\Controllers\NotificationController@index');
$router->post('/projects/proshare/notifications/mark-read', 'Projects\ProShare\Controllers\NotificationController@markRead');

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
