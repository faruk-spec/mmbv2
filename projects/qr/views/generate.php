<?php
// Production-Ready QR Code Generator with AI Design
// Futuristic UI with theme integration and live preview
?>

<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px; background: linear-gradient(135deg, var(--purple), var(--cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
    <i class="fas fa-qrcode"></i> Generate QR Code
</h1>

<div class="grid grid-2">
    <div class="glass-card">
        <h3 class="section-title">
            <i class="fas fa-sliders-h"></i> Configuration
        </h3>
        
        <form method="POST" action="/projects/qr/generate" id="qrForm" enctype="multipart/form-data">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input type="hidden" name="qr_data_url" id="qrDataUrl">
            
            <!-- QR Type Selection -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-tag"></i> Content Type
                </label>
                <select name="type" class="form-select" id="qrType">
                    <option value="url">🌐 URL / Website</option>
                    <option value="text">📝 Plain Text</option>
                    <option value="email">📧 Email Address</option>
                    <option value="phone">📞 Phone Number</option>
                    <option value="sms">💬 SMS Message</option>
                    <option value="whatsapp">💚 WhatsApp</option>
                    <option value="wifi">📶 WiFi Network</option>
                    <option value="vcard">👤 vCard (Contact)</option>
                    <option value="location">📍 Location</option>
                    <option value="event">📅 Event (Calendar)</option>
                    <option value="payment">💳 Payment</option>
                </select>
            </div>
            
            <!-- Simple Content Field -->
            <div class="form-group" id="simpleContent">
                <label class="form-label" id="contentLabel">Content</label>
                <textarea name="content" id="contentField" class="form-textarea" rows="4" placeholder="Enter content..."></textarea>
            </div>
            
            <!-- WhatsApp Fields -->
            <div id="whatsappFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Phone Number (with country code)</label>
                    <input type="text" name="whatsapp_phone" id="whatsappPhone" class="form-input" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Message (Optional)</label>
                    <textarea name="whatsapp_message" id="whatsappMessage" class="form-textarea" rows="3" placeholder="Pre-filled message..."></textarea>
                </div>
            </div>
            
            <!-- WiFi Fields -->
            <div id="wifiFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Network Name (SSID)</label>
                    <input type="text" name="wifi_ssid" id="wifiSsid" class="form-input" placeholder="MyNetwork">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="text" name="wifi_password" id="wifiPassword" class="form-input" placeholder="Network password">
                </div>
                <div class="form-group">
                    <label class="form-label">Security Type</label>
                    <select name="wifi_encryption" id="wifiEncryption" class="form-select">
                        <option value="WPA">WPA/WPA2</option>
                        <option value="WEP">WEP</option>
                        <option value="">None (Open)</option>
                    </select>
                </div>
            </div>
            
            <!-- vCard Fields -->
            <div id="vcardFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="vcard_name" id="vcardName" class="form-input" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="vcard_phone" id="vcardPhone" class="form-input" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="vcard_email" id="vcardEmail" class="form-input" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Organization (Optional)</label>
                    <input type="text" name="vcard_org" id="vcardOrg" class="form-input" placeholder="Company Name">
                </div>
            </div>
            
            <!-- Location Fields -->
            <div id="locationFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="text" name="location_lat" id="locationLat" class="form-input" placeholder="40.7128">
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="text" name="location_lng" id="locationLng" class="form-input" placeholder="-74.0060">
                </div>
            </div>
            
            <!-- Event Fields -->
            <div id="eventFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="event_title" id="eventTitle" class="form-input" placeholder="Birthday Party">
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date & Time</label>
                    <input type="datetime-local" name="event_start" id="eventStart" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">End Date & Time</label>
                    <input type="datetime-local" name="event_end" id="eventEnd" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Location (Optional)</label>
                    <input type="text" name="event_location" id="eventLocation" class="form-input" placeholder="123 Main St">
                </div>
            </div>
            
            <!-- Payment Fields -->
            <div id="paymentFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_type" id="paymentType" class="form-select">
                        <option value="upi">UPI (India)</option>
                        <option value="paypal">PayPal</option>
                        <option value="bitcoin">Bitcoin</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Address/ID</label>
                    <input type="text" name="payment_address" id="paymentAddress" class="form-input" placeholder="username@upi or email">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Optional)</label>
                    <input type="number" step="0.01" name="payment_amount" id="paymentAmount" class="form-input" placeholder="10.00">
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Design Options -->
            <h4 class="subsection-title">
                <i class="fas fa-palette"></i> Design Options
            </h4>
            
            <div class="grid grid-2" style="gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Size</label>
                    <select name="size" id="qrSize" class="form-select">
                        <option value="150">Small (150x150)</option>
                        <option value="200">Medium (200x200)</option>
                        <option value="300" selected>Large (300x300)</option>
                        <option value="400">Extra Large (400x400)</option>
                        <option value="500">Huge (500x500)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Error Correction</label>
                    <select name="error_correction" id="errorCorrection" class="form-select">
                        <option value="L">Low (7%)</option>
                        <option value="M">Medium (15%)</option>
                        <option value="Q">Quartile (25%)</option>
                        <option value="H" selected>High (30%)</option>
                    </select>
                </div>
            </div>
            
            <!-- Color Customization -->
            <h4 class="subsection-title">
                <i class="fas fa-palette"></i> Colors
            </h4>
            
            <div class="grid grid-2" style="gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Foreground Color</label>
                    <input type="color" name="foreground_color" id="qrColor" value="#000000" class="form-input color-input">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <input type="color" name="background_color" id="qrBgColor" value="#ffffff" class="form-input color-input">
                </div>
            </div>
            
            <!-- Gradient Toggle for Foreground -->
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="gradient_enabled" id="gradientEnabled" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Gradient Foreground</strong>
                        <small>Apply gradient to foreground color</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="gradientColorGroup" style="display: none;">
                <label class="form-label">Gradient End Color</label>
                <input type="color" name="gradient_color" id="gradientColor" value="#9945ff" class="form-input color-input">
            </div>
            
            <!-- Transparent Background Toggle -->
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="transparent_bg" id="transparentBg" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Transparent Background</strong>
                        <small>Make background transparent (for overlays)</small>
                    </span>
                </label>
            </div>
            
            <!-- Background Image Upload -->
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="bg_image_enabled" id="bgImageEnabled" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Background Image</strong>
                        <small>Add custom background image</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="bgImageGroup" style="display: none;">
                <label class="form-label">Upload Background Image</label>
                <input type="file" name="bg_image" id="bgImage" class="form-input" accept="image/*">
                <small>Recommended: Square image, transparent PNG works best</small>
            </div>
            
            <div class="divider"></div>
            
            <!-- Design Customization -->
            <h4 class="subsection-title">
                <i class="fas fa-shapes"></i> Design
            </h4>
            
            <!-- QR Customization Options -->
            <div class="grid grid-2" style="gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-vector-square"></i> Corner Style (Markers)
                    </label>
                    <select name="corner_style" id="cornerStyle" class="form-select">
                        <option value="square">Square Corners</option>
                        <option value="extra-rounded">Extra Rounded</option>
                        <option value="dot">Dot Corners</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-th"></i> Dot Pattern
                    </label>
                    <select name="dot_style" id="dotStyle" class="form-select">
                        <option value="square">Square Dots</option>
                        <option value="rounded">Rounded Dots</option>
                        <option value="dots">Circle Dots</option>
                        <option value="classy">Classy Style</option>
                        <option value="classy-rounded">Classy Rounded</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-2" style="gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Marker Border Pattern</label>
                    <select name="marker_border_style" id="markerBorderStyle" class="form-select">
                        <option value="square">Square</option>
                        <option value="rounded">Rounded</option>
                        <option value="dot">Dot</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Marker Center Pattern</label>
                    <select name="marker_center_style" id="markerCenterStyle" class="form-select">
                        <option value="square">Square</option>
                        <option value="dot">Dot</option>
                    </select>
                </div>
            </div>
            
            <!-- Custom Marker Colors Toggle -->
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="custom_marker_color" id="customMarkerColor" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Custom Marker Color</strong>
                        <small>Use different color for corner markers</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="markerColorGroup" style="display: none;">
                <label class="form-label">Marker Color</label>
                <input type="color" name="marker_color" id="markerColor" value="#9945ff" class="form-input color-input">
            </div>
            
            <!-- Different Marker Colors Toggle -->
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="different_markers" id="differentMarkers" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Different Marker Colors</strong>
                        <small>Use unique color for each corner marker (limited library support)</small>
                    </span>
                </label>
            </div>
            
            <div id="differentMarkerColorsGroup" style="display: none;">
                <small style="color: var(--text-secondary); margin-bottom: 10px; display: block;">
                    <i class="fas fa-info-circle"></i> Note: The QR library has limited support for per-marker colors. Top-left color will be used as primary.
                </small>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Top Left (Primary)</label>
                        <input type="color" name="marker_tl_color" id="markerTLColor" value="#9945ff" class="form-input color-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Top Right</label>
                        <input type="color" name="marker_tr_color" id="markerTRColor" value="#00f0ff" class="form-input color-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bottom Left</label>
                        <input type="color" name="marker_bl_color" id="markerBLColor" value="#ff2ec4" class="form-input color-input">
                    </div>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Logo Options -->
            <h4 class="subsection-title">
                <i class="fas fa-image"></i> Logo
            </h4>
            
            <div class="form-group">
                <label class="form-label">Logo Options</label>
                <select name="logo_option" id="logoOption" class="form-select">
                    <option value="none">No Logo</option>
                    <option value="default">Default Logo</option>
                    <option value="upload">Upload Your Logo</option>
                </select>
            </div>
            
            <div class="form-group" id="defaultLogoGroup" style="display: none;">
                <label class="form-label">Select Default Logo</label>
                <select name="default_logo" id="defaultLogo" class="form-select">
                    <option value="qr">QR Code Icon</option>
                    <option value="star">Star</option>
                    <option value="heart">Heart</option>
                    <option value="check">Check Mark</option>
                </select>
            </div>
            
            <div class="form-group" id="uploadLogoGroup" style="display: none;">
                <label class="form-label">Upload Your Logo</label>
                <input type="file" name="logo" id="logoUpload" class="form-input" accept="image/*">
                <small>PNG or JPG, max 2MB. Square images work best.</small>
            </div>
            
            <div id="logoOptionsGroup" style="display: none;">
                <!-- Remove Background Toggle -->
                <div class="feature-toggle">
                    <label class="toggle-label">
                        <input type="checkbox" name="logo_remove_bg" id="logoRemoveBg" value="1" class="toggle-input">
                        <span class="toggle-slider"></span>
                        <span class="toggle-text">
                            <strong>Remove Background Behind Logo</strong>
                            <small>Clear area behind logo for better visibility</small>
                        </span>
                    </label>
                </div>
                
                <!-- Logo Size Slider -->
                <div class="form-group">
                    <label class="form-label">Logo Size: <span id="logoSizeValue">0.3</span></label>
                    <input type="range" name="logo_size" id="logoSize" min="0.1" max="0.5" step="0.05" value="0.3" class="form-input" style="padding: 8px;">
                    <small>Adjust the size of logo in QR code (0.1 to 0.5)</small>
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Frame Options -->
            <h4 class="subsection-title">
                <i class="fas fa-border-all"></i> Frame
            </h4>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-border-style"></i> Frame Style
                </label>
                <select name="frame_style" id="frameStyle" class="form-select">
                    <option value="none">No Frame</option>
                    <option value="square">Square Frame</option>
                    <option value="circle">Circle Frame</option>
                    <option value="rounded">Rounded Corners</option>
                    <option value="banner-top">Banner Top</option>
                    <option value="banner-bottom">Banner Bottom</option>
                    <option value="bubble">Speech Bubble</option>
                    <option value="badge">Badge Style</option>
                </select>
                <small style="color: var(--text-secondary);">Add a decorative frame around your QR code</small>
            </div>
            
            <div class="form-group" id="frameTextGroup" style="display: none;">
                <label class="form-label">Frame Label</label>
                <input type="text" name="frame_label" id="frameLabel" class="form-input" placeholder="SCAN ME" maxlength="20">
                <small>Text to display on the frame (max 20 characters)</small>
            </div>
            
            <div class="form-group" id="frameFontGroup" style="display: none;">
                <label class="form-label">Label Font</label>
                <select name="frame_font" id="frameFont" class="form-select">
                    <option value="Arial, sans-serif">Arial</option>
                    <option value="'Courier New', monospace">Courier</option>
                    <option value="'Times New Roman', serif">Times New Roman</option>
                    <option value="Verdana, sans-serif">Verdana</option>
                    <option value="Georgia, serif">Georgia</option>
                    <option value="'Comic Sans MS', cursive">Comic Sans</option>
                </select>
            </div>
            
            <div class="form-group" id="frameColorGroup" style="display: none;">
                <label class="form-label">Custom Frame Color</label>
                <input type="color" name="frame_color" id="frameColor" value="#9945ff" class="form-input color-input">
            </div>
            
            <div class="divider"></div>
            
            <!-- Advanced Features -->
            <h4 class="subsection-title">
                <i class="fas fa-rocket"></i> Advanced Features
            </h4>
            
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="is_dynamic" id="isDynamic" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Dynamic QR Code</strong>
                        <small>Change URL later without regenerating</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="redirectUrlGroup" style="display: none;">
                <label class="form-label">Redirect URL</label>
                <input type="url" name="redirect_url" id="redirectUrl" class="form-input" placeholder="https://example.com">
                <small>This URL can be edited later</small>
            </div>
            
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="has_password" id="hasPassword" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Password Protection</strong>
                        <small>Require password to scan</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="passwordGroup" style="display: none;">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="qrPassword" class="form-input" placeholder="Enter password">
            </div>
            
            <div class="feature-toggle">
                <label class="toggle-label">
                    <input type="checkbox" name="has_expiry" id="hasExpiry" value="1" class="toggle-input">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">
                        <strong>Set Expiry Date</strong>
                        <small>QR code stops working after this date</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="expiryGroup" style="display: none;">
                <label class="form-label">Expires On</label>
                <input type="datetime-local" name="expires_at" id="expiresAt" class="form-input">
            </div>
            
            <div class="divider"></div>
            
            <!-- Action Button -->
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-save"></i> Generate & Save QR Code
                <span class="btn-shine"></span>
            </button>
        </form>
    </div>
    
    <!-- Preview Panel -->
    <div class="glass-card preview-panel">
        <h3 class="section-title">
            <i class="fas fa-eye"></i> Live Preview
        </h3>
        
        <div id="qrPreviewContainer" class="preview-container">
            <?php if (isset($_SESSION['generated_qr'])): ?>
                <div class="qr-preview">
                    <div id="qrcode"></div>
                    <script>
                        // Regenerate QR from session using QRCodeStyling
                        (function tryGenerateQR() {
                            if (typeof QRCodeStyling !== 'undefined') {
                                try {
                                    const qrDiv = document.getElementById('qrcode');
                                    const sessionQR = new QRCodeStyling({
                                        width: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        height: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        data: <?= json_encode($_SESSION['generated_qr']['content']) ?>,
                                        dotsOptions: {
                                            color: <?= json_encode($_SESSION['generated_qr']['foreground_color'] ?? '#000000') ?>,
                                            type: "square"
                                        },
                                        backgroundOptions: {
                                            color: <?= json_encode($_SESSION['generated_qr']['background_color'] ?? '#ffffff') ?>
                                        },
                                        qrOptions: {
                                            errorCorrectionLevel: "H"
                                        }
                                    });
                                    
                                    sessionQR.append(qrDiv);
                                    
                                    // Add download button after generation
                                    setTimeout(function() {
                                        addDownloadButton(sessionQR);
                                    }, 300);
                                } catch (error) {
                                    console.error('Error generating QR from session:', error);
                                }
                            } else {
                                setTimeout(tryGenerateQR, 100);
                            }
                        })();
                    </script>
                    
                    <div class="qr-info">
                        <p><strong>Type:</strong> <?= htmlspecialchars($_SESSION['generated_qr']['type'] ?? 'url') ?></p>
                        <p><strong>Size:</strong> <?= htmlspecialchars($_SESSION['generated_qr']['size'] ?? 300) ?>px</p>
                        <?php if (isset($_SESSION['generated_qr']['is_dynamic']) && $_SESSION['generated_qr']['is_dynamic']): ?>
                            <p><span class="badge badge-dynamic">🔄 Dynamic</span></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['has_password']) && $_SESSION['generated_qr']['has_password']): ?>
                            <p><span class="badge badge-secure">🔒 Protected</span></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php unset($_SESSION['generated_qr']); ?>
            <?php else: ?>
                <div id="emptyState" class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <p class="empty-title">Preview will appear here</p>
                    <p class="empty-subtitle">QR code updates automatically as you type</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QR Code Styling Library (Better than QRCode.js) -->
