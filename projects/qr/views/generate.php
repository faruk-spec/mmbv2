<?php
// Production-Ready QR Code Generator with AI Design
// Futuristic UI with theme integration and live preview
?>

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
                    <option value="location"><i class="fas fa-map-marker-alt"></i> Location</option>
                    <option value="phone"><i class="fas fa-phone"></i> Phone Number</option>
                    <option value="sms"><i class="fas fa-sms"></i> SMS Message</option>
                    <option value="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</option>
                    <option value="skype"><i class="fab fa-skype"></i> Skype</option>
                    <option value="zoom"><i class="fas fa-video"></i> Zoom</option>
                    <option value="wifi"><i class="fas fa-wifi"></i> WiFi Network</option>
                    <option value="vcard"><i class="fas fa-id-card"></i> vCard (Contact)</option>
                    <option value="event"><i class="fas fa-calendar-alt"></i> Event (Calendar)</option>
                    <option value="paypal"><i class="fab fa-paypal"></i> PayPal</option>
                    <option value="payment"><i class="fas fa-credit-card"></i> Payment (UPI)</option>
                </select>
            </div>
            
            <!-- Simple Content Field -->
            <div class="form-group" id="simpleContent">
                <label class="form-label" id="contentLabel">Content</label>
                <textarea name="content" id="contentField" class="form-textarea" rows="4" placeholder="Enter content..."></textarea>
            </div>
            
            <!-- Email Fields -->
            <div id="emailFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Send To</label>
                    <input type="email" name="email_to" id="emailTo" class="form-input" placeholder="recipient@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Subject</label>
                    <input type="text" name="email_subject" id="emailSubject" class="form-input" placeholder="Email subject">
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="email_body" id="emailBody" class="form-textarea" rows="3" placeholder="Email message body..."></textarea>
                </div>
            </div>
            
            <!-- Phone Fields -->
            <div id="phoneFields" style="display: none;">
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Country Code</label>
                        <select name="phone_country" id="phoneCountry" class="form-select">
                            <option value="+1">+1 (US/Canada)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+91">+91 (India)</option>
                            <option value="+86">+86 (China)</option>
                            <option value="+61">+61 (Australia)</option>
                            <option value="+49">+49 (Germany)</option>
                            <option value="+33">+33 (France)</option>
                            <option value="+81">+81 (Japan)</option>
                            <option value="+82">+82 (South Korea)</option>
                            <option value="+55">+55 (Brazil)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" id="phoneNumber" class="form-input" placeholder="1234567890">
                    </div>
                </div>
            </div>
            
            <!-- SMS Fields -->
            <div id="smsFields" style="display: none;">
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Country Code</label>
                        <select name="sms_country" id="smsCountry" class="form-select">
                            <option value="+1">+1 (US/Canada)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+91">+91 (India)</option>
                            <option value="+86">+86 (China)</option>
                            <option value="+61">+61 (Australia)</option>
                            <option value="+49">+49 (Germany)</option>
                            <option value="+33">+33 (France)</option>
                            <option value="+81">+81 (Japan)</option>
                            <option value="+82">+82 (South Korea)</option>
                            <option value="+55">+55 (Brazil)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="sms_number" id="smsNumber" class="form-input" placeholder="1234567890">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Message</label>
                    <textarea name="sms_message" id="smsMessage" class="form-textarea" rows="3" placeholder="SMS message..."></textarea>
                </div>
            </div>
            
            <!-- WhatsApp Fields -->
            <div id="whatsappFields" style="display: none;">
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Country Code</label>
                        <select name="whatsapp_country" id="whatsappCountry" class="form-select">
                            <option value="+1">+1 (US/Canada)</option>
                            <option value="+44">+44 (UK)</option>
                            <option value="+91">+91 (India)</option>
                            <option value="+86">+86 (China)</option>
                            <option value="+61">+61 (Australia)</option>
                            <option value="+49">+49 (Germany)</option>
                            <option value="+33">+33 (France)</option>
                            <option value="+81">+81 (Japan)</option>
                            <option value="+82">+82 (South Korea)</option>
                            <option value="+55">+55 (Brazil)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="whatsapp_phone" id="whatsappPhone" class="form-input" placeholder="1234567890">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Message (Optional)</label>
                    <textarea name="whatsapp_message" id="whatsappMessage" class="form-textarea" rows="3" placeholder="Pre-filled message..."></textarea>
                </div>
            </div>
            
            <!-- Skype Fields -->
            <div id="skypeFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Action Type</label>
                    <select name="skype_action" id="skypeAction" class="form-select">
                        <option value="chat">Chat</option>
                        <option value="call">Call</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Skype Username</label>
                    <input type="text" name="skype_username" id="skypeUsername" class="form-input" placeholder="username">
                </div>
            </div>
            
            <!-- Zoom Fields -->
            <div id="zoomFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Meeting ID</label>
                    <input type="text" name="zoom_meeting_id" id="zoomMeetingId" class="form-input" placeholder="123 456 789">
                </div>
                <div class="form-group">
                    <label class="form-label">Password (Optional)</label>
                    <input type="text" name="zoom_password" id="zoomPassword" class="form-input" placeholder="Meeting password">
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
                    <label class="form-label">Title</label>
                    <input type="text" name="vcard_title" id="vcardTitle" class="form-input" placeholder="Mr. / Ms. / Dr.">
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">First Name</label>
                        <input type="text" name="vcard_firstname" id="vcardFirstName" class="form-input" placeholder="John">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="vcard_lastname" id="vcardLastName" class="form-input" placeholder="Doe">
                    </div>
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Phone (Home)</label>
                        <input type="text" name="vcard_phone_home" id="vcardPhoneHome" class="form-input" placeholder="+1234567890">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone (Mobile)</label>
                        <input type="text" name="vcard_phone_mobile" id="vcardPhoneMobile" class="form-input" placeholder="+1234567890">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="vcard_email" id="vcardEmail" class="form-input" placeholder="john@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Website (URL)</label>
                    <input type="url" name="vcard_website" id="vcardWebsite" class="form-input" placeholder="https://example.com">
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Company</label>
                        <input type="text" name="vcard_company" id="vcardCompany" class="form-input" placeholder="Company Name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="vcard_jobtitle" id="vcardJobTitle" class="form-input" placeholder="Position">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone (Office)</label>
                    <input type="text" name="vcard_phone_office" id="vcardPhoneOffice" class="form-input" placeholder="+1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input type="text" name="vcard_address" id="vcardAddress" class="form-input" placeholder="123 Main Street">
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Post Code</label>
                        <input type="text" name="vcard_postcode" id="vcardPostCode" class="form-input" placeholder="12345">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="vcard_city" id="vcardCity" class="form-input" placeholder="New York">
                    </div>
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">State</label>
                        <input type="text" name="vcard_state" id="vcardState" class="form-input" placeholder="NY">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <input type="text" name="vcard_country" id="vcardCountry" class="form-input" placeholder="USA">
                    </div>
                </div>
            </div>
            
            <!-- Location Fields -->
            <div id="locationFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Search Address</label>
                    <input type="text" name="location_address" id="locationAddress" class="form-input" placeholder="Enter address to search...">
                    <small class="form-help">Enter an address and coordinates will be filled automatically (requires manual entry for now)</small>
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Latitude</label>
                        <input type="text" name="location_lat" id="locationLat" class="form-input" placeholder="40.7128">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Longitude</label>
                        <input type="text" name="location_lng" id="locationLng" class="form-input" placeholder="-74.0060">
                    </div>
                </div>
                <small class="form-help">Tip: You can use Google Maps to find coordinates - right-click on a location</small>
            </div>
            
            <!-- Event Fields -->
            <div id="eventFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="event_title" id="eventTitle" class="form-input" placeholder="Birthday Party">
                </div>
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="event_location" id="eventLocation" class="form-input" placeholder="123 Main St">
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Start Time</label>
                        <input type="datetime-local" name="event_start" id="eventStart" class="form-input">
                    </div>
                    <div class="form-group">
                        <label class="form-label">End Time</label>
                        <input type="datetime-local" name="event_end" id="eventEnd" class="form-input">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Reminder Before Event</label>
                    <select name="event_reminder" id="eventReminder" class="form-select">
                        <option value="">No Reminder</option>
                        <option value="5">5 minutes before</option>
                        <option value="15">15 minutes before</option>
                        <option value="30">30 minutes before</option>
                        <option value="60">1 hour before</option>
                        <option value="120">2 hours before</option>
                        <option value="1440">24 hours before</option>
                        <option value="2880">48 hours before</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Link (Optional)</label>
                    <input type="url" name="event_link" id="eventLink" class="form-input" placeholder="https://example.com/event">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea name="event_notes" id="eventNotes" class="form-textarea" rows="3" placeholder="Additional event details..."></textarea>
                </div>
            </div>
            
            <!-- PayPal Fields -->
            <div id="paypalFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Payment Type</label>
                    <select name="paypal_type" id="paypalType" class="form-select">
                        <option value="buynow">Buy Now</option>
                        <option value="addtocart">Add to Cart</option>
                        <option value="donations">Donations</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Email (to receive payments)</label>
                    <input type="email" name="paypal_email" id="paypalEmail" class="form-input" placeholder="merchant@example.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Item Name</label>
                    <input type="text" name="paypal_item_name" id="paypalItemName" class="form-input" placeholder="Product Name">
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Item ID</label>
                        <input type="text" name="paypal_item_id" id="paypalItemId" class="form-input" placeholder="SKU-123">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Price</label>
                        <input type="number" step="0.01" name="paypal_price" id="paypalPrice" class="form-input" placeholder="10.00">
                    </div>
                </div>
                <div class="grid grid-2" style="gap: 15px;">
                    <div class="form-group">
                        <label class="form-label">Currency</label>
                        <select name="paypal_currency" id="paypalCurrency" class="form-select">
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="INR">INR - Indian Rupee</option>
                            <option value="JPY">JPY - Japanese Yen</option>
                            <option value="AUD">AUD - Australian Dollar</option>
                            <option value="CAD">CAD - Canadian Dollar</option>
                            <option value="CNY">CNY - Chinese Yuan</option>
                            <option value="BRL">BRL - Brazilian Real</option>
                            <option value="MXN">MXN - Mexican Peso</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Shipping</label>
                        <input type="number" step="0.01" name="paypal_shipping" id="paypalShipping" class="form-input" placeholder="5.00">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Tax Rate %</label>
                    <input type="number" step="0.01" name="paypal_tax" id="paypalTax" class="form-input" placeholder="10.00">
                </div>
            </div>
            
            <!-- Payment (UPI) Fields -->
            <div id="paymentFields" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Payment Type</label>
                    <select name="payment_type" id="paymentType" class="form-select">
                        <option value="upi">UPI (India)</option>
                        <option value="paytm">Paytm</option>
                        <option value="phonepe">PhonePe</option>
                        <option value="googlepay">Google Pay</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">UPI ID</label>
                    <input type="text" name="payment_upi_id" id="paymentUpiId" class="form-input" placeholder="username@upi">
                </div>
                <div class="form-group">
                    <label class="form-label">Payee Name (Optional)</label>
                    <input type="text" name="payment_name" id="paymentName" class="form-input" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount (Optional)</label>
                    <input type="number" step="0.01" name="payment_amount" id="paymentAmount" class="form-input" placeholder="100.00">
                </div>
                <div class="form-group">
                    <label class="form-label">Note (Optional)</label>
                    <input type="text" name="payment_note" id="paymentNote" class="form-input" placeholder="Payment for...">
                </div>
            </div>
            
            <div class="divider"></div>
            
            <!-- Design Options -->
            <h4 class="subsection-title collapsible-header" onclick="toggleSection('designOptions')">
                <span><i class="fas fa-palette"></i> Design Options</span>
                <i class="fas fa-chevron-right collapse-icon"></i>
            </h4>
            <div id="designOptions" class="collapsible-content collapsed">
            
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
                        <strong><i class="fas fa-palette"></i> Gradient Foreground</strong>
                        <small>Enable smooth color gradient effect</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="gradientColorGroup" style="display: none;">
                <label class="form-label"><i class="fas fa-palette"></i> Gradient End Color</label>
                <input type="color" name="gradient_color" id="gradientColor" value="#9945ff" class="form-input color-input">
                <small class="help-text">
                    <i class="fas fa-magic"></i> Creates a smooth gradient from foreground color to this color.
                </small>
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
            </div><!-- End Design Options collapsible -->
            
            <div class="divider"></div>
            
            <!-- Design Customization with Visual Presets -->
            <h4 class="subsection-title collapsible-header" onclick="toggleSection('designPresets')">
                <span><i class="fas fa-shapes"></i> Design Presets</span>
                <i class="fas fa-chevron-right collapse-icon"></i>
            </h4>
            <div id="designPresets" class="collapsible-content collapsed">
            
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
            
            </div><!-- End Design Presets collapsible -->
            
            <div class="divider"></div>
            
            <!-- Logo Options -->
            <h4 class="subsection-title collapsible-header" onclick="toggleSection('logoOptions')">
                <span><i class="fas fa-image"></i> Logo</span>
                <i class="fas fa-chevron-right collapse-icon"></i>
            </h4>
            <div id="logoOptions" class="collapsible-content collapsed">
            
            <div class="form-group">
                <label class="form-label">Logo Options</label>
                <div class="logo-option-grid">
                    <div class="logo-option-item active" data-option="none" onclick="selectLogoOption('none')">
                        <div class="logo-option-icon">
                            <i class="fas fa-ban"></i>
                        </div>
                        <span class="logo-option-label">No Logo</span>
                    </div>
                    <div class="logo-option-item" data-option="default" onclick="selectLogoOption('default')">
                        <div class="logo-option-icon">
                            <i class="fas fa-icons"></i>
                        </div>
                        <span class="logo-option-label">Default Logo</span>
                    </div>
                    <div class="logo-option-item" data-option="upload" onclick="selectLogoOption('upload')">
                        <div class="logo-option-icon">
                            <i class="fas fa-upload"></i>
                        </div>
                        <span class="logo-option-label">Upload Logo</span>
                    </div>
                </div>
                <input type="hidden" name="logo_option" id="logoOption" value="none">
            </div>
            
            <!-- Default Logo Icon Selector -->
            <div class="form-group" id="defaultLogoGroup" style="display: none;">
                <label class="form-label">Select Default Logo Icon</label>
                <div class="logo-icon-grid">
                    <!-- Basic Shapes -->
                    <div class="logo-icon-item" data-logo="qr" onclick="selectDefaultLogo('qr')" title="QR Code">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="star" onclick="selectDefaultLogo('star')" title="Star">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="heart" onclick="selectDefaultLogo('heart')" title="Heart">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="check" onclick="selectDefaultLogo('check')" title="Check">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="circle" onclick="selectDefaultLogo('circle')" title="Circle">
                        <i class="fas fa-circle"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="square" onclick="selectDefaultLogo('square')" title="Square">
                        <i class="fas fa-square"></i>
                    </div>
                    
                    <!-- Social Media -->
                    <div class="logo-icon-item" data-logo="facebook" onclick="selectDefaultLogo('facebook')" title="Facebook">
                        <i class="fab fa-facebook"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="instagram" onclick="selectDefaultLogo('instagram')" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="twitter" onclick="selectDefaultLogo('twitter')" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="linkedin" onclick="selectDefaultLogo('linkedin')" title="LinkedIn">
                        <i class="fab fa-linkedin"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="youtube" onclick="selectDefaultLogo('youtube')" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="tiktok" onclick="selectDefaultLogo('tiktok')" title="TikTok">
                        <i class="fab fa-tiktok"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="pinterest" onclick="selectDefaultLogo('pinterest')" title="Pinterest">
                        <i class="fab fa-pinterest"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="snapchat" onclick="selectDefaultLogo('snapchat')" title="Snapchat">
                        <i class="fab fa-snapchat"></i>
                    </div>
                    
                    <!-- Business -->
                    <div class="logo-icon-item" data-logo="shop" onclick="selectDefaultLogo('shop')" title="Shop">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="cart" onclick="selectDefaultLogo('cart')" title="Cart">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="store" onclick="selectDefaultLogo('store')" title="Store">
                        <i class="fas fa-store"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="email" onclick="selectDefaultLogo('email')" title="Email">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="phone" onclick="selectDefaultLogo('phone')" title="Phone">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="location" onclick="selectDefaultLogo('location')" title="Location">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    
                    <!-- Tech & Apps -->
                    <div class="logo-icon-item" data-logo="android" onclick="selectDefaultLogo('android')" title="Android">
                        <i class="fab fa-android"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="apple" onclick="selectDefaultLogo('apple')" title="Apple">
                        <i class="fab fa-apple"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="windows" onclick="selectDefaultLogo('windows')" title="Windows">
                        <i class="fab fa-windows"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="chrome" onclick="selectDefaultLogo('chrome')" title="Chrome">
                        <i class="fab fa-chrome"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="wifi" onclick="selectDefaultLogo('wifi')" title="WiFi">
                        <i class="fas fa-wifi"></i>
                    </div>
                    <div class="logo-icon-item" data-logo="bluetooth" onclick="selectDefaultLogo('bluetooth')" title="Bluetooth">
                        <i class="fab fa-bluetooth"></i>
                    </div>
                </div>
                <input type="hidden" name="default_logo" id="defaultLogo" value="">
                <!-- Logo Preview -->
                <div id="selectedLogoPreview" style="display: none; margin-top: 15px; padding: 15px; background: rgba(87, 96, 255, 0.1); border-radius: 10px; border: 1px solid rgba(87, 96, 255, 0.3);">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, var(--purple), var(--cyan)); border-radius: 10px; color: white; font-size: 28px; position: relative;">
                            <i id="selectedLogoIcon" class="fas fa-qrcode" style="color: white; z-index: 2; position: relative;"></i>
                        </div>
                        <div>
                            <div style="font-size: 13px; color: var(--text-secondary); margin-bottom: 3px;">Selected Logo:</div>
                            <div id="selectedLogoName" style="font-weight: 600; color: var(--text-primary);">None</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group" id="uploadLogoGroup" style="display: none;">
                <label class="form-label">Upload Your Logo</label>
                <input type="file" name="logo" id="logoUpload" class="form-input" accept="image/*">
                <small>PNG or JPG, max 2MB. Square images work best.</small>
            </div>
            
            <div id="logoOptionsGroup" style="display: none;">
                <!-- Logo Color Option -->
                <div class="form-group" id="logoColorOption">
                    <label class="form-label"><i class="fas fa-palette"></i> Logo Color</label>
                    <input type="color" name="logo_color" id="logoColor" value="#9945ff" class="form-input color-input">
                    <small class="help-text">
                        <i class="fas fa-info-circle"></i> Customize the color of default logo icons. Works with icon logos only.
                    </small>
                </div>
                
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
            </div><!-- End Logo Options collapsible -->
            
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
                                    // For password/expiry protected QRs, content is already the access URL
                                    const qrData = <?= json_encode($_SESSION['generated_qr']['content']) ?>;
                                    
                                    const sessionQR = new QRCodeStyling({
                                        width: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        height: <?= $_SESSION['generated_qr']['size'] ?? 300 ?>,
                                        data: qrData,
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
                        <?php if (isset($_SESSION['generated_qr']['access_url'])): ?>
                            <p><strong>Access URL:</strong> <code style="font-size: 11px; word-break: break-all;"><?= htmlspecialchars($_SESSION['generated_qr']['access_url']) ?></code></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['is_dynamic']) && $_SESSION['generated_qr']['is_dynamic']): ?>
                            <p><span class="badge badge-dynamic">🔄 Dynamic</span></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['has_password']) && $_SESSION['generated_qr']['has_password']): ?>
                            <p><span class="badge badge-secure">🔒 Protected</span></p>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['generated_qr']['expires_at'])): ?>
                            <p><span class="badge badge-expiry">⏰ Expires: <?= htmlspecialchars(date('M d, Y', strtotime($_SESSION['generated_qr']['expires_at']))) ?></span></p>
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
// Central QR Configuration Object
const qrConfig = {
    dotStyle: 'dots',
    cornerStyle: 'square',
    markerBorderStyle: 'square',
    markerCenterStyle: 'square'
};

