<?php
/**
 * Web Routes
 * 
 * @package MMB\Routes
 */

use Core\View;
use Core\Auth;
use Core\Helpers;

// Home page
$router->get('/', function() {
    // Don't redirect logged-in users, let them view the home page
    View::render('home');
});

// Authentication routes
$router->get('/login', 'AuthController@showLogin');
$router->post('/login', 'AuthController@login');
$router->get('/register', 'AuthController@showRegister');
$router->post('/register', 'AuthController@register');
$router->get('/logout', 'AuthController@logout');
$router->post('/logout', 'AuthController@logout');

// Password reset
$router->get('/forgot-password', 'AuthController@showForgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'AuthController@showResetPassword');
$router->post('/reset-password', 'AuthController@resetPassword');

// Email verification
$router->get('/verify-email/{token}', 'AuthController@verifyEmail');

// OTP email verification (new registration flow)
$router->get('/verify-otp', 'AuthController@showVerifyOtp');
$router->post('/verify-otp', 'AuthController@verifyOtp');
$router->post('/verify-otp/resend', 'AuthController@resendOtp');

// Google OAuth routes
$router->get('/auth/google', 'GoogleOAuthController@redirectToGoogle');
$router->get('/auth/google/callback', 'GoogleOAuthController@callback');
$router->get('/auth/google/link', 'GoogleOAuthController@link', ['auth']);
$router->post('/auth/google/unlink', 'GoogleOAuthController@unlink', ['auth']);

// User Dashboard
$router->get('/dashboard', 'DashboardController@index', ['auth']);
$router->get('/profile', 'DashboardController@profile', ['auth']);
$router->post('/profile', 'DashboardController@updateProfile', ['auth']);
$router->get('/security', 'DashboardController@security', ['auth']);
$router->post('/security/set-password', 'DashboardController@setPassword', ['auth']);
$router->post('/security/password', 'DashboardController@updatePassword', ['auth']);
$router->post('/security/revoke-session', 'DashboardController@revokeSession', ['auth']);
$router->post('/security/revoke-sessions-bulk', 'DashboardController@revokeSessionsBulk', ['auth']);
$router->get('/activity', 'DashboardController@activity', ['auth']);
$router->get('/settings', 'DashboardController@settings', ['auth']);
$router->post('/settings', 'DashboardController@updateSettings', ['auth']);

// ── Mail (Webmail inbox) ──────────────────────────────────────────────────────
// Accessible at /mail (and later can point mail.domain.in subdomain here)
// Access is restricted to admin + users with 'mail' permission (enforced in MailController::__construct)
$router->get('/mail', 'MailController@inbox', ['auth']);
$router->get('/mail/compose', 'MailController@compose', ['auth']);
$router->post('/mail/compose', 'MailController@send', ['auth']);
$router->get('/mail/sent', 'MailController@sent', ['auth']);
$router->get('/mail/sent/view/{id}', 'MailController@viewSent', ['auth']);
$router->post('/mail/sent/reply', 'MailController@replySent', ['auth']);
$router->get('/mail/search', 'MailController@search', ['auth']);
$router->get('/mail/settings', 'MailController@settings', ['auth']);
$router->post('/mail/settings', 'MailController@saveSettings', ['auth']);
$router->get('/mail/suggest-recipients', 'MailController@suggestRecipients', ['auth']);
$router->get('/mail/view/{id}', 'MailController@viewMessage', ['auth']);
$router->post('/mail/reply', 'MailController@reply', ['auth']);
$router->post('/mail/forward', 'MailController@forward', ['auth']);
$router->post('/mail/mark-read', 'MailController@markRead', ['auth']);
$router->post('/mail/delete', 'MailController@delete', ['auth']);
$router->post('/mail/archive', 'MailController@archive', ['auth']);
$router->post('/mail/star', 'MailController@star', ['auth']);
$router->post('/mail/sync', 'MailController@sync', ['auth']);

// Plans & Subscriptions
$router->get('/plans', 'PlansController@index', ['auth']);
$router->get('/plans/subscribe/{slug}', 'PlansController@subscribe', ['auth']);
$router->post('/plans/subscribe/{slug}', 'PlansController@processSubscribe', ['auth']);

// Notification API
$router->get('/api/notifications', 'NotificationController@getList', ['auth']);
$router->post('/api/notifications/mark-read', 'NotificationController@markRead', ['auth']);
$router->post('/api/notifications/mark-all-read', 'NotificationController@markAllRead', ['auth']);
$router->get('/notifications', 'NotificationController@viewAll', ['auth']);

// Real-time notification stream (SSE) and WebSocket token
$router->get('/notifications/stream', 'NotificationStreamController@stream', ['auth']);
$router->get('/api/ws/token', 'NotificationController@wsToken', ['auth']);

// Support Tickets (logged-in users)
$router->get('/support', 'SupportController@index', ['auth']);
$router->get('/help', 'SupportController@index', ['auth']); // alias for /help sidebar link
$router->get('/support/create', 'SupportController@createForm', ['auth']);
$router->post('/support/create', 'SupportController@store', ['auth']);
// New wizard (React / API-driven ticket creation)
$router->get('/support/new', function () {
    \Core\View::render('support/create-wizard', [
        'title'          => 'Create Support Ticket',
        'currentPage'    => 'create',
        'isSupportAdmin' => false,
        'user'           => \Core\Auth::user(),
        'isLoggedIn'     => true,
        'csrf_token'     => \Core\Security::generateCsrfToken(),
    ]);
}, ['auth']);
$router->get('/support/view/{id}', 'SupportController@show', ['auth']);
$router->post('/support/view/{id}/reply', 'SupportController@reply', ['auth']);
$router->get('/support/faq', 'SupportController@faq', ['auth']);
$router->get('/support/live', 'SupportController@liveSupport', ['auth']);
$router->get('/support/help', 'SupportController@help', ['auth']);
$router->get('/support/announcements', 'SupportController@announcements', ['auth']);

// Admin portal within support portal
$router->get('/support/admin/tickets', 'SupportController@adminTickets', ['auth']);
$router->get('/support/admin/live', 'SupportController@adminLive', ['auth']);
$router->get('/support/admin/reports', 'SupportController@adminReports', ['auth']);
$router->get('/support/admin/reports/export', 'SupportController@adminExportReportsCsv', ['auth']);
$router->get('/support/admin/ticket/{id}', 'SupportController@adminViewTicket', ['auth']);
$router->post('/support/admin/ticket/{id}/reply', 'SupportController@adminReplyTicket', ['auth']);
$router->post('/support/admin/ticket/{id}/status', 'SupportController@adminUpdateTicketStatus', ['auth']);
$router->post('/support/admin/ticket/{id}/priority', 'SupportController@adminUpdateTicketPriority', ['auth']);
$router->post('/support/admin/ticket/{id}/assign', 'SupportController@adminAssignTicketAgent', ['auth']);

// Live Chat (no auth — works for guests too)
$router->post('/support/live/start', 'SupportLiveChatController@start');
$router->post('/support/live/send', 'SupportLiveChatController@send');
$router->get('/support/live/messages', 'SupportLiveChatController@poll');
$router->post('/support/live/close', 'SupportLiveChatController@close');

// Support API — dynamic ticket wizard
$router->get('/api/support/groups',     'Api\\SupportApiController@getGroups',    ['auth']);
$router->get('/api/support/categories', 'Api\\SupportApiController@getCategories', ['auth']);
$router->get('/api/support/template',   'Api\\SupportApiController@getTemplate',   ['auth']);
$router->post('/api/support/tickets',   'Api\\SupportApiController@submitTicket',  ['auth']);

// Support Admin API — template builder
$router->get('/api/admin/support/groups',                          'Api\\SupportApiController@adminGetGroups',           ['auth', 'admin']);
$router->post('/api/admin/support/groups',                         'Api\\SupportApiController@adminCreateGroup',         ['auth', 'admin']);
$router->put('/api/admin/support/groups/{id}',                     'Api\\SupportApiController@adminUpdateGroup',         ['auth', 'admin']);
$router->delete('/api/admin/support/groups/{id}',                  'Api\\SupportApiController@adminDeleteGroup',         ['auth', 'admin']);
$router->get('/api/admin/support/categories',                      'Api\\SupportApiController@adminGetCategories',       ['auth', 'admin']);
$router->post('/api/admin/support/categories',                     'Api\\SupportApiController@adminCreateCategory',      ['auth', 'admin']);
$router->put('/api/admin/support/categories/{id}',                 'Api\\SupportApiController@adminUpdateCategory',      ['auth', 'admin']);
$router->delete('/api/admin/support/categories/{id}',              'Api\\SupportApiController@adminDeleteCategory',      ['auth', 'admin']);
$router->get('/api/admin/support/template/{id}/history',           'Api\\SupportApiController@adminGetTemplateHistory',  ['auth', 'admin']);
$router->get('/api/admin/support/template/version/{id}',           'Api\\SupportApiController@adminGetTemplateVersion',  ['auth', 'admin']);
$router->get('/api/admin/support/template/{id}',                   'Api\\SupportApiController@adminGetTemplate',         ['auth', 'admin']);
$router->post('/api/admin/support/template/{id}',                  'Api\\SupportApiController@adminSaveTemplate',        ['auth', 'admin']);

// Session alert polling — checked every ~30 s by logged-in browsers
$router->get('/api/session-alerts', function() {
    if (!\Core\Auth::check()) {
        \Core\Helpers::json(['alert' => null]);
        return;
    }
    $userId = \Core\Auth::id();
    try {
        $db = \Core\Database::getInstance();
        $row = $db->fetch(
            "SELECT `value` FROM settings WHERE `key` = ?",
            ['new_login_notify_' . $userId]
        );
        if ($row) {
            $data = json_decode($row['value'], true);
            $sessionCreatedAt = $_SESSION['_login_time'] ?? 0;
            if (!empty($data['time']) && $data['time'] > $sessionCreatedAt + 5) {
                // Consume the notification so it shows only once
                $db->delete('settings', '`key` = ?', ['new_login_notify_' . $userId]);
                \Core\Helpers::json(['alert' => $data]);
                return;
            }
        }
    } catch (\Exception $e) { /* non-fatal */ }
    \Core\Helpers::json(['alert' => null]);
}, ['auth']);

// Serve support attachments (restricted file types)
$router->get('/support-attachments/{file}', function($file) {
    if (!\Core\Auth::check()) {
        http_response_code(403);
        exit('Forbidden');
    }
    $requestedFile = (string) $file;
    $decodedFile = rawurldecode($requestedFile);
    if (preg_match('/[\/\\\\]|\.\./', $decodedFile)) {
        http_response_code(403);
        exit('Forbidden');
    }
    $allowedExtensions = ['pdf', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'txt', 'zip', 'doc', 'docx', 'xlsx', 'csv'];
    $ext = strtolower(pathinfo($requestedFile, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExtensions, true)) {
        http_response_code(403);
        exit('Forbidden');
    }
    $fullPath = BASE_PATH . '/storage/uploads/support_attachments/' . $requestedFile;
    $real = realpath($fullPath);
    $base = realpath(BASE_PATH . '/storage/uploads/support_attachments');
    if (!$real || !$base || strpos($real, $base) !== 0 || !is_file($real)) {
        http_response_code(404);
        exit('File not found');
    }
    $mime = mime_content_type($real) ?: 'application/octet-stream';
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($real));
    header('Content-Disposition: inline; filename="' . basename($real) . '"');
    readfile($real);
    exit;
}, ['auth']);