<script src="https://unpkg.com/qr-code-styling@1.6.0-rc.1/lib/qr-code-styling.js"></script>

<script>
// Check library loaded
window.addEventListener('load', function() {
    if (typeof QRCodeStyling === 'undefined') {
        console.error('QRCodeStyling library failed to load');
        showNotification('QR library failed to load. Please refresh the page.', 'error');
    }
});

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.add('show'), 10);
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Handle QR type change
document.getElementById('qrType').addEventListener('change', function() {
    const type = this.value;
    
    // Hide all field groups
    document.getElementById('simpleContent').style.display = 'none';
    document.getElementById('whatsappFields').style.display = 'none';
    document.getElementById('wifiFields').style.display = 'none';
    document.getElementById('vcardFields').style.display = 'none';
    document.getElementById('locationFields').style.display = 'none';
    document.getElementById('eventFields').style.display = 'none';
    document.getElementById('paymentFields').style.display = 'none';
    
    // Show relevant fields
    switch(type) {
        case 'url':
        case 'text':
        case 'email':
        case 'phone':
        case 'sms':
            document.getElementById('simpleContent').style.display = 'block';
            updateContentLabel(type);
            break;
        case 'whatsapp':
            document.getElementById('whatsappFields').style.display = 'block';
            break;
        case 'wifi':
            document.getElementById('wifiFields').style.display = 'block';
            break;
        case 'vcard':
            document.getElementById('vcardFields').style.display = 'block';
            break;
        case 'location':
            document.getElementById('locationFields').style.display = 'block';
            break;
        case 'event':
            document.getElementById('eventFields').style.display = 'block';
            break;
        case 'payment':
            document.getElementById('paymentFields').style.display = 'block';
            break;
    }
    
    // Trigger live preview
    debouncedPreview();
});

