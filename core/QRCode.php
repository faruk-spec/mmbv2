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
        // Use our standalone QR code generator
        return QRCodeGenerator::generateSVG($data, $size);
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
        try {
            return QRCodeGenerator::generate($data, $size);
        } catch (\Exception $e) {
            Logger::error('QR generation error: ' . $e->getMessage());
            return false;
        }
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
        try {
            return QRCodeGenerator::saveToFile($data, $filepath, $size);
        } catch (\Exception $e) {
            Logger::error('QR save error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate QR code as inline SVG (for better styling)
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels
     * @return string SVG markup
     */
    public static function generateSVG(string $data, int $size = 200): string
    {
        try {
            return QRCodeGenerator::generateSVG($data, $size);
        } catch (\Exception $e) {
            Logger::error('QR SVG generation error: ' . $e->getMessage());
            return '<svg></svg>';
        }
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
