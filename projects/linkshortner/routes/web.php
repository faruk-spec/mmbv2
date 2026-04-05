<?php
/**
 * LinkShortner Routes
 *
 * @package MMB\Projects\LinkShortner
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/linkshortner', 'Projects\LinkShortner\Controllers\DashboardController@index');
$router->get('/projects/linkshortner/dashboard', 'Projects\LinkShortner\Controllers\DashboardController@index');

// Links management
$router->get('/projects/linkshortner/links', 'Projects\LinkShortner\Controllers\LinkController@index');
$router->get('/projects/linkshortner/create', 'Projects\LinkShortner\Controllers\LinkController@create');
$router->post('/projects/linkshortner/create', 'Projects\LinkShortner\Controllers\LinkController@store');
$router->get('/projects/linkshortner/links/{id}/edit', 'Projects\LinkShortner\Controllers\LinkController@edit');
$router->post('/projects/linkshortner/links/{id}/update', 'Projects\LinkShortner\Controllers\LinkController@update');
$router->post('/projects/linkshortner/links/{id}/delete', 'Projects\LinkShortner\Controllers\LinkController@delete');
$router->post('/projects/linkshortner/links/{id}/toggle', 'Projects\LinkShortner\Controllers\LinkController@toggle');

// Analytics
$router->get('/projects/linkshortner/analytics', 'Projects\LinkShortner\Controllers\AnalyticsController@index');
$router->get('/projects/linkshortner/analytics/{code}', 'Projects\LinkShortner\Controllers\AnalyticsController@show');

// Settings
$router->get('/projects/linkshortner/settings', 'Projects\LinkShortner\Controllers\SettingsController@index');
$router->post('/projects/linkshortner/settings', 'Projects\LinkShortner\Controllers\SettingsController@update');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