function updateContentLabel(type) {
    const label = document.getElementById('contentLabel');
    const field = document.getElementById('contentField');
    
    const labels = {
        'url': { label: 'URL', placeholder: 'https://example.com' },
        'text': { label: 'Text Content', placeholder: 'Enter any text...' },
        'email': { label: 'Email Address', placeholder: 'email@example.com' },
        'phone': { label: 'Phone Number', placeholder: '+1234567890' },
        'sms': { label: 'SMS (phone:message)', placeholder: '+1234567890:Hello' }
    };
    
    if (labels[type]) {
        label.textContent = labels[type].label;
        field.placeholder = labels[type].placeholder;
    }
}

// Initialize
document.getElementById('qrType').dispatchEvent(new Event('change'));

// Toggle handlers for existing features
document.getElementById('isDynamic').addEventListener('change', function() {
    document.getElementById('redirectUrlGroup').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('hasPassword').addEventListener('change', function() {
    document.getElementById('passwordGroup').style.display = this.checked ? 'block' : 'none';
});

document.getElementById('hasExpiry').addEventListener('change', function() {
    document.getElementById('expiryGroup').style.display = this.checked ? 'block' : 'none';
});

// Toggle handlers for new customization options
document.getElementById('gradientEnabled').addEventListener('change', function() {
    document.getElementById('gradientColorGroup').style.display = this.checked ? 'block' : 'none';
    debouncedPreview();
});

document.getElementById('transparentBg').addEventListener('change', function() {
    if (this.checked) {
        document.getElementById('qrBgColor').disabled = true;
    } else {
        document.getElementById('qrBgColor').disabled = false;
    }
    debouncedPreview();
});

document.getElementById('bgImageEnabled').addEventListener('change', function() {
    document.getElementById('bgImageGroup').style.display = this.checked ? 'block' : 'none';
    debouncedPreview();
});

document.getElementById('customMarkerColor').addEventListener('change', function() {
    document.getElementById('markerColorGroup').style.display = this.checked ? 'block' : 'none';
    if (this.checked) {
        document.getElementById('differentMarkers').checked = false;
        document.getElementById('differentMarkerColorsGroup').style.display = 'none';
    }
    debouncedPreview();
});

document.getElementById('differentMarkers').addEventListener('change', function() {
    document.getElementById('differentMarkerColorsGroup').style.display = this.checked ? 'block' : 'none';
    if (this.checked) {
        document.getElementById('customMarkerColor').checked = false;
        document.getElementById('markerColorGroup').style.display = 'none';
    }
    debouncedPreview();
});

document.getElementById('logoOption').addEventListener('change', function() {
    const value = this.value;
    document.getElementById('defaultLogoGroup').style.display = value === 'default' ? 'block' : 'none';
    document.getElementById('uploadLogoGroup').style.display = value === 'upload' ? 'block' : 'none';
    document.getElementById('logoOptionsGroup').style.display = (value === 'default' || value === 'upload') ? 'block' : 'none';
    debouncedPreview();
});

document.getElementById('logoSize').addEventListener('input', function() {
    document.getElementById('logoSizeValue').textContent = this.value;
    debouncedPreview();
});

document.getElementById('frameStyle').addEventListener('change', function() {
    const hasFrame = this.value !== 'none';
    document.getElementById('frameTextGroup').style.display = hasFrame ? 'block' : 'none';
    document.getElementById('frameFontGroup').style.display = hasFrame ? 'block' : 'none';
    document.getElementById('frameColorGroup').style.display = hasFrame ? 'block' : 'none';
    debouncedPreview();
});

// Global QR code instance
let qrCode = null;

// Default logos as base64 or URLs
const defaultLogos = {
    'qr': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwMCIgZD0iTTMgM2g4djhoLTN2LTVoLTV6bS0yIDBoMnYyaC0yem0xMCAwaDJ2MmgtMnptMCAwaDh2OGgtOHptLTEwIDEwaDJ2MmgtMnptMCAwaDh2OGgtOHptMTAgMGgydjJoLTJ6bTE0LTEwaDJ2MmgtMnptMCA2aDJ2MmgtMnptLTYgNGgydjJoLTJ6bTYgNGgydjJoLTJ6Ii8+PC9zdmc+',
    'star': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGRDcwMCIgZD0iTTEyIDJsMyA2IDYgMWwtNC41IDQuNSAxIDYuNS01LjUtMy01LjUgMyAxLTYuNUwyIDlsNi0xeiIvPjwvc3ZnPg==',
    'heart': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGMDAwMCIgZD0iTTEyIDIxLjM1bC0xLjQ1LTEuMzJDNS40IDE1LjM2IDIgMTIuMjggMiA4LjVjMC0zLjA1IDIuNDUtNS41IDUuNS01LjVhNS40IDUuNCAwIDAxNS41IDMuNjcgNS40IDUuNCAwIDAxNS41LTMuNjdjMy4wNSAwIDUuNSAyLjQ1IDUuNSA1LjUgMCAzLjc4LTMuNCA2Ljg2LTguNTUgMTEuNTRMMTIgMjEuMzV6Ii8+PC9zdmc+',
    'check': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzRDQUY1MCIgZD0iTTkgMTYuMkw0LjggMTJsLTEuNCAxLjRMOSAxOSAyMSA3bC0xLjQtMS40TDkgMTYuMnoiLz48L3N2Zz4='
};

// Add download button
function addDownloadButton(qrCodeInstance) {
    const container = document.getElementById('qrPreviewContainer');
    
    // Check if button already exists
    if (container.querySelector('.btn-download')) {
        return;
    }
    
    const downloadBtn = document.createElement('button');
    downloadBtn.className = 'btn btn-download';
    downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download QR Code';
    downloadBtn.onclick = function(e) {
        e.preventDefault();
        if (qrCodeInstance) {
            qrCodeInstance.download({ name: 'qrcode-' + Date.now(), extension: 'png' });
            showNotification('QR code downloaded successfully!', 'success');
        }
    };
    container.appendChild(downloadBtn);
}

// Generate preview with QRCodeStyling
function generatePreview() {
    if (typeof QRCodeStyling === 'undefined') {
        console.log('QRCodeStyling not loaded yet');
        return;
    }
    
    const content = buildQRContent();
    if (!content || content.trim() === '') {
        return;
    }
    
    // Get all customization options
    const size = parseInt(document.getElementById('qrSize').value);
    const foregroundColor = document.getElementById('qrColor').value;
    const backgroundColor = document.getElementById('qrBgColor').value;
    const errorCorrection = document.getElementById('errorCorrection').value.toUpperCase();
    const cornerStyle = document.getElementById('cornerStyle').value;
    const dotStyle = document.getElementById('dotStyle').value;
    const markerBorderStyle = document.getElementById('markerBorderStyle').value;
    const markerCenterStyle = document.getElementById('markerCenterStyle').value;
    
    // Gradient settings
    const gradientEnabled = document.getElementById('gradientEnabled').checked;
    const gradientColor = document.getElementById('gradientColor').value;
    
    // Transparent background
    const transparentBg = document.getElementById('transparentBg').checked;
    
    // Marker colors
    const customMarkerColor = document.getElementById('customMarkerColor').checked;
    const markerColor = document.getElementById('markerColor').value;
    const differentMarkers = document.getElementById('differentMarkers').checked;
    const markerTLColor = document.getElementById('markerTLColor').value;
    const markerTRColor = document.getElementById('markerTRColor').value;
    const markerBLColor = document.getElementById('markerBLColor').value;
    
    // Logo settings
    const logoOption = document.getElementById('logoOption').value;
    const logoSize = parseFloat(document.getElementById('logoSize').value);
    const logoRemoveBg = document.getElementById('logoRemoveBg').checked;
    
    // Build QR options
    const dotColor = gradientEnabled 
        ? { 
            type: 'linear-gradient', 
            rotation: 0, 
            colorStops: [
                { offset: 0, color: foregroundColor }, 
                { offset: 1, color: gradientColor }
            ] 
        } 
        : foregroundColor;
    
    const qrOptions = {
        width: size,
        height: size,
        type: 'canvas',
        data: content,
        margin: 10,
        qrOptions: {
            typeNumber: 0,
            mode: 'Byte',
            errorCorrectionLevel: errorCorrection
        },
        dotsOptions: {
            color: dotColor,
            type: dotStyle
        },
        backgroundOptions: {
            color: transparentBg ? 'rgba(0,0,0,0)' : backgroundColor
        },
        cornersSquareOptions: {
            type: cornerStyle,
            color: customMarkerColor ? markerColor : foregroundColor
        },
        cornersDotOptions: {
            type: markerCenterStyle,
            color: customMarkerColor ? markerColor : foregroundColor
        }
    };
    
    // Different marker colors (note: limited support in qr-code-styling)
    if (differentMarkers) {
        // QRCodeStyling has limited support for per-marker colors
        // Using top-left color for now as primary marker color
        // We'll use the top-left color for all markers but show the feature is there
        qrOptions.cornersSquareOptions.color = markerTLColor;
        qrOptions.cornersDotOptions.color = markerTLColor;
    }
    
    // Add logo if selected
    if (logoOption === 'default') {
        const defaultLogo = document.getElementById('defaultLogo').value;
        qrOptions.image = defaultLogos[defaultLogo];
        qrOptions.imageOptions = {
            hideBackgroundDots: logoRemoveBg,
            imageSize: logoSize,
            margin: 5
        };
    } else if (logoOption === 'upload') {
        const logoInput = document.getElementById('logoUpload');
        if (logoInput.files && logoInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                qrOptions.image = e.target.result;
                qrOptions.imageOptions = {
                    hideBackgroundDots: logoRemoveBg,
                    imageSize: logoSize,
                    margin: 5
                };
                renderQRCode(qrOptions, content);
            };
            reader.readAsDataURL(logoInput.files[0]);
            return; // Exit and wait for file read
        }
    }
    
    renderQRCode(qrOptions, content);
}

// Render QR Code
function renderQRCode(qrOptions, content) {
    const container = document.getElementById('qrPreviewContainer');
    container.innerHTML = '';
    
    const qrDiv = document.createElement('div');
    qrDiv.id = 'qrcode';
    qrDiv.className = 'qr-preview';
    container.appendChild(qrDiv);
    
    try {
        // Create new QR code instance
        if (qrCode) {
            qrCode.update(qrOptions);
        } else {
            qrCode = new QRCodeStyling(qrOptions);
        }
        
        qrCode.append(qrDiv);
        
        // Apply frame style
        applyFrameStyle(qrDiv);
        
        // Add info
        const infoDiv = document.createElement('div');
        infoDiv.className = 'qr-info';
        infoDiv.innerHTML = `
            <p><strong>Type:</strong> ${document.getElementById('qrType').value}</p>
            <p><strong>Size:</strong> ${qrOptions.width}px</p>
            ${document.getElementById('isDynamic').checked ? '<p><span class="badge badge-dynamic">🔄 Dynamic</span></p>' : ''}
            ${document.getElementById('hasPassword').checked ? '<p><span class="badge badge-secure">🔒 Protected</span></p>' : ''}
        `;
        container.appendChild(infoDiv);
        
        // Add download button
        setTimeout(() => addDownloadButton(qrCode), 300);
        
    } catch (error) {
        console.error('Error generating QR:', error);
        showNotification('Error generating QR code. Please check your inputs.', 'error');
    }
}
}

