<?php
// Enhanced QR Code Generator with Advanced Options
// This file replaces generate.php with production-ready features
?>

<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px;">Generate QR Code - Advanced Options</h1>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 20px;">QR Code Configuration</h3>
        
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
                    <option value="payment">💳 Payment (UPI/PayPal)</option>
                </select>
            </div>
            
            <!-- Simple Content Field (for URL, Text, Phone, Email) -->
            <div class="form-group" id="simpleContent">
                <label class="form-label" id="contentLabel">Content</label>
                <textarea name="content" id="contentField" class="form-textarea" rows="4" placeholder="Enter URL, text, or other content..."></textarea>
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
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            
            <!-- Design Options -->
            <h4 style="margin-bottom: 15px;">🎨 Design Options</h4>
            
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
                        <option value="H" selected>High (30%) - Recommended</option>
                    </select>
                </div>
            </div>
            
            <div class="grid grid-2" style="gap: 15px;">
                <div class="form-group">
                    <label class="form-label">Foreground Color</label>
                    <input type="color" name="foreground_color" id="qrColor" value="#000000" class="form-input" style="height: 50px;">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Background Color</label>
                    <input type="color" name="background_color" id="qrBgColor" value="#ffffff" class="form-input" style="height: 50px;">
                </div>
            </div>
            
            <!-- Frame Style -->
            <div class="form-group">
                <label class="form-label">Frame Style</label>
                <select name="frame_style" id="frameStyle" class="form-select">
                    <option value="none">No Frame</option>
                    <option value="square">Square Frame</option>
                    <option value="circle">Circle Frame</option>
                    <option value="rounded">Rounded Corners</option>
                    <option value="banner">Banner Style</option>
                    <option value="bubble">Speech Bubble</option>
                </select>
            </div>
            
            <!-- Logo Upload -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-image"></i> Logo (Optional)
                    <span style="font-size: 12px; color: #666;">(Max 2MB, PNG/JPG)</span>
                </label>
                <input type="file" name="logo" id="logoUpload" class="form-input" accept="image/png,image/jpeg,image/jpg">
                <small style="color: #666;">Logo will be centered in the QR code</small>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            
            <!-- Advanced Features -->
            <h4 style="margin-bottom: 15px;">⚡ Advanced Features</h4>
            
            <!-- Dynamic QR Toggle -->
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center;">
                    <input type="checkbox" name="is_dynamic" id="isDynamic" value="1" style="margin-right: 10px; width: 20px; height: 20px;">
                    <span>
                        <strong>Dynamic QR Code</strong>
                        <small style="display: block; color: #666;">URL can be changed later without regenerating QR</small>
                    </span>
                </label>
            </div>
            
            <!-- Redirect URL (for dynamic QR) -->
            <div class="form-group" id="redirectUrlGroup" style="display: none;">
                <label class="form-label">Redirect URL</label>
                <input type="url" name="redirect_url" id="redirectUrl" class="form-input" placeholder="https://example.com">
                <small style="color: #666;">This URL can be edited later without changing the QR code</small>
            </div>
            
            <!-- Password Protection -->
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center;">
                    <input type="checkbox" name="has_password" id="hasPassword" value="1" style="margin-right: 10px; width: 20px; height: 20px;">
                    <span>
                        <strong>Password Protection</strong>
                        <small style="display: block; color: #666;">Require password to scan</small>
                    </span>
                </label>
            </div>
            
            <!-- Password Field -->
            <div class="form-group" id="passwordGroup" style="display: none;">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="qrPassword" class="form-input" placeholder="Enter password">
            </div>
            
            <!-- Expiry Date -->
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center;">
                    <input type="checkbox" name="has_expiry" id="hasExpiry" value="1" style="margin-right: 10px; width: 20px; height: 20px;">
                    <span>
                        <strong>Set Expiry Date</strong>
                        <small style="display: block; color: #666;">QR code will stop working after this date</small>
                    </span>
                </label>
            </div>
            
            <!-- Expiry Date Field -->
            <div class="form-group" id="expiryGroup" style="display: none;">
                <label class="form-label">Expires On</label>
                <input type="datetime-local" name="expires_at" id="expiresAt" class="form-input">
            </div>
            
            <!-- Campaign -->
            <div class="form-group">
                <label class="form-label">Campaign (Optional)</label>
                <select name="campaign_id" id="campaignId" class="form-select">
                    <option value="">No Campaign</option>
                    <?php
                    // TODO: Load user's campaigns from database
                    // For now, just show placeholder
                    ?>
                    <option value="1">Marketing Campaign 2024</option>
                    <option value="2">Product Launch</option>
                </select>
                <small style="color: #666;">Group QR codes into campaigns for better organization</small>
            </div>
            
            <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn btn-secondary" onclick="generatePreview()" style="flex: 1; min-width: 150px;">
                    <i class="fas fa-eye"></i> Preview QR
                </button>
                <button type="submit" class="btn btn-primary" style="flex: 2; min-width: 200px;">
                    <i class="fas fa-save"></i> Generate & Save QR Code
                </button>
            </div>
        </form>
    </div>
    
    <!-- Preview Panel -->
    <div class="card" style="text-align: center; position: sticky; top: 20px;">
        <h3 style="margin-bottom: 20px;">Preview</h3>
        
        <div id="qrPreviewContainer">
            <?php if (isset($_SESSION['generated_qr'])): ?>
                <div class="qr-preview">
                    <div id="qrcode" style="display: inline-block;"></div>
                    <script>
                        // Regenerate QR from session data
                        (function tryGenerateQR() {
                            if (typeof QRCode !== 'undefined') {
                                try {
                                    const qrDiv = document.getElementById('qrcode');
                                    new QRCode(qrDiv, {
                                        text: <?= json_encode($_SESSION['generated_qr']['content']) ?>,
                                        width: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        height: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        colorDark: <?= json_encode($_SESSION['generated_qr']['foreground_color'] ?? '#000000') ?>,
                                        colorLight: <?= json_encode($_SESSION['generated_qr']['background_color'] ?? '#ffffff') ?>,
                                        correctLevel: QRCode.CorrectLevel.H
                                    });
                                    
                                    // Show download button after generation
                                    setTimeout(function() {
                                        const canvas = qrDiv.querySelector('canvas');
                                        if (canvas) {
                                            const downloadBtn = document.createElement('button');
                                            downloadBtn.className = 'btn btn-primary';
                                            downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download QR Code';
                                            downloadBtn.style.marginTop = '20px';
                                            downloadBtn.onclick = function() {
                                                const link = document.createElement('a');
                                                link.download = 'qrcode-<?= time() ?>.png';
                                                link.href = canvas.toDataURL();
                                                link.click();
                                            };
                                            qrDiv.appendChild(downloadBtn);
                                        }
                                    }, 200);
                                } catch (error) {
                                    console.error('Error generating QR from session:', error);
                                }
                            } else {
                                setTimeout(tryGenerateQR, 100);
                            }
                        })();
                    </script>
                    
                    <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: left;">
                        <p style="margin: 5px 0;"><strong>Type:</strong> <?= htmlspecialchars($_SESSION['generated_qr']['type'] ?? 'url') ?></p>
                        <p style="margin: 5px 0;"><strong>Size:</strong> <?= htmlspecialchars($_SESSION['generated_qr']['size'] ?? 300) ?>x<?= htmlspecialchars($_SESSION['generated_qr']['size'] ?? 300) ?>px</p>
                        <?php if (isset($_SESSION['generated_qr']['is_dynamic']) && $_SESSION['generated_qr']['is_dynamic']): ?>
                            <p style="margin: 5px 0;"><strong>🔄 Dynamic QR</strong> - URL can be changed later</p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['has_password']) && $_SESSION['generated_qr']['has_password']): ?>
                            <p style="margin: 5px 0;"><strong>🔒 Password Protected</strong></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['expires_at']) && $_SESSION['generated_qr']['expires_at']): ?>
                            <p style="margin: 5px 0;"><strong>⏰ Expires:</strong> <?= htmlspecialchars($_SESSION['generated_qr']['expires_at']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php unset($_SESSION['generated_qr']); ?>
            <?php else: ?>
                <div id="emptyState" style="padding: 60px 20px; color: #999;">
                    <i class="fas fa-qrcode" style="font-size: 64px; margin-bottom: 20px; opacity: 0.3;"></i>
                    <p>QR code preview will appear here</p>
                    <p style="font-size: 14px;">Fill in the form and click "Preview QR" to see your QR code</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QRCode.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Check if QRCode library loaded
window.addEventListener('load', function() {
    if (typeof QRCode === 'undefined') {
        console.error('QRCode.js library failed to load from CDN');
        alert('QR Code library failed to load. Please check your internet connection.');
    }
});

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
});

