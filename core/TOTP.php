<?php
/**
 * TOTP (Time-Based One-Time Password) Implementation
 * Compatible with Google Authenticator and other TOTP apps
 * 
 * @package MMB\Core
 */

namespace Core;

class TOTP
{
    /**
     * Generate a random secret key
     * 
     * @param int $length Length of the secret (default 16 chars = 80 bits)
     * @return string Base32-encoded secret
     */
    public static function generateSecret(int $length = 16): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; // Base32 alphabet
        $secret = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, $max)];
        }
        
        return $secret;
    }
    
    /**
     * Generate the current TOTP code
     * 
     * @param string $secret Base32-encoded secret
     * @param int|null $timestamp Unix timestamp (null for current time)
     * @param int $period Time period in seconds (default 30)
     * @param int $digits Number of digits in the code (default 6)
     * @return string The TOTP code
     */
    public static function getCode(string $secret, ?int $timestamp = null, int $period = 30, int $digits = 6): string
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        $counter = floor($timestamp / $period);
        $secretKey = self::base32Decode($secret);
        
        // Generate HMAC-SHA1 hash
        $hash = hash_hmac('sha1', pack('N*', 0, $counter), $secretKey, true);
        
        // Dynamic truncation
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % pow(10, $digits);
        
        return str_pad((string)$code, $digits, '0', STR_PAD_LEFT);
    }
    
    /**
     * Verify a TOTP code
     * 
     * @param string $secret Base32-encoded secret
     * @param string $code The code to verify
     * @param int $window Number of time periods to check (default 1 = ±30 seconds)
     * @param int|null $timestamp Unix timestamp (null for current time)
     * @return bool True if code is valid
     */
    public static function verifyCode(string $secret, string $code, int $window = 1, ?int $timestamp = null): bool
    {
        if ($timestamp === null) {
            $timestamp = time();
        }
        
        // Check current time period and adjacent periods
        for ($i = -$window; $i <= $window; $i++) {
            $testTimestamp = $timestamp + ($i * 30);
            $testCode = self::getCode($secret, $testTimestamp);
            
            if (hash_equals($testCode, $code)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the provisioning URI for QR code generation
     * 
     * @param string $secret Base32-encoded secret
     * @param string $accountName User's email or username
     * @param string $issuer Application name
     * @return string The otpauth:// URI
     */
    public static function getProvisioningUri(string $secret, string $accountName, string $issuer = 'MyMultiBranch'): string
    {
        $params = [
            'secret' => $secret,
            'issuer' => $issuer,
            'algorithm' => 'SHA1',
            'digits' => '6',
            'period' => '30'
        ];
        
        return 'otpauth://totp/' . rawurlencode($issuer . ':' . $accountName) . '?' . http_build_query($params);
    }
    
    /**
     * Get QR code data URL for the provisioning URI
     * Uses Google Charts API to generate QR code
     * 
     * @param string $provisioningUri The otpauth:// URI
     * @param int $size QR code size in pixels (default 200)
     * @return string The QR code image URL
     */
    public static function getQRCodeUrl(string $provisioningUri, int $size = 200): string
    {
        return 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . 
               '&chld=M|0&cht=qr&chl=' . urlencode($provisioningUri);
    }
    
    /**
     * Decode a Base32-encoded string
     * 
     * @param string $encoded Base32-encoded string
     * @return string Decoded binary string
     */
    private static function base32Decode(string $encoded): string
    {
        $encoded = strtoupper($encoded);
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $decoded = '';
        $buffer = 0;
        $bitsInBuffer = 0;
        
        for ($i = 0; $i < strlen($encoded); $i++) {
            $char = $encoded[$i];
            $pos = strpos($alphabet, $char);
            
            if ($pos === false) {
                continue; // Skip invalid characters
            }
            
            $buffer = ($buffer << 5) | $pos;
            $bitsInBuffer += 5;
            
            if ($bitsInBuffer >= 8) {
                $bitsInBuffer -= 8;
                $decoded .= chr(($buffer >> $bitsInBuffer) & 0xFF);
            }
        }
        
        return $decoded;
    }
    
    /**
     * Generate backup codes for 2FA recovery
     * 
     * @param int $count Number of backup codes to generate (default 10)
     * @param int $length Length of each code (default 8)
     * @return array Array of backup codes
     */
    public static function generateBackupCodes(int $count = 10, int $length = 8): array
    {
        $codes = [];
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $count; $i++) {
            $code = '';
            for ($j = 0; $j < $length; $j++) {
                $code .= $chars[random_int(0, $max)];
            }
            // Format as XXXX-XXXX for readability
            $codes[] = substr($code, 0, 4) . '-' . substr($code, 4, 4);
        }
        
        return $codes;
    }
}
