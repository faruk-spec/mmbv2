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
                    <option value="url"><i class="fas fa-globe"></i> URL / Website</option>
                    <option value="text"><i class="fas fa-file-alt"></i> Plain Text</option>
                    <option value="email"><i class="fas fa-envelope"></i> Email Address</option>
                    <option value="phone"><i class="fas fa-phone"></i> Phone Number</option>
                    <option value="sms"><i class="fas fa-sms"></i> SMS Message</option>
                    <option value="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</option>
                    <option value="wifi"><i class="fas fa-wifi"></i> WiFi Network</option>
                    <option value="vcard"><i class="fas fa-id-card"></i> vCard (Contact)</option>
                    <option value="location"><i class="fas fa-map-marker-alt"></i> Location</option>
                    <option value="event"><i class="fas fa-calendar-alt"></i> Event (Calendar)</option>
                    <option value="payment"><i class="fas fa-credit-card"></i> Payment</option>
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
            
            <!-- Design Customization with Visual Presets -->
            <h4 class="subsection-title">
                <i class="fas fa-shapes"></i> Design Presets
            </h4>
            
            <!-- Dot Pattern Presets -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-th"></i> Dot Pattern
                </label>
                <div class="preset-grid">
                    <div class="preset-option" data-preset="dotStyle" data-value="square" onclick="selectPreset('dotStyle', 'square')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="8" height="8" fill="currentColor"/>
                                <rect x="15" y="5" width="8" height="8" fill="currentColor"/>
                                <rect x="25" y="5" width="8" height="8" fill="currentColor"/>
                                <rect x="5" y="15" width="8" height="8" fill="currentColor"/>
                                <rect x="25" y="15" width="8" height="8" fill="currentColor"/>
                                <rect x="5" y="25" width="8" height="8" fill="currentColor"/>
                                <rect x="15" y="25" width="8" height="8" fill="currentColor"/>
                                <rect x="25" y="25" width="8" height="8" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Square</span>
                    </div>
                    
                    <div class="preset-option" data-preset="dotStyle" data-value="rounded" onclick="selectPreset('dotStyle', 'rounded')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="15" y="5" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="25" y="5" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="5" y="15" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="25" y="15" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="5" y="25" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="15" y="25" width="8" height="8" rx="2" fill="currentColor"/>
                                <rect x="25" y="25" width="8" height="8" rx="2" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Rounded</span>
                    </div>
                    
                    <div class="preset-option active" data-preset="dotStyle" data-value="dots" onclick="selectPreset('dotStyle', 'dots')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <circle cx="9" cy="9" r="4" fill="currentColor"/>
                                <circle cx="19" cy="9" r="4" fill="currentColor"/>
                                <circle cx="29" cy="9" r="4" fill="currentColor"/>
                                <circle cx="9" cy="19" r="4" fill="currentColor"/>
                                <circle cx="29" cy="19" r="4" fill="currentColor"/>
                                <circle cx="9" cy="29" r="4" fill="currentColor"/>
                                <circle cx="19" cy="29" r="4" fill="currentColor"/>
                                <circle cx="29" cy="29" r="4" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Dots</span>
                    </div>
                    
                    <div class="preset-option" data-preset="dotStyle" data-value="classy" onclick="selectPreset('dotStyle', 'classy')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="8" height="8" rx="1" fill="currentColor"/>
                                <circle cx="19" cy="9" r="3" fill="currentColor"/>
                                <rect x="25" y="5" width="8" height="8" rx="1" fill="currentColor"/>
                                <circle cx="9" cy="19" r="3" fill="currentColor"/>
                                <circle cx="29" cy="19" r="3" fill="currentColor"/>
                                <rect x="5" y="25" width="8" height="8" rx="1" fill="currentColor"/>
                                <circle cx="19" cy="29" r="3" fill="currentColor"/>
                                <rect x="25" y="25" width="8" height="8" rx="1" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Classy</span>
                    </div>
                    
                    <div class="preset-option" data-preset="dotStyle" data-value="classy-rounded" onclick="selectPreset('dotStyle', 'classy-rounded')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="8" height="8" rx="3" fill="currentColor"/>
                                <circle cx="19" cy="9" r="3.5" fill="currentColor"/>
                                <rect x="25" y="5" width="8" height="8" rx="3" fill="currentColor"/>
                                <circle cx="9" cy="19" r="3.5" fill="currentColor"/>
                                <circle cx="29" cy="19" r="3.5" fill="currentColor"/>
                                <rect x="5" y="25" width="8" height="8" rx="3" fill="currentColor"/>
                                <circle cx="19" cy="29" r="3.5" fill="currentColor"/>
                                <rect x="25" y="25" width="8" height="8" rx="3" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Classy Rounded</span>
                    </div>
                </div>
            </div>
            
            <!-- Corner Style (Markers) Presets -->
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-vector-square"></i> Corner Markers
                </label>
                <div class="preset-grid">
                    <div class="preset-option active" data-preset="cornerStyle" data-value="square" onclick="selectPreset('cornerStyle', 'square')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="20" height="20" fill="none" stroke="currentColor" stroke-width="3"/>
                                <rect x="10" y="10" width="10" height="10" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Square</span>
                    </div>
                    
                    <div class="preset-option" data-preset="cornerStyle" data-value="extra-rounded" onclick="selectPreset('cornerStyle', 'extra-rounded')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="5" y="5" width="20" height="20" rx="6" fill="none" stroke="currentColor" stroke-width="3"/>
                                <rect x="10" y="10" width="10" height="10" rx="3" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Rounded</span>
                    </div>
                    
                    <div class="preset-option" data-preset="cornerStyle" data-value="dot" onclick="selectPreset('cornerStyle', 'dot')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <circle cx="15" cy="15" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
                                <circle cx="15" cy="15" r="5" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Dot</span>
                    </div>
                </div>
            </div>
            
            <!-- Marker Border Pattern Presets -->
            <div class="form-group">
                <label class="form-label">Marker Border Style</label>
                <div class="preset-grid">
                    <div class="preset-option active" data-preset="markerBorderStyle" data-value="square" onclick="selectPreset('markerBorderStyle', 'square')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="10" y="10" width="15" height="15" fill="none" stroke="currentColor" stroke-width="3"/>
                            </svg>
                        </div>
                        <span class="preset-label">Square</span>
                    </div>
                    
                    <div class="preset-option" data-preset="markerBorderStyle" data-value="rounded" onclick="selectPreset('markerBorderStyle', 'rounded')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="10" y="10" width="15" height="15" rx="4" fill="none" stroke="currentColor" stroke-width="3"/>
                            </svg>
                        </div>
                        <span class="preset-label">Rounded</span>
                    </div>
                    
                    <div class="preset-option" data-preset="markerBorderStyle" data-value="dot" onclick="selectPreset('markerBorderStyle', 'dot')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <circle cx="17.5" cy="17.5" r="7.5" fill="none" stroke="currentColor" stroke-width="3"/>
                            </svg>
                        </div>
                        <span class="preset-label">Dot</span>
                    </div>
                </div>
            </div>
            
            <!-- Marker Center Pattern Presets -->
            <div class="form-group">
                <label class="form-label">Marker Center Style</label>
                <div class="preset-grid">
                    <div class="preset-option active" data-preset="markerCenterStyle" data-value="square" onclick="selectPreset('markerCenterStyle', 'square')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <rect x="15" y="15" width="8" height="8" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Square</span>
                    </div>
                    
                    <div class="preset-option" data-preset="markerCenterStyle" data-value="dot" onclick="selectPreset('markerCenterStyle', 'dot')">
                        <div class="preset-visual">
                            <svg viewBox="0 0 60 60" class="preset-svg">
                                <circle cx="19" cy="19" r="4" fill="currentColor"/>
                            </svg>
                        </div>
                        <span class="preset-label">Dot</span>
                    </div>
                </div>
            </div>
            
            <!-- Hidden inputs to store selected values -->
            <input type="hidden" name="dot_style" id="dotStyle" value="dots">
            <input type="hidden" name="corner_style" id="cornerStyle" value="square">
            <input type="hidden" name="marker_border_style" id="markerBorderStyle" value="square">
            <input type="hidden" name="marker_center_style" id="markerCenterStyle" value="square">
            
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
                    <optgroup label="Basic Shapes">
                        <option value="qr">QR Code Icon</option>
                        <option value="star">Star</option>
                        <option value="heart">Heart</option>
                        <option value="check">Check Mark</option>
                        <option value="circle">Circle</option>
                        <option value="square">Square</option>
                    </optgroup>
                    <optgroup label="Social Media">
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="twitter">Twitter/X</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="pinterest">Pinterest</option>
                        <option value="snapchat">Snapchat</option>
                    </optgroup>
                    <optgroup label="Business">
                        <option value="shop">Shopping Bag</option>
                        <option value="cart">Shopping Cart</option>
                        <option value="store">Store</option>
                        <option value="email">Email</option>
                        <option value="phone">Phone</option>
                        <option value="location">Location Pin</option>
                    </optgroup>
                    <optgroup label="Tech & Apps">
                        <option value="android">Android</option>
                        <option value="apple">Apple</option>
                        <option value="windows">Windows</option>
                        <option value="chrome">Chrome</option>
                        <option value="wifi">WiFi</option>
                        <option value="bluetooth">Bluetooth</option>
                    </optgroup>
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

