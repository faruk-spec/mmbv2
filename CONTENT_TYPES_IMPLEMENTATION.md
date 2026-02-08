# QR Code Content Types - Complete Implementation Guide

## Overview
This document provides comprehensive details about the 14 content types implemented in the QR code generator, including field specifications, data formats, and QR code standards.

---

## Content Type Specifications

### 1. URL / Website
**Fields:**
- URL input (text area)

**QR Data Format:**
```
https://example.com
```

**Use Case:** Direct users to websites, landing pages, product pages

---

### 2. Plain Text
**Fields:**
- Text content (text area)

**QR Data Format:**
```
Any plain text content
```

**Use Case:** Display information, messages, instructions

---

### 3. Email Address
**Fields:**
- Send To (email input)
- Subject (text input)
- Message (text area)

**QR Data Format:**
```
mailto:recipient@example.com?subject=Hello&body=Message%20text
```

**Standard:** RFC 6068 (mailto URI scheme)

**Use Case:** Pre-compose emails, contact forms, support requests

---

### 4. Location
**Fields:**
- Search Address (helper text input)
- Latitude (decimal degrees)
- Longitude (decimal degrees)

**QR Data Format:**
```
geo:40.7128,-74.0060
```

**Standard:** RFC 5870 (geo URI scheme)

**Use Case:** Share locations, navigate to venues, mark points of interest

**Helper Features:**
- Address search field for user convenience
- Instructions to use Google Maps for finding coordinates
- Right-click method explained

---

### 5. Phone Number
**Fields:**
- Country Code (dropdown - 10 countries)
- Phone Number (text input)

**Country Codes Available:**
- +1 (US/Canada)
- +44 (UK)
- +91 (India)
- +86 (China)
- +61 (Australia)
- +49 (Germany)
- +33 (France)
- +81 (Japan)
- +82 (South Korea)
- +55 (Brazil)

**QR Data Format:**
```
tel:+11234567890
```

**Standard:** RFC 3966 (tel URI scheme)

**Use Case:** Quick dialing, business cards, customer service

---

### 6. SMS Message
**Fields:**
- Country Code (dropdown - 10 countries)
- Phone Number (text input)
- Message (text area)

**QR Data Format:**
```
sms:+11234567890?body=Your%20message%20here
```

**Standard:** RFC 5724 (sms URI scheme)

**Use Case:** Pre-filled text messages, marketing campaigns, feedback

---

### 7. WhatsApp
**Fields:**
- Country Code (dropdown - 10 countries)
- Phone Number (text input)
- Message (text area, optional)

**QR Data Format:**
```
https://wa.me/11234567890?text=Your%20message%20here
```

**Standard:** WhatsApp Click to Chat API

**Use Case:** Customer support, business communication, social sharing

---

### 8. Skype
**Fields:**
- Action Type (dropdown: Chat, Call)
- Username (text input)

**QR Data Format:**
```
skype:username?chat
skype:username?call
```

**Standard:** Skype URI scheme

**Use Case:** Business calls, customer support, remote meetings

---

### 9. Zoom
**Fields:**
- Meeting ID (text input)
- Password (text input, optional)

**QR Data Format:**
```
https://zoom.us/j/123456789?pwd=password
```

**Standard:** Zoom meeting URL format

**Use Case:** Virtual meetings, webinars, online classes

---

### 10. WiFi Network
**Fields:**
- Network Name / SSID (text input)
- Security Type (dropdown: WPA/WPA2, WEP, None)
- Password (text input)

**QR Data Format:**
```
WIFI:T:WPA;S:NetworkName;P:password;;
```

**Standard:** MECARD WiFi format

**Use Case:** Guest WiFi access, office networks, event venues

**Security Types:**
- WPA/WPA2 (most common)
- WEP (legacy)
- None (open networks)

---

### 11. vCard (Contact)
**Fields:**
- Title (text input) - Mr./Ms./Dr.
- First Name (text input)
- Last Name (text input)
- Phone (Home) (text input)
- Phone (Mobile) (text input)
- E-mail (email input)
- Website (URL) (url input)
- Company (text input)
- Job Title (text input)
- Phone (Office) (text input)
- Address (text input)
- Post Code (text input)
- City (text input)
- State (text input)
- Country (text input)

