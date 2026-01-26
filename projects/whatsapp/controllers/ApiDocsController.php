<?php
/**
 * WhatsApp API Documentation Controller
 * 
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\View;

class ApiDocsController
{
    private $user;
    
    public function __construct()
    {
        $this->user = Auth::user();
    }
    
    /**
     * Display API documentation
     */
    public function index()
    {
        $apiEndpoints = $this->getApiEndpoints();
        
        View::render('whatsapp/api-docs', [
            'user' => $this->user,
            'endpoints' => $apiEndpoints,
            'pageTitle' => 'WhatsApp API Documentation'
        ]);
    }
    
    /**
     * Get API endpoints documentation
     */
    private function getApiEndpoints()
    {
        return [
            [
                'name' => 'Send Message',
                'method' => 'POST',
                'endpoint' => '/api/whatsapp/send-message',
                'description' => 'Send a text message to a WhatsApp number',
                'parameters' => [
                    ['name' => 'session_id', 'type' => 'integer', 'required' => true, 'description' => 'WhatsApp session ID'],
                    ['name' => 'recipient', 'type' => 'string', 'required' => true, 'description' => 'Recipient phone number (with country code)'],
                    ['name' => 'message', 'type' => 'string', 'required' => true, 'description' => 'Message text to send'],
                ],
                'example' => [
                    'request' => [
                        'session_id' => 1,
                        'recipient' => '+1234567890',
                        'message' => 'Hello from WhatsApp API!'
                    ],
                    'response' => [
                        'success' => true,
                        'message' => 'Message sent successfully',
                        'message_id' => 123
                    ]
                ]
            ],
            [
                'name' => 'Send Media',
                'method' => 'POST',
                'endpoint' => '/api/whatsapp/send-media',
                'description' => 'Send media (image, video, document) to a WhatsApp number',
                'parameters' => [
                    ['name' => 'session_id', 'type' => 'integer', 'required' => true, 'description' => 'WhatsApp session ID'],
                    ['name' => 'recipient', 'type' => 'string', 'required' => true, 'description' => 'Recipient phone number'],
                    ['name' => 'media_url', 'type' => 'string', 'required' => true, 'description' => 'URL of the media file'],
                    ['name' => 'caption', 'type' => 'string', 'required' => false, 'description' => 'Optional caption for the media'],
                ],
            ],
            [
                'name' => 'Get Messages',
                'method' => 'GET',
                'endpoint' => '/api/whatsapp/messages',
                'description' => 'Retrieve message history for a session',
                'parameters' => [
                    ['name' => 'session_id', 'type' => 'integer', 'required' => true, 'description' => 'WhatsApp session ID'],
                    ['name' => 'recipient', 'type' => 'string', 'required' => false, 'description' => 'Filter by recipient phone number'],
                    ['name' => 'limit', 'type' => 'integer', 'required' => false, 'description' => 'Number of messages to return (default: 50)'],
                    ['name' => 'offset', 'type' => 'integer', 'required' => false, 'description' => 'Pagination offset (default: 0)'],
                ],
            ],
            [
                'name' => 'Get Contacts',
                'method' => 'GET',
                'endpoint' => '/api/whatsapp/contacts',
                'description' => 'Get list of contacts from a WhatsApp session',
                'parameters' => [
                    ['name' => 'session_id', 'type' => 'integer', 'required' => true, 'description' => 'WhatsApp session ID'],
                ],
            ],
            [
                'name' => 'Get Session Status',
                'method' => 'GET',
                'endpoint' => '/api/whatsapp/status',
                'description' => 'Check the connection status of a WhatsApp session',
                'parameters' => [
                    ['name' => 'session_id', 'type' => 'integer', 'required' => true, 'description' => 'WhatsApp session ID'],
                ],
            ],
        ];
    }
}