// Debounced live preview - Define early to avoid reference errors
// Performance optimization: Increased debounce delay and added loading state
let previewTimeout;
let isGenerating = false;
window.debouncedPreview = function() {
    console.log('debouncedPreview called');
    clearTimeout(previewTimeout);
    previewTimeout = setTimeout(() => {
        console.log('Debounce timeout expired, checking generatePreview...');
        if (isGenerating) {
            console.log('Preview generation already in progress, skipping...');
            return;
        }
        if (typeof generatePreview === 'function') {
            console.log('Calling generatePreview...');
            isGenerating = true;
            generatePreview();
            // Reset flag after generation completes
            setTimeout(() => { isGenerating = false; }, 100);
        } else {
            console.error('generatePreview is not a function!');
        }
    }, 800); // Increased from 500ms to 800ms for better performance
};
const debouncedPreview = window.debouncedPreview;

// Collapsible Section Toggle Function with Accordion Behavior
window.toggleSection = function(sectionId) {
    const content = document.getElementById(sectionId);
    const header = content.previousElementSibling;
    
    if (!content) return;
    
    // Check if this section is currently collapsed
    const isCollapsed = content.classList.contains('collapsed');
    
    // Accordion behavior: Close all other sections first
    const allSections = ['designOptions', 'designPresets', 'logoOptions'];
    allSections.forEach(id => {
        if (id !== sectionId) {
            const otherContent = document.getElementById(id);
            const otherHeader = otherContent?.previousElementSibling;
            if (otherContent && !otherContent.classList.contains('collapsed')) {
                otherContent.classList.add('collapsed');
                otherHeader?.classList.remove('expanded');
                // Save collapsed state
                localStorage.setItem('qr_section_' + id, 'collapsed');
            }
        }
    });
    
    // Toggle the clicked section
    if (isCollapsed) {
        content.classList.remove('collapsed');
        header.classList.add('expanded');
        // Save state to localStorage
        localStorage.setItem('qr_section_' + sectionId, 'expanded');
    } else {
        content.classList.add('collapsed');
        header.classList.remove('expanded');
        // Save state to localStorage
        localStorage.setItem('qr_section_' + sectionId, 'collapsed');
    }
};