function updateContentLabel(type) {
    const label = document.getElementById('contentLabel');
    const field = document.getElementById('contentField');
    
    switch(type) {
        case 'url':
            label.textContent = 'URL';
            field.placeholder = 'https://example.com';
            break;
        case 'text':
            label.textContent = 'Text Content';
            field.placeholder = 'Enter any text...';
            break;
        case 'email':
            label.textContent = 'Email Address';
            field.placeholder = 'email@example.com';
            break;
        case 'phone':
            label.textContent = 'Phone Number';
            field.placeholder = '+1234567890';
            break;
        case 'sms':
            label.textContent = 'SMS (phone:message)';
            field.placeholder = '+1234567890:Hello World';
            break;
    }
}

// Initialize on page load
document.getElementById('qrType').dispatchEvent(new Event('change'));

// Toggle dynamic QR redirect URL field
document.getElementById('isDynamic').addEventListener('change', function() {
    document.getElementById('redirectUrlGroup').style.display = this.checked ? 'block' : 'none';
});

// Toggle password field
document.getElementById('hasPassword').addEventListener('change', function() {
    document.getElementById('passwordGroup').style.display = this.checked ? 'block' : 'none';
});

// Toggle expiry field
document.getElementById('hasExpiry').addEventListener('change', function() {
    document.getElementById('expiryGroup').style.display = this.checked ? 'block' : 'none';
});