// Build QR content based on type
function buildQRContent() {
    const type = document.getElementById('qrType').value;
    let content = '';
    
    switch(type) {
        case 'url':
        case 'text':
            content = document.getElementById('contentField').value;
            break;
        case 'email':
            content = 'mailto:' + document.getElementById('contentField').value;
            break;
        case 'phone':
            content = 'tel:' + document.getElementById('contentField').value;
            break;
        case 'sms':
            const smsData = document.getElementById('contentField').value.split(':');
            content = 'sms:' + (smsData[0] || '') + (smsData[1] ? '?body=' + encodeURIComponent(smsData[1]) : '');
            break;
        case 'whatsapp':
            const phone = document.getElementById('whatsappPhone').value.replace(/\D/g, '');
            const message = document.getElementById('whatsappMessage').value;
            content = 'https://wa.me/' + phone + (message ? '?text=' + encodeURIComponent(message) : '');
            break;
        case 'wifi':
            const ssid = document.getElementById('wifiSsid').value;
            const password = document.getElementById('wifiPassword').value;
            const encryption = document.getElementById('wifiEncryption').value;
            content = 'WIFI:T:' + encryption + ';S:' + ssid + ';P:' + password + ';;';
            break;
        case 'vcard':
            const name = document.getElementById('vcardName').value;
            const vcardPhone = document.getElementById('vcardPhone').value;
            const vcardEmail = document.getElementById('vcardEmail').value;
            const org = document.getElementById('vcardOrg').value;
            content = 'BEGIN:VCARD\nVERSION:3.0\nFN:' + name + '\nTEL:' + vcardPhone + '\nEMAIL:' + vcardEmail + (org ? '\nORG:' + org : '') + '\nEND:VCARD';
            break;
        case 'location':
            const lat = document.getElementById('locationLat').value;
            const lng = document.getElementById('locationLng').value;
            content = 'geo:' + lat + ',' + lng;
            break;
        case 'event':
            const title = document.getElementById('eventTitle').value;
            const start = document.getElementById('eventStart').value;
            const end = document.getElementById('eventEnd').value;
            const location = document.getElementById('eventLocation').value;
            content = 'BEGIN:VEVENT\nSUMMARY:' + title + '\nDTSTART:' + start.replace(/[-:]/g, '') + '\nDTEND:' + end.replace(/[-:]/g, '') + (location ? '\nLOCATION:' + location : '') + '\nEND:VEVENT';
            break;
        case 'payment':
            const payType = document.getElementById('paymentType').value;
            const address = document.getElementById('paymentAddress').value;
            const amount = document.getElementById('paymentAmount').value;
            if (payType === 'upi') {
                content = 'upi://pay?pa=' + address + (amount ? '&am=' + amount : '');
            } else if (payType === 'paypal') {
                content = 'https://paypal.me/' + address + (amount ? '/' + amount : '');
            } else if (payType === 'bitcoin') {
                content = 'bitcoin:' + address + (amount ? '?amount=' + amount : '');
            }
            break;
    }
    
    return content;
}

