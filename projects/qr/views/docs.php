<?php
/**
 * QR Generator — User Documentation
 */
?>
<style>
.docs-hero{background:linear-gradient(135deg,rgba(153,69,255,.15),rgba(0,240,255,.08));border:1px solid var(--border-color);border-radius:14px;padding:2.5rem 2rem;margin-bottom:2rem;text-align:center;}
.docs-hero h1{font-size:2rem;font-weight:800;background:linear-gradient(135deg,var(--purple),var(--cyan));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0 0 .5rem;}
.docs-toc{background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.25rem 1.5rem;margin-bottom:2rem;}
.docs-toc h3{font-size:.85rem;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.05em;margin:0 0 .75rem;}
.docs-toc ul{list-style:none;margin:0;padding:0;columns:2;column-gap:2rem;}
.docs-toc li{padding:2px 0;}
.docs-toc a{color:var(--cyan);font-size:.83rem;text-decoration:none;}
.docs-toc a:hover{text-decoration:underline;}
.docs-section{background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.5rem;margin-bottom:1.5rem;scroll-margin-top:80px;}
.docs-section h2{font-size:1.1rem;font-weight:700;color:var(--text-primary);margin:0 0 .25rem;display:flex;align-items:center;gap:.5rem;}
.docs-section .section-meta{font-size:.75rem;color:var(--text-secondary);margin-bottom:.75rem;}
.docs-section p,.docs-section li{font-size:.875rem;color:var(--text-secondary);line-height:1.7;}
.docs-section ol,.docs-section ul{padding-left:1.25rem;margin:.5rem 0;}
.docs-section li{margin-bottom:.25rem;}
.badge-free{background:rgba(0,255,136,.1);color:var(--green);border:1px solid rgba(0,255,136,.3);padding:2px 8px;border-radius:12px;font-size:.7rem;font-weight:700;}
.badge-pro{background:rgba(153,69,255,.15);color:var(--purple);border:1px solid rgba(153,69,255,.3);padding:2px 8px;border-radius:12px;font-size:.7rem;font-weight:700;}
.badge-enterprise{background:rgba(255,46,196,.12);color:var(--magenta);border:1px solid rgba(255,46,196,.3);padding:2px 8px;border-radius:12px;font-size:.7rem;font-weight:700;}
.docs-tip{background:rgba(0,240,255,.06);border-left:3px solid var(--cyan);padding:.75rem 1rem;border-radius:0 8px 8px 0;margin:.75rem 0;font-size:.83rem;color:var(--text-secondary);}
.docs-warn{background:rgba(255,159,64,.07);border-left:3px solid #ff9f40;padding:.75rem 1rem;border-radius:0 8px 8px 0;margin:.75rem 0;font-size:.83rem;color:var(--text-secondary);}
.feature-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem;margin:.75rem 0;}
.feature-item{background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:.75rem;display:flex;align-items:flex-start;gap:.5rem;}
.feature-item i{margin-top:2px;flex-shrink:0;width:16px;text-align:center;}
.feature-item strong{font-size:.83rem;color:var(--text-primary);display:block;}
.feature-item span{font-size:.75rem;color:var(--text-secondary);}
code.inline{background:rgba(0,0,0,.3);padding:2px 6px;border-radius:4px;font-size:.82rem;font-family:monospace;}
@media(max-width:640px){.docs-toc ul{columns:1;}.feature-grid{grid-template-columns:1fr;}.docs-hero h1{font-size:1.5rem;}}
</style>

<div class="docs-hero">
    <h1><i class="fas fa-book-open" style="-webkit-text-fill-color:var(--purple);"></i> QR Generator Documentation</h1>
    <p style="color:var(--text-secondary);margin:0;font-size:.9rem;">Complete guide to all 25 features of the QR Generator</p>
</div>

