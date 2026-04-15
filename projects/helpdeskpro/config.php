<?php
/**
 * Helpdesk Pro Configuration
 *
 * @package MMB\Projects\HelpdeskPro
 */

return [
    'name' => 'Helpdesk Pro',
    'version' => '1.0.0',
    'description' => 'Ticketing and live support desk with AI + human handoff',
    'features' => [
        'ticketing' => true,
        'live_support' => true,
        'email_notifications' => true,
        'agent_handoff' => true,
    ],
    'ticket_priorities' => ['low', 'medium', 'high', 'urgent'],
    'ticket_statuses' => ['open', 'in_progress', 'waiting_customer', 'resolved', 'closed'],
];
