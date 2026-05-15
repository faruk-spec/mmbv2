<?php
/**
 * Projects Configuration
 * 
 * @package MMB\Config
 */

return [
    'codexpro' => [
        'name' => 'CodeXPro',
        'description' => 'Advanced code editor and IDE platform',
        'icon' => 'code',
        'color' => '#00f0ff',
        'enabled' => true,
        'database' => 'mmb_codexpro',
        'url' => '/projects/codexpro',
        'tier' => 'enterprise',
    ],
    
    'devzone' => [
        'name'        => 'DevZone',
        'description' => 'Developer collaboration and project management',
        'icon'        => 'terminal',
        'color'       => '#ff2ec4',
        'enabled'     => true,
        'url'         => '/projects/devzone',
        'tier'        => 'enterprise',
    ],
    
    'proshare' => [
        'name' => 'ProShare',
        'description' => 'Secure file sharing platform',
        'icon' => 'share-2',
        'color' => '#ffaa00',
        'enabled' => true,
        'database' => 'mmb_proshare',
        'url' => '/projects/proshare',
        'tier' => 'freemium',
    ],
    
    'qr' => [
        'name' => 'QR Generator',
        'description' => 'QR code generation and management',
        'icon' => 'grid',
        'color' => '#9945ff',
        'enabled' => true,
        'database' => 'mmb_qr',
        'url' => '/projects/qr',
        'tier' => 'free',
    ],
    
    'resumex' => [
        'name' => 'ResumeX',
        'description' => 'Professional resume builder',
        'icon' => 'file-text',
        'color' => '#ff6b6b',
        'enabled' => true,
        'database' => 'mmb_resumex',
        'url' => '/projects/resumex',
        'tier' => 'free',
    ],
    
    'whatsapp' => [
        'name' => 'WhatsApp API',
        'description' => 'WhatsApp API automation and messaging platform',
        'icon' => 'message-circle',
        'color' => '#25D366',
        'enabled' => true,
        'database' => 'mmb_whatsapp',
        'url' => '/projects/whatsapp',
        'tier' => 'enterprise',
    ],
    'convertx' => [
        'name' => 'ConvertX',
        'description' => 'AI-powered file conversion and document processing platform',
        'icon' => 'file-export',
        'color' => '#6366f1',
        'enabled' => true,
        'database' => 'mmb_convertx',
        'url' => '/projects/convertx',
        'tier' => 'freemium',
    ],
    'billx' => [
        'name' => 'BillX',
        'description' => 'On-the-go bill & receipt generator with live preview',
        'icon' => 'file-text',
        'color' => '#f59e0b',
        'enabled' => true,
        'database' => 'mmb_billx',
        'url' => '/projects/billx',
        'tier' => 'freemium',
    ],
    'idcard' => [
        'name' => 'CardX',
        'description' => 'AI-powered professional ID card generator with templates',
        'icon' => 'id-card',
        'color' => '#6366f1',
        'enabled' => true,
        'database' => 'mmb_idcard',
        'url' => '/projects/idcard',
        'tier' => 'freemium',
    ],
    'formx' => [
        'name' => 'FormX',
        'description' => 'Drag-and-drop form builder with analytics and submission management',
        'icon' => 'wpforms',
        'color' => '#00f0ff',
        'enabled' => true,
        'database' => 'mmb_formx',
        'url' => '/projects/formx',
        'tier' => 'freemium',
    ],

    'linkshortner' => [
        'name' => 'LinkShortner',
        'description' => 'URL shortener with click analytics, QR codes, and branded links',
        'icon' => 'link',
        'color' => '#00d4ff',
        'enabled' => true,
        'database' => 'mmb_linkshortner',
        'url' => '/projects/linkshortner',
        'tier' => 'free',
    ],

    'notex' => [
        'name' => 'NoteX',
        'description' => 'Private cloud notes with rich text editor, folders, tags and secure sharing',
        'icon' => 'sticky-note',
        'color' => '#ffd700',
        'enabled' => true,
        'database' => 'mmb_notex',
        'url' => '/projects/notex',
        'tier' => 'free',
    ],
];