<!-- Table of Contents -->
<div class="docs-toc">
    <h3>Table of Contents</h3>
    <ul>
        <li><a href="#static-qr">Static QR Codes</a></li>
        <li><a href="#dynamic-qr">Dynamic QR Codes</a></li>
        <li><a href="#content-types">Content Types</a></li>
        <li><a href="#analytics">Scan Analytics</a></li>
        <li><a href="#campaigns">Campaign Management</a></li>
        <li><a href="#password">Password Protection</a></li>
        <li><a href="#expiry">Expiry Date</a></li>
        <li><a href="#scan-limit">Max Scan Limit</a></li>
        <li><a href="#utm">UTM Tracking</a></li>
        <li><a href="#qr-label">QR Label / Note</a></li>
        <li><a href="#colors">Custom Colors</a></li>
        <li><a href="#logo">Custom Logo</a></li>
        <li><a href="#frames">Frame Styles</a></li>
        <li><a href="#presets">Design Presets</a></li>
        <li><a href="#logo-bg">Remove Logo Background</a></li>
        <li><a href="#download">Download Formats</a></li>
        <li><a href="#bulk">Bulk Generation</a></li>
        <li><a href="#ai-design">AI Design</a></li>
        <li><a href="#api">API Access</a></li>
        <li><a href="#whitelabel">White-Label</a></li>
        <li><a href="#team">Team Roles</a></li>
        <li><a href="#support">Priority Support</a></li>
        <li><a href="#export">Export Scan Data</a></li>
    </ul>
</div>

<!-- Static QR -->
<div class="docs-section" id="static-qr">
    <h2><i class="fas fa-qrcode" style="color:var(--cyan);"></i> Static QR Codes <span class="badge-free">Free</span></h2>
    <div class="section-meta">Available on all plans</div>
    <p>Static QR codes encode content directly into the QR image. Once generated, the destination cannot be changed without creating a new QR code.</p>
    <ul>
        <li>Content is embedded permanently in the QR pattern</li>
        <li>Works without internet connectivity on the scanning device</li>
        <li>Best for: business cards, menus, permanent signage</li>
        <li>Supported types: URL, Text, Email, Phone, SMS, WiFi, vCard, Location, and more</li>
    </ul>
    <div class="docs-tip">💡 Static QR codes are simpler and work offline. Use them when the destination won't change.</div>
</div>

<!-- Dynamic QR -->
<div class="docs-section" id="dynamic-qr">
    <h2><i class="fas fa-sync" style="color:var(--purple);"></i> Dynamic QR Codes <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Dynamic QR feature</div>
    <p>Dynamic QR codes use a redirect URL so you can change the destination after printing without reprinting the QR code.</p>
    <ul>
        <li>Change the destination URL anytime from your dashboard</li>
        <li>Track scans, locations, device types, and time</li>
        <li>Support password protection, expiry dates, and scan limits</li>
        <li>Scan analytics available (requires Analytics feature)</li>
    </ul>
    <ol>
        <li>Check <strong>Enable Dynamic QR</strong> on the generate page</li>
        <li>Enter your destination URL in the redirect field</li>
        <li>Generate and download your QR code</li>
        <li>Update the destination anytime from <strong>My QR Codes → Edit</strong></li>
    </ol>
    <div class="docs-warn">⚠️ Dynamic QR codes require internet access to redirect. They will not work offline.</div>
</div>