// Build QR content from form fields
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

// Generate preview
function generatePreview() {
    // Check if library is loaded
    if (typeof QRCode === 'undefined') {
        alert('QR Code library is still loading. Please wait a moment and try again.');
        return;
    }
    
    // Build content
    const content = buildQRContent();
    
    // Validate
    if (!content || content.trim() === '') {
        alert('Please fill in all required fields for the selected QR type.');
        return;
    }
    
    // Get options
    const size = parseInt(document.getElementById('qrSize').value);
    const foregroundColor = document.getElementById('qrColor').value;
    const backgroundColor = document.getElementById('qrBgColor').value;
    const errorCorrection = document.getElementById('errorCorrection').value;
    
    // Map error correction level
    let correctLevel = QRCode.CorrectLevel.H;
    switch(errorCorrection) {
        case 'L': correctLevel = QRCode.CorrectLevel.L; break;
        case 'M': correctLevel = QRCode.CorrectLevel.M; break;
        case 'Q': correctLevel = QRCode.CorrectLevel.Q; break;
        case 'H': correctLevel = QRCode.CorrectLevel.H; break;
    }
    
    // Clear preview container
    const container = document.getElementById('qrPreviewContainer');
    container.innerHTML = '';
    
    // Create QR div
    const qrDiv = document.createElement('div');
    qrDiv.id = 'qrcode';
    qrDiv.style.display = 'inline-block';
    container.appendChild(qrDiv);
    
    // Generate QR code
    try {
        const qrcode = new QRCode(qrDiv, {
            text: content,
            width: size,
            height: size,
            colorDark: foregroundColor,
            colorLight: backgroundColor,
            correctLevel: correctLevel
        });
        
        // Wait for canvas to be created, then add download button
        setTimeout(function() {
            const canvas = qrDiv.querySelector('canvas');
            if (canvas) {
                // Store data URL for form submission
                document.getElementById('qrDataUrl').value = canvas.toDataURL();
                
                // Add info section
                const infoDiv = document.createElement('div');
                infoDiv.style.cssText = 'margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; text-align: left;';
                infoDiv.innerHTML = `
                    <p style="margin: 5px 0;"><strong>Type:</strong> ${document.getElementById('qrType').value}</p>
                    <p style="margin: 5px 0;"><strong>Size:</strong> ${size}x${size}px</p>
                    <p style="margin: 5px 0;"><strong>Error Correction:</strong> ${errorCorrection} Level</p>
                    ${document.getElementById('isDynamic').checked ? '<p style="margin: 5px 0;"><strong>🔄 Dynamic QR</strong> - URL can be changed later</p>' : ''}
                    ${document.getElementById('hasPassword').checked ? '<p style="margin: 5px 0;"><strong>🔒 Password Protected</strong></p>' : ''}
                    ${document.getElementById('hasExpiry').checked ? '<p style="margin: 5px 0;"><strong>⏰ Expires:</strong> ' + document.getElementById('expiresAt').value + '</p>' : ''}
                `;
                container.appendChild(infoDiv);
                
                // Add download button
                const downloadBtn = document.createElement('button');
                downloadBtn.className = 'btn btn-primary';
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download QR Code';
                downloadBtn.style.marginTop = '20px';
                downloadBtn.onclick = function() {
                    const link = document.createElement('a');
                    link.download = 'qrcode-' + Date.now() + '.png';
                    link.href = canvas.toDataURL();
                    link.click();
                };
                container.appendChild(downloadBtn);
            }
        }, 200);
        
    } catch (error) {
        console.error('Error generating QR code:', error);
        alert('Error generating QR code. Please check your inputs and try again.');
    }
}

// Auto-update preview on color/size change
document.getElementById('qrColor').addEventListener('change', function() {
    if (document.getElementById('qrDataUrl').value) {
        generatePreview();
    }
});

document.getElementById('qrBgColor').addEventListener('change', function() {
    if (document.getElementById('qrDataUrl').value) {
        generatePreview();
    }
});

document.getElementById('qrSize').addEventListener('change', function() {
    if (document.getElementById('qrDataUrl').value) {
        generatePreview();
    }
});
</script>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #333;
}

.form-input,
.form-select,
.form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: #7c3aed;
    box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.1);
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.card {
    background: white;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.grid {
    display: grid;
    gap: 30px;
}

.grid-2 {
    grid-template-columns: 1fr 1fr;
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
}

h4 {
    color: #333;
    font-size: 18px;
    margin-bottom: 15px;
}

hr {
    margin: 30px 0;
    border: none;
    border-top: 1px solid #eee;
}

small {
    font-size: 12px;
    color: #666;
    display: block;
    margin-top: 5px;
}
</style>