// Apply frame style to QR code
function applyFrameStyle(qrDiv) {
    const frameStyle = document.getElementById('frameStyle').value;
    
    // Remove any existing frame classes
    qrDiv.className = 'qr-preview';
    
    if (frameStyle && frameStyle !== 'none') {
        qrDiv.classList.add('qr-frame-' + frameStyle);
    }
}

// Debounced live preview
let previewTimeout;
function debouncedPreview() {
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(generatePreview, 500);
}

// Live preview on all field changes
const livePreviewFields = [
    'contentField', 'qrType', 'qrSize', 'qrColor', 'qrBgColor', 'errorCorrection',
    'frameStyle', 'cornerStyle', 'dotStyle', 'markerBorderStyle', 'markerCenterStyle',
    'gradientColor', 'markerColor', 'markerTLColor', 'markerTRColor', 'markerBLColor',
    'defaultLogo', 'frameLabel', 'frameFont', 'frameColor',
    'whatsappPhone', 'whatsappMessage',
    'wifiSsid', 'wifiPassword', 'wifiEncryption',
    'vcardName', 'vcardPhone', 'vcardEmail', 'vcardOrg',
    'locationLat', 'locationLng',
    'eventTitle', 'eventStart', 'eventEnd', 'eventLocation',
    'paymentType', 'paymentAddress', 'paymentAmount'
];