<!-- Content Types -->
<div class="docs-section" id="content-types">
    <h2><i class="fas fa-tag" style="color:var(--cyan);"></i> Content Types <span class="badge-free">Free</span></h2>
    <div class="section-meta">16 content types supported</div>
    <p>Choose the right content type for your use case. Each type formats the QR code data according to the relevant standard.</p>
    <div class="feature-grid">
        <div class="feature-item"><i class="fas fa-globe" style="color:var(--cyan);"></i><div><strong>URL / Website</strong><span>Link to any web page</span></div></div>
        <div class="feature-item"><i class="fas fa-file-alt" style="color:var(--purple);"></i><div><strong>Plain Text</strong><span>Display text directly</span></div></div>
        <div class="feature-item"><i class="fas fa-envelope" style="color:var(--cyan);"></i><div><strong>Email</strong><span>Pre-fill email compose</span></div></div>
        <div class="feature-item"><i class="fas fa-phone" style="color:var(--green);"></i><div><strong>Phone Call</strong><span>Dial a number</span></div></div>
        <div class="feature-item"><i class="fas fa-sms" style="color:var(--purple);"></i><div><strong>SMS</strong><span>Pre-fill text message</span></div></div>
        <div class="feature-item"><i class="fab fa-whatsapp" style="color:var(--green);"></i><div><strong>WhatsApp</strong><span>Open WhatsApp chat</span></div></div>
        <div class="feature-item"><i class="fas fa-wifi" style="color:var(--cyan);"></i><div><strong>WiFi</strong><span>Connect to WiFi network</span></div></div>
        <div class="feature-item"><i class="fas fa-map-marker-alt" style="color:var(--magenta);"></i><div><strong>Location</strong><span>Open map at coordinates</span></div></div>
        <div class="feature-item"><i class="fas fa-id-card" style="color:var(--purple);"></i><div><strong>vCard</strong><span>Share contact info</span></div></div>
        <div class="feature-item"><i class="fas fa-calendar" style="color:var(--cyan);"></i><div><strong>Calendar Event</strong><span>Add event to calendar</span></div></div>
        <div class="feature-item"><i class="fas fa-share-alt" style="color:var(--magenta);"></i><div><strong>Social Media</strong><span>Link to social profiles</span></div></div>
        <div class="feature-item"><i class="fas fa-mobile-alt" style="color:var(--green);"></i><div><strong>App Store</strong><span>iOS / Android app link</span></div></div>
        <div class="feature-item"><i class="fab fa-bitcoin" style="color:#f7931a;"></i><div><strong>Crypto</strong><span>Crypto payment address</span></div></div>
        <div class="feature-item"><i class="fas fa-utensils" style="color:var(--cyan);"></i><div><strong>Restaurant Menu</strong><span>Link to menu with table</span></div></div>
        <div class="feature-item"><i class="fab fa-paypal" style="color:#003087;"></i><div><strong>PayPal</strong><span>Payment / donation link</span></div></div>
        <div class="feature-item"><i class="fas fa-rupee-sign" style="color:var(--green);"></i><div><strong>UPI Payment</strong><span>Indian UPI payment</span></div></div>
    </div>
</div>

<!-- Analytics -->
<div class="docs-section" id="analytics">
    <h2><i class="fas fa-chart-line" style="color:var(--purple);"></i> Scan Analytics <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Analytics feature • <a href="/projects/qr/analytics" style="color:var(--cyan);">Go to Analytics →</a></div>
    <p>Track every scan of your dynamic QR codes in real time.</p>
    <ul>
        <li><strong>Scan Trends</strong> — daily scan counts over a date range</li>
        <li><strong>Top QR Codes</strong> — which codes are scanned the most</li>
        <li><strong>Device types</strong> — mobile vs. desktop breakdown</li>
        <li><strong>Location data</strong> — country and city of each scan</li>
        <li><strong>Referrer tracking</strong> — where scans came from</li>
        <li><strong>Date range filters</strong> — today, 7d, 30d, 90d, or custom</li>
    </ul>
    <div class="docs-tip">💡 Analytics are only recorded for <strong>Dynamic QR</strong> codes.</div>
</div>

<!-- Campaigns -->
<div class="docs-section" id="campaigns">
    <h2><i class="fas fa-folder" style="color:var(--cyan);"></i> Campaign Management <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Campaigns feature • <a href="/projects/qr/campaigns" style="color:var(--cyan);">Go to Campaigns →</a></div>
    <p>Group related QR codes together into campaigns for organised tracking and management.</p>
    <ol>
        <li>Go to <strong>Campaigns → New Campaign</strong></li>
        <li>Give the campaign a name and optional description</li>
        <li>When generating a QR code, select the campaign from the dropdown</li>
        <li>View aggregate scan stats per campaign in the campaign detail page</li>
    </ol>
</div>

<!-- Password Protection -->
<div class="docs-section" id="password">
    <h2><i class="fas fa-lock" style="color:var(--purple);"></i> Password Protection <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Password Protection feature</div>
    <p>Require scanners to enter a password before viewing the QR content.</p>
    <ol>
        <li>On the generate page, expand <strong>Advanced Features</strong></li>
        <li>Enter a password (minimum 4 characters) in the <strong>Password Protection</strong> field</li>
        <li>Generate and share the QR code</li>
        <li>When scanned, users see a branded access form before being redirected</li>
    </ol>
    <div class="docs-warn">⚠️ Passwords are hashed and cannot be recovered. Keep a record of passwords you set.</div>
</div>

<!-- Expiry -->
<div class="docs-section" id="expiry">
    <h2><i class="fas fa-clock" style="color:var(--cyan);"></i> Expiry Date <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Expiry Date feature</div>
    <p>Set a date and time after which the QR code stops working and shows an "expired" page.</p>
    <ol>
        <li>In <strong>Advanced Features</strong>, set the <strong>Expiry Date/Time</strong></li>
        <li>After this time, scanning the QR shows a customisable expired message</li>
        <li>The QR image itself remains unchanged — only the redirect is disabled</li>
    </ol>