**QR Data Format:**
```
BEGIN:VCARD
VERSION:3.0
N:Doe;John;Mr.;;
FN:Mr. John Doe
TEL;TYPE=HOME:+1234567890
TEL;TYPE=CELL:+1234567891
TEL;TYPE=WORK:+1234567892
EMAIL:john@example.com
URL:https://example.com
ORG:Company Name
TITLE:Software Engineer
ADR:;;123 Main Street;New York;NY;12345;USA
END:VCARD
```

**Standard:** vCard 3.0 (RFC 2426)

**Use Case:** Business cards, contact sharing, networking events

**Field Mapping:**
- N: Last;First;Title (structured name)
- FN: Full formatted name
- TEL;TYPE=HOME: Home phone
- TEL;TYPE=CELL: Mobile phone
- TEL;TYPE=WORK: Office phone
- EMAIL: Email address
- URL: Website
- ORG: Organization/Company
- TITLE: Job title
- ADR: ;;Street;City;State;PostCode;Country

---

### 12. Event (Calendar)
**Fields:**
- Event Title (text input)
- Location (text input)
- Start Time (datetime-local input)
- End Time (datetime-local input)
- Reminder Before Event (dropdown)
- Link (url input, optional)
- Notes (text area, optional)

**Reminder Options:**
- No Reminder
- 5 minutes before
- 15 minutes before
- 30 minutes before
- 1 hour before
- 2 hours before
- 24 hours before
- 48 hours before

**QR Data Format:**
```
BEGIN:VEVENT
SUMMARY:Birthday Party
LOCATION:123 Main Street
DTSTART:20260208T180000
DTEND:20260208T220000
BEGIN:VALARM
TRIGGER:-PT60M
ACTION:DISPLAY
DESCRIPTION:Event Reminder
END:VALARM
URL:https://example.com/event
DESCRIPTION:Additional notes about the event
END:VEVENT
```

**Standard:** iCalendar (RFC 5545)

**Use Case:** Event invitations, conference schedules, appointments

**Time Format:** YYYYMMDDTHHmmss (ISO 8601 basic format)

---

### 13. PayPal
**Fields:**
- Payment Type (dropdown: Buy Now, Add to Cart, Donations)
- Email (email input) - Email address to receive payments
- Item Name (text input)
- Item ID (text input) - SKU or identifier
- Price (number input)
- Currency (dropdown - 10 currencies)
- Shipping (number input)
- Tax Rate % (number input)

**Currency Options:**
- USD - US Dollar
- EUR - Euro
- GBP - British Pound
- INR - Indian Rupee
- JPY - Japanese Yen
- AUD - Australian Dollar
- CAD - Canadian Dollar
- CNY - Chinese Yuan
- BRL - Brazilian Real
- MXN - Mexican Peso

**QR Data Format:**
```
https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=merchant@example.com&item_name=Product%20Name&item_number=SKU-123&amount=10.00&currency_code=USD&shipping=5.00&tax_rate=10.00
```

**Standard:** PayPal Payment Standard API

**Use Case:** E-commerce, donations, service payments, product sales

**Payment Types:**
- Buy Now: Immediate purchase
- Add to Cart: Shopping cart functionality
- Donations: Charitable contributions

---

### 14. Payment (UPI)
**Fields:**
- Payment Type (dropdown: UPI, Paytm, PhonePe, Google Pay)
- UPI ID (text input) - username@upi format
- Payee Name (text input, optional)
- Amount (number input, optional)
- Note (text input, optional)

