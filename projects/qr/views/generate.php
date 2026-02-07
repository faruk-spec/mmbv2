<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px;">Generate QR Code</h1>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 20px;">QR Code Options</h3>
        
        <form method="POST" action="/projects/qr/generate" id="qrForm">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            <input type="hidden" name="qr_data_url" id="qrDataUrl">
            
            <div class="form-group">
                <label class="form-label">Content Type</label>
                <select name="type" class="form-select" id="qrType">
                    <option value="url">URL / Website</option>
                    <option value="text">Plain Text</option>
                    <option value="email">Email Address</option>
                    <option value="phone">Phone Number</option>
                    <option value="sms">SMS Message</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="wifi">WiFi Network</option>
                    <option value="vcard">vCard (Contact)</option>
                    <option value="location">Location</option>
                    <option value="event">Event (Calendar)</option>
                    <option value="payment">Payment (UPI/PayPal)</option>
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
                    <input type="text" name="event_title" id="eventTitle" class="form-input" placeholder="Meeting Title">
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
                    <input type="text" name="event_location" id="eventLocation" class="form-input" placeholder="Conference Room A">
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
                    <input type="text" name="payment_address" id="paymentAddress" class="form-input" placeholder="username@upi or email@paypal.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Optional)</label>
                    <input type="text" name="payment_amount" id="paymentAmount" class="form-input" placeholder="100.00">
                </div>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Size (px)</label>
                    <select name="size" id="qrSize" class="form-select">
                        <option value="150">Small (150x150)</option>
                        <option value="200">Medium (200x200)</option>
                        <option value="300" selected>Large (300x300)</option>
                        <option value="400">Extra Large (400x400)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">QR Color</label>
                    <input type="color" name="color" id="qrColor" value="#000000" class="form-input" style="height: 45px; padding: 5px;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Background Color</label>
                <input type="color" name="bg_color" id="qrBgColor" value="#ffffff" class="form-input" style="height: 45px; padding: 5px; width: 100px;">
            </div>
            
            <button type="button" class="btn btn-secondary" onclick="generatePreview()" style="margin-right: 10px;">Preview QR</button>
            <button type="submit" class="btn btn-primary">Generate QR Code</button>
        </form>
    </div>
    
    <div class="card" style="text-align: center;">
        <h3 style="margin-bottom: 20px;">Preview</h3>
        
        <div id="qrPreviewContainer">
            <?php if (isset($_SESSION['generated_qr'])): ?>
                <div class="qr-preview">
                    <div id="qrcode" style="display: inline-block;"></div>
                    <script>
                        // Regenerate QR from saved data
                        new QRCode(document.getElementById("qrcode"), {
                            text: <?= json_encode($_SESSION['generated_qr']['content']) ?>,
                            width: <?= (int)$_SESSION['generated_qr']['size'] ?>,
                            height: <?= (int)$_SESSION['generated_qr']['size'] ?>,
                            colorDark: "<?= htmlspecialchars($_SESSION['generated_qr']['foreground_color'] ?? '#000000') ?>",
                            colorLight: "<?= htmlspecialchars($_SESSION['generated_qr']['background_color'] ?? '#ffffff') ?>",
                            correctLevel: QRCode.CorrectLevel.H
                        });
                    </script>
                </div>
                
                <div style="margin-top: 20px;">
                    <p style="color: var(--text-secondary); margin-bottom: 15px; font-size: 14px;">
                        Type: <?= htmlspecialchars($_SESSION['generated_qr']['type']) ?><br>
                        Content: <?= htmlspecialchars(substr($_SESSION['generated_qr']['content'], 0, 50)) ?><?= strlen($_SESSION['generated_qr']['content']) > 50 ? '...' : '' ?>
                    </p>
                    
                    <button onclick="downloadQR()" class="btn btn-primary">Download QR Code</button>
                </div>
            <?php else: ?>
                <div id="emptyState" style="padding: 60px 20px; color: var(--text-secondary);">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity: 0.5; margin-bottom: 20px;">
                        <rect x="3" y="3" width="7" height="7"/>
                        <rect x="14" y="3" width="7" height="7"/>
                        <rect x="14" y="14" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/>
                    </svg>
                    <p>Your QR code will appear here</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Dynamic form field management
