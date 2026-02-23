<?php
/**
 * QR Code Controller
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\Security;
use Core\Helpers;
use Core\Logger;
use Projects\QR\Models\QRModel;
use Projects\QR\Models\SettingsModel;
use Projects\QR\Services\QRFeatureService;

class QRController
{
    private QRModel $qrModel;
    private SettingsModel $settingsModel;
    private QRFeatureService $featureService;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
        $this->settingsModel = new SettingsModel();
        $this->featureService = new QRFeatureService();
    }
    
    /**
     * Show QR generation form
     */
    public function showForm(): void
    {
        $userId = Auth::id();
        $settings = [];
        
        // Load user settings if logged in
        if ($userId) {
            $settings = $this->settingsModel->get($userId);
        }
        
        // Check for design preset parameter
        $preset = $_GET['preset'] ?? null;
        $presetSettings = $this->getPresetSettings($preset);
        
        // Merge preset with user settings (preset takes precedence)
        if ($presetSettings) {
            $settings = array_merge($settings, $presetSettings);
        }

        // Resolve user features so the view can show/lock UI elements.
        // For guests (not logged in), allow all features (permissive default).
        $userFeatures = $userId
            ? $this->featureService->getFeatures($userId)
            : array_fill_keys(QRFeatureService::ALL_FEATURES, true);
        
        $this->render('generate', [
            'title'        => 'Generate QR Code',
            'user'         => Auth::user(),
            'settings'     => $settings,
            'preset'       => $preset,
            'userFeatures' => $userFeatures,
        ]);
    }
    
    /**
     * Get preset design settings
     */
    private function getPresetSettings(?string $preset): array
    {
        if (!$preset) {
            return [];
        }
        
        $presets = [
            'modern' => [
                'default_foreground_color' => '#667eea',
                'default_background_color' => '#ffffff',
                'default_corner_style' => 'extra-rounded',
                'default_dot_style' => 'rounded',
                'default_marker_border_style' => 'extra-rounded',
                'default_marker_center_style' => 'extra-rounded',
                'default_gradient_enabled' => 0,
            ],
            'vibrant' => [
                'default_foreground_color' => '#f093fb',
                'default_background_color' => '#ffffff',
                'default_corner_style' => 'dot',
                'default_dot_style' => 'classy-rounded',
                'default_marker_border_style' => 'extra-rounded',
                'default_marker_center_style' => 'dot',
                'default_gradient_enabled' => 1,
                'default_gradient_color' => '#f5576c',
            ],
            'professional' => [
                'default_foreground_color' => '#2c3e50',
                'default_background_color' => '#ffffff',
                'default_corner_style' => 'square',
                'default_dot_style' => 'square',
                'default_marker_border_style' => 'square',
                'default_marker_center_style' => 'square',
                'default_gradient_enabled' => 0,
            ],
            'gradient' => [
                'default_foreground_color' => '#4facfe',
                'default_background_color' => '#ffffff',
                'default_corner_style' => 'extra-rounded',
                'default_dot_style' => 'rounded',
                'default_marker_border_style' => 'extra-rounded',
                'default_marker_center_style' => 'extra-rounded',
                'default_gradient_enabled' => 1,
                'default_gradient_color' => '#00f2fe',
            ],
        ];
        
        return $presets[$preset] ?? [];
    }
    
    /**
     * Generate QR code with enhanced features
     */
    public function generate(): void
    {
        // Verify CSRF
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }

        $userId = Auth::id();

        // ── Feature & plan enforcement (logged-in users only) ─────────────
        if ($userId) {
            $isDynamicRequested = isset($_POST['is_dynamic']);
            $passwordRequested  = !empty($_POST['qr_password']);
            $expiryRequested    = !empty($_POST['expires_at']);

            // Check feature locks
            if ($isDynamicRequested && !$this->featureService->can($userId, 'dynamic_qr')) {
                Helpers::flash('error', 'Dynamic QR codes are not available on your current plan.');
                Helpers::redirect('/projects/qr/generate');
                return;
            }
            if ($passwordRequested && !$this->featureService->can($userId, 'password_protection')) {
                Helpers::flash('error', 'Password protection is not available on your current plan.');
                Helpers::redirect('/projects/qr/generate');
                return;
            }
            if ($expiryRequested && !$this->featureService->can($userId, 'expiry_date')) {
                Helpers::flash('error', 'Expiry date is not available on your current plan.');
                Helpers::redirect('/projects/qr/generate');
                return;
            }

            // Check plan limits
            $limits = $this->featureService->getPlanLimits($userId);
            if ($limits) {
                if ($isDynamicRequested) {
                    $maxDynamic = (int) $limits['max_dynamic_qr'];
                    if ($maxDynamic !== -1 && $this->qrModel->countDynamicByUser($userId) >= $maxDynamic) {
                        Helpers::flash('error', "You have reached your plan limit of {$maxDynamic} dynamic QR code(s).");
                        Helpers::redirect('/projects/qr/generate');
                        return;
                    }
                } else {
                    $maxStatic = (int) $limits['max_static_qr'];
                    if ($maxStatic !== -1 && $this->qrModel->countStaticByUser($userId) >= $maxStatic) {
                        Helpers::flash('error', "You have reached your plan limit of {$maxStatic} static QR code(s).");
                        Helpers::redirect('/projects/qr/generate');
                        return;
                    }
                }
            }
        }

        // Get content based on type
        $type = Security::sanitize($_POST['type'] ?? 'text');
        $content = $this->buildContent($type, $_POST);

        // Append UTM parameters for URL type
        if ($type === 'url' && !empty($content)) {
            $utmParams = [];
            foreach (['utm_source','utm_medium','utm_campaign','utm_term','utm_content'] as $k) {
                $v = Security::sanitize($_POST[$k] ?? '');
                if ($v !== '') $utmParams[$k] = $v;
            }
            if ($utmParams) {
                $sep = str_contains($content, '?') ? '&' : '?';
                $content .= $sep . http_build_query($utmParams);
            }
        }
        
        if (empty($content)) {
            Helpers::flash('error', 'Please enter content for the QR code.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Basic settings
        $size = max(100, min(500, (int) ($_POST['size'] ?? 300)));
        $foregroundColor = '#' . ltrim(Security::sanitize($_POST['foreground_color'] ?? '000000'), '#');
        $backgroundColor = '#' . ltrim(Security::sanitize($_POST['background_color'] ?? 'ffffff'), '#');
        $errorCorrection = Security::sanitize($_POST['error_correction'] ?? 'H');
        
        // Gradient settings
        $gradientEnabled = isset($_POST['gradient_enabled']) ? 1 : 0;
        $gradientColor = '#' . ltrim(Security::sanitize($_POST['gradient_color'] ?? '9945ff'), '#');
        $transparentBg = isset($_POST['transparent_bg']) ? 1 : 0;
        
        // Design styles
        $cornerStyle = Security::sanitize($_POST['corner_style'] ?? 'square');
        $dotStyle = Security::sanitize($_POST['dot_style'] ?? 'dots');
        $markerBorderStyle = Security::sanitize($_POST['marker_border_style'] ?? 'square');
        $markerCenterStyle = Security::sanitize($_POST['marker_center_style'] ?? 'square');
        
        // Marker colors
        $customMarkerColor = isset($_POST['custom_marker_color']) ? 1 : 0;
        $markerColor = $customMarkerColor ? '#' . ltrim(Security::sanitize($_POST['marker_color'] ?? '9945ff'), '#') : null;
        
        // Frame options
        $frameStyle = Security::sanitize($_POST['frame_style'] ?? 'none');
        $frameLabel = Security::sanitize($_POST['frame_label'] ?? '');
        $frameFont = Security::sanitize($_POST['frame_font'] ?? '');
        $frameColor = !empty($_POST['frame_color']) ? '#' . ltrim(Security::sanitize($_POST['frame_color']), '#') : null;
        
        // Logo options
        $logoColor = '#' . ltrim(Security::sanitize($_POST['logo_color'] ?? '9945ff'), '#');
        $logoSize = !empty($_POST['logo_size']) ? (float) $_POST['logo_size'] : 0.3;
        $logoRemoveBg = isset($_POST['logo_remove_bg']) ? 1 : 0;

        // QR label/note (for organization)
        $qrLabel = Security::sanitize($_POST['qr_label'] ?? '');

        // Scan limit
        $scanLimitRaw = trim($_POST['scan_limit'] ?? '');
        $scanLimit = ($scanLimitRaw !== '' && is_numeric($scanLimitRaw) && (int)$scanLimitRaw > 0)
            ? (int) $scanLimitRaw : null;
        
        // Advanced features
        $isDynamic  = isset($_POST['is_dynamic']) ? 1 : 0;
        $redirectUrl = $isDynamic ? Security::sanitize($_POST['redirect_url'] ?? '') : null;

        // Password protection — available for all QR types (not just dynamic)
        $rawPassword = $_POST['qr_password'] ?? '';
        if ($rawPassword !== '' && strlen($rawPassword) < 4) {
            Helpers::flash('error', 'QR password must be at least 4 characters long.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        $passwordHash = ($rawPassword !== '') ? password_hash($rawPassword, PASSWORD_DEFAULT) : null;
        $hasPassword  = $passwordHash !== null ? 1 : 0;

        // Expiry date — available for all QR types
        $expiresAtRaw = Security::sanitize($_POST['expires_at'] ?? '');
        $expiresAt    = null;
        if ($expiresAtRaw !== '') {
            $parsedExpiry = strtotime($expiresAtRaw);
            if ($parsedExpiry === false || $parsedExpiry <= time()) {
                Helpers::flash('error', 'Expiry date must be a valid date/time in the future.');
                Helpers::redirect('/projects/qr/generate');
                return;
            }
            $expiresAt = date('Y-m-d H:i:s', $parsedExpiry);
        }
        
        // Validate redirect URL for dynamic QR — must be http/https only
        if ($isDynamic && $redirectUrl !== null && $redirectUrl !== '') {
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL) ||
                !in_array(strtolower(parse_url($redirectUrl, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
                Helpers::flash('error', 'Redirect URL must be a valid http or https URL.');
                Helpers::redirect('/projects/qr/generate');
                return;
            }
        }

        // Validate error correction level
        if (!in_array($errorCorrection, ['L', 'M', 'Q', 'H'], true)) {
            $errorCorrection = 'H';
        }

        // Clamp logo size to a safe range
        $logoSize = max(0.1, min(0.5, $logoSize));

        // Campaign — verify ownership to prevent attaching QR to another user's campaign
        $campaignId = !empty($_POST['campaign_id']) ? (int) $_POST['campaign_id'] : null;
        if (!$campaignId && !empty($_GET['campaign_id'])) {
            $campaignId = (int) $_GET['campaign_id'];
        }
        if ($campaignId && $userId) {
            // Confirm the campaign belongs to this user
            try {
                $campaignCheck = \Core\Database::getInstance()->fetch(
                    "SELECT id FROM qr_campaigns WHERE id = ? AND user_id = ? LIMIT 1",
                    [$campaignId, $userId]
                );
                if (!$campaignCheck) {
                    Logger::error("QR generate: user {$userId} attempted to use campaign {$campaignId} which does not belong to them.");
                    $campaignId = null;
                }
            } catch (\Exception $e) {
                $campaignId = null;
            }
        }
        
        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->handleLogoUpload($_FILES['logo']);
        }
        
        // Store in session for immediate display
        $_SESSION['generated_qr'] = [
            'content'          => $content,
            'type'             => $type,
            'size'             => $size,
            'foreground_color' => $foregroundColor,
            'background_color' => $backgroundColor,
            'error_correction' => $errorCorrection,
            'gradient_enabled' => $gradientEnabled,
            'gradient_color'   => $gradientColor,
            'frame_style'      => $frameStyle,
            'is_dynamic'       => $isDynamic,
            'has_password'     => $hasPassword,
            'expires_at'       => $expiresAt,
            'created_at'       => date('Y-m-d H:i:s'),
        ];
        
        // Save to database
        if ($userId) {
            $qrId = $this->qrModel->save($userId, [
                'content' => $content,
                'type' => $type,
                'size' => $size,
                'foreground_color' => $foregroundColor,
                'background_color' => $backgroundColor,
                'error_correction' => $errorCorrection,
                'gradient_enabled' => $gradientEnabled,
                'gradient_color' => $gradientColor,
                'transparent_bg' => $transparentBg,
                'corner_style' => $cornerStyle,
                'dot_style' => $dotStyle,
                'marker_border_style' => $markerBorderStyle,
                'marker_center_style' => $markerCenterStyle,
                'custom_marker_color' => $customMarkerColor,
                'marker_color' => $markerColor,
                'frame_style' => $frameStyle,
                'frame_label' => $frameLabel,
                'frame_font' => $frameFont,
                'frame_color' => $frameColor,
                'logo_path' => $logoPath,
                'logo_color' => $logoColor,
                'logo_size' => $logoSize,
                'logo_remove_bg' => $logoRemoveBg,
                'is_dynamic' => $isDynamic,
                'redirect_url' => $redirectUrl,
                'password_hash' => $passwordHash,
                'expires_at' => $expiresAt,
                'campaign_id' => $campaignId,
                'note' => $qrLabel ?: null,
                'scan_limit' => $scanLimit,
                'status' => 'active'
            ]);
            
            if ($qrId) {
                // For password/expiry/scan-limit protected non-dynamic QRs, generate an access URL
                $needsAccessUrl = ($hasPassword || $expiresAt || $scanLimit) && !$isDynamic;
                if ($needsAccessUrl) {
                    $shortCode = $this->generateShortCode($qrId);
                    $this->qrModel->updateShortCode($qrId, $shortCode);
                    $accessUrl = APP_URL . '/projects/qr/access/' . $shortCode;
                    $this->qrModel->update($qrId, $userId, ['content' => $accessUrl]);
                    $_SESSION['generated_qr']['content'] = $accessUrl;
                    $_SESSION['generated_qr']['short_code'] = $shortCode;
                    $_SESSION['generated_qr']['access_url'] = $accessUrl;
                }

                // Generate short code for dynamic QR codes
                if ($isDynamic) {
                    $shortCode = $this->generateShortCode($qrId);
                    $this->qrModel->updateShortCode($qrId, $shortCode);
                    
                    // Build access URL
                    $accessUrl = APP_URL . '/projects/qr/access/' . $shortCode;
                    
                    // Update the content field to the access URL for dynamic QRs
                    $this->qrModel->update($qrId, $userId, ['content' => $accessUrl]);
                    // Update session content so the displayed QR has the correct access URL
                    $_SESSION['generated_qr']['content'] = $accessUrl;
                    
                    // Update session with short code URL for display
                    $_SESSION['generated_qr']['short_code'] = $shortCode;
                    $_SESSION['generated_qr']['access_url'] = $accessUrl;
                }

                Logger::activity($userId, 'qr_generated', ['type' => $type, 'qr_id' => $qrId, 'is_dynamic' => $isDynamic, 'campaign_id' => $campaignId, 'label' => $qrLabel]);

                // Send in-app notification to user
                try {
                    \Core\Notification::send($userId, 'qr_generated',
                        'Your QR code (' . $type . ($qrLabel ? " — {$qrLabel}" : '') . ') was created successfully.',
                        ['qr_id' => $qrId, 'type' => $type]
                    );
                } catch (\Exception $e) {
                    // Notification failure must not block QR creation
                }

                $message = 'QR code generated successfully!';
                if ($isDynamic && isset($shortCode)) {
                    $message .= ' Access URL: ' . APP_URL . '/projects/qr/access/' . $shortCode;
                }
                Helpers::flash('success', $message);
            } else {
                Logger::error('Failed to save QR code to database for user ' . $userId);
                Helpers::flash('error', 'Failed to save QR code to database.');
            }
        } else {
            Helpers::flash('success', 'QR code generated successfully!');
        }
        
        Helpers::redirect('/projects/qr/generate');
    }
    
    /**
     * Build QR content based on type
     */
    private function buildContent(string $type, array $data): string
    {
        switch ($type) {
            case 'url':
            case 'text':
                return Security::sanitize($data['content'] ?? '');

            case 'email':
                // Use dedicated email fields from the form
                $to      = Security::sanitize($data['email_to'] ?? ($data['content'] ?? ''));
                $subject = Security::sanitize($data['email_subject'] ?? '');
                $body    = Security::sanitize($data['email_body'] ?? '');
                $params  = [];
                if ($subject !== '') $params[] = 'subject=' . rawurlencode($subject);
                if ($body    !== '') $params[] = 'body='    . rawurlencode($body);
                return 'mailto:' . $to . ($params ? '?' . implode('&', $params) : '');

            case 'phone':
                // Combine country code + number from dedicated fields
                $country = Security::sanitize($data['phone_country'] ?? '');
                $number  = preg_replace('/\D/', '', $data['phone_number'] ?? ($data['content'] ?? ''));
                $full    = $country . $number;
                return 'tel:' . ($full ?: Security::sanitize($data['content'] ?? ''));

            case 'sms':
                // Use dedicated sms fields from the form
                $country = Security::sanitize($data['sms_country'] ?? '');
                $number  = preg_replace('/\D/', '', $data['sms_number'] ?? '');
                $message = Security::sanitize($data['sms_message'] ?? '');
                $phone   = $country . $number;
                if (empty($phone)) {
                    // Fallback: legacy content field format phone:message
                    $parts   = explode(':', $data['content'] ?? '');
                    $phone   = $parts[0] ?? '';
                    $message = $message ?: ($parts[1] ?? '');
                }
                return 'sms:' . $phone . ($message ? '?body=' . urlencode($message) : '');

            case 'whatsapp':
                $country = Security::sanitize($data['whatsapp_country'] ?? '');
                $number  = preg_replace('/\D/', '', $data['whatsapp_phone'] ?? '');
                $phone   = $country . $number;
                $message = $data['whatsapp_message'] ?? '';
                return 'https://wa.me/' . $phone . ($message ? '?text=' . urlencode($message) : '');

            case 'skype':
                $action   = Security::sanitize($data['skype_action'] ?? 'chat');
                $username = Security::sanitize($data['skype_username'] ?? ($data['content'] ?? ''));
                return 'skype:' . $username . '?' . $action;

            case 'zoom':
                $meetingId = preg_replace('/\s+/', '', $data['zoom_meeting_id'] ?? ($data['content'] ?? ''));
                $pwd       = Security::sanitize($data['zoom_password'] ?? '');
                $url       = 'https://zoom.us/j/' . $meetingId;
                return $url . ($pwd ? '?pwd=' . urlencode($pwd) : '');

            case 'wifi':
                $ssid       = Security::sanitize($data['wifi_ssid'] ?? '');
                $password   = Security::sanitize($data['wifi_password'] ?? '');
                $encryption = Security::sanitize($data['wifi_encryption'] ?? 'WPA');
                return "WIFI:T:$encryption;S:$ssid;P:$password;;";

            case 'vcard':
                // Use the split first/last name fields (or combined vcard_name fallback)
                $first  = Security::sanitize($data['vcard_firstname'] ?? '');
                $last   = Security::sanitize($data['vcard_lastname']  ?? '');
                $name   = trim("$first $last") ?: Security::sanitize($data['vcard_name'] ?? '');
                $title  = Security::sanitize($data['vcard_title']    ?? '');
                $phoneH = Security::sanitize($data['vcard_phone_home']   ?? ($data['vcard_phone'] ?? ''));
                $phoneM = Security::sanitize($data['vcard_phone_mobile'] ?? '');
                $phoneO = Security::sanitize($data['vcard_phone_office'] ?? '');
                $email  = Security::sanitize($data['vcard_email']    ?? '');
                $org    = Security::sanitize($data['vcard_company']  ?? ($data['vcard_org'] ?? ''));
                $job    = Security::sanitize($data['vcard_jobtitle'] ?? '');
                $url    = Security::sanitize($data['vcard_website']  ?? '');
                $addr   = Security::sanitize($data['vcard_address']  ?? '');
                $city   = Security::sanitize($data['vcard_city']     ?? '');
                $state  = Security::sanitize($data['vcard_state']    ?? '');
                $zip    = Security::sanitize($data['vcard_postcode'] ?? '');
                $country = Security::sanitize($data['vcard_country'] ?? '');

                $vc  = "BEGIN:VCARD\nVERSION:3.0\n";
                $vc .= "FN:$name\n";
                if ($title)  $vc .= "TITLE:$title\n";
                if ($org)    $vc .= "ORG:$org\n";
                if ($job)    $vc .= "ROLE:$job\n";
                if ($phoneH) $vc .= "TEL;TYPE=HOME:$phoneH\n";
                if ($phoneM) $vc .= "TEL;TYPE=CELL:$phoneM\n";
                if ($phoneO) $vc .= "TEL;TYPE=WORK:$phoneO\n";
                if ($email)  $vc .= "EMAIL:$email\n";
                if ($url)    $vc .= "URL:$url\n";
                if ($addr || $city || $state || $zip || $country) {
                    $vc .= "ADR;TYPE=HOME:;;$addr;$city;$state;$zip;$country\n";
                }
                $vc .= "END:VCARD";
                return $vc;

            case 'location':
                $lat = Security::sanitize($data['location_lat'] ?? '');
                $lng = Security::sanitize($data['location_lng'] ?? '');
                return "geo:$lat,$lng";

            case 'event':
                $evTitle  = Security::sanitize($data['event_title']    ?? '');
                $start    = str_replace(['-', ':', ' '], '', $data['event_start'] ?? '');
                $end      = str_replace(['-', ':', ' '], '', $data['event_end']   ?? '');
                $location = Security::sanitize($data['event_location'] ?? '');
                $notes    = Security::sanitize($data['event_notes']    ?? '');
                $evUrl    = Security::sanitize($data['event_link']     ?? '');
                $ical  = "BEGIN:VEVENT\nSUMMARY:$evTitle\nDTSTART:$start\nDTEND:$end";
                if ($location) $ical .= "\nLOCATION:$location";
                if ($notes)    $ical .= "\nDESCRIPTION:$notes";
                if ($evUrl)    $ical .= "\nURL:$evUrl";
                $ical .= "\nEND:VEVENT";
                return $ical;

            case 'paypal':
                // Dedicated PayPal form fields
                $ppEmail    = Security::sanitize($data['paypal_email']     ?? '');
                $ppType     = Security::sanitize($data['paypal_type']      ?? 'buynow');
                $ppItem     = Security::sanitize($data['paypal_item_name'] ?? '');
                $ppItemId   = Security::sanitize($data['paypal_item_id']   ?? '');
                $ppPrice    = Security::sanitize($data['paypal_price']     ?? '');
                $ppCurrency = Security::sanitize($data['paypal_currency']  ?? 'USD');
                $ppShipping = Security::sanitize($data['paypal_shipping']  ?? '');
                $ppTax      = Security::sanitize($data['paypal_tax']       ?? '');
                if (empty($ppEmail)) return ''; // triggers "Please enter content" flash, consistent with other types
                $params = [
                    'business' => $ppEmail,
                    'cmd'      => '_' . $ppType,
                ];
                if ($ppItem)     $params['item_name']   = $ppItem;
                if ($ppItemId)   $params['item_number']  = $ppItemId;
                if ($ppPrice)    $params['amount']       = $ppPrice;
                if ($ppCurrency) $params['currency_code'] = $ppCurrency;
                if ($ppShipping) $params['shipping']     = $ppShipping;
                if ($ppTax)      $params['tax_rate']     = $ppTax;
                return 'https://www.paypal.com/cgi-bin/webscr?' . http_build_query($params);

            case 'payment':
                // UPI / local payment wallets
                $payType = Security::sanitize($data['payment_type'] ?? 'upi');
                // Form uses payment_upi_id for the UPI address field
                $upiId   = Security::sanitize($data['payment_upi_id']  ?? ($data['payment_address'] ?? ''));
                $amount  = Security::sanitize($data['payment_amount']  ?? '');
                $name    = Security::sanitize($data['payment_name']    ?? '');
                $note    = Security::sanitize($data['payment_note']    ?? '');
                $params  = ['pa' => $upiId];
                if ($name)   $params['pn'] = $name;
                if ($amount) $params['am'] = $amount;
                if ($note)   $params['tn'] = $note;
                return 'upi://pay?' . http_build_query($params);

            case 'social':
                $platform = Security::sanitize($data['social_platform'] ?? 'custom');
                $handle   = trim(Security::sanitize($data['social_handle'] ?? ''));
                if (empty($handle)) return '';
                // If user entered a full URL, keep it; otherwise prepend base URL
                if (filter_var($handle, FILTER_VALIDATE_URL)) return $handle;
                $bases = [
                    'facebook'  => 'https://facebook.com/',
                    'instagram' => 'https://instagram.com/',
                    'twitter'   => 'https://twitter.com/',
                    'linkedin'  => 'https://linkedin.com/in/',
                    'youtube'   => 'https://youtube.com/@',
                    'tiktok'    => 'https://tiktok.com/@',
                    'snapchat'  => 'https://snapchat.com/add/',
                    'discord'   => 'https://discord.gg/',
                    'telegram'  => 'https://t.me/',
                    'github'    => 'https://github.com/',
                ];
                $base = $bases[$platform] ?? '';
                return $base . ltrim($handle, '@/');

            case 'app_store':
                $platform   = Security::sanitize($data['app_platform'] ?? 'both');
                $iosUrl     = Security::sanitize($data['app_ios_url'] ?? '');
                $androidUrl = Security::sanitize($data['app_android_url'] ?? '');
                if ($platform === 'ios')     return $iosUrl;
                if ($platform === 'android') return $androidUrl;
                // For "both" — return iOS URL (the dynamic redirect handles detection)
                return $iosUrl ?: $androidUrl;

            case 'crypto':
                $coin    = Security::sanitize($data['crypto_coin'] ?? 'bitcoin');
                $address = Security::sanitize($data['crypto_address'] ?? '');
                $amount  = Security::sanitize($data['crypto_amount'] ?? '');
                $label   = Security::sanitize($data['crypto_label'] ?? '');
                if (empty($address)) return '';
                $schemes = [
                    'bitcoin'  => 'bitcoin',
                    'ethereum' => 'ethereum',
                    'litecoin' => 'litecoin',
                    'dogecoin' => 'dogecoin',
                    'usdt'     => 'tether',
                    'bnb'      => 'bnb',
                ];
                $scheme = $schemes[$coin] ?? $coin;
                $params = [];
                if ($amount !== '') $params[] = 'amount=' . urlencode($amount);
                if ($label  !== '') $params[] = 'label='  . urlencode($label);
                return $scheme . ':' . $address . ($params ? '?' . implode('&', $params) : '');

            case 'menu':
                $url   = Security::sanitize($data['menu_url'] ?? '');
                $table = Security::sanitize($data['menu_table'] ?? '');
                if (empty($url)) return '';
                // Append table identifier as a hash fragment for tracking
                return $url . ($table ? (str_contains($url, '?') ? '&' : '?') . 'table=' . urlencode($table) : '');
                
            default:
                return Security::sanitize($data['content'] ?? '');
        }
        
        return '';
    }
    
    /**
     * Handle logo upload
     */
    private function handleLogoUpload(array $file): ?string
    {
        $maxSize = 2 * 1024 * 1024; // 2 MB

        if ($file['size'] > $maxSize) {
            Helpers::flash('warning', 'Logo file size must be less than 2MB.');
            return null;
        }

        // Use finfo to detect actual MIME type from file content (not client-supplied header).
        $finfo    = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        $mimeToExt = [
            'image/png'  => 'png',
            'image/jpeg' => 'jpg',
        ];

        if (!isset($mimeToExt[$mimeType])) {
            Helpers::flash('warning', 'Logo must be a PNG or JPG image.');
            return null;
        }

        // Extension derived from verified MIME — never from the original filename.
        $extension = $mimeToExt[$mimeType];

        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../../storage/qr_logos/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = uniqid('logo_', true) . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/storage/qr_logos/' . date('Y/m') . '/' . $filename;
        }

        Helpers::flash('warning', 'Failed to upload logo.');
        return null;
    }
    
    /**
     * Generate short code for dynamic QR
     */
    private function generateShortCode(int $qrId): string
    {
        // Generate a short alphanumeric code
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Prepend QR ID to ensure uniqueness
        return $code . $qrId;
    }
    
    /**
     * Show QR code history
     */
    public function history(): void
    {
        $userId = Auth::id();
        $history = [];
        $totalCount = 0;
        $perPage = (int)($_GET['per_page'] ?? 10);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        
        // Validate per_page value
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        if ($userId) {
            // Fetch QR codes from database with pagination
            $history = $this->qrModel->getByUser($userId, $perPage, $offset);
            
            // Get total count for pagination
            $totalCount = $this->qrModel->countByUser($userId);
        }
        
        $totalPages = $totalCount > 0 ? (int)ceil($totalCount / $perPage) : 1;
        
        $this->render('history', [
            'title' => 'QR Code History',
            'user' => Auth::user(),
            'history' => $history,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'offset' => $offset
        ]);
    }
    
    /**
     * Download QR code
     */
    public function download(): void
    {
        $qr = $_SESSION['generated_qr'] ?? null;
        
        if (!$qr) {
            Helpers::flash('error', 'No QR code to download.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Client-side handles download
        Helpers::redirect('/projects/qr/generate');
    }
    
    /**
     * Delete QR code
     */
    public function delete(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        $userId = Auth::id();
        
        if ($id && $userId) {
            if ($this->qrModel->delete($id, $userId)) {
                Logger::activity($userId, 'qr_deleted', ['qr_id' => $id]);
                Helpers::flash('success', 'QR code deleted successfully.');
            } else {
                Helpers::flash('error', 'Failed to delete QR code.');
            }
        } else {
            Helpers::flash('error', 'Invalid request.');
        }
        
        Helpers::redirect('/projects/qr/history');
    }
    
    /**
     * Bulk delete QR codes
     */
    public function bulkDelete(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $ids = $_POST['qr_ids'] ?? [];
        $userId = Auth::id();
        
        if (empty($ids) || !is_array($ids)) {
            Helpers::flash('error', 'No QR codes selected.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->qrModel->delete((int)$id, $userId)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            Logger::activity($userId, 'qr_bulk_deleted', ['count' => $deleted, 'ids' => array_map('intval', $ids)]);
            Helpers::flash('success', "$deleted QR code(s) deleted successfully.");
        } else {
            Helpers::flash('error', 'Failed to delete QR codes.');
        }
        
        Helpers::redirect('/projects/qr/history');
    }
    
    /**
     * Bulk print QR codes
     */
    public function bulkPrint(): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            header('Location: /login');
            exit;
        }
        
        // Get parameters
        $ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];
        $pageSize = $_GET['pageSize'] ?? 'a4';
        $qrSize = $_GET['qrSize'] ?? 'medium';
        $margins = $_GET['margins'] ?? 'normal';
        $removeBg = isset($_GET['removeBg']) && $_GET['removeBg'] === '1';
        $showLabels = isset($_GET['showLabels']) && $_GET['showLabels'] === '1';
        
        if (empty($ids)) {
            echo "No QR codes selected.";
            return;
        }
        
        // Fetch QR codes
        $qrCodes = [];
        foreach ($ids as $id) {
            $qr = $this->qrModel->getById((int)$id, $userId);
            if ($qr) {
                $qrCodes[] = $qr;
            }
        }
        
        if (empty($qrCodes)) {
            echo "No valid QR codes found.";
            return;
        }
        
        // Render print view
        $this->render('bulk-print', [
            'title' => 'Print QR Codes',
            'qrCodes' => $qrCodes,
            'pageSize' => $pageSize,
            'qrSize' => $qrSize,
            'margins' => $margins,
            'removeBg' => $removeBg,
            'showLabels' => $showLabels
        ]);
    }
    
    /**
     * Update QR code campaign assignment
     */
    public function updateCampaign(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
        
        $qrId = (int)($_POST['qr_id'] ?? 0);
        $campaignId = !empty($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : null;
        $userId = Auth::id();
        
        if (!$qrId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
        
        // Verify QR code belongs to user
        $qr = $this->qrModel->getById($qrId, $userId);
        if (!$qr) {
            echo json_encode(['success' => false, 'message' => 'QR code not found.']);
            return;
        }
        
        // Update campaign
        if ($this->qrModel->update($qrId, $userId, ['campaign_id' => $campaignId])) {
            echo json_encode(['success' => true, 'message' => 'Campaign updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update campaign.']);
        }
    }
    
    /**
     * View QR code details
     */
    public function view(int $id): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to view QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $this->render('view', [
            'title' => 'View QR Code',
            'user' => Auth::user(),
            'qr' => $qr
        ]);
    }
    
    /**
     * Show edit form for dynamic QR code
     */
    public function edit(int $id): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to edit QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $this->render('edit', [
            'title' => 'Edit QR Code',
            'user' => Auth::user(),
            'qr' => $qr
        ]);
    }
    
    /**
     * Update dynamic QR code
     */
    public function update(int $id): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to update QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        // Get existing QR code
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        if (!($qr['is_dynamic'] ?? false)) {
            Helpers::flash('error', 'This QR code is not dynamic and cannot be edited.');
            Helpers::redirect('/projects/qr/view/' . $id);
            return;
        }
        
        // Update data
        $updateData = [
            'redirect_url' => Security::sanitize($_POST['redirect_url'] ?? ''),
            'status' => Security::sanitize($_POST['status'] ?? 'active')
        ];
        
        if ($this->qrModel->update($id, $userId, $updateData)) {
            Logger::activity($userId, 'qr_updated', ['qr_id' => $id]);
            Helpers::flash('success', 'QR code updated successfully!');
        } else {
            Helpers::flash('error', 'Failed to update QR code.');
        }
        
        Helpers::redirect('/projects/qr/view/' . $id);
    }
    
    /**
     * Show access form for dynamic QR codes — enforces expiry, scan limit, and password protection
     */
    public function showAccessForm(string $code): void
    {
        $qr = $this->qrModel->getByShortCode($code);
        
        if (!$qr) {
            http_response_code(404);
            echo "QR code not found";
            return;
        }

        // Block check
        if (!empty($qr['status']) && $qr['status'] === 'blocked') {
            http_response_code(403);
            echo "This QR code has been blocked.";
            return;
        }

        // Expiry check — use DateTime to avoid strtotime() returning false for corrupt data
        if (!empty($qr['expires_at'])) {
            $expiryTs = strtotime($qr['expires_at']);
            if ($expiryTs !== false && $expiryTs < time()) {
                $this->render('expired', ['title' => 'QR Code Expired', 'qr' => $qr]);
                return;
            }
        }

        // Scan-limit check
        $scanLimit = isset($qr['scan_limit']) ? (int) $qr['scan_limit'] : -1;
        if ($scanLimit !== -1 && (int) ($qr['scan_count'] ?? 0) >= $scanLimit) {
            http_response_code(410);
            echo "This QR code has reached its maximum number of scans.";
            return;
        }

        // Password protection check
        if (!empty($qr['password_hash'])) {
            $this->render('access', ['title' => 'Protected QR Code', 'qr' => $qr, 'code' => $code]);
            return;
        }

        // All checks passed — track scan and redirect
        $this->trackAndRedirect($qr);
    }

    /**
     * Verify password submission for a password-protected QR code
     */
    public function verifyAccess(string $code): void
    {
        $qr = $this->qrModel->getByShortCode($code);

        if (!$qr) {
            http_response_code(404);
            echo "QR code not found";
            return;
        }

        // Re-run expiry / block checks even on POST
        if (!empty($qr['status']) && $qr['status'] === 'blocked') {
            http_response_code(403);
            echo "This QR code has been blocked.";
            return;
        }
        if (!empty($qr['expires_at'])) {
            $expiryTs = strtotime($qr['expires_at']);
            if ($expiryTs !== false && $expiryTs < time()) {
                $this->render('expired', ['title' => 'QR Code Expired', 'qr' => $qr]);
                return;
            }
        }

        // If no password required, just redirect
        if (empty($qr['password_hash'])) {
            $this->trackAndRedirect($qr);
            return;
        }

        // Verify submitted password
        $submitted = $_POST['password'] ?? '';
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            $this->render('access', ['title' => 'Protected QR Code', 'qr' => $qr, 'code' => $code, 'error' => 'Invalid request.']);
            return;
        }

        if (password_verify($submitted, $qr['password_hash'])) {
            $this->trackAndRedirect($qr);
        } else {
            $this->render('access', ['title' => 'Protected QR Code', 'qr' => $qr, 'code' => $code, 'error' => 'Incorrect password. Please try again.']);
        }
    }
    
    /**
     * Track scan and redirect to QR code destination
     */
    private function trackAndRedirect(array $qr): void
    {
        // Track the scan
        $this->qrModel->trackScan((int) $qr['id'], [
            'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
        $this->redirectQR($qr);
    }

    /**
     * Redirect QR code to its destination
     */
    /**
     * Return true only for safe http/https URLs (prevents open-redirect to
     * javascript:/data:/ftp: schemes and CRLF header injection).
     */
    private function isSafeUrl(string $url): bool
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }
        $scheme = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
        return in_array($scheme, ['http', 'https'], true);
    }

    private function redirectQR(array $qr): void
    {
        // For dynamic QR codes, use redirect_url
        if ($qr['is_dynamic'] && !empty($qr['redirect_url'])) {
            if ($this->isSafeUrl($qr['redirect_url'])) {
                header('Location: ' . $qr['redirect_url']);
                exit;
            }
            // Unsafe stored URL — render content page instead of redirecting
        }
        
        // For static QR codes, redirect to content directly
        // Handle different content types
        $content = $qr['content'];
        
        // If it's already a safe URL, redirect
        if ($this->isSafeUrl($content)) {
            header('Location: ' . $content);
            exit;
        }
        
        // Otherwise display the content
        $this->render('content', [
            'title' => 'QR Code Content',
            'qr' => $qr,
            'content' => $content
        ]);
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include PROJECT_PATH . '/views/layout.php';
    }
}