// Initialize collapsed state from localStorage on page load
window.addEventListener('DOMContentLoaded', function() {
    const sections = ['designOptions', 'designPresets', 'logoOptions'];
    sections.forEach(sectionId => {
        const content = document.getElementById(sectionId);
        const header = content?.previousElementSibling;
        if (!content) return;
        
        // Check localStorage for saved state
        const savedState = localStorage.getItem('qr_section_' + sectionId);
        
        // Default to collapsed if no state is saved
        if (!savedState || savedState === 'collapsed') {
            content.classList.add('collapsed');
            header?.classList.remove('expanded');
        } else {
            content.classList.remove('collapsed');
            header?.classList.add('expanded');
        }
    });
});

// Preset Selection Function (Global scope for onclick handlers)
window.selectPreset = function(presetType, value) {
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
    
    // Trigger preview update (check if function exists - defined in DOMContentLoaded)
    if (typeof window.debouncedPreview === 'function') {
        window.debouncedPreview();
    } else if (typeof debouncedPreview === 'function') {
        debouncedPreview();
    }
};

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

// Apply template settings to form
function applyTemplateToForm(settings) {
    console.log('Applying template settings:', settings);
    
    // Basic settings
    if (settings.size) document.getElementById('qrSize').value = settings.size;
    if (settings.foregroundColor) document.getElementById('qrColor').value = settings.foregroundColor;
    if (settings.backgroundColor) document.getElementById('qrBgColor').value = settings.backgroundColor;
    if (settings.errorCorrection) document.getElementById('errorCorrection').value = settings.errorCorrection;
    
    // Gradient settings
    if (settings.gradientEnabled) {
        document.getElementById('gradientEnabled').checked = true;
        document.getElementById('gradientColorGroup').style.display = 'block';
        if (settings.gradientColor) document.getElementById('gradientColor').value = settings.gradientColor;
    }
    
    // Transparent background
    if (settings.transparentBg) {
        document.getElementById('transparentBg').checked = true;
        document.getElementById('qrBgColor').disabled = true;
    }
    
    // Design styles
    if (settings.cornerStyle) document.getElementById('cornerStyle').value = settings.cornerStyle;
    if (settings.dotStyle) document.getElementById('dotStyle').value = settings.dotStyle;
    if (settings.markerBorderStyle) document.getElementById('markerBorderStyle').value = settings.markerBorderStyle;
    if (settings.markerCenterStyle) document.getElementById('markerCenterStyle').value = settings.markerCenterStyle;
    
    // Marker color
    if (settings.customMarkerColor) {
        document.getElementById('customMarkerColor').checked = true;
        document.getElementById('markerColorGroup').style.display = 'block';
        if (settings.markerColor) document.getElementById('markerColor').value = settings.markerColor;
    }
    
    // Frame settings
    if (settings.frameStyle) {
        document.getElementById('frameStyle').value = settings.frameStyle;
        if (settings.frameStyle !== 'none') {
            document.getElementById('frameTextGroup').style.display = 'block';
            document.getElementById('frameFontGroup').style.display = 'block';
            document.getElementById('frameColorGroup').style.display = 'block';
            if (settings.frameLabel) document.getElementById('frameLabel').value = settings.frameLabel;
            if (settings.frameFont) document.getElementById('frameFont').value = settings.frameFont;
            if (settings.frameColor) document.getElementById('frameColor').value = settings.frameColor;
        }
    }
    
    // Logo settings
    if (settings.logoColor) document.getElementById('logoColor').value = settings.logoColor;
    if (settings.logoSize) document.getElementById('logoSize').value = settings.logoSize;
    if (settings.logoRemoveBg) document.getElementById('logoRemoveBg').checked = true;
    
    // Logo option and default logo
    if (settings.logoOption) {
        window.selectLogoOption(settings.logoOption);
        if (settings.logoOption === 'default' && settings.defaultLogo) {
            // Use setTimeout to ensure selectLogoOption has finished
            setTimeout(() => {
                window.selectDefaultLogo(settings.defaultLogo);
            }, 100);
        }
    }
    
    // Trigger preview update
    setTimeout(() => {
        if (typeof window.debouncedPreview === 'function') {
            window.debouncedPreview();
        }
    }, 200);
}