</div>

<!-- Scan Limit -->
<div class="docs-section" id="scan-limit">
    <h2><i class="fas fa-tachometer-alt" style="color:var(--purple);"></i> Max Scan Limit <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Scan Limit feature</div>
    <p>Automatically deactivate a QR code after it has been scanned a set number of times. Ideal for limited-offer promotions.</p>
    <ol>
        <li>In <strong>Advanced Features</strong>, enter a number in <strong>Max Scan Limit</strong></li>
        <li>After that many unique scans, the QR shows a "limit reached" page</li>
        <li>You can reset or remove the limit by editing the QR code</li>
    </ol>
</div>

<!-- UTM Tracking -->
<div class="docs-section" id="utm">
    <h2><i class="fas fa-tag" style="color:var(--cyan);"></i> UTM Tracking Parameters <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires UTM Tracking feature • URL type only</div>
    <p>Automatically append Google Analytics UTM parameters to your URL so you can track QR scans in Google Analytics / GA4.</p>
    <ul>
        <li><code class="inline">utm_source</code> — e.g. <code class="inline">qr_code</code></li>
        <li><code class="inline">utm_medium</code> — e.g. <code class="inline">print</code></li>
        <li><code class="inline">utm_campaign</code> — e.g. <code class="inline">summer_sale</code></li>
        <li><code class="inline">utm_term</code> — optional keyword</li>
        <li><code class="inline">utm_content</code> — optional variant identifier</li>
    </ul>
    <div class="docs-tip">💡 UTM parameters are appended server-side on every redirect, so the QR code image never changes when you update them.</div>
</div>

<!-- QR Label -->
<div class="docs-section" id="qr-label">
    <h2><i class="fas fa-sticky-note" style="color:var(--purple);"></i> QR Label / Note <span class="badge-free">Free</span></h2>
    <div class="section-meta">Available on all plans</div>
    <p>Add a private label or note to each QR code for your own reference. Labels are only visible to you in the dashboard — they do not appear on the QR image.</p>
</div>

<!-- Custom Colors -->
<div class="docs-section" id="colors">
    <h2><i class="fas fa-palette" style="color:var(--cyan);"></i> Custom Colors <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Custom Colors feature</div>
    <p>Change the foreground and background colors of the QR code. You can also enable a gradient effect on the foreground.</p>
    <ul>
        <li><strong>Foreground color</strong> — color of the QR dots</li>
        <li><strong>Background color</strong> — color of the background</li>
        <li><strong>Gradient</strong> — smooth two-color gradient across the foreground</li>
        <li><strong>Transparent background</strong> — removes the background entirely (PNG only)</li>
    </ul>
    <div class="docs-warn">⚠️ Always ensure sufficient contrast between foreground and background for reliable scanning.</div>
</div>

<!-- Custom Logo -->
<div class="docs-section" id="logo">
    <h2><i class="fas fa-image" style="color:var(--purple);"></i> Custom Logo / Branding <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Custom Logo feature</div>
    <p>Embed your brand logo in the centre of the QR code.</p>
    <ol>
        <li>Expand the <strong>Logo</strong> section on the generate page</li>
        <li>Choose <strong>Upload Logo</strong> and select a PNG, JPG, or SVG image</li>
        <li>Adjust logo size with the slider (10%–50%)</li>
        <li>Optionally set a logo color and enable <strong>Remove Background</strong></li>
    </ol>
    <div class="docs-tip">💡 Use a square logo with a transparent background for best results. Increase error correction to High (H) when adding a logo.</div>
</div>

<!-- Frame Styles -->
<div class="docs-section" id="frames">
    <h2><i class="fas fa-border-all" style="color:var(--cyan);"></i> Frame Styles <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Frame Styles feature</div>
    <p>Add a decorative frame around your QR code with optional label text.</p>
    <ul>
        <li>Styles: Square, Circle, Rounded, Banner Top/Bottom, Speech Bubble, Badge</li>
        <li>Add custom text (e.g. "SCAN ME", "Visit our website")</li>
        <li>Choose label font and custom frame color</li>
    </ul>
</div>

