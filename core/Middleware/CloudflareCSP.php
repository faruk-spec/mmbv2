<?php
/**
 * Optional: Add Content Security Policy Headers
 * 
 * This file can be included in index.php or as middleware to fix
 * Cloudflare CORS errors if Cloudflare Web Analytics is enabled.
 * 
 * Only use this if you're seeing CORS errors from cloudflareinsights.com
 * and want to keep Cloudflare analytics enabled.
 * 
 * @package MMB\Core
 */

namespace Core\Middleware;

use Core\Security;

class CloudflareCSP
{
    /**
     * Add CSP headers to allow Cloudflare beacon
     */
    public static function addHeaders(): void
    {
        // Only add if not already set
        if (!headers_sent()) {
            $nonce = Security::getCspNonce();
            // Allow Cloudflare scripts and connections
            header("Content-Security-Policy: " . implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'nonce-{$nonce}' https://static.cloudflareinsights.com https://cdnjs.cloudflare.com https://fonts.googleapis.com",
                "script-src-attr 'none'",
                "style-src 'self' 'nonce-{$nonce}' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
                "style-src-attr 'none'",
                "connect-src 'self' https://cloudflareinsights.com",
                "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
                "img-src 'self' data: https:",
                "frame-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "upgrade-insecure-requests"
            ]));
        }
    }
    
    /**
     * Alternative: Add only minimal CSP for Cloudflare
     */
    public static function addMinimalHeaders(): void
    {
        if (!headers_sent()) {
            $nonce = Security::getCspNonce();
            // Minimal CSP just for Cloudflare beacon
            header("Content-Security-Policy: script-src 'self' 'nonce-{$nonce}' https://static.cloudflareinsights.com; script-src-attr 'none'; connect-src 'self' https://cloudflareinsights.com;");
        }
    }
}

/**
 * Usage Instructions:
 * 
 * Option 1: Add to index.php (before any output)
 * -----------
 * require_once BASE_PATH . '/core/Middleware/CloudflareCSP.php';
 * \Core\Middleware\CloudflareCSP::addMinimalHeaders();
 * 
 * Option 2: Add to .htaccess (Apache) only if you can inject a dynamic nonce
 * -----------
 * Static .htaccess values cannot generate a fresh nonce per request, so the PHP middleware
 * approach above is recommended. Using a static nonce value (or falling back to unsafe-inline)
 * would significantly weaken the CSP and should be avoided. If your server can inject a
 * per-request nonce, mirror the same script-src / script-src-attr directives there.
 * 
 * Option 3: Add to nginx.conf (Nginx) only if you can inject a dynamic nonce
 * -----------
 * Static nginx config also cannot generate a fresh nonce by itself. Use application-level
 * middleware or server-side scripting that injects a unique nonce on every response; otherwise
 * the CSP loses most of the protection that nonce-based execution is meant to provide.
 * 
 * Option 4: Disable in Cloudflare Dashboard (Recommended if not needed)
 * -----------
 * 1. Login to Cloudflare
 * 2. Go to Analytics > Web Analytics
 * 3. Turn OFF if you don't need it
 * 
 * Note: The CORS error is cosmetic and doesn't break functionality.
 * Only fix if console errors bother you or you need the analytics.
 */
