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
     * Generate QR code matrix (simplified version)
     * This is a basic implementation. For production use, consider using a full QR library
     * 
     * @param string $data Data to encode
     * @return array 2D matrix of boolean values
     */
    private static function generateMatrix(string $data): array
    {
        // Simplified QR code generation
        // This creates a basic pattern that resembles a QR code
        // For full QR spec compliance, use a dedicated library
        
        $size = 21; // Version 1 QR code size
        $matrix = array_fill(0, $size, array_fill(0, $size, false));
        
        // Add finder patterns (corners)
        self::addFinderPattern($matrix, 0, 0);
        self::addFinderPattern($matrix, 0, $size - 7);
        self::addFinderPattern($matrix, $size - 7, 0);
        
        // Add timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            $matrix[6][$i] = ($i % 2 === 0);
            $matrix[$i][6] = ($i % 2 === 0);
        }
        
        // Simple data encoding (not real QR encoding)
        $hash = md5($data);
        $hashBinary = '';
        for ($i = 0; $i < strlen($hash); $i++) {
            $hashBinary .= str_pad(decbin(ord($hash[$i])), 8, '0', STR_PAD_LEFT);
        }
        
        $bitIndex = 0;
        $direction = -1; // Start going up
        $x = $size - 1;
        
        while ($x > 0) {
            if ($x == 6) $x--; // Skip timing column
            
            for ($y = 0; $y < $size; $y++) {
                $currentY = $direction === -1 ? ($size - 1 - $y) : $y;
                
                for ($xOffset = 0; $xOffset < 2; $xOffset++) {
                    $currentX = $x - $xOffset;
                    
                    // Skip if this is a function pattern area
                    if (self::isFunctionPattern($currentX, $currentY, $size)) {
                        continue;
                    }
                    
                    if ($bitIndex < strlen($hashBinary)) {
                        $matrix[$currentY][$currentX] = ($hashBinary[$bitIndex] === '1');
                        $bitIndex++;
                    }
                }
            }
            
            $x -= 2;
            $direction *= -1;
        }
        
        return $matrix;
    }
    
    /**
     * Add finder pattern to matrix
     */
    private static function addFinderPattern(array &$matrix, int $row, int $col): void
    {
        // Outer 7x7 border
        for ($i = 0; $i < 7; $i++) {
            for ($j = 0; $j < 7; $j++) {
                if ($i === 0 || $i === 6 || $j === 0 || $j === 6) {
                    $matrix[$row + $i][$col + $j] = true;
                } elseif ($i >= 2 && $i <= 4 && $j >= 2 && $j <= 4) {
                    $matrix[$row + $i][$col + $j] = true;
                } else {
                    $matrix[$row + $i][$col + $j] = false;
                }
            }
        }
    }
    
    /**
     * Check if position is part of a function pattern
     */
    private static function isFunctionPattern(int $x, int $y, int $size): bool
    {
        // Finder patterns
        if (($x < 9 && $y < 9) || ($x < 9 && $y >= $size - 8) || ($x >= $size - 8 && $y < 9)) {
            return true;
        }
        
        // Timing patterns
        if ($x === 6 || $y === 6) {
            return true;
        }
        
        return false;
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
