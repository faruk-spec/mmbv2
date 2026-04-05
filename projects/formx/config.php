<?php
/**
 * FormX Project Configuration
 *
 * @package MMB\Projects\FormX
 */

return [
    'name'        => 'FormX',
    'version'     => '1.0.0',
    'description' => 'Drag-and-drop form builder',

    'features' => [
        'max_forms'       => 100,
        'max_submissions' => 10000,
        'csv_export'      => true,
        'email_notify'    => true,
    ],
];