// 2FA routes
// Two-Factor Authentication routes
$router->get('/2fa/setup', 'TwoFactorController@setup', ['auth']);
$router->post('/2fa/enable', 'TwoFactorController@enable', ['auth']);
$router->post('/2fa/disable', 'TwoFactorController@disable', ['auth']);
$router->get('/2fa/backup-codes', 'TwoFactorController@showBackupCodes', ['auth']);
$router->get('/2fa/verify', 'TwoFactorController@showVerify');
$router->post('/2fa/verify', 'TwoFactorController@verify');

// Serve uploaded files (avatars, etc.) - route since storage/ is blocked by htaccess
$router->get('/uploads/{path:wildcard}', function($path) {
    // Only allow image files in uploads directory
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    
    if (!in_array($ext, $allowedExtensions)) {
        http_response_code(403);
        exit('Forbidden');
    }
    
    // Prevent path traversal
    $realBase = realpath(BASE_PATH . '/storage/uploads');
    $filePath = realpath(BASE_PATH . '/storage/uploads/' . $path);
    
    if (!$filePath || !str_starts_with($filePath, $realBase)) {
        http_response_code(404);
        exit('Not found');
    }
    
    if (!file_exists($filePath)) {
        http_response_code(404);
        exit('Not found');
    }
    
    $mimeTypes = [
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png'  => 'image/png',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
    ];
    
    header('Content-Type: ' . ($mimeTypes[$ext] ?? 'application/octet-stream'));
    // Restrict SVG execution context for security
    if ($ext === 'svg') {
        header('Content-Security-Policy: default-src \'none\'');
        header('X-Content-Type-Options: nosniff');
    }
    header('Content-Length: ' . filesize($filePath));
    header('Cache-Control: public, max-age=31536000');
    readfile($filePath);
    exit;
});

