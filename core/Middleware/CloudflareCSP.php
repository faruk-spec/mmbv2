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

class CloudflareCSP
{
    /**
     * Add CSP headers to allow Cloudflare beacon
     */
    public static function addHeaders(): void
    {
        // Only add if not already set
        if (!headers_sent()) {
            // Allow Cloudflare scripts and connections
            header("Content-Security-Policy: " . implode('; ', [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://static.cloudflareinsights.com https://cdnjs.cloudflare.com https://fonts.googleapis.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com",
                "connect-src 'self' https://cloudflareinsights.com",
                "font-src 'self' https://fonts.gstatic.com https://cdnjs.cloudflare.com",
                "img-src 'self' data: https:",
                "frame-src 'self'"
            ]));
        }
    }
    
    /**
     * Alternative: Add only minimal CSP for Cloudflare
     */
    public static function addMinimalHeaders(): void
    {
        if (!headers_sent()) {
            // Minimal CSP just for Cloudflare beacon
            header("Content-Security-Policy: script-src 'self' 'unsafe-inline' https://static.cloudflareinsights.com; connect-src 'self' https://cloudflareinsights.com;");
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
 * Option 2: Add to .htaccess (Apache)
 * -----------
 * Header set Content-Security-Policy "script-src 'self' 'unsafe-inline' https://static.cloudflareinsights.com; connect-src 'self' https://cloudflareinsights.com;"
 * 
 * Option 3: Add to nginx.conf (Nginx)
 * -----------
 * add_header Content-Security-Policy "script-src 'self' 'unsafe-inline' https://static.cloudflareinsights.com; connect-src 'self' https://cloudflareinsights.com;";
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
