<?php
/**
 * NoteX Routes
 *
 * @package MMB\Projects\NoteX
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/notex', 'Projects\NoteX\Controllers\DashboardController@index');
$router->get('/projects/notex/dashboard', 'Projects\NoteX\Controllers\DashboardController@index');

// Notes
$router->get('/projects/notex/notes', 'Projects\NoteX\Controllers\NoteController@index');
$router->get('/projects/notex/create', 'Projects\NoteX\Controllers\NoteController@create');
$router->post('/projects/notex/create', 'Projects\NoteX\Controllers\NoteController@store');
$router->get('/projects/notex/notes/{id}', 'Projects\NoteX\Controllers\NoteController@show');
$router->get('/projects/notex/notes/{id}/edit', 'Projects\NoteX\Controllers\NoteController@edit');
$router->post('/projects/notex/notes/{id}/update', 'Projects\NoteX\Controllers\NoteController@update');
$router->post('/projects/notex/notes/{id}/delete', 'Projects\NoteX\Controllers\NoteController@delete');
$router->post('/projects/notex/notes/{id}/pin', 'Projects\NoteX\Controllers\NoteController@togglePin');
$router->post('/projects/notex/notes/{id}/archive', 'Projects\NoteX\Controllers\NoteController@toggleArchive');
$router->post('/projects/notex/notes/{id}/share', 'Projects\NoteX\Controllers\NoteController@share');

// Folders
$router->get('/projects/notex/folders', 'Projects\NoteX\Controllers\FolderController@index');
$router->post('/projects/notex/folders/create', 'Projects\NoteX\Controllers\FolderController@create');
$router->post('/projects/notex/folders/{id}/rename', 'Projects\NoteX\Controllers\FolderController@rename');
$router->post('/projects/notex/folders/{id}/delete', 'Projects\NoteX\Controllers\FolderController@delete');

// Settings
$router->get('/projects/notex/settings', 'Projects\NoteX\Controllers\SettingsController@index');
$router->post('/projects/notex/settings', 'Projects\NoteX\Controllers\SettingsController@update');

// Shared note (public access via token)
$router->get('/projects/notex/shared/{token}', 'Projects\NoteX\Controllers\NoteController@viewShared');

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
