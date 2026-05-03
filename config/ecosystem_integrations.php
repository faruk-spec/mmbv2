<?php

return [
    'apps' => [
        'qr'           => [],
        'linkshortner' => [],
        'proshare'     => [],
        'formx'        => [],
    ],

    'routes' => [
        'qr_generate' => [
            'type' => 'project',
            'app'  => 'qr',
            'path' => '/generate',
        ],
        'linkshortner_create' => [
            'type' => 'project',
            'app'  => 'linkshortner',
            'path' => '/create',
        ],
        'linkshortner_analytics' => [
            'type' => 'project',
            'app'  => 'linkshortner',
            'path' => '/analytics/{code}',
        ],
        'proshare_preview' => [
            'type' => 'project',
            'app'  => 'proshare',
            'path' => '/preview/{short_code}',
        ],
        'proshare_text_public' => [
            'type' => 'public',
            'path' => '/t/{short_code}',
        ],
        'linkshortner_public' => [
            'type' => 'public',
            'path' => '/l/{code}',
        ],
        'formx_public' => [
            'type' => 'public',
            'path' => '/forms/{slug}',
        ],
    ],

    'entities' => [
        'qr_url' => [
            'actions' => [
                [
                    'id'       => 'shorten_url',
                    'type'     => 'link',
                    'route'    => 'linkshortner_create',
                    'query'    => ['url' => 'content_url'],
                    'label'    => 'Shorten URL',
                    'icon'     => 'fa-compress-alt',
                    'title'    => 'Shorten this URL with LinkShortner',
                    'requires' => ['content_url'],
                    'validate' => 'url',
                    'class'    => 'btn btn-secondary btn-sm icon-only-btn',
                    'style'    => 'padding: 0.5rem 0.75rem; text-decoration: none; color:#00f0ff;',
                ],
            ],
        ],

        'linkshortner_link' => [
            'actions' => [
                [
                    'id'       => 'generate_qr',
                    'type'     => 'qr_modal',
                    'url_from' => 'public_url',
                    'label'    => 'Generate QR',
                    'icon'     => 'fa-qrcode',
                    'title'    => 'Generate QR',
                    'requires' => ['public_url'],
                    'class'    => 'btn btn-secondary btn-sm',
                ],
            ],
        ],

        'proshare_file' => [
            'actions' => [
                [
                    'id'       => 'generate_qr',
                    'type'     => 'qr_modal',
                    'url_from' => 'public_url',
                    'label'    => 'Generate QR',
                    'icon'     => 'fa-qrcode',
                    'title'    => 'Generate QR',
                    'requires' => ['public_url'],
                    'class'    => 'btn btn-secondary',
                    'style'    => 'padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;',
                ],
                [
                    'id'       => 'shorten_url',
                    'type'     => 'link',
                    'route'    => 'linkshortner_create',
                    'query'    => ['url' => 'public_url'],
                    'label'    => 'Shorten URL',
                    'icon'     => 'fa-compress-alt',
                    'title'    => 'Shorten with LinkShortner',
                    'requires' => ['public_url'],
                    'validate' => 'url',
                    'class'    => 'btn btn-secondary',
                    'style'    => 'padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;',
                ],
            ],
        ],

        'proshare_text' => [
            'actions' => [
                [
                    'id'       => 'generate_qr',
                    'type'     => 'qr_modal',
                    'url_from' => 'public_url',
                    'label'    => 'Generate QR',
                    'icon'     => 'fa-qrcode',
                    'title'    => 'Generate QR',
                    'requires' => ['public_url'],
                    'class'    => 'btn btn-secondary',
                    'style'    => 'padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;',
                ],
                [
                    'id'       => 'shorten_url',
                    'type'     => 'link',
                    'route'    => 'linkshortner_create',
                    'query'    => ['url' => 'public_url'],
                    'label'    => 'Shorten URL',
                    'icon'     => 'fa-compress-alt',
                    'title'    => 'Shorten with LinkShortner',
                    'requires' => ['public_url'],
                    'validate' => 'url',
                    'class'    => 'btn btn-secondary',
                    'style'    => 'padding: 6px 10px; font-size: 0.8rem; color:#00f0ff;',
                ],
            ],
        ],

        'formx_form' => [
            'actions' => [
                [
                    'id'       => 'generate_qr',
                    'type'     => 'qr_modal',
                    'url_from' => 'public_url',
                    'label'    => 'Generate QR',
                    'icon'     => 'fa-qrcode',
                    'title'    => 'Generate QR for form link',
                    'requires' => ['public_url'],
                    'class'    => 'fx-action-btn',
                    'style'    => 'background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.2);color:#00f0ff;',
                ],
                [
                    'id'       => 'shorten_url',
                    'type'     => 'link',
                    'route'    => 'linkshortner_create',
                    'query'    => ['url' => 'public_url'],
                    'label'    => 'Shorten URL',
                    'icon'     => 'fa-compress-alt',
                    'title'    => 'Shorten form link',
                    'requires' => ['public_url'],
                    'validate' => 'url',
                    'class'    => 'fx-action-btn',
                    'style'    => 'background:rgba(0,240,255,.08);border-color:rgba(0,240,255,.2);color:#00f0ff;',
                ],
            ],
        ],
    ],
];