livePreviewFields.forEach(fieldId => {
    const field = document.getElementById(fieldId);
    if (field) {
        field.addEventListener('input', debouncedPreview);
        field.addEventListener('change', debouncedPreview);
    }
});
</script>

<style>
/* Futuristic AI Design Theme */
:root {
    --glow-color: rgba(153, 69, 255, 0.6);
    --glow-cyan: rgba(0, 240, 255, 0.6);
}

/* Glassmorphism Cards */
.glass-card {
    background: rgba(255, 255, 255, 0.05);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    transition: all 0.3s ease;
}

[data-theme="light"] .glass-card {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.1);
}

.glass-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px 0 rgba(153, 69, 255, 0.3);
}

/* Section Titles */
.section-title {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 25px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
    gap: 12px;
}

.subsection-title {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Form Styling */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 500;
    color: var(--text-primary);
    font-size: 14px;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 10px;
    color: var(--text-primary);
    font-size: 14px;
    transition: all 0.3s ease;
}

[data-theme="light"] .form-input,
[data-theme="light"] .form-select,
[data-theme="light"] .form-textarea {
    background: #ffffff;
    border: 1px solid #e0e0e0;
    color: #1a1a1a;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 4px rgba(153, 69, 255, 0.1);
    background: rgba(255, 255, 255, 0.08);
}