document.getElementById('qrType').addEventListener('change', function() {
    const type = this.value;
    
    // Hide all dynamic fields
    document.getElementById('simpleContent').style.display = 'none';
    document.getElementById('whatsappFields').style.display = 'none';
    document.getElementById('wifiFields').style.display = 'none';
    document.getElementById('vcardFields').style.display = 'none';
    document.getElementById('locationFields').style.display = 'none';
    document.getElementById('eventFields').style.display = 'none';
    document.getElementById('paymentFields').style.display = 'none';
    
    // Show relevant fields based on type
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
            const start = document.getElementById('eventStart').value.replace(/[-:]/g, '').replace('T', '') + '00Z';
            const end = document.getElementById('eventEnd').value.replace(/[-:]/g, '').replace('T', '') + '00Z';
            const location = document.getElementById('eventLocation').value;
            content = 'BEGIN:VEVENT\nSUMMARY:' + title + '\nDTSTART:' + start + '\nDTEND:' + end + (location ? '\nLOCATION:' + location : '') + '\nEND:VEVENT';
            break;
        case 'payment':
            const payType = document.getElementById('paymentType').value;
            const address = document.getElementById('paymentAddress').value;
            const amount = document.getElementById('paymentAmount').value;
            if (payType === 'upi') {
                content = 'upi://pay?pa=' + address + (amount ? '&am=' + amount : '');
            } else if (payType === 'paypal') {
                content = 'https://www.paypal.me/' + address + (amount ? '/' + amount : '');
            } else {
                content = 'bitcoin:' + address + (amount ? '?amount=' + amount : '');
            }
            break;
    }
    
    // Update hidden field with full content
    document.getElementById('contentField').value = content;
    
    return content;
}

let qrcode = null;

function generatePreview() {
    const content = buildQRContent();
    
    if (!content) {
        alert('Please fill in all required fields');
        return;
    }
    
    const size = parseInt(document.getElementById('qrSize').value);
    const colorDark = document.getElementById('qrColor').value;
    const colorLight = document.getElementById('qrBgColor').value;
    
    // Clear previous QR
    const container = document.getElementById('qrPreviewContainer');
    container.innerHTML = '<div id="qrcode" style="display: inline-block;"></div>';
    
    // Hide empty state
    const emptyState = document.getElementById('emptyState');
    if (emptyState) {
        emptyState.style.display = 'none';
    }
    
    // Generate new QR
    qrcode = new QRCode(document.getElementById("qrcode"), {
        text: content,
        width: size,
        height: size,
        colorDark: colorDark,
        colorLight: colorLight,
        correctLevel: QRCode.CorrectLevel.H
    });
    
    // Add download button
    setTimeout(() => {
        const canvas = document.querySelector('#qrcode canvas');
        if (canvas) {
            const dataUrl = canvas.toDataURL('image/png');
            document.getElementById('qrDataUrl').value = dataUrl;
            
            container.innerHTML += '<div style="margin-top: 20px;"><button type="button" onclick="downloadQR()" class="btn btn-primary">Download QR Code</button></div>';
        }
    }, 100);
}

function downloadQR() {
    const canvas = document.querySelector('#qrcode canvas');
    if (canvas) {
        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'qrcode.png';
        link.href = dataUrl;
        link.click();
    }
}

// Auto-generate preview when form changes
const formInputs = document.querySelectorAll('#qrForm input, #qrForm select, #qrForm textarea');
formInputs.forEach(input => {
    if (input.type !== 'hidden' && input.name !== '_csrf_token') {
        input.addEventListener('change', () => {
            if (document.querySelector('#qrcode canvas')) {
                generatePreview();
            }
        });
    }
});
</script>