// Initialize all event listeners when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing QR Generator...');
    
    // Check for template to apply from localStorage
    const applyTemplateId = localStorage.getItem('applyTemplateId');
    if (applyTemplateId) {
        console.log('Loading template:', applyTemplateId);
        localStorage.removeItem('applyTemplateId');
        
        // Fetch template data
        fetch('/projects/qr/templates/get?id=' + applyTemplateId)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.template) {
                    applyTemplateToForm(data.template.settings);
                    showNotification('Template "' + data.template.name + '" applied successfully!', 'success');
                } else {
                    showNotification('Failed to load template', 'error');
                }
            })
            .catch(error => {
                console.error('Error loading template:', error);
                showNotification('Error loading template', 'error');
            });
    }
    
    // Check library loaded
    window.addEventListener('load', function() {
        if (typeof QRCodeStyling === 'undefined') {
            console.error('QRCodeStyling library failed to load');
            showNotification('QR library failed to load. Please refresh the page.', 'error');
        }
    });

// Logo Option Selector Functions
window.selectLogoOption = function(option) {
    // Update hidden input
    const logoOptionInput = document.getElementById('logoOption');
    if (logoOptionInput) {
        logoOptionInput.value = option;
    }
    
    // Update visual active state
    document.querySelectorAll('.logo-option-item').forEach(item => {
        item.classList.remove('active');
    });
    const optionItem = document.querySelector(`[data-option="${option}"]`);
    if (optionItem) {
        optionItem.classList.add('active');
    }
    
    // Show/hide relevant sections
    const defaultLogoGroup = document.getElementById('defaultLogoGroup');
    const uploadLogoGroup = document.getElementById('uploadLogoGroup');
    const logoOptionsGroup = document.getElementById('logoOptionsGroup');
    
    if (defaultLogoGroup) {
        defaultLogoGroup.style.display = option === 'default' ? 'block' : 'none';
    }
    if (uploadLogoGroup) {
        uploadLogoGroup.style.display = option === 'upload' ? 'block' : 'none';
    }
    if (logoOptionsGroup) {
        logoOptionsGroup.style.display = (option === 'default' || option === 'upload') ? 'block' : 'none';
    }
    
    // Clear logo selections when switching to none
    if (option === 'none') {
        // Clear default logo selection
        const defaultLogoInput = document.getElementById('defaultLogo');
        if (defaultLogoInput) {
            defaultLogoInput.value = '';
        }
        // Clear upload
        const logoUploadInput = document.getElementById('logoUpload');
        if (logoUploadInput) {
            logoUploadInput.value = '';
        }
        // Hide preview
        const preview = document.getElementById('selectedLogoPreview');
        if (preview) {
            preview.style.display = 'none';
        }
        // Remove active state from all icons
        document.querySelectorAll('.logo-icon-item').forEach(item => {
            item.classList.remove('active');
        });
    }
    
    if (typeof debouncedPreview === 'function') debouncedPreview();
};

window.selectDefaultLogo = function(logo) {
    // Update hidden input
    const defaultLogoInput = document.getElementById('defaultLogo');
    if (defaultLogoInput) {
        defaultLogoInput.value = logo;
    }
    
    // Update visual active state
    document.querySelectorAll('.logo-icon-item').forEach(item => {
        item.classList.remove('active');
    });
    const selected = document.querySelector(`[data-logo="${logo}"]`);
    if (selected) {
        selected.classList.add('active');
        
        // Update preview
        const preview = document.getElementById('selectedLogoPreview');
        const previewIcon = document.getElementById('selectedLogoIcon');
        const previewName = document.getElementById('selectedLogoName');
        
        if (preview && previewIcon && previewName) {
            preview.style.display = 'block';
            
            // Copy the icon from the selected item
            const iconElement = selected.querySelector('i');
            if (iconElement) {
                previewIcon.className = iconElement.className;
            }
            
            // Set the name from title attribute
            const title = selected.getAttribute('title') || logo;
            previewName.textContent = title;
        }
    }
    
    if (typeof debouncedPreview === 'function') debouncedPreview();
};

// Handle QR type change
const qrTypeElement = document.getElementById('qrType');
if (qrTypeElement) {
    qrTypeElement.addEventListener('change', function() {
    const type = this.value;
    
    // Hide all field groups
    document.getElementById('simpleContent').style.display = 'none';
    document.getElementById('emailFields').style.display = 'none';
    document.getElementById('phoneFields').style.display = 'none';
    document.getElementById('smsFields').style.display = 'none';
    document.getElementById('whatsappFields').style.display = 'none';
    document.getElementById('skypeFields').style.display = 'none';
    document.getElementById('zoomFields').style.display = 'none';
    document.getElementById('wifiFields').style.display = 'none';
    document.getElementById('vcardFields').style.display = 'none';
    document.getElementById('locationFields').style.display = 'none';
    document.getElementById('eventFields').style.display = 'none';
    document.getElementById('paypalFields').style.display = 'none';
    document.getElementById('paymentFields').style.display = 'none';
    
    // Show relevant fields
    switch(type) {
        case 'url':
        case 'text':
            document.getElementById('simpleContent').style.display = 'block';
            updateContentLabel(type);
            break;
        case 'email':
            document.getElementById('emailFields').style.display = 'block';
            break;
        case 'phone':
            document.getElementById('phoneFields').style.display = 'block';
            break;
        case 'sms':
            document.getElementById('smsFields').style.display = 'block';
            break;
        case 'whatsapp':
            document.getElementById('whatsappFields').style.display = 'block';
            break;
        case 'skype':
            document.getElementById('skypeFields').style.display = 'block';
            break;
        case 'zoom':
            document.getElementById('zoomFields').style.display = 'block';
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
        case 'paypal':
            document.getElementById('paypalFields').style.display = 'block';
            break;
        case 'payment':
            document.getElementById('paymentFields').style.display = 'block';
            break;
    }
    
    // Trigger live preview
    debouncedPreview();
    });
}

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

// Initialize qrType to show correct fields
const qrTypeInitElement = document.getElementById('qrType');
if (qrTypeInitElement) {
    qrTypeInitElement.dispatchEvent(new Event('change'));
}

// Toggle handlers for existing features
const isDynamicEl = document.getElementById('isDynamic');
if (isDynamicEl) {
    isDynamicEl.addEventListener('change', function() {
        const redirectUrlGroup = document.getElementById('redirectUrlGroup');
        if (redirectUrlGroup) {
            redirectUrlGroup.style.display = this.checked ? 'block' : 'none';
        }
    });
}

const hasPasswordEl = document.getElementById('hasPassword');
if (hasPasswordEl) {
    hasPasswordEl.addEventListener('change', function() {
        const passwordGroup = document.getElementById('passwordGroup');
        if (passwordGroup) {
            passwordGroup.style.display = this.checked ? 'block' : 'none';
        }
    });
}

const hasExpiryEl = document.getElementById('hasExpiry');
if (hasExpiryEl) {
    hasExpiryEl.addEventListener('change', function() {
        const expiryGroup = document.getElementById('expiryGroup');
        if (expiryGroup) {
            expiryGroup.style.display = this.checked ? 'block' : 'none';
        }
    });
}