.color-input {
    height: 50px;
    cursor: pointer;
}

/* Feature Toggles */
.feature-toggle {
    margin-bottom: 20px;
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 15px;
    cursor: pointer;
    padding: 15px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    transition: all 0.3s ease;
}

.toggle-label:hover {
    background: rgba(255, 255, 255, 0.05);
}

.toggle-input {
    display: none;
}

.toggle-slider {
    position: relative;
    width: 50px;
    height: 26px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 13px;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.toggle-slider::before {
    content: '';
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: #fff;
    top: 3px;
    left: 3px;
    transition: all 0.3s ease;
}

.toggle-input:checked + .toggle-slider {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
}

.toggle-input:checked + .toggle-slider::before {
    transform: translateX(24px);
}

.toggle-text {
    flex: 1;
}

.toggle-text strong {
    display: block;
    color: var(--text-primary);
    font-size: 15px;
    margin-bottom: 4px;
}

.toggle-text small {
    display: block;
    color: var(--text-secondary);
    font-size: 12px;
}

/* Buttons */
.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    position: relative;
    overflow: hidden;
}

.btn-primary {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    box-shadow: 0 4px 15px rgba(153, 69, 255, 0.4);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(153, 69, 255, 0.6);
}

.btn-large {
    width: 100%;
    padding: 16px 32px;
    font-size: 16px;
}