<!-- Design Presets -->
<div class="docs-section" id="presets">
    <h2><i class="fas fa-shapes" style="color:var(--purple);"></i> Design Presets <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Design Presets feature</div>
    <p>Apply ready-made visual styles with one click — sets dot pattern, corner style, and colors simultaneously.</p>
    <ul>
        <li><strong>Dot Patterns</strong>: Square, Rounded, Dots, Classy, Classy Rounded</li>
        <li><strong>Corner Styles</strong>: Square, Extra Rounded, Dot</li>
        <li><strong>Marker Styles</strong>: border and centre can use different shapes</li>
    </ul>
</div>

<!-- Logo BG -->
<div class="docs-section" id="logo-bg">
    <h2><i class="fas fa-eraser" style="color:var(--cyan);"></i> Remove Logo Background <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Remove Logo Background feature</div>
    <p>Clears the QR code area behind your logo so the logo appears to float on the pattern rather than having a white box behind it.</p>
</div>

<!-- Download -->
<div class="docs-section" id="download">
    <h2><i class="fas fa-download" style="color:var(--purple);"></i> Download Formats <span class="badge-free">PNG Free</span> <span class="badge-pro">SVG/PDF Pro</span></h2>
    <div class="section-meta">PNG always available; SVG and PDF require plan features</div>
    <p>Click the <strong>Download</strong> button on the preview and select your format:</p>
    <ul>
        <li><strong>PNG</strong> — raster image, best for digital use and email</li>
        <li><strong>SVG</strong> — vector format, scales infinitely, best for print and design</li>
        <li><strong>PDF</strong> — print-ready document, ideal for professional printing</li>
    </ul>
    <div class="docs-tip">💡 For print, always use SVG or PDF to ensure crisp quality at any size.</div>
</div>

<!-- Bulk -->
<div class="docs-section" id="bulk">
    <h2><i class="fas fa-layer-group" style="color:var(--cyan);"></i> Bulk Generation <span class="badge-enterprise">Enterprise</span></h2>
    <div class="section-meta">Requires Bulk Generation feature • <a href="/projects/qr/bulk" style="color:var(--cyan);">Go to Bulk Generate →</a></div>
    <p>Generate hundreds of QR codes at once by uploading a CSV file.</p>
    <ol>
        <li>Prepare a CSV file with columns: <code class="inline">url</code>, <code class="inline">label</code> (optional), <code class="inline">type</code> (optional)</li>
        <li>Go to <strong>Bulk Generate</strong> and upload the CSV</li>
        <li>Preview the first rows and confirm</li>
        <li>Download the generated ZIP archive containing all QR codes</li>
    </ol>
    <div class="docs-tip">💡 CSV must have a header row. Maximum 500 rows per upload on the Enterprise plan.</div>
</div>

<!-- AI Design -->
<div class="docs-section" id="ai-design">
    <h2><i class="fas fa-magic" style="color:var(--purple);"></i> AI Design <span class="badge-enterprise">Enterprise</span></h2>
    <div class="section-meta">Requires AI Design feature</div>
    <p>Use AI-powered design presets that automatically select optimal colors, patterns, and styles for common use cases.</p>
    <ul>
        <li>Presets for: Business, Restaurant, Event, Retail, Healthcare, Education, Travel</li>
        <li>Each preset applies coordinated colors and QR styles</li>
        <li>Apply via URL parameter: <code class="inline">/projects/qr/generate?preset=restaurant</code></li>
    </ul>
</div>

<!-- API -->
<div class="docs-section" id="api">
    <h2><i class="fas fa-code" style="color:var(--cyan);"></i> API Access <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires API Access feature • <a href="/projects/qr/api" style="color:var(--cyan);">Go to API page →</a></div>
    <p>Generate and manage QR codes programmatically using the REST API.</p>
    <ul>
        <li><strong>Base URL:</strong> <code class="inline"><?= htmlspecialchars($_SERVER['HTTP_HOST'] ?? 'mmbtech.online') ?>/api/qr</code></li>
        <li>Authenticate with <code class="inline">Authorization: Bearer &lt;YOUR_API_KEY&gt;</code></li>
    </ul>
    <div class="feature-grid">
        <div class="feature-item"><i class="fas fa-list" style="color:var(--cyan);"></i><div><strong>GET /api/qr</strong><span>List your QR codes</span></div></div>
        <div class="feature-item"><i class="fas fa-eye" style="color:var(--purple);"></i><div><strong>GET /api/qr/{code}</strong><span>Get one QR code</span></div></div>
        <div class="feature-item"><i class="fas fa-plus" style="color:var(--green);"></i><div><strong>POST /api/qr</strong><span>Create a QR code</span></div></div>
        <div class="feature-item"><i class="fas fa-trash" style="color:var(--magenta);"></i><div><strong>DELETE /api/qr/{code}</strong><span>Delete a QR code</span></div></div>
    </div>
    <p>Generate your API key from the <a href="/projects/qr/api" style="color:var(--cyan);">API Access</a> page in the sidebar.</p>