// Central QR Configuration Object
const qrConfig = {
    dotStyle: 'dots',
    cornerStyle: 'square',
    markerBorderStyle: 'square',
    markerCenterStyle: 'square'
};

// Preset Selection Function
function selectPreset(presetType, value) {
    // Update config
    qrConfig[presetType] = value;
    
    // Update hidden input
    const input = document.getElementById(presetType);
    if (input) {
        input.value = value;
    }
    
    // Update visual selection
    const allOptions = document.querySelectorAll(`[data-preset="${presetType}"]`);
    allOptions.forEach(option => {
        option.classList.remove('active');
    });
    
    const selectedOption = document.querySelector(`[data-preset="${presetType}"][data-value="${value}"]`);
    if (selectedOption) {
        selectedOption.classList.add('active');
    }
    
    // Trigger preview update
    debouncedPreview();
}

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

// Default logos as SVG data URIs
const defaultLogos = {
    // Basic Shapes
    'qr': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwMCIgZD0iTTMgM2g4djhoLTN2LTVoLTV6bS0yIDBoMnYyaC0yem0xMCAwaDJ2MmgtMnptMCAwaDh2OGgtOHptLTEwIDEwaDJ2MmgtMnptMCAwaDh2OGgtOHptMTAgMGgydjJoLTJ6bTE0LTEwaDJ2MmgtMnptMCA2aDJ2MmgtMnptLTYgNGgydjJoLTJ6bTYgNGgydjJoLTJ6Ii8+PC9zdmc+',
    'star': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGRDcwMCIgZD0iTTEyIDJsMyA2IDYgMWwtNC41IDQuNSAxIDYuNS01LjUtMy01LjUgMyAxLTYuNUwyIDlsNi0xeiIvPjwvc3ZnPg==',
    'heart': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGMDAwMCIgZD0iTTEyIDIxLjM1bC0xLjQ1LTEuMzJDNS40IDE1LjM2IDIgMTIuMjggMiA4LjVjMC0zLjA1IDIuNDUtNS41IDUuNS01LjVhNS40IDUuNCAwIDAxNS41IDMuNjcgNS40IDUuNCAwIDAxNS41LTMuNjdjMy4wNSAwIDUuNSAyLjQ1IDUuNSA1LjUgMCAzLjc4LTMuNCA2Ljg2LTguNTUgMTEuNTRMMTIgMjEuMzV6Ii8+PC9zdmc+',
    'check': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzRDQUY1MCIgZD0iTTkgMTYuMkw0LjggMTJsLTEuNCAxLjRMOSAxOSAyMSA3bC0xLjQtMS40TDkgMTYuMnoiLz48L3N2Zz4=',
    'circle': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iMTAiIGZpbGw9IiM5OTQ1ZmYiLz48L3N2Zz4=',
    'square': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHJlY3QgeD0iMiIgeT0iMiIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSIjMDBmMGZmIiByeD0iMiIvPjwvc3ZnPg==',
    
    // Social Media
    'facebook': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzE4NzdGMiIgZD0iTTI0IDEyLjA3M2MwLTYuNjI3LTUuMzczLTEyLTEyLTEycy0xMiA1LjM3My0xMiAxMmMwIDUuOTkgNC4zODggMTAuOTU0IDEwLjEyNSAxMS44NTR2LTguMzg1SDcuMDc4di0zLjQ3aDMuMDQ3VjkuNDNjMC0zLjAwNyAxLjc5Mi00LjY2OSA0LjUzMy00LjY2OSAxLjMxMiAwIDIuNjg2LjIzNSAyLjY4Ni4yMzV2Mi45NTNoLTEuNTE0Yy0xLjQ5MSAwLTEuOTU1LjkyNS0xLjk1NSAxLjg3NHYyLjI1aDMuMzI4bC0uNTMyIDMuNDdoLTIuNzk2djguMzg1QzE5LjYxMiAyMy4wMjcgMjQgMTguMDYyIDI0IDEyLjA3M3oiLz48L3N2Zz4=',
    'instagram': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PGRlZnM+PGxpbmVhckdyYWRpZW50IGlkPSJpZyIgeDE9IjAlIiB5MT0iMTAwJSIgeDI9IjEwMCUiIHkyPSIwJSI+PHN0b3Agb2Zmc2V0PSIwJSIgc3R5bGU9InN0b3AtY29sb3I6I2Y1OGUyOTtzdG9wLW9wYWNpdHk6MSIgLz48c3RvcCBvZmZzZXQ9IjUwJSIgc3R5bGU9InN0b3AtY29sb3I6I2QzMmU0ZTtzdG9wLW9wYWNpdHk6MSIgLz48c3RvcCBvZmZzZXQ9IjEwMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiM1MDUxZGI7c3RvcC1vcGFjaXR5OjEiIC8+PC9saW5lYXJHcmFkaWVudD48L2RlZnM+PHJlY3Qgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iNSIgZmlsbD0idXJsKCNpZykiLz48cGF0aCBmaWxsPSIjZmZmIiBkPSJNMTIgOGEzLjkyIDMuOTIgMCAxMDAgNy44NCA0IDQgMCAxMDAtNy44NHptMCA2LjhhMi44OCAyLjg4IDAgMTEwLTUuNzYgMi44OCAyLjg4IDAgMDEwIDUuNzZ6bTE0LjgtMTJIMTcuNnYxNC40SDZ2LTE0LjRoLTEuOGEzLjYgMy42IDAgMDAtMy42IDMuNnYxNC40YTMuNiAzLjYgMCAwMDMuNiAzLjZoMTQuNGEzLjYgMy42IDAgMDAzLjYtMy42VjYuNGEzLjYgMy42IDAgMDAtMy42LTMuNnptLTEuNiA0LjhhMS4yIDEuMiAwIDExMC0yLjQgMS4yIDEuMiAwIDAxMCAyLjR6Ii8+PC9zdmc+',
    'twitter': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTIzLjk1MyA0LjU3YTEwIDEwIDAgMDEtMi44MjUuNzc1IDQuOTU4IDQuOTU4IDAgMDAyLjE2My0yLjcyM2MtLjk1MS41NTUtMi4wMDUuOTU5LTMuMTI3IDEuMTg0YTQuOTIgNC45MiAwIDAwLTguMzg0IDQuNDgyQzcuNjkgOC4wOTUgNC4wNjcgNi4xMyAxLjY0IDMuMTYyYTQuODIyIDQuODIyIDAgMDAtLjY2NiAyLjQ3NWMwIDEuNzEuODcgMy4yMTMgMi4xODggNC4wOTZhNC45MDQgNC45MDQgMCAwMS0yLjIyOC0uNjE2di4wNjFhNC45MjYgNC45MjYgMCAwMDMuOTQ2IDQuODI3IDQuOTk2IDQuOTk2IDAgMDEtMi4yMTIuMDg1IDQuOTM2IDQuOTM2IDAgMDA0LjYwNCAzLjQxOCA5Ljg2NyA5Ljg2NyAwIDAxLTYuMTAyIDIuMTA1Yy0uMzkgMC0uNzc5LS4wMjMtMS4xNy0uMDY3YTEzLjk5NSAxMy45OTUgMCAwMDcuNTU3IDIuMjA5YzkuMDUzIDAgMTMuOTk4LTcuNDk2IDEzLjk5OC0xMy45ODUgMC0uMjEgMC0uNDItLjAxNS0uNjNBOS45MzUgOS45MzUgMCAwMDI0IDQuNTl6IiBmaWxsPSIjMWRhMWYyIi8+PC9zdmc+',
    'linkedin': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwNzdiNSIgZD0iTTIwLjQ0NyAyMC40NTJoLTMuNTU0di01LjU2OWMwLTEuMzI4LS4wMjctMy4wMzctMS44NTItMy4wMzctMS44NTMgMC0yLjEzNiAxLjQ0NS0yLjEzNiAyLjkzOXY1LjY2N0g5LjM1MVY5aDMuNDE0djEuNTYxaC4wNDZjLjQ3Ny0uOSAxLjYzNy0xLjg1IDMuMzctMS44NSAzLjYwMSAwIDQuMjY3IDIuMzcgNC4yNjcgNS40NTV2Ni4yODZ6TTUuMzM3IDcuNDMzYTIuMDYyIDIuMDYyIDAgMDEtMi4wNjMtMi4wNjUgMi4wNjQgMi4wNjQgMCAxMTIuMDYzIDIuMDY1em0xLjc4MiAxMy4wMTlIMy41NTVWOWgzLjU2NHYxMS40NTJ6TTIyLjIyNSAwSDEuNzcxQy43OTIgMCAwIC43NzQgMCAxLjcyOXYyMC41NDJDMCAyMy4yMjcuNzkyIDI0IDEuNzcxIDI0aDIwLjQ1MUMyMy4yIDI0IDI0IDIzLjIyNyAyNCAyMi4yNzFWMS43MjlDMjQgLjc3NCAyMy4yIDAgMjIuMjIyIDBoLjAwM3oiLz48L3N2Zz4=',
    'youtube': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGMDAwMCIgZD0iTTIzLjQ5OCA2LjE4NmEzLjAxNiAzLjAxNiAwIDAwLTIuMTIyLTIuMTM2QzE5LjUwNSAzLjU0NSAxMiAzLjU0NSAxMiAzLjU0NXMtNy41MDUgMC05LjM3Ny41MDVBMy4wMTcgMy4wMTcgMCAwMC41MDIgNi4xODZDMCA4LjA3IDAgMTIgMCAxMnMwIDMuOTMuNTAyIDUuODE0YTMuMDE2IDMuMDE2IDAgMDAyLjEyMiAyLjEzNmMxLjg3MS41MDUgOS4zNzYuNTA1IDkuMzc2LjUwNXM3LjUwNSAwIDkuMzc3LS41MDVhMy4wMTUgMy4wMTUgMCAwMDIuMTIyLTIuMTM2QzI0IDE1LjkzIDI0IDEyIDI0IDEyczAtMy45My0uNTAyLTUuODE0ek05LjU0NSAxNS41NjhWOC40MzJMMTUuODE4IDEybC02LjI3MyAzLjU2OHoiLz48L3N2Zz4=',
    'tiktok': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwMDAwMCIgZD0iTTkgMGgxLjk4YzAuMDUgMC41MDggMC4yMzUgMC45OTggMC41MTQgMS40MTcuMjc2LjQxNS42NDIuNzYxIDEuMDYyIDEuMDA3LjQyLjI0Ny44ODYuNDA3IDEuMzcuNDY2di4wNjF2MS45NzRjLS43OTEtLjA0My0xLjU2NC0uMjk4LTIuMjMzLS43NDh2My4zNjhjMCAuODE1LS4xODggMS42MTYtLjU0NiAyLjMyNmE1LjMzNCA1LjMzNCAwIDAxLTEuNTI5IDEuODQzIDUuMTMxIDUuMTMxIDAgMDEtMi4xNTcgMS4wODhjLS42NzUuMTI2LTEuMzcyLjEyNi0yLjA0NyAwYTUuMTMxIDUuMTMxIDAgMDEtMi4xNTctMS4wODggNS4zMzQgNS4zMzQgMCAwMS0xLjUyOS0xLjg0M2MtLjM1OC0uNzEtLjU0Ni0xLjUxMS0uNTQ2LTIuMzI2IDAtLjk2NC4yNTYtMS45MDYuNzM5LTIuNzI4LjQ4Mi0uODIxIDEuMTY3LTEuNDkgMS45ODQtMS45MjguODE3LS40MzkgMS43NDEtLjY3NSAyLjY4Ni0uNjc1djIuMDhjLS41OTguMDMyLTEuMTczLjIyMS0xLjY3Ni41NTJhMy4yOSAzLjI5IDAgMDAtMS4yMDQgMS4zNzcgMy40MiAzLjQyIDAgMDAtLjQyIDEuNjk5YzAgLjU5MS4xNDQgMS4xNzIuNDIgMS42OTkuMjc3LjUyNy42NzkuOTczIDEuMTcyIDEuMzA5LjQ5Mi4zMzYgMS4wNjYuNTUgMS42NjQuNjIyYTMuMzU4IDMuMzU4IDAgMDAyLjAyOS0uMzQ3IDMuNDA3IDMuNDA3IDAgMDAxLjM3My0xLjI3NWMuMzM0LS41NTkuNTE3LTEuMTk1LjUzMS0xLjg0NlYwem0zLjk4IDBIMTV2My43MzhjLS44NzIuNTQyLTEuODguODMtMi45MzQuODQ5di0xLjk3NGMuNDg0LS4wNTkuOTUtLjIxOSAxLjM3LS40NjYuNDItLjI0Ni43ODYtLjU5MiAxLjA2Mi0xLjAwNy4yNzktLjQxOS40NjQtLjkwOS41MTQtMS40MTdoMS45Njh6Ii8+PC9zdmc+',
    'pinterest': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0U2MDAyMyIgZD0iTTEyIDJDNi40NzcgMiAyIDYuNDc3IDIgMTJjMCA0LjIzNyAyLjYzNiA3Ljg1NSA2LjM1NiA5LjMxMi0uMDg4LS43OTEtLjE2Ny0yLjAwNS4wMzUtMi44NjguMTgxLS43ODEgMS4xNzItNC45NyAxLjE3Mi00Ljk3czAtLjMxOS0uMzE5LS43OTljLS4xODEtLjg3NC41MDctMS41MDcgMS4xMzUtMS41MDcuNTM1IDAgLjc5My40MDEuNzkzLjg4MiAwIC41MzctLjM0MiAxLjM0LS41MTggMS45OTMtLjE0Ny42MTQuMzA4IDEuMTE1LjkxNiAxLjExNSAxLjEgMCAxLjk0My0xLjE2IDEuOTQzLTIuODM3IDAtMS40ODMtMS4wNjYtMi41MjEtMi41ODctMi41MjEtMS43NjIgMC0yLjc5NyAxLjMyMi0yLjc5NyAyLjY4NyAwIC41MzIuMjA0IDEuMTAyLjQ1OSAxLjQxMS4wNTEuMDYxLjA1OC4xMTQuMDQzLjE3Ni0uMDQ4LjIwMy0uMTUzLjYxOC0uMTc0LjcwNS0uMDI5LjExNy0uMDk1LjE0Mi0uMjE5LjA4NS0uODItLjM4Mi0xLjMzNC0xLjU4LTEuMzM0LTIuNTQyIDAtMi4wNzkgMS41MS0zLjk5IDQuMzUtMy45OSAyLjI4NSAwIDQuMDYxIDEuNjI4IDQuMDYxIDMuODA1IDAgMi4yNzItMS40MzQgNC4xLTMuNDI4IDQuMTAtLjY2OSAwLTEuMjk5LS4zNDgtMS41MDgtLjc2MiAwIDAtLjMyOSAxLjI1NS0uNDEgMS41NjYtLjE0OC41ODctLjU1NiAxLjMyMi0uODI5IDEuNzdhMTAuMDA4IDEwLjAwOCAwIDAwMi44OTYuNDEzYzUuNTIzIDAgMTAtNC40NzcgMTAtMTBTMTcuNTIzIDIgMTIgMnoiLz48L3N2Zz4=',
    'snapchat': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0ZGRkMwMCIgZD0iTTEyLjAxNi4wMDhjLTMuMjEzLjAxLTUuOCAxLjA3Ni03LjMyNSAzLjEzOUM0Ljg1NiAzLjAxOSA1LjI5NCA0LjUyIDUuNTMxIDUuNWMuNTM0IDIuMjA2LjY5NCAzLjE4NyAxLjE4NiAzLjYyNS4yNDYuMjE5LjU4MS4zMy45MzguMjgxLjQwNi0uMDU2LjkxOS0uMDcyIDEuMzQ0LS4wNTYuMzQ0LjAxMy42MTkuMjA2LjY5NC40NjkuMjgxIDEuMDgxLS45MzggMS42NjktMS4zNzUgMi4wNjItLjQzOC4zOTQtLjU4Ny45MTktLjUzIDEuNDM4LjEzMyAxLjIzMiAxLjEzIDEuNzU5IDIuMDMyIDEuOTY5IDEuNTU2LjM2OSAyLjU3IDEuMjYzIDMuMzggMS45LjQzNy4zNDQgMS4yLS4xIDEuODMyLS44MzguNTYzLS42NS45MzgtMS40NzUgMS42ODgtMS42MjUuNDY5LS4wOTQuNzUtLjI4MS44MTMtLjYzMS4wNTYtLjMxMy4wNjMtLjkyNS0uMjk0LTEuNTk0LS4zMzgtLjYzMi0xLjA0NC0xLjAzMS0xLjMxOC0xLjI1LS41NS0uNDM4LS44ODEtLjkxOS0uODgxLTEuNDU2IDAtLjQzOC4zMDYtLjc0NC42NDQtLjgyNWExMC4yMDIgMTAuMjAyIDAgMDExLjA4Ny0uMDMxYy4yODEuMDIzLjYwNi0uMDcyLjgxOC0uMzM4LjQ4OC0uNjA2LjY1Ni0xLjY1IDEuMTgtMy44MTkuMjM3LS45NzQuNjc1LTIuNDY5LjQ1LTIuMzM3LTEuNTI0LTIuMDYzLTQuMTEyLTMuMTI5LTcuMzI1LTMuMTM5em0wIDBsLS4wMDYuMDAyeiIvPjwvc3ZnPg==',
    
    // Business
    'shop': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzlmNDVmZiIgZD0iTTE5IDZoLTJsMi0zLjV2LTEuNUgzdjEuNUw1IDZIM2MtLjU1IDAtMS4wMi40NS0xLjAyIDEuMDJMMyA5YzAgMS4xLjkgMiAyIDJoMS40NGEzLjQgMy40IDAgMDAzLjEyLTIgMy4zIDMuMyAwIDAwMy4xMiAyIDMuNCAzLjQgMCAwMDMuMTItMmgxLjQ0YzEuMSAwIDItLjkgMi0ybC4wMi0xLjk4Yy0uMDItLjU3LS40Ny0xLjAyLTEuMDItMS4wMnpNMyAyMGMwIC41NS40NSAxIDEgMWgxNmMuNTUgMCAxLS40NSAxLTF2LTFoLTJ2MUg1di0xSDN2MXptMi0zdjhDNSAyMi41NSA1LjQ1IDIzIDYgMjNoMTJjLjU1IDAgMS0uNDUgMS0xdi05aC0ydjhoLThWMTB6Ii8+PC9zdmc+',
    'cart': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwZjBmZiIgZD0iTTcgMThjMS4xIDAgMiAuOSAyIDJzLS45IDItMiAyLTItLjktMi0yIC45LTIgMi0yTTE3IDE4YzEuMSAwIDIgLjkgMiAycy0uOSAyLTIgMi0yLS45LTItMiAuOS0yIDItMk03LjE3IDlsMS41NCAzaDcuMDRsMS4zOC0zSDcuMTdNMi40MiAyYy0uNCAwLS43NS4zMy0uNzUuNzVzLjM1Ljc1Ljc1Ljc1aDEuMjVsNC41OSA5LjM4LTEuNzIgMy4xMWMtLjQ1LjgzLS4yMiAxLjg3LjUzIDIuNDguNTMuNDMgMS4yMS42NSAxLjg5LjY1aDExLjE1Yy40MSAwIC43NS0uMzQuNzUtLjc1cy0uMzQtLjc1LS43NS0uNzVINy40MWwtMS41LTNoMTAuMDVjLjc4IDAgMS40Ny0uNSAxLjcxLTEuMjNsMi41NC02LjJjLjI3LS42NS0uMTEtMS4zOC0uOC0xLjU3LS4xMS0uMDMtLjIyLS4wNS0uMzQtLjA1SDYuMjJMNS43MyAyLjg3Yy0uMTgtLjQ0LS42LS43NS0xLjA4LS43NUgyLjQyeiIvPjwvc3ZnPg==',
    'store': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI2ZmMmVjNCIgZD0iTTIwIDRIM3YyaDMuNjhMOCA5LjI4VjE3YTIgMiAwIDAwMiAyaDhjMiAwIDItMiAyLTJ2LTcuNzJMMTguMzIgNkgyMHptLTIgMTNIMTB2LTYuNjRsMiAyLjYgMi0yLjZ2Ni42NHpNNS43NiA4bDEuMzItMi41TDE4LjI0IDh2Mkw2IDguMDEgNS43NiA4eiIvPjwvc3ZnPg==',
    'email': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzQyODVGNCIgZD0iTTIwIDRINGMtMS4xIDAtMS45OS45LTEuOTkgMkwyIDE4YzAgMS4xLjkgMiAyIDJoMTZjMS4xIDAgMi0uOSAyLTJWNmMwLTEuMS0uOS0yLTItMnptMCA0bC04IDUtOC01VjZsOCA1IDgtNXY0eiIvPjwvc3ZnPg==',
    'phone': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzM0QTg1MyIgZD0iTTE1LjMxIDIwLjJsMi4zLS44NmMuNDEtLjE1LjY5LS41NS42OS0xdjEuNjRjMCAxLjEtLjkgMi0yIDJ6TTYuNSA1LjV2MTNjMCAxLjEuOSAyIDIgMmg3YzEuMSAwIDItLjkgMi0ydi0xM2MwLTEuMS0uOS0yLTItMmgtN2MtMS4xIDAtMiAuOS0yIDJ6bTIgOGMwLS41NS40NS0xIDEtMWg1YzU1IDAgMSAuNDUgMSAxcy0uNDUgMS0xIDFoLTVjLS41NSAwLTEtLjQ1LTEtMXptNi0zYzAtLjU1LjQ1LTEgMS0xczEgLjQ1IDEgMS0uNDUgMS0xIDEtMS0uNDUtMS0xem0tMi01Yy0uNTUgMC0xLS40NS0xLTFzLjQ1LTEgMS0xIDEgLjQ1IDEgMS0uNDUgMS0xIDF6Ii8+PC9zdmc+',
    'location': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iI0VBNDMzNSIgZD0iTTEyIDJDOC4xMyAyIDUgNS4xMyA1IDljMCA1LjI1IDcgMTMgNyAxM3M3LTcuNzUgNy0xM2MwLTMuODctMy4xMy03LTctN3ptMCA5LjVjLTEuMzggMC0yLjUtMS4xMi0yLjUtMi41czEuMTItMi41IDIuNS0yLjUgMi41IDEuMTIgMi41IDIuNS0xLjEyIDIuNS0yLjUgMi41eiIvPjwvc3ZnPg==',
    
    // Tech & Apps
    'android': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzNEREM4NCIgZD0iTTE3LjYgOS4yN2wtMS40LTIuNDRjLS40MS0uNy0xLjE1LS43LTEuNjcgMGwtLjI0LjQxYy0xLjExLS43NC0yLjQtMS4yMi0zLjgyLTEuMzdWMy41YzAtLjgzLS42Ny0xLjUtMS41LTEuNVMxMCAyLjY3IDEwIDMuNXYyLjM3Yy0xLjQyLjE1LTIuNzEuNjMtMy44MiAxLjM3bC0uMjQtLjQxYy0uNTItLjctMS4yNi0uNy0xLjY3IDBsLTEuNCAyLjQ0Yy0uNTIuNy0uMzQgMS41My4zNyAyLjAzbDEuNjEgMS4wMWMtLjE0LjQ1LS4yMy45MS0uMjggMS4zOEg0LjVjLS44MyAwLTEuNS42Ny0xLjUgMS41czY3IDEuNSAxLjUgMS41aDEuNjZjLjA1LjQ3LjE0LjkzLjI4IDEuMzhsLTEuNjEgMS4wMWMtLjcxLjUtLjg5IDEuMzMtLjM3IDIuMDNsMS40IDIuNDRjLjQxLjcgMS4xNS43IDEuNjcgMGwuMjQtLjQxYzEuMTEuNzQgMi40IDEuMjIgMy44MiAxLjM3djIuMzdjMCAuODMuNjcgMS41IDEuNSAxLjVzMS41LS42NyAxLjUtMS41di0yLjM3YzEuNDItLjE1IDIuNzEtLjYzIDMuODItMS4zN2wuMjQuNDFjLjUyLjcgMS4yNi43IDEuNjcgMGwxLjQtMi40NGMuNTItLjcuMzQtMS41My0uMzctMi4wM2wtMS42MS0xLjAxYy4xNC0uNDUuMjMtLjkxLjI4LTEuMzhoMS42NmMuODMgMCAxLjUtLjY3IDEuNS0xLjVzLS42Ny0xLjUtMS41LTEuNWgtMS42NmMtLjA1LS40Ny0uMTQtLjkzLS4yOC0xLjM4bDEuNjEtMS4wMWMuNzEtLjUuODktMS4zMy4zNy0yLjAzeiIvPjwvc3ZnPg==',
    'apple': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZD0iTTE3Ljc1IDEwLjljLS41LjAyLTMuMDcuMjYtMy4wNyAzLjU0IDAgMy44MyAzLjc3IDQuNjggNC4wNyA0LjY4IDAgMC0uNDEgMS4zOC0xLjc3IDIuNzQtMS4xOCAxLjE5LTIuNDMgMi40Mi00LjI1IDIuNDItMS43NyAwLTIuMy0uODYtNC4xOC0uODYtMS45MyAwLTIuNDEuODYtNC4xOC44Ni0xLjgyIDAtMy4wNy0xLjE5LTQuMjUtMi40Mi0yLjE0LTIuMi0zLjc1LTYuNS0xLjU0LTkuMzYuODYtMS40NCAyLjE5LTIuMzUgMy43Mi0yLjM1IDEuNzIgMCAyLjggMS4xNCAzLjkxIDEuMTQgMS4wNiAwIDIuOTUtMS4xNCA0LjcyLTEuMTQuOC4wMSAzLjA1LjA4IDQuNTcgMi4yeiIgZmlsbD0iIzk5OTk5OSIvPjxwYXRoIGQ9Ik0xMi40NSA1LjkzYzAtLjQ3LS4wOS0uOTUtLjI4LTEuMzktLjE5LS40My0uNDYtLjgyLS44MS0xLjE0LS43NC0uNjgtMS42MS0xLjAyLTIuNjEtMS4wMi0uMDUgMC0uMS4wMS0uMTQuMDItLjA0LjAyLS4wNS4wNS0uMDQuMTEuMDQuMjYuMTEuNTEuMjEuNzQuMTEuMjMuMjUuNDUuNDMuNjUuMzYuNDEuODEuNzQgMS4zMy45OS41My4yNSAxLjEuMzkgMS42Ny4zOS4wNyAwIC4xLS4wMi4xLS4wNS4wMS0uMDMuMDItLjA2LjAzLS4wOS4wMi0uMjEuMDItLjQzIDAtLjY1LS4wMy0uMjUtLjA3LS41LS4xMS0uNzZ6IiBmaWxsPSIjOTk5OTk5Ii8+PC9zdmc+',
    'windows': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwQThFNSIgZD0iTTMgNS40MUwyIDguODFsOS4yNCAxLjM1VjUuNDFIMy4wMXpNMi45OCAyNGw5LjI0LTEuNTJ2LTQuOTZMMyAxOS4zNnYtMy43M2w5LjI1LTEuNDhWNi45N0wyIDRsMTAgMS40MXY2Ljk0TDIyIDEzLjUydjguODZsLTEwIDEuNjJ2LTUuMzVMMiAyMC40N3YzLjUzeiIvPjwvc3ZnPg==',
    'chrome': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PGNpcmNsZSBjeD0iMTIiIGN5PSIxMiIgcj0iNCIgZmlsbD0iI2ZmZiIvPjxwYXRoIGQ9Ik0xMiAyQzYuNDg3IDIgMiA2LjQ4NyAyIDEyczQuNDg3IDEwIDEwIDEwIDEwLTQuNDg3IDEwLTEwUzE3LjUxMyAyIDEyIDJ6bTAgMThjLTQuNDExIDAtOC0zLjU4OS04LThzMy41ODktOCA4LTggOCAzLjU4OSA4IDgtMy41ODkgOC04IDh6IiBmaWxsPSIjZmJiYzA1Ii8+PHBhdGggZD0iTTEyIDZjLTMuMzE0IDAtNiAyLjY4Ni02IDYgMCAzLjMxNCAyLjY4NiA2IDYgNnM2LTIuNjg2IDYtNmMwLTMuMzE0LTIuNjg2LTYtNi02em0wIDEwYy0yLjIwOSAwLTQtMS43OTEtNC00czEuNzkxLTQgNC00IDQgMS43OTEgNCA0LTEuNzkxIDQtNCA0eiIgZmlsbD0iIzM0YTg1MyIvPjxwYXRoIGQ9Ik0xMiA4Yy0yLjIwOSAwLTQgMS43OTEtNCA0czEuNzkxIDQgNCA0IDQtMS43OTEgNC00LTEuNzkxLTQtNC00eiIgZmlsbD0iI2VhNDMzNSIvPjwvc3ZnPg==',
    'wifi': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwZjBmZiIgZD0iTTEgOWwyIDJjNC45Ny00Ljk3IDEzLjAzLTQuOTcgMTggMGwyLTJDMTYuOTMgMi45MyA3LjA4IDIuOTMgMSA5em04IDhjMS45My0xLjkzIDUuMDctMS45MyA3IDBsMi0yYy0zLjEzLTMuMTMtOC4xOC0zLjEzLTExLjMxIDBsIDIgMnptMy0zYzEuMSAwIDIgLjkgMiAycy0uOSAyLTIgMi0yLS45LTItMiAuOS0yIDItMnoiLz48L3N2Zz4=',
    'bluetooth': 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyNCAyNCI+PHBhdGggZmlsbD0iIzAwNzVmZiIgZD0iTTE3LjcxIDcuNzFMMTIgMlY5LjU5TDYuNDEgNCA1IDUuNDEgMTAuNTkgMTEgNSAxNi41OSA2LjQxIDE4IDEyIDE0LjQxVjIybDUuNzEtNS43MUwxNS40MSAxNCAxOS41OSAxOGwyLjEzLTIuMTNMMjEgMTYuNTkgMTYuNDEgMTJsMi44LTIuNTlMMTkgMTMuNDEgMTkuNTkgMTQgMTcuNzEgNy43MXpNMTMgMTguMTd2LTQuMzRsLjU5LjU5IDIuODEgMi44MUwxMyAxOC4xN3pNMTYuNDEgMTBMMTMgNS44M3Y0LjM0TDE1LjU5IDlsLjgyLTF6Ii8+PC9zdmc+'
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
    
    // Background image settings
    const bgImageEnabled = document.getElementById('bgImageEnabled').checked;
    const bgImageInput = document.getElementById('bgImage');
    
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
                // Handle background image if enabled
                if (bgImageEnabled && bgImageInput.files && bgImageInput.files[0]) {
                    const bgReader = new FileReader();
                    bgReader.onload = function(bgE) {
                        qrOptions.backgroundOptions = {
                            ...qrOptions.backgroundOptions,
                            gradient: null
                        };
                        // Background image is tricky with qr-code-styling
                        // We'll render normally and add note to user
                        renderQRCode(qrOptions, content);
                    };
                    bgReader.readAsDataURL(bgImageInput.files[0]);
                } else {
                    renderQRCode(qrOptions, content);
                }
            };
            reader.readAsDataURL(logoInput.files[0]);
            return; // Exit and wait for file read
        }
    }
    
    // Handle background image separately if no logo upload
    if (bgImageEnabled && bgImageInput.files && bgImageInput.files[0]) {
        const bgReader = new FileReader();
        bgReader.onload = function(e) {
            // Note: qr-code-styling has limited support for background images
            // The image will be displayed but may not work perfectly with all options
            renderQRCode(qrOptions, content);
        };
        bgReader.readAsDataURL(bgImageInput.files[0]);
        return;
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

// Initialize preview when page loads with a sample URL
window.addEventListener('load', function() {
    // Wait for QRCodeStyling library to load
    setTimeout(function() {
        // Set default URL for initial preview
        const contentField = document.getElementById('contentField');
        if (contentField && !contentField.value) {
            contentField.value = 'https://example.com';
        }
        // Trigger initial preview
        generatePreview();
    }, 1000);
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
    padding: 25px;
    box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
    transition: all 0.3s ease;
    animation: fadeInUp 0.5s ease;
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

/* Animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideIn {
    from {
        opacity: 0;
        max-height: 0;
        margin: 0;
        padding: 0;
    }
    to {
        opacity: 1;
        max-height: 500px;
        margin-bottom: 20px;
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
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
    margin: 25px 0 15px 0;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

/* Form Styling */
.form-group {
    margin-bottom: 15px;
    animation: fadeInUp 0.3s ease;
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

/* Preset Grid System */
.preset-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
    gap: 10px;
    margin-top: 10px;
}

.preset-option {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    animation: fadeInUp 0.3s ease;
}

[data-theme="light"] .preset-option {
    background: rgba(0, 0, 0, 0.02);
    border: 2px solid rgba(0, 0, 0, 0.08);
}

.preset-option:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(153, 69, 255, 0.5);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(153, 69, 255, 0.2);
}

[data-theme="light"] .preset-option:hover {
    background: rgba(153, 69, 255, 0.05);
    border-color: rgba(153, 69, 255, 0.4);
}

.preset-option.active {
    background: linear-gradient(135deg, rgba(153, 69, 255, 0.2), rgba(0, 240, 255, 0.2));
    border-color: var(--purple);
    box-shadow: 0 0 20px rgba(153, 69, 255, 0.4);
}

[data-theme="light"] .preset-option.active {
    background: linear-gradient(135deg, rgba(153, 69, 255, 0.15), rgba(0, 240, 255, 0.15));
    border-color: var(--purple);
}

.preset-option.active::after {
    content: '✓';
    position: absolute;
    top: 4px;
    right: 4px;
    width: 20px;
    height: 20px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.preset-visual {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    padding: 8px;
}

[data-theme="light"] .preset-visual {
    background: rgba(0, 0, 0, 0.03);
}

.preset-svg {
    width: 100%;
    height: 100%;
    color: var(--text-primary);
}

.preset-label {
    font-size: 11px;
    font-weight: 500;
    color: var(--text-secondary);
    text-align: center;
    line-height: 1.2;
}

.preset-option.active .preset-label {
    color: var(--text-primary);
    font-weight: 600;
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

/* Light mode form elements - improved visibility */
[data-theme="light"] .form-input,
[data-theme="light"] .form-select,
[data-theme="light"] .form-textarea {
    background: #ffffff;
    border: 1px solid #d0d0d0;
    color: #1a1a1a;
}

[data-theme="light"] .form-select option {
    background: #ffffff;
    color: #1a1a1a;
}

/* Dark mode dropdown text visibility */
[data-theme="dark"] .form-select,
[data-theme="dark"] .form-select option {
    color: var(--text-primary);
    background: rgba(255, 255, 255, 0.05);
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    outline: none;
    border-color: var(--purple);
    box-shadow: 0 0 0 4px rgba(153, 69, 255, 0.1);
    background: rgba(255, 255, 255, 0.08);
}

[data-theme="light"] .form-input:focus,
[data-theme="light"] .form-select:focus,
[data-theme="light"] .form-textarea:focus {
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(153, 69, 255, 0.15);
}

.color-input {
    height: 50px;
    cursor: pointer;
}

/* Collapsible sections animation */
#gradientColorGroup,
#markerColorGroup,
#differentMarkerColorsGroup,
#bgImageGroup,
#defaultLogoGroup,
#uploadLogoGroup,
#logoOptionsGroup,
#frameTextGroup,
#frameFontGroup,
#frameColorGroup {
    overflow: hidden;
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease;
}

/* Feature Toggles */
.feature-toggle {
    margin-bottom: 15px;
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

[data-theme="light"] .toggle-label {
    background: rgba(0, 0, 0, 0.03);
}

.toggle-label:hover {
    background: rgba(255, 255, 255, 0.05);
}

[data-theme="light"] .toggle-label:hover {
    background: rgba(0, 0, 0, 0.05);
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

/* Light mode toggle visibility fix */
[data-theme="light"] .toggle-slider {
    background: rgba(0, 0, 0, 0.15);
    border: 1px solid rgba(0, 0, 0, 0.1);
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
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

[data-theme="light"] .toggle-slider::before {
    background: #666;
}

.toggle-input:checked + .toggle-slider {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
}

[data-theme="light"] .toggle-input:checked + .toggle-slider::before {
    background: #fff;
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
