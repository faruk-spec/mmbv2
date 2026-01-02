<?php
/**
 * ImgTxt Routes
 * 
 * @package MMB\Projects\ImgTxt
 */

use Core\Router;

$router = new Router(false);

// Dashboard
$router->get('/projects/imgtxt', 'Projects\ImgTxt\Controllers\DashboardController@index');
$router->get('/projects/imgtxt/dashboard', 'Projects\ImgTxt\Controllers\DashboardController@index');

// OCR Processing
$router->get('/projects/imgtxt/upload', 'Projects\ImgTxt\Controllers\OCRController@showUpload');
$router->post('/projects/imgtxt/upload', 'Projects\ImgTxt\Controllers\OCRController@upload');
$router->post('/projects/imgtxt/process', 'Projects\ImgTxt\Controllers\OCRController@process');
$router->get('/projects/imgtxt/result/{id}', 'Projects\ImgTxt\Controllers\OCRController@result');
$router->get('/projects/imgtxt/download/{id}', 'Projects\ImgTxt\Controllers\OCRController@download');

// Batch Processing
$router->get('/projects/imgtxt/batch', 'Projects\ImgTxt\Controllers\BatchController@index');
$router->post('/projects/imgtxt/batch/create', 'Projects\ImgTxt\Controllers\BatchController@create');
$router->get('/projects/imgtxt/batch/{id}', 'Projects\ImgTxt\Controllers\BatchController@show');
$router->get('/projects/imgtxt/batch/{id}/download', 'Projects\ImgTxt\Controllers\BatchController@download');

// History
$router->get('/projects/imgtxt/history', 'Projects\ImgTxt\Controllers\HistoryController@index');
$router->delete('/projects/imgtxt/history/{id}', 'Projects\ImgTxt\Controllers\HistoryController@delete');
$router->post('/projects/imgtxt/history/clear', 'Projects\ImgTxt\Controllers\HistoryController@clear');

// Settings
$router->get('/projects/imgtxt/settings', 'Projects\ImgTxt\Controllers\SettingsController@index');
$router->post('/projects/imgtxt/settings', 'Projects\ImgTxt\Controllers\SettingsController@update');
$router->post('/projects/imgtxt/settings/update', 'Projects\ImgTxt\Controllers\SettingsController@update');

// Dispatch the route
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