// Public pages (no auth required)
$router->get('/pages/{slug:wildcard}', 'PagesController@show');

$router->get('/projects/{project}', function($project) {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\View::render('errors/project-disabled', ['project' => $project]);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->get('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\View::render('errors/project-disabled', ['project' => $project]);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->post('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->put('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

$router->delete('/projects/{project}/{path:wildcard}', function($project, $path = '') {
    // Check if project is enabled
    if (!\Core\Helpers::isProjectEnabled($project)) {
        \Core\Helpers::json(['error' => 'Project is disabled'], 503);
        return;
    }
    
    $projectFile = BASE_PATH . '/projects/' . $project . '/index.php';
    if (file_exists($projectFile)) {
        require_once $projectFile;
    } else {
        http_response_code(404);
        View::render('errors/404');
    }
}, ['auth']);

// ProShare anonymous short URLs (must be defined here for global access)
$router->get('/s/{shortcode}', 'Projects\ProShare\Controllers\DownloadController@download');
$router->get('/t/{shortcode}', 'Projects\ProShare\Controllers\TextShareController@view');

// LinkShortner – public redirect (no auth required)
$router->get('/l/{code}', 'Projects\LinkShortner\Controllers\RedirectController@redirect');


// ── FormX public form pages ───────────────────────────────────────────────────
$router->get('/forms/{slug}',  'FormXPublicController@show');
$router->post('/forms/{slug}', 'FormXPublicController@submit');

// ── Short URL routes — hide /projects/ prefix from users ──────────────────────
// These mirror the /projects/{project}/... routes but without the /projects/ segment.
$knownProjects = ['qr','proshare','formx','codexpro','convertx','idcard','linkshortner','notex','resumex','billx','whatsapp','devzone'];

foreach ($knownProjects as $_proj) {
    $router->get('/' . $_proj, function() use ($_proj) {
        if (!\Core\Helpers::isProjectEnabled($_proj)) {
            \Core\View::render('errors/project-disabled', ['project' => $_proj]);
            return;
        }
        $f = BASE_PATH . '/projects/' . $_proj . '/index.php';
        if (file_exists($f)) require_once $f;
        else { http_response_code(404); \Core\View::render('errors/404'); }
    }, ['auth']);

    $router->get('/' . $_proj . '/{path:wildcard}', function($path = '') use ($_proj) {
        if (!\Core\Helpers::isProjectEnabled($_proj)) {
            \Core\View::render('errors/project-disabled', ['project' => $_proj]);
            return;
        }
        $f = BASE_PATH . '/projects/' . $_proj . '/index.php';
        if (file_exists($f)) require_once $f;
        else { http_response_code(404); \Core\View::render('errors/404'); }
    }, ['auth']);

    $router->post('/' . $_proj . '/{path:wildcard}', function($path = '') use ($_proj) {
        if (!\Core\Helpers::isProjectEnabled($_proj)) {
            \Core\Helpers::json(['error' => 'Project is disabled'], 503);
            return;
        }
        $f = BASE_PATH . '/projects/' . $_proj . '/index.php';
        if (file_exists($f)) require_once $f;
        else { http_response_code(404); \Core\View::render('errors/404'); }
    }, ['auth']);

    $router->put('/' . $_proj . '/{path:wildcard}', function($path = '') use ($_proj) {
        if (!\Core\Helpers::isProjectEnabled($_proj)) {
            \Core\Helpers::json(['error' => 'Project is disabled'], 503);
            return;
        }
        $f = BASE_PATH . '/projects/' . $_proj . '/index.php';
        if (file_exists($f)) require_once $f;
        else { http_response_code(404); \Core\View::render('errors/404'); }
    }, ['auth']);

    $router->delete('/' . $_proj . '/{path:wildcard}', function($path = '') use ($_proj) {
        if (!\Core\Helpers::isProjectEnabled($_proj)) {
            \Core\Helpers::json(['error' => 'Project is disabled'], 503);
            return;
        }
        $f = BASE_PATH . '/projects/' . $_proj . '/index.php';
        if (file_exists($f)) require_once $f;
        else { http_response_code(404); \Core\View::render('errors/404'); }
    }, ['auth']);
}
unset($_proj);
