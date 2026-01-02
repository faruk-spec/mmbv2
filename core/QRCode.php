<?php
/**
 * QR Code Generator
 * 
 * Simple QR code generation using SVG
 * Part of Phase 7: Advanced ProShare Features
 * 
 * @package MMB\Core
 */

namespace Core;

class QRCode
{
    /**
     * Generate QR code as SVG
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels (default 200)
     * @return string SVG markup
     */
    public static function generate(string $data, int $size = 200): string
    {
        // For a production implementation, you would use a proper QR code library
        // This is a placeholder that generates a simple visual representation
        
        // Use Google Charts API as a simple solution (requires internet)
        $encodedData = urlencode($data);
        $apiUrl = "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl={$encodedData}";
        
        // Return an image tag that loads the QR code
        return "<img src=\"{$apiUrl}\" alt=\"QR Code\" width=\"{$size}\" height=\"{$size}\" />";
    }
    
    /**
     * Generate QR code as data URL (base64 encoded image)
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels
     * @return string|false Data URL or false on failure
     */
    public static function generateDataUrl(string $data, int $size = 200)
    {
        $encodedData = urlencode($data);
        $apiUrl = "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl={$encodedData}";
        
        // Fetch the image
        $imageData = @file_get_contents($apiUrl);
        if ($imageData === false) {
            return false;
        }
        
        // Convert to base64
        $base64 = base64_encode($imageData);
        return "data:image/png;base64,{$base64}";
    }
    
    /**
     * Generate QR code and save to file
     * 
     * @param string $data Data to encode
     * @param string $filepath Path to save the QR code
     * @param int $size Size in pixels
     * @return bool Success status
     */
    public static function saveToFile(string $data, string $filepath, int $size = 200): bool
    {
        $encodedData = urlencode($data);
        $apiUrl = "https://chart.googleapis.com/chart?cht=qr&chs={$size}x{$size}&chl={$encodedData}";
        
        // Fetch the image
        $imageData = @file_get_contents($apiUrl);
        if ($imageData === false) {
            return false;
        }
        
        // Save to file
        return file_put_contents($filepath, $imageData) !== false;
    }
    
    /**
     * Generate QR code as inline SVG (for better styling)
     * 
     * This is a simplified version for demonstration
     * For production, use a proper QR code library like endroid/qr-code
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels
     * @return string SVG markup
     */
    public static function generateSVG(string $data, int $size = 200): string
    {
        // This is a placeholder
        // In production, you should use a proper PHP QR code library
        $hash = md5($data);
        
        $svg = <<<SVG
<svg width="{$size}" height="{$size}" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
    <rect width="100" height="100" fill="white"/>
    <text x="50" y="50" text-anchor="middle" font-size="8" fill="black">QR Code</text>
    <text x="50" y="60" text-anchor="middle" font-size="6" fill="gray">{$hash}</text>
</svg>
SVG;
        
        return $svg;
    }
    
    /**
     * Generate share link with QR code
     * 
     * @param string $url URL to share
     * @param string $title Optional title
     * @return array HTML and download URL
     */
    public static function generateShareLink(string $url, string $title = ''): array
    {
        $qrCode = self::generate($url, 200);
        $dataUrl = self::generateDataUrl($url, 200);
        
        return [
            'html' => $qrCode,
            'data_url' => $dataUrl,
            'download_url' => $dataUrl,
            'share_url' => $url,
            'title' => $title ?: 'Scan to access'
        ];
    }
}