// Toggle handlers for new customization options
const gradientEnabledEl = document.getElementById('gradientEnabled');
if (gradientEnabledEl) {
    gradientEnabledEl.addEventListener('change', function() {
        const gradientColorGroup = document.getElementById('gradientColorGroup');
        if (gradientColorGroup) {
            gradientColorGroup.style.display = this.checked ? 'block' : 'none';
        }
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

const transparentBgEl = document.getElementById('transparentBg');
if (transparentBgEl) {
    transparentBgEl.addEventListener('change', function() {
        const qrBgColor = document.getElementById('qrBgColor');
        if (qrBgColor) {
            qrBgColor.disabled = this.checked;
        }
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

const customMarkerColorEl = document.getElementById('customMarkerColor');
if (customMarkerColorEl) {
    customMarkerColorEl.addEventListener('change', function() {
        const markerColorGroup = document.getElementById('markerColorGroup');
        
        if (markerColorGroup) {
            markerColorGroup.style.display = this.checked ? 'block' : 'none';
        }
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

const logoOptionEl = document.getElementById('logoOption');
if (logoOptionEl) {
    logoOptionEl.addEventListener('change', function() {
        const value = this.value;
        const defaultLogoGroup = document.getElementById('defaultLogoGroup');
        const uploadLogoGroup = document.getElementById('uploadLogoGroup');
        const logoOptionsGroup = document.getElementById('logoOptionsGroup');
        
        if (defaultLogoGroup) {
            defaultLogoGroup.style.display = value === 'default' ? 'block' : 'none';
        }
        if (uploadLogoGroup) {
            uploadLogoGroup.style.display = value === 'upload' ? 'block' : 'none';
        }
        if (logoOptionsGroup) {
            logoOptionsGroup.style.display = (value === 'default' || value === 'upload') ? 'block' : 'none';
        }
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

const logoSizeEl = document.getElementById('logoSize');
if (logoSizeEl) {
    logoSizeEl.addEventListener('input', function() {
        const logoSizeValue = document.getElementById('logoSizeValue');
        if (logoSizeValue) {
            logoSizeValue.textContent = this.value;
        }
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

const frameStyleEl = document.getElementById('frameStyle');
if (frameStyleEl) {
    frameStyleEl.addEventListener('change', function() {
        const hasFrame = this.value !== 'none';
        const frameTextGroup = document.getElementById('frameTextGroup');
        const frameFontGroup = document.getElementById('frameFontGroup');
        const frameColorGroup = document.getElementById('frameColorGroup');
        
        if (frameTextGroup) frameTextGroup.style.display = hasFrame ? 'block' : 'none';
        if (frameFontGroup) frameFontGroup.style.display = hasFrame ? 'block' : 'none';
        if (frameColorGroup) frameColorGroup.style.display = hasFrame ? 'block' : 'none';
        
        if (typeof debouncedPreview === 'function') debouncedPreview();
    });
}

// Global QR code instance
let qrCode = null;

// Function to apply color to SVG logo
function applyColorToSVG(svgDataUri, color) {
    try {
        // Decode the base64 SVG
        const base64Data = svgDataUri.split(',')[1];
        let svgString = atob(base64Data);
        
        // Replace fill colors in the SVG with the new color
        // Exclude 'none', 'transparent', and preserve those values
        // Match fill="#HEXCOLOR" or fill='#HEXCOLOR' but not fill="none" or fill="transparent"
        svgString = svgString.replace(/fill="(?!none|transparent)[^"]*"/gi, `fill="${color}"`);
        svgString = svgString.replace(/fill='(?!none|transparent)[^']*'/gi, `fill='${color}'`);
        
        // Re-encode to base64 with Unicode support
        const newBase64 = btoa(unescape(encodeURIComponent(svgString)));
        return 'data:image/svg+xml;base64,' + newBase64;
    } catch (e) {
        console.error('Error applying color to SVG:', e);
        return svgDataUri; // Return original if error
    }
}

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
    
    // Check if buttons already exist
    if (container.querySelector('.btn-download')) {
        return;
    }
    
    // Create button container for side-by-side buttons
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'qr-action-buttons';
    buttonContainer.style.cssText = 'display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap;';
    
    // Download QR Code button
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
    
    // Save as Template button
    const saveTemplateBtn = document.createElement('button');
    saveTemplateBtn.className = 'btn btn-save-template';
    saveTemplateBtn.innerHTML = '<i class="fas fa-save"></i> Save as Template';
    saveTemplateBtn.onclick = function(e) {
        e.preventDefault();
        showSaveTemplateModal();
    };
    
    buttonContainer.appendChild(downloadBtn);
    buttonContainer.appendChild(saveTemplateBtn);
    container.appendChild(buttonContainer);
}

// Show save template modal
function showSaveTemplateModal() {
    const modal = document.createElement('div');
    modal.className = 'template-modal';
    modal.innerHTML = `
        <div class="template-modal-overlay" onclick="closeSaveTemplateModal()"></div>
        <div class="template-modal-content">
            <div class="template-modal-header">
                <h3><i class="fas fa-save"></i> Save as Template</h3>
                <button class="template-modal-close" onclick="closeSaveTemplateModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="template-modal-body">
                <div class="form-group">
                    <label class="form-label">Template Name *</label>
                    <input type="text" id="templateName" class="form-input" placeholder="e.g., Business Card Blue" required aria-required="true" aria-describedby="templateNameError">
                    <small id="templateNameError" style="color: #ff4757; display: none; margin-top: 5px;">
                        Please enter a template name
                    </small>
                </div>
                <div class="form-group">
                    <label class="toggle-label" style="display: flex; align-items: center; gap: 10px;">
                        <input type="checkbox" id="templateIsPublic" class="toggle-input">
                        <span class="toggle-slider"></span>
                        <span style="color: var(--text-primary);">Make this template public</span>
                    </label>
                    <small style="color: var(--text-secondary); display: block; margin-top: 5px;">
                        Public templates can be used by other users
                    </small>
                </div>
            </div>
            <div class="template-modal-footer">
                <button class="btn btn-secondary" onclick="closeSaveTemplateModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveCurrentTemplate()">
                    <i class="fas fa-save"></i> Save Template
                </button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    document.getElementById('templateName').focus();
}

// Close save template modal
window.closeSaveTemplateModal = function() {
    const modal = document.querySelector('.template-modal');
    if (modal) {
        modal.remove();
    }
};

// Save current settings as template
window.saveCurrentTemplate = async function() {
    const templateName = document.getElementById('templateName').value.trim();
    const isPublic = document.getElementById('templateIsPublic').checked;
    const errorMsg = document.getElementById('templateNameError');
    
    if (!templateName) {
        if (errorMsg) {
            errorMsg.style.display = 'block';
        }
        showNotification('Please enter a template name', 'error');
        return;
    }
    
    // Hide error if shown
    if (errorMsg) {
        errorMsg.style.display = 'none';
    }
    
    // Collect all current settings
    const settings = {
        size: document.getElementById('qrSize').value,
        foregroundColor: document.getElementById('qrColor').value,
        backgroundColor: document.getElementById('qrBgColor').value,
        errorCorrection: document.getElementById('errorCorrection').value,
        cornerStyle: document.getElementById('cornerStyle').value,
        dotStyle: document.getElementById('dotStyle').value,
        markerBorderStyle: document.getElementById('markerBorderStyle').value,
        markerCenterStyle: document.getElementById('markerCenterStyle').value,
        gradientEnabled: document.getElementById('gradientEnabled').checked,
        gradientColor: document.getElementById('gradientColor').value,
        transparentBg: document.getElementById('transparentBg').checked,
        customMarkerColor: document.getElementById('customMarkerColor').checked,
        markerColor: document.getElementById('markerColor').value,
        logoOption: document.getElementById('logoOption').value,
        defaultLogo: document.getElementById('defaultLogo')?.value,
        logoColor: document.getElementById('logoColor').value,
        logoSize: document.getElementById('logoSize').value,
        logoRemoveBg: document.getElementById('logoRemoveBg').checked,
        frameStyle: document.getElementById('frameStyle')?.value,
        frameLabel: document.getElementById('frameLabel')?.value,
        frameFont: document.getElementById('frameFont')?.value,
        frameColor: document.getElementById('frameColor')?.value
    };
    
    try {
        const formData = new FormData();
        formData.append('name', templateName);
        formData.append('settings', JSON.stringify(settings));
        if (isPublic) {
            formData.append('is_public', '1');
        }
        
        const response = await fetch('/projects/qr/templates/create', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showNotification('Template saved successfully!', 'success');
            closeSaveTemplateModal();
        } else {
            showNotification(data.message || 'Failed to save template', 'error');
        }
    } catch (error) {
        console.error('Error saving template:', error);
        showNotification('Error saving template', 'error');
    }
}


// Generate preview with QRCodeStyling
window.generatePreview = function() {
    console.log('generatePreview function called');
    if (typeof QRCodeStyling === 'undefined') {
        console.log('QRCodeStyling not loaded yet');
        return;
    }
    
    const content = buildQRContent();
    console.log('Built QR content:', content);
    if (!content || content.trim() === '') {
        console.log('No content to generate QR code');
        return;
    }
    
    console.log('Proceeding with QR generation...');
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
    
    // Logo settings
    const logoOption = document.getElementById('logoOption').value;
    const logoSize = parseFloat(document.getElementById('logoSize').value);
    const logoRemoveBg = document.getElementById('logoRemoveBg').checked;
    const logoColor = document.getElementById('logoColor').value;
    
    // Build QR options
    const dotColor = gradientEnabled 
        ? { 
            type: 'gradient', 
            rotation: 0, 
            colorStops: [
                { offset: 0, color: foregroundColor }, 
                { offset: 1, color: gradientColor }
            ] 
        } 
        : foregroundColor;
    
    // Background color - transparent or solid
    const bgColor = transparentBg ? 'rgba(0,0,0,0)' : backgroundColor;
    
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
            color: bgColor
        },
        cornersSquareOptions: {
            type: cornerStyle,
            color: customMarkerColor ? markerColor : (gradientEnabled ? dotColor : foregroundColor)
        },
        cornersDotOptions: {
            type: markerCenterStyle,
            color: customMarkerColor ? markerColor : (gradientEnabled ? dotColor : foregroundColor)
        }
    };
    
    // Add logo if selected
    if (logoOption === 'default') {
        const defaultLogoEl = document.getElementById('defaultLogo');
        if (defaultLogoEl && defaultLogoEl.value) {
            const defaultLogo = defaultLogoEl.value;
            if (defaultLogos[defaultLogo]) {
                // Apply logo color to the SVG
                qrOptions.image = applyColorToSVG(defaultLogos[defaultLogo], logoColor);
                qrOptions.imageOptions = {
                    hideBackgroundDots: logoRemoveBg,
                    imageSize: logoSize,
                    margin: 5
                };
            }
        }
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
    
    // Apply transparent background if enabled
    const transparentBg = document.getElementById('transparentBg').checked;
    if (transparentBg) {
        qrDiv.style.background = 'transparent';
        // Add a checkered pattern to show transparency
        qrDiv.style.backgroundImage = 'repeating-conic-gradient(#f0f0f0 0% 25%, #ffffff 0% 50%) 50% / 20px 20px';
        qrDiv.style.backgroundBlendMode = 'normal';
    }
    
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

// Build QR content based on type
window.buildQRContent = function() {
    const type = document.getElementById('qrType').value;
    let content = '';
    
    switch(type) {
        case 'url':
        case 'text':
            content = document.getElementById('contentField').value;
            break;
        case 'email':
            const emailTo = document.getElementById('emailTo').value;
            const emailSubject = document.getElementById('emailSubject').value;
            const emailBody = document.getElementById('emailBody').value;
            content = 'mailto:' + emailTo;
            if (emailSubject || emailBody) {
                content += '?';
                if (emailSubject) content += 'subject=' + encodeURIComponent(emailSubject);
                if (emailSubject && emailBody) content += '&';
                if (emailBody) content += 'body=' + encodeURIComponent(emailBody);
            }
            break;
        case 'phone':
            const phoneCountry = document.getElementById('phoneCountry').value;
            const phoneNumber = document.getElementById('phoneNumber').value;
            content = 'tel:' + phoneCountry + phoneNumber.replace(/\D/g, '');
            break;
        case 'sms':
            const smsCountry = document.getElementById('smsCountry').value;
            const smsNumber = document.getElementById('smsNumber').value;
            const smsMessage = document.getElementById('smsMessage').value;
            content = 'sms:' + smsCountry + smsNumber.replace(/\D/g, '');
            if (smsMessage) content += '?body=' + encodeURIComponent(smsMessage);
            break;
        case 'whatsapp':
            const whatsappCountry = document.getElementById('whatsappCountry').value;
            const whatsappPhone = document.getElementById('whatsappPhone').value;
            const whatsappMessage = document.getElementById('whatsappMessage').value;
            const fullPhone = whatsappCountry.replace('+', '') + whatsappPhone.replace(/\D/g, '');
            content = 'https://wa.me/' + fullPhone;
            if (whatsappMessage) content += '?text=' + encodeURIComponent(whatsappMessage);
            break;
        case 'skype':
            const skypeAction = document.getElementById('skypeAction').value;
            const skypeUsername = document.getElementById('skypeUsername').value;
            content = 'skype:' + skypeUsername + '?' + skypeAction;
            break;
        case 'zoom':
            const zoomMeetingId = document.getElementById('zoomMeetingId').value;
            const zoomPassword = document.getElementById('zoomPassword').value;
            content = 'https://zoom.us/j/' + zoomMeetingId.replace(/\D/g, '');
            if (zoomPassword) content += '?pwd=' + zoomPassword;
            break;
        case 'wifi':
            const ssid = document.getElementById('wifiSsid').value;
            const password = document.getElementById('wifiPassword').value;
            const encryption = document.getElementById('wifiEncryption').value;
            content = 'WIFI:T:' + encryption + ';S:' + ssid + ';P:' + password + ';;';
            break;
        case 'vcard':
            const title = document.getElementById('vcardTitle').value;
            const firstName = document.getElementById('vcardFirstName').value;
            const lastName = document.getElementById('vcardLastName').value;
            const phoneHome = document.getElementById('vcardPhoneHome').value;
            const phoneMobile = document.getElementById('vcardPhoneMobile').value;
            const phoneOffice = document.getElementById('vcardPhoneOffice').value;
            const vcardEmail = document.getElementById('vcardEmail').value;
            const website = document.getElementById('vcardWebsite').value;
            const company = document.getElementById('vcardCompany').value;
            const jobTitle = document.getElementById('vcardJobTitle').value;
            const address = document.getElementById('vcardAddress').value;
            const postCode = document.getElementById('vcardPostCode').value;
            const city = document.getElementById('vcardCity').value;
            const state = document.getElementById('vcardState').value;
            const country = document.getElementById('vcardCountry').value;
            
            content = 'BEGIN:VCARD\nVERSION:3.0\n';
            if (title || firstName || lastName) {
                content += 'N:' + lastName + ';' + firstName + ';' + title + ';;\n';
                content += 'FN:' + (title ? title + ' ' : '') + firstName + ' ' + lastName + '\n';
            }
            if (phoneHome) content += 'TEL;TYPE=HOME:' + phoneHome + '\n';
            if (phoneMobile) content += 'TEL;TYPE=CELL:' + phoneMobile + '\n';
            if (phoneOffice) content += 'TEL;TYPE=WORK:' + phoneOffice + '\n';
            if (vcardEmail) content += 'EMAIL:' + vcardEmail + '\n';
            if (website) content += 'URL:' + website + '\n';
            if (company) content += 'ORG:' + company + '\n';
            if (jobTitle) content += 'TITLE:' + jobTitle + '\n';
            if (address || city || state || postCode || country) {
                content += 'ADR:;;' + address + ';' + city + ';' + state + ';' + postCode + ';' + country + '\n';
            }
            content += 'END:VCARD';
            break;
        case 'location':
            const lat = document.getElementById('locationLat').value;
            const lng = document.getElementById('locationLng').value;
            content = 'geo:' + lat + ',' + lng;
            break;
        case 'event':
            const eventTitle = document.getElementById('eventTitle').value;
            const eventLocation = document.getElementById('eventLocation').value;
            const eventStart = document.getElementById('eventStart').value;
            const eventEnd = document.getElementById('eventEnd').value;
            const eventReminder = document.getElementById('eventReminder').value;
            const eventLink = document.getElementById('eventLink').value;
            const eventNotes = document.getElementById('eventNotes').value;
            
            content = 'BEGIN:VEVENT\n';
            if (eventTitle) content += 'SUMMARY:' + eventTitle + '\n';
            if (eventLocation) content += 'LOCATION:' + eventLocation + '\n';
            if (eventStart) content += 'DTSTART:' + eventStart.replace(/[-:]/g, '').replace('T', '') + '\n';
            if (eventEnd) content += 'DTEND:' + eventEnd.replace(/[-:]/g, '').replace('T', '') + '\n';
            if (eventReminder) {
                content += 'BEGIN:VALARM\n';
                content += 'TRIGGER:-PT' + eventReminder + 'M\n';
                content += 'ACTION:DISPLAY\n';
                content += 'DESCRIPTION:Event Reminder\n';
                content += 'END:VALARM\n';
            }
            if (eventLink) content += 'URL:' + eventLink + '\n';
            if (eventNotes) content += 'DESCRIPTION:' + eventNotes + '\n';
            content += 'END:VEVENT';
            break;
        case 'paypal':
            const paypalType = document.getElementById('paypalType').value;
            const paypalEmail = document.getElementById('paypalEmail').value;
            const paypalItemName = document.getElementById('paypalItemName').value;
            const paypalItemId = document.getElementById('paypalItemId').value;
            const paypalPrice = document.getElementById('paypalPrice').value;
            const paypalCurrency = document.getElementById('paypalCurrency').value;
            const paypalShipping = document.getElementById('paypalShipping').value;
            const paypalTax = document.getElementById('paypalTax').value;
            
            content = 'https://www.paypal.com/cgi-bin/webscr?cmd=_xclick';
            content += '&business=' + encodeURIComponent(paypalEmail);
            if (paypalItemName) content += '&item_name=' + encodeURIComponent(paypalItemName);
            if (paypalItemId) content += '&item_number=' + encodeURIComponent(paypalItemId);
            if (paypalPrice) content += '&amount=' + paypalPrice;
            if (paypalCurrency) content += '&currency_code=' + paypalCurrency;
            if (paypalShipping) content += '&shipping=' + paypalShipping;
            if (paypalTax) content += '&tax_rate=' + paypalTax;
            break;
        case 'payment':
            const payType = document.getElementById('paymentType').value;
            const upiId = document.getElementById('paymentUpiId').value;
            const payeeName = document.getElementById('paymentName').value;
            const amount = document.getElementById('paymentAmount').value;
            const note = document.getElementById('paymentNote').value;
            
            content = 'upi://pay?pa=' + upiId;
            if (payeeName) content += '&pn=' + encodeURIComponent(payeeName);
            if (amount) content += '&am=' + amount;
            if (note) content += '&tn=' + encodeURIComponent(note);
            break;
    }
    
    return content;
}

// Apply frame style to QR code
function applyFrameStyle(qrDiv) {
    const frameStyleEl = document.getElementById('frameStyle');
    if (!frameStyleEl) return;
    
    const frameStyle = frameStyleEl.value;
    
    // Remove any existing frame classes
    qrDiv.className = 'qr-preview';
    
    if (frameStyle && frameStyle !== 'none') {
        qrDiv.classList.add('qr-frame-' + frameStyle);
        
        // Add frame label if provided
        const frameLabelEl = document.getElementById('frameLabel');
        if (frameLabelEl && frameLabelEl.value && frameLabelEl.value.trim()) {
            const frameLabel = document.createElement('div');
            frameLabel.className = 'frame-label';
            frameLabel.textContent = frameLabelEl.value.trim();
            
            // Apply custom font if selected
            const frameFontEl = document.getElementById('frameFont');
            if (frameFontEl && frameFontEl.value) {
                frameLabel.style.fontFamily = frameFontEl.value;
            }
            
            // Apply custom color if provided
            const frameColorEl = document.getElementById('frameColor');
            if (frameColorEl && frameColorEl.value) {
                frameLabel.style.color = frameColorEl.value;
            }
            
            // Insert label based on frame style
            if (frameStyle === 'banner-top') {
                qrDiv.insertBefore(frameLabel, qrDiv.firstChild);
            } else {
                qrDiv.appendChild(frameLabel);
            }
        }
    }
}

    // Live preview on all field changes
    const livePreviewFields = [
        'contentField', 'qrType', 'qrSize', 'qrColor', 'qrBgColor', 'errorCorrection',
        'frameStyle', 'cornerStyle', 'dotStyle', 'markerBorderStyle', 'markerCenterStyle',
        'gradientColor', 'markerColor', 'logoColor',
        'defaultLogo', 'frameLabel', 'frameFont', 'frameColor',
        // Email fields
        'emailTo', 'emailSubject', 'emailBody',
        // Phone fields
        'phoneCountry', 'phoneNumber',
        // SMS fields
        'smsCountry', 'smsNumber', 'smsMessage',
        // WhatsApp fields
        'whatsappCountry', 'whatsappPhone', 'whatsappMessage',
        // Skype fields
        'skypeAction', 'skypeUsername',
        // Zoom fields
        'zoomMeetingId', 'zoomPassword',
        // WiFi fields
        'wifiSsid', 'wifiPassword', 'wifiEncryption',
        // vCard fields
        'vcardTitle', 'vcardFirstName', 'vcardLastName', 'vcardPhoneHome', 'vcardPhoneMobile',
        'vcardEmail', 'vcardWebsite', 'vcardCompany', 'vcardJobTitle', 'vcardPhoneOffice',
        'vcardAddress', 'vcardPostCode', 'vcardCity', 'vcardState', 'vcardCountry',
        // Location fields
        'locationAddress', 'locationLat', 'locationLng',
        // Event fields
        'eventTitle', 'eventLocation', 'eventStart', 'eventEnd', 'eventReminder', 'eventLink', 'eventNotes',
        // PayPal fields
        'paypalType', 'paypalEmail', 'paypalItemName', 'paypalItemId', 'paypalPrice',
        'paypalCurrency', 'paypalShipping', 'paypalTax',
        // Payment fields
        'paymentType', 'paymentUpiId', 'paymentName', 'paymentAmount', 'paymentNote'
    ];

    // Initialize preview when page loads with a sample URL
    setTimeout(function() {
        console.log('Preview initialization starting...');
        // Wait for QRCodeStyling library to load
        // Set default URL for initial preview
        const contentField = document.getElementById('contentField');
        if (contentField && !contentField.value) {
            contentField.value = 'https://example.com';
        }
        // Trigger initial preview
        console.log('Calling generatePreview for initial load...');
        generatePreview();
    }, 1000);

    // Attach event listeners to all live preview fields
    console.log('Attaching event listeners to', livePreviewFields.length, 'fields...');
    livePreviewFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            console.log('Attached listeners to:', fieldId);
            field.addEventListener('input', debouncedPreview);
            field.addEventListener('change', debouncedPreview);
        }
    });

    // Add event listeners for file inputs
    const logoUploadInput = document.getElementById('logoUpload');
    if (logoUploadInput) {
        logoUploadInput.addEventListener('change', debouncedPreview);
    }

    // Add event listeners for checkboxes that should trigger preview
    const previewCheckboxes = ['logoRemoveBg'];
    previewCheckboxes.forEach(checkboxId => {
        const checkbox = document.getElementById(checkboxId);
        if (checkbox) {
            checkbox.addEventListener('change', debouncedPreview);
        }
    });

}); // End DOMContentLoaded
</script>

<style>
/* Collapsible Sections Styles */
.collapsible-header {
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.5rem 0.75rem; /* More compact padding */
    background: rgba(153, 69, 255, 0.1);
    border-radius: 0.5rem;
    margin-bottom: 0.5rem; /* More compact spacing */
    transition: all 0.3s ease;
    user-select: none;
}

[data-theme="light"] .collapsible-header {
    background: rgba(153, 69, 255, 0.05);
}

.collapsible-header:hover {
    background: rgba(153, 69, 255, 0.15);
    transform: translateY(-0.0625rem);
}

[data-theme="light"] .collapsible-header:hover {
    background: rgba(153, 69, 255, 0.1);
}

.collapsible-header span {
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Reduced from var(--space-sm) */
    font-size: 0.9rem; /* Smaller font for more compact design */
    transition: color 0.3s ease; /* Add transition for text color */
}

/* Enhanced expanded state - change text color */
.collapsible-header.expanded {
    background: rgba(153, 69, 255, 0.2); /* Stronger background when expanded */
}

.collapsible-header.expanded span {
    color: var(--purple); /* Highlight text color when expanded */
    font-weight: 600; /* Make text bolder when expanded */
}

[data-theme="light"] .collapsible-header.expanded {
    background: rgba(153, 69, 255, 0.15);
}

.collapse-icon {
    transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), color 0.3s ease;
    color: rgba(153, 69, 255, 0.7);
    transform: rotate(-90deg); /* Start rotated -90deg when collapsed */
    font-size: 0.9rem;
}

.collapsible-header:hover .collapse-icon {
    color: var(--purple);
    transform: scale(1.15) rotate(-90deg); /* Keep collapsed rotation on hover */
}

/* Rotate icon when expanded - smooth 0deg rotation for more visible change */
.collapsible-header.expanded .collapse-icon {
    transform: rotate(0deg); /* Rotate to 0deg when expanded */
    color: var(--purple);
}

.collapsible-header.expanded:hover .collapse-icon {
    transform: scale(1.15) rotate(0deg); /* Keep expanded rotation on hover */
}

.collapsible-content {
    max-height: 10000px;
    overflow: hidden;
    transition: max-height 0.4s ease-out, opacity 0.3s ease-out;
    opacity: 1;
}

.collapsible-content.collapsed {
    max-height: 0;
    opacity: 0;
    transition: max-height 0.4s ease-in, opacity 0.3s ease-in;
}

/* Performance Optimizations for Smooth Scrolling */
.qr-main {
    /* Enable hardware acceleration */
    will-change: scroll-position;
    -webkit-overflow-scrolling: touch;
    /* Optimize paint performance */
    contain: layout style;
}

.glass-card {
    /* Optimize transform and opacity animations */
    will-change: transform, box-shadow;
}

.qr-preview-container {
    /* Isolate preview rendering */
    contain: layout style paint;
    will-change: contents;
}

/* Reduce animation complexity on scroll */
@media (prefers-reduced-motion: no-preference) {
    * {
        scroll-behavior: smooth;
    }
}

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
    border-radius: 1.25rem; /* 20px to rem */
    padding: 1rem; /* Reduced from 1.5625rem for more compact design */
    box-shadow: 0 0.5rem 2rem 0 rgba(0, 0, 0, 0.37);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeInUp 0.5s ease;
}

[data-theme="light"] .glass-card {
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid rgba(0, 0, 0, 0.1);
    box-shadow: 0 0.5rem 2rem 0 rgba(0, 0, 0, 0.1);
}

.glass-card:hover {
    transform: translateY(-0.125rem); /* -2px to rem, reduced from -2px for smoother performance */
    box-shadow: 0 0.75rem 2.5rem 0 rgba(153, 69, 255, 0.3);
}

/* Animations - Optimized for performance */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 1.25rem, 0); /* Use translate3d for GPU acceleration */
    }
    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
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
        max-height: 31.25rem; /* 500px to rem */
        margin-bottom: 1.25rem;
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
    font-size: 1.125rem; /* Further reduced for compact design */
    font-weight: 600;
    margin-bottom: 0.875rem; /* More compact spacing */
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Reduced from 0.75rem */
}

.subsection-title {
    font-size: 0.875rem; /* Smaller for more compact design */
    font-weight: 600;
    margin: 0.75rem 0 0.5rem 0; /* More compact margins */
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem; /* Reduced from 10px */
}

/* Form Styling */
.form-group {
    margin-bottom: 0.625rem; /* More compact spacing */
    animation: fadeInUp 0.3s ease;
}

.form-label {
    display: flex;
    align-items: center;
    gap: 0.375rem; /* More compact gap */
    margin-bottom: 0.375rem; /* More compact bottom margin */
    font-weight: 500;
    color: var(--text-primary);
    font-size: 0.8125rem; /* Smaller font for compact design */
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
    padding: 8px 12px; /* More compact padding */
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px; /* Slightly smaller border radius */
    color: var(--text-primary);
    font-size: 13px; /* Smaller font size */
    transition: all 0.3s ease;
}

/* Default dark mode (when no theme or dark theme) - MAXIMUM SPECIFICITY */
.form-select,
.form-select option,
.form-select optgroup,
:root .form-select,
:root .form-select option,
:root .form-select optgroup,
html:not([data-theme="light"]) .form-select,
html:not([data-theme="light"]) .form-select option,
html:not([data-theme="light"]) .form-select optgroup,
body .form-select,
body .form-select option,
body .form-select optgroup {
    color: #e8eefc !important;
    background: #1a1a2e !important;
    background-color: #1a1a2e !important;
}

/* Light mode form elements - improved visibility */
[data-theme="light"] .form-input,
[data-theme="light"] .form-select,
[data-theme="light"] .form-textarea {
    background: #ffffff !important;
    background-color: #ffffff !important;
    border: 1px solid #d0d0d0;
    color: #1a1a1a !important;
}

[data-theme="light"] .form-select option,
[data-theme="light"] .form-select optgroup {
    background: #ffffff !important;
    background-color: #ffffff !important;
    color: #1a1a1a !important;
}

/* Dark mode dropdown text visibility - ULTRA explicit */
[data-theme="dark"] .form-select,
[data-theme="dark"] .form-select option,
[data-theme="dark"] .form-select optgroup,
html[data-theme="dark"] .form-select,
html[data-theme="dark"] .form-select option,
html[data-theme="dark"] .form-select optgroup {
    color: #e8eefc !important;
    background: #1a1a2e !important;
    background-color: #1a1a2e !important;
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
    height: 45px; /* More compact height */
    cursor: pointer;
    border: 2px solid rgba(153, 69, 255, 0.3); /* Add purple border for better visibility */
    transition: border-color 0.3s ease;
}

.color-input:hover {
    border-color: rgba(153, 69, 255, 0.6); /* Highlight on hover */
}

.color-input:focus {
    border-color: var(--purple); /* Strong highlight on focus */
    box-shadow: 0 0 0 3px rgba(153, 69, 255, 0.15);
}

/* Collapsible sections animation */
#gradientColorGroup,
#markerColorGroup,
#differentMarkerColorsGroup,
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
    margin-bottom: 0.5rem; /* More compact spacing */
}

.toggle-label {
    display: flex;
    align-items: center;
    gap: 0.5rem; /* More compact gap */
    cursor: pointer;
    padding: 0.5rem; /* More compact padding */
    background: rgba(255, 255, 255, 0.03);
    border-radius: 0.625rem; /* Slightly smaller border radius */
    transition: all 0.3s ease;
    border: 1px solid transparent; /* Add border for enhanced state */
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

/* Enhanced state when toggle is checked */
.toggle-input:checked ~ .toggle-text strong,
.toggle-input:checked + .toggle-slider + .toggle-text strong {
    color: var(--purple);
}

/* Add subtle glow to the entire toggle when checked */
.toggle-label:has(.toggle-input:checked) {
    background: rgba(153, 69, 255, 0.08);
    border-color: rgba(153, 69, 255, 0.3);
}

[data-theme="light"] .toggle-label:has(.toggle-input:checked) {
    background: rgba(153, 69, 255, 0.05);
    border-color: rgba(153, 69, 255, 0.2);
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
    font-size: 0.8125rem; /* Smaller for more compact design */
    margin-bottom: 0.125rem; /* More compact spacing */
    transition: color 0.3s ease; /* Add transition for color change */
}

.toggle-text small {
    display: block;
    color: var(--text-secondary);
    font-size: 0.6875rem; /* Smaller for more compact design */
}

/* Help Text Styling */
.help-text {
    display: block;
    margin-top: 0.25rem;
    color: var(--text-secondary);
    font-size: 0.7rem;
}

/* Logo Option Selector */
.logo-option-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-top: 10px;
}

.logo-option-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 16px 12px;
    background: rgba(255, 255, 255, 0.03);
    border: 2px solid transparent;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

[data-theme="light"] .logo-option-item {
    background: rgba(0, 0, 0, 0.03);
}

.logo-option-item:hover {
    background: rgba(255, 255, 255, 0.06);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(153, 69, 255, 0.2);
}

[data-theme="light"] .logo-option-item:hover {
    background: rgba(0, 0, 0, 0.06);
}

.logo-option-item.active {
    background: linear-gradient(135deg, rgba(153, 69, 255, 0.2), rgba(0, 240, 255, 0.2));
    border-color: var(--purple);
}

.logo-option-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    font-size: 20px;
    color: var(--text-primary);
}

[data-theme="light"] .logo-option-icon {
    background: rgba(0, 0, 0, 0.05);
}

.logo-option-item.active .logo-option-icon {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
}

.logo-option-label {
    font-size: 12px;
    font-weight: 500;
    color: var(--text-secondary);
    text-align: center;
}

.logo-option-item.active .logo-option-label {
    color: var(--text-primary);
    font-weight: 600;
}

/* Logo Icon Grid */
.logo-icon-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(55px, 1fr));
    gap: 10px;
    margin-top: 10px;
    max-height: 300px;
    overflow-y: auto;
    padding: 10px;
    background: rgba(255, 255, 255, 0.02);
    border-radius: 10px;
}

[data-theme="light"] .logo-icon-grid {
    background: rgba(0, 0, 0, 0.02);
}

.logo-icon-item {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid transparent;
    border-radius: 10px;
    font-size: 24px;
    color: var(--text-primary);
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.logo-icon-item i {
    font-size: 24px;
    z-index: 1;
    display: inline-block;
    position: relative;
    color: inherit;
    pointer-events: none;
}

[data-theme="light"] .logo-icon-item {
    background: rgba(0, 0, 0, 0.05);
}

.logo-icon-item:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(153, 69, 255, 0.3);
}

[data-theme="light"] .logo-icon-item:hover {
    background: rgba(0, 0, 0, 0.1);
}

.logo-icon-item.active {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    border-color: var(--cyan);
    color: white;
    transform: scale(1.05);
    box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.3);
}

.logo-icon-item.active i {
    color: white;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    font-weight: 900;
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

.btn-save-template {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border: none;
}

.btn-save-template:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(153, 69, 255, 0.4);
}

.qr-action-buttons .btn {
    flex: 1;
    min-width: 200px;
}

/* Template Save Modal */
.template-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.template-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

.template-modal-content {
    position: relative;
    background: var(--glass-bg);
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    overflow: hidden;
    animation: modalSlideIn 0.3s ease;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.template-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid var(--glass-border);
}

.template-modal-header h3 {
    margin: 0;
    font-size: 20px;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 10px;
}

.template-modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    font-size: 20px;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 5px;
    transition: all 0.2s;
}

.template-modal-close:hover {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.template-modal-body {
    padding: 25px;
}

.template-modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding: 20px 25px;
    border-top: 1px solid var(--glass-border);
    background: rgba(0, 0, 0, 0.1);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid var(--glass-border);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

[data-theme="light"] .btn-secondary {
    background: rgba(0, 0, 0, 0.05);
    border-color: rgba(0, 0, 0, 0.1);
}

[data-theme="light"] .btn-secondary:hover {
    background: rgba(0, 0, 0, 0.1);
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

/* Frame Label Styles */
.frame-label {
    font-size: 16px;
    font-weight: 700;
    text-align: center;
    padding: 12px 24px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    border-radius: 8px;
    margin: 10px 0;
    letter-spacing: 1px;
    text-transform: uppercase;
}

.qr-frame-banner-top .frame-label {
    order: -1;
    margin-bottom: 15px;
}

.qr-frame-banner-bottom .frame-label {
    margin-top: 15px;
}

.qr-frame-badge .frame-label {
    border-radius: 50px;
    padding: 8px 20px;
    font-size: 14px;
}

.qr-frame-bubble .frame-label {
    border-radius: 20px;
    padding: 10px 20px;
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
    margin: 15px 0; /* More compact margin */
}

/* Grid - Optimized with rem units */
.grid {
    display: grid;
    gap: 1.875rem; /* 30px to rem */
}

.grid-2 {
    grid-template-columns: 1fr 1fr;
}

@media (max-width: 64rem) { /* 1024px to rem */
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .preview-panel {
        position: static;
        max-height: none;
    }
}

@media (max-width: 768px) {
    .qr-action-buttons {
        flex-direction: column;
    }
    
    .qr-action-buttons .btn {
        width: 100%;
        min-width: auto;
    }
    
    .template-modal-content {
        width: 95%;
        padding: 15px;
    }
    
    .logo-icon-grid {
        grid-template-columns: repeat(auto-fill, minmax(50px, 1fr));
        gap: 8px;
        max-height: 250px;
    }
    
    .sample-download-section {
        padding: 12px;
    }
}

@media (max-width: 480px) {
    .glass-card {
        padding: 15px;
    }
    
    .section-title {
        font-size: 18px;
    }
    
    .form-label {
        font-size: 13px;
    }
    
    .btn {
        font-size: 14px;
        padding: 12px 20px;
    }
    
    .logo-icon-item {
        width: 45px;
        height: 45px;
        font-size: 20px;
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