**Payment Types:**
- UPI (Universal standard)
- Paytm (Popular wallet)
- PhonePe (Digital payment)
- Google Pay (Google's payment app)

**QR Data Format:**
```
upi://pay?pa=username@upi&pn=John%20Doe&am=100.00&tn=Payment%20for%20services
```

**Standard:** NPCI UPI specification (India)

**Use Case:** Peer-to-peer payments, merchant payments, bill splitting

**Parameters:**
- pa: Payee Address (UPI ID)
- pn: Payee Name
- am: Amount
- tn: Transaction Note
- cu: Currency (defaults to INR)

---

## Technical Implementation Details

### Field Visibility Logic
Each content type has its own field group that is shown/hidden based on the selected type:

```javascript
// Field groups
- simpleContent (url, text)
- emailFields
- phoneFields
- smsFields
- whatsappFields
- skypeFields
- zoomFields
- wifiFields
- vcardFields
- locationFields
- eventFields
- paypalFields
- paymentFields
```

### QR Content Generation
The `buildQRContent()` function handles all content types:

1. **Gets field values** from the appropriate input elements
2. **Formats data** according to the standard for each type
3. **Encodes special characters** using `encodeURIComponent()`
4. **Returns formatted string** ready for QR code generation

### Live Preview
All fields are connected to the live preview system:
- Changes trigger debounced preview update (500ms delay)
- Preview updates automatically on field change
- No page reload required

### Data Validation
- Required fields are enforced through HTML5 validation
- Format validation for emails, URLs, numbers
- Proper encoding prevents injection attacks

---

## QR Code Standards Compliance

### Encoding
- **UTF-8**: All text content
- **URL Encoding**: Query parameters and special characters
- **Format Compliance**: Each type follows its specification

### Error Correction Levels
- **L (Low)**: 7% recovery
- **M (Medium)**: 15% recovery
- **Q (Quartile)**: 25% recovery
- **H (High)**: 30% recovery (default)

### Size Options
- Small: 150x150px
- Medium: 200x200px
- Large: 300x300px (default)
- Extra Large: 400x400px
- Huge: 500x500px

---

## Browser Compatibility

### Desktop Browsers
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Mobile Browsers
- ✅ Chrome Mobile
- ✅ Safari iOS
- ✅ Samsung Internet
- ✅ Firefox Mobile

### Input Type Support
- `datetime-local`: Modern browsers (fallback for older)
- `email`: All browsers
- `url`: All browsers
- `number`: All browsers
- `color`: Modern browsers

---

## Testing Checklist

### QR Code Scanning Tests
- [ ] URL QR opens browser to correct page
- [ ] Email QR opens mail client with pre-filled fields
- [ ] Phone QR initiates call
- [ ] SMS QR opens messaging app with text
- [ ] WhatsApp QR opens WhatsApp with message
- [ ] Skype QR launches Skype with correct action
- [ ] Zoom QR opens Zoom with meeting details
- [ ] WiFi QR connects to network
- [ ] vCard QR creates contact with all fields
- [ ] Location QR opens maps to correct coordinates
- [ ] Event QR creates calendar entry with reminder
- [ ] PayPal QR opens payment page with details
- [ ] UPI QR opens payment app with transaction

### Cross-Platform Tests
- [ ] iOS devices (iPhone, iPad)
- [ ] Android devices (various brands)
- [ ] Desktop QR scanners
- [ ] Web-based QR scanners

### Field Validation Tests
- [ ] Required fields prevent generation
- [ ] Invalid emails show error
- [ ] Invalid URLs show error
- [ ] Number fields accept decimals
- [ ] Country codes format correctly
- [ ] Special characters encode properly

---

## Known Limitations

### Location (Map with Search)
- **Current**: Manual coordinate entry
- **Future**: Google Maps API integration for address search
- **Workaround**: Users can use Google Maps to find coordinates

### Currency Support
- PayPal: 10 most common currencies
- UPI: INR only (India-specific)
- Future: More currency options can be added

### Date/Time Formats
- Browser compatibility varies for `datetime-local` input
- Older browsers may show text input instead
- Format is validated server-side

---

## Future Enhancements

### Planned Features
1. **Google Maps Integration**
   - Address autocomplete
   - Drag-and-drop marker
   - Reverse geocoding

2. **QR Code Templates**
   - Save frequently used configurations
   - Quick-select presets
   - Template library

3. **Bulk Generation**
   - CSV import for multiple QR codes
   - Batch processing
   - ZIP download

4. **Analytics**
   - Scan tracking
   - Location data
   - Time-based analytics

5. **Advanced vCard**
   - Multiple phone numbers
   - Multiple emails
   - Social media links
   - Profile photo

6. **Enhanced PayPal**
   - Subscription support
   - Recurring payments
   - Multiple items

---

## Support & Resources

### Documentation
- [QR Code Standards](https://www.qrcode.com/en/)
- [vCard Specification](https://datatracker.ietf.org/doc/html/rfc2426)
- [iCalendar Specification](https://datatracker.ietf.org/doc/html/rfc5545)
- [URI Schemes](https://www.iana.org/assignments/uri-schemes/uri-schemes.xhtml)

### Testing Tools
- QR Code Scanner apps (iOS/Android)
- Online QR decoders
- Browser developer tools

### Contact Support
For issues or feature requests, please contact the development team.

---

**Last Updated:** 2026-02-08
**Version:** 1.0.0
**Status:** Production Ready ✅
