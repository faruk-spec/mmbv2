<?php
/**
 * SheetDocs Project Entry Point
 * 
 * @package MMB\Projects\SheetDocs
 */

// This file serves as the entry point for the SheetDocs project
// All routes are handled by the main application router

// Load project configuration
$projectConfig = require __DIR__ . '/config.php';

// Register project routes
require __DIR__ . '/routes/web.php';

// The actual routing and execution is handled by the main application
// This file is included by the main index.php when accessing SheetDocs URLs