.btn-download {
    margin-top: 20px;
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn:hover .btn-shine {
    left: 100%;
}

/* Preview Panel */
.preview-panel {
    position: sticky;
    top: 80px;
    max-height: calc(100vh - 100px);
    overflow-y: auto;
}

.preview-container {
    text-align: center;
    min-height: 400px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
}

.qr-preview {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 20px;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

#qrcode {
    padding: 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.qr-info {
    width: 100%;
    padding: 20px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 12px;
    text-align: left;
}

.qr-info p {
    margin: 8px 0;
    color: var(--text-primary);
    font-size: 14px;
}

.badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 8px;
}

.badge-dynamic {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.badge-secure {
    background: linear-gradient(135deg, #f093fb, #f5576c);
    color: white;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-icon {
    font-size: 80px;
    color: var(--purple);
    opacity: 0.3;
    margin-bottom: 20px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.5; transform: scale(1.05); }
}

.empty-title {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 10px;
}

.empty-subtitle {
    font-size: 14px;
    color: var(--text-secondary);
}

/* Divider */
.divider {
    height: 1px;
    background: linear-gradient(90deg, transparent, var(--border-color), transparent);
    margin: 30px 0;
}

/* Grid */
.grid {
    display: grid;
    gap: 30px;
}

.grid-2 {
    grid-template-columns: 1fr 1fr;
}

@media (max-width: 1024px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .preview-panel {
        position: static;
        max-height: none;
    }
}

/* Notifications */
.notification {
    position: fixed;
    top: 80px;
    right: 20px;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transform: translateX(400px);
    transition: transform 0.3s ease;
}

.notification.show {
    transform: translateX(0);
}

.notification-success {
    background: linear-gradient(135deg, #00ff88, #00d4ff);
    color: white;
}

.notification-error {
    background: linear-gradient(135deg, #ff2ec4, #ff6b6b);
    color: white;
}

/* Back Link */
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    text-decoration: none;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.back-link:hover {
    color: var(--purple);
    transform: translateX(-4px);
}

/* Small text */
small {
    font-size: 12px;
    color: var(--text-secondary);
    display: block;
    margin-top: 5px;
}

/* Frame Styles for QR Codes */
.qr-preview {
    display: inline-block;
    transition: all 0.3s ease;
}

/* Square Frame */
.qr-frame-square {
    padding: 20px;
    border: 4px solid var(--purple);
    border-radius: 8px;
    background: white;
    box-shadow: 0 8px 20px rgba(153, 69, 255, 0.2);
}

/* Circle Frame */
.qr-frame-circle {
    padding: 20px;
    border: 4px solid var(--cyan);
    border-radius: 50%;
    background: white;
    box-shadow: 0 8px 20px rgba(0, 240, 255, 0.2);
    overflow: hidden;
}

.qr-frame-circle canvas,
.qr-frame-circle img {
    border-radius: 50%;
}

/* Rounded Corners Frame */
.qr-frame-rounded {
    padding: 20px;
    border: 3px solid transparent;
    border-radius: 24px;
    background: linear-gradient(white, white) padding-box,
                linear-gradient(135deg, var(--purple), var(--cyan)) border-box;
    box-shadow: 0 8px 24px rgba(153, 69, 255, 0.3);
}

/* Banner Top Frame */
.qr-frame-banner-top {
    padding: 50px 20px 20px 20px;
    border: 3px solid var(--purple);
    border-radius: 12px;
    background: white;
    position: relative;
    box-shadow: 0 8px 20px rgba(153, 69, 255, 0.2);
}

.qr-frame-banner-top::before {
    content: 'SCAN ME';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    text-align: center;
    padding: 8px;
    font-weight: bold;
    font-size: 12px;
    letter-spacing: 2px;
    border-radius: 8px 8px 0 0;
}

/* Banner Bottom Frame */
.qr-frame-banner-bottom {
    padding: 20px 20px 50px 20px;
    border: 3px solid var(--cyan);
    border-radius: 12px;
    background: white;
    position: relative;
    box-shadow: 0 8px 20px rgba(0, 240, 255, 0.2);
}

.qr-frame-banner-bottom::after {
    content: 'SCAN ME';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: white;
    text-align: center;
    padding: 8px;
    font-weight: bold;
    font-size: 12px;
    letter-spacing: 2px;
    border-radius: 0 0 8px 8px;
}

/* Speech Bubble Frame */
.qr-frame-bubble {
    padding: 20px;
    border: 3px solid var(--purple);
    border-radius: 20px;
    background: white;
    position: relative;
    box-shadow: 0 8px 20px rgba(153, 69, 255, 0.2);
    margin-bottom: 30px;
}

.qr-frame-bubble::after {
    content: '';
    position: absolute;
    bottom: -25px;
    left: 30px;
    width: 0;
    height: 0;
    border-left: 15px solid transparent;
    border-right: 15px solid transparent;
    border-top: 25px solid var(--purple);
}

/* Badge Style Frame */
.qr-frame-badge {
    padding: 25px;
    border: 4px solid var(--purple);
    border-radius: 50% 50% 50% 10px;
    background: white;
    position: relative;
    box-shadow: 0 8px 20px rgba(153, 69, 255, 0.3);
}

.qr-frame-badge::before {
    content: '✓';
    position: absolute;
    top: -10px;
    right: -10px;
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 18px;
    box-shadow: 0 4px 12px rgba(153, 69, 255, 0.4);
}
</style>