</div>

<!-- White-Label -->
<div class="docs-section" id="whitelabel">
    <h2><i class="fas fa-trademark" style="color:var(--purple);"></i> White-Label / Custom Domain <span class="badge-enterprise">Enterprise</span></h2>
    <div class="section-meta">Requires White-Label feature</div>
    <p>Use your own domain for QR redirect URLs so scans go through <code class="inline">qr.yourbrand.com</code> instead of the platform domain.</p>
    <ul>
        <li>Dynamic QR redirect links use your custom domain</li>
        <li>Password and expiry pages display your branding</li>
        <li>Requires a DNS CNAME record pointing to the platform</li>
    </ul>
    <p>Contact support to set up white-label for your account.</p>
</div>

<!-- Team Roles -->
<div class="docs-section" id="team">
    <h2><i class="fas fa-users" style="color:var(--cyan);"></i> Team Roles <span class="badge-enterprise">Enterprise</span></h2>
    <div class="section-meta">Requires Team Roles feature</div>
    <p>Manage team access to your QR codes with role-based permissions.</p>
    <ul>
        <li><strong>User</strong> — can manage own QR codes only</li>
        <li><strong>Manager</strong> — can manage team QR codes</li>
        <li><strong>Owner</strong> — full account control</li>
    </ul>
    <p>Team role management is configured by the account owner. Contact your administrator.</p>
</div>

<!-- Priority Support -->
<div class="docs-section" id="support">
    <h2><i class="fas fa-headset" style="color:var(--purple);"></i> Priority Support <span class="badge-enterprise">Enterprise</span></h2>
    <div class="section-meta">Requires Priority Support feature</div>
    <p>Enterprise plan subscribers receive priority email support with a guaranteed 4-hour response time during business hours (9am–6pm IST, Mon–Fri).</p>
    <p>Email: <a href="mailto:support@mmbtech.online" style="color:var(--cyan);">support@mmbtech.online</a></p>
</div>

<!-- Export -->
<div class="docs-section" id="export">
    <h2><i class="fas fa-file-export" style="color:var(--cyan);"></i> Export Scan Data <span class="badge-pro">Pro</span></h2>
    <div class="section-meta">Requires Export Scan Data feature • Available in <a href="/projects/qr/analytics" style="color:var(--cyan);">Analytics</a></div>
    <p>Download your QR scan history as a CSV file for analysis in Excel, Google Sheets, or any BI tool.</p>
    <ol>
        <li>Go to <strong>Analytics</strong></li>
        <li>Set the date range filter</li>
        <li>Click <strong>Export CSV</strong></li>
        <li>The CSV includes: QR name, scan time, device, country, city, referrer</li>
    </ol>
</div>

<!-- Upgrade CTA -->
<div class="glass-card" style="text-align:center;padding:2rem;margin-top:1.5rem;background:linear-gradient(135deg,rgba(153,69,255,.1),rgba(0,240,255,.06));">
    <i class="fas fa-crown" style="font-size:2rem;color:var(--purple);display:block;margin-bottom:.75rem;"></i>
    <h3 style="color:var(--text-primary);margin:0 0 .5rem;">Unlock All Features</h3>
    <p style="color:var(--text-secondary);font-size:.875rem;margin:0 0 1.25rem;">Upgrade your plan to access Dynamic QR, Analytics, API, Bulk Generation and more.</p>
    <a href="/projects/qr/plan" style="display:inline-flex;align-items:center;gap:8px;padding:10px 28px;background:linear-gradient(135deg,var(--purple),var(--cyan));border-radius:8px;color:#000;font-weight:700;font-size:.9rem;text-decoration:none;">
        <i class="fas fa-arrow-up"></i> View Plans
    </a>
</div>
