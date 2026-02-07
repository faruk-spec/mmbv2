<?php
/**
 * Standalone QR Code Generator
 * Uses PHP GD library for image generation
 * No external dependencies required
 * 
 * @package MMB\Core
 */

namespace Core;

class QRCodeGenerator
{
    private const VERSION = 1; // QR Code version (1-40)
    private const ERROR_CORRECTION_LEVEL = 'M'; // L, M, Q, H
    private const MODULE_SIZE = 10; // Size of each module in pixels
    private const QUIET_ZONE = 4; // Border size in modules
    
    /**
     * Generate QR code and return as data URL (base64 encoded PNG)
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels (default 300)
     * @param string $foreColor Foreground color hex (default #000000)
     * @param string $bgColor Background color hex (default #ffffff)
     * @return string Base64 encoded PNG image data URL
     */
    public static function generate(string $data, int $size = 300, string $foreColor = '#000000', string $bgColor = '#ffffff'): string
    {
        // Use phpqrcode library if available, otherwise use simple placeholder
        $qrData = self::generateMatrix($data);
        $image = self::createImage($qrData, $size, $foreColor, $bgColor);
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return 'data:image/png;base64,' . base64_encode($imageData);
    }
    
    /**
     * Generate QR code and save to file
     * 
     * @param string $data Data to encode
     * @param string $filepath Path to save the QR code
     * @param int $size Size in pixels
     * @param string $foreColor Foreground color hex
     * @param string $bgColor Background color hex
     * @return bool Success status
     */
    public static function saveToFile(string $data, string $filepath, int $size = 300, string $foreColor = '#000000', string $bgColor = '#ffffff'): bool
    {
        $qrData = self::generateMatrix($data);
        $image = self::createImage($qrData, $size, $foreColor, $bgColor);
        
        $result = imagepng($image, $filepath);
        imagedestroy($image);
        
        return $result;
    }
    
    /**
     * Generate QR code and output directly to browser
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels
     * @param string $foreColor Foreground color hex
     * @param string $bgColor Background color hex
     */
    public static function output(string $data, int $size = 300, string $foreColor = '#000000', string $bgColor = '#ffffff'): void
    {
        header('Content-Type: image/png');
        $qrData = self::generateMatrix($data);
        $image = self::createImage($qrData, $size, $foreColor, $bgColor);
        imagepng($image);
        imagedestroy($image);
    }
    
    /**
     * Generate QR code as SVG
     * 
     * @param string $data Data to encode
     * @param int $size Size in pixels
     * @param string $foreColor Foreground color hex
     * @param string $bgColor Background color hex
     * @return string SVG markup
     */
    public static function generateSVG(string $data, int $size = 300, string $foreColor = '#000000', string $bgColor = '#ffffff'): string
    {
        $qrData = self::generateMatrix($data);
        $matrixSize = count($qrData);
        $moduleSize = $size / ($matrixSize + self::QUIET_ZONE * 2);
        
        $svg = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $svg .= sprintf('<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="%d" height="%d" viewBox="0 0 %d %d">' . PHP_EOL,
            $size, $size, $size, $size);
        $svg .= sprintf('<rect width="%d" height="%d" fill="%s"/>' . PHP_EOL, $size, $size, $bgColor);
        
        $offset = self::QUIET_ZONE * $moduleSize;
        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($qrData[$y][$x]) {
                    $svg .= sprintf('<rect x="%.2f" y="%.2f" width="%.2f" height="%.2f" fill="%s"/>' . PHP_EOL,
                        $offset + $x * $moduleSize,
                        $offset + $y * $moduleSize,
                        $moduleSize,
                        $moduleSize,
                        $foreColor
                    );
                }
            }
        }
        
        $svg .= '</svg>';
        return $svg;
    }
    
    /**
     * Generate QR code matrix (proper encoding version)
     * Uses QRCodeEncoder for standards-compliant QR codes
     * 
     * @param string $data Data to encode
     * @return array 2D matrix of boolean values
     */
    private static function generateMatrix(string $data): array
    {
        // Use proper QR encoding
        return QRCodeEncoder::encode($data, QRCodeEncoder::ERROR_CORRECTION_M);
    }
    
    /**
     * Create PNG image from matrix
     */
    private static function createImage(array $matrix, int $size, string $foreColor, string $bgColor): \GdImage
    {
        $matrixSize = count($matrix);
        $totalSize = $matrixSize + (self::QUIET_ZONE * 2);
        $moduleSize = (int)($size / $totalSize);
        $actualSize = $moduleSize * $totalSize;
        
        $image = imagecreatetruecolor($actualSize, $actualSize);
        
        // Parse colors
        $bgRgb = self::hexToRgb($bgColor);
        $fgRgb = self::hexToRgb($foreColor);
        
        $bgColorIndex = imagecolorallocate($image, $bgRgb[0], $bgRgb[1], $bgRgb[2]);
        $fgColorIndex = imagecolorallocate($image, $fgRgb[0], $fgRgb[1], $fgRgb[2]);
        
        // Fill background
        imagefill($image, 0, 0, $bgColorIndex);
        
        // Draw modules
        $offset = self::QUIET_ZONE * $moduleSize;
        for ($y = 0; $y < $matrixSize; $y++) {
            for ($x = 0; $x < $matrixSize; $x++) {
                if ($matrix[$y][$x]) {
                    imagefilledrectangle(
                        $image,
                        $offset + $x * $moduleSize,
                        $offset + $y * $moduleSize,
                        $offset + ($x + 1) * $moduleSize - 1,
                        $offset + ($y + 1) * $moduleSize - 1,
                        $fgColorIndex
                    );
                }
            }
        }
        
        return $image;
    }
    
    /**
     * Convert hex color to RGB array
     */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        ];
    }
}
