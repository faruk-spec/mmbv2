<?php
/**
 * Simple Math CAPTCHA (no external dependencies)
 *
 * Generates a simple arithmetic challenge stored in the session.
 * No Google reCAPTCHA or third-party services are used.
 *
 * @package MMB\Core
 */

namespace Core;

class Captcha
{
    private const SESSION_KEY = '_captcha_answer';
    private const SESSION_TTL = '_captcha_time';
    private const EXPIRE_SECONDS = 600; // 10 minutes

    /**
     * Generate a new captcha challenge and store the answer in the session.
     * Returns an array: ['question' => 'What is 3 + 7?', 'answer' => 10]
     */
    public static function generate(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $a = random_int(1, 9);
        $b = random_int(1, 9);
        $ops = ['+', '-', '×'];
        $op = $ops[array_rand($ops)];

        switch ($op) {
            case '+':
                $answer = $a + $b;
                break;
            case '-':
                // Ensure non-negative result
                if ($a < $b) { [$a, $b] = [$b, $a]; }
                $answer = $a - $b;
                break;
            case '×':
                $a = random_int(1, 5);
                $b = random_int(1, 5);
                $answer = $a * $b;
                break;
            default:
                $answer = $a + $b;
        }

        $_SESSION[self::SESSION_KEY] = (string)$answer;
        $_SESSION[self::SESSION_TTL] = time();

        return [
            'question' => "What is {$a} {$op} {$b}?",
            'answer'   => $answer,
        ];
    }

    /**
     * Verify the user-supplied answer against the session-stored answer.
     * The session entry is cleared after a single verification attempt.
     */
    public static function verify(string $userAnswer): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $stored = $_SESSION[self::SESSION_KEY] ?? null;
        $storedTime = $_SESSION[self::SESSION_TTL] ?? 0;

        // Clear immediately (one-time use)
        unset($_SESSION[self::SESSION_KEY], $_SESSION[self::SESSION_TTL]);

        if ($stored === null) {
            return false;
        }

        // Check expiry
        if ((time() - (int)$storedTime) > self::EXPIRE_SECONDS) {
            return false;
        }

        return trim($userAnswer) === $stored;
    }

    /**
     * Render an SVG image for the captcha question.
     * Returns the raw SVG string.
     */
    public static function renderSvg(string $question): string
    {
        // Obfuscate the text slightly with random letter spacing
        $chars = str_split(htmlspecialchars($question, ENT_QUOTES));
        $svgText = implode('', array_map(fn($c) => "<tspan dy=\"" . random_int(-2, 2) . "\">{$c}</tspan>", $chars));

        $width  = 220;
        $height = 60;
        $bg     = '#1a1a2e';
        $color  = '#00f0ff';

        // Add a few random noise lines
        $noise = '';
        for ($i = 0; $i < 4; $i++) {
            $x1 = random_int(0, $width);
            $y1 = random_int(0, $height);
            $x2 = random_int(0, $width);
            $y2 = random_int(0, $height);
            $noise .= "<line x1=\"{$x1}\" y1=\"{$y1}\" x2=\"{$x2}\" y2=\"{$y2}\" stroke=\"rgba(255,255,255,0.15)\" stroke-width=\"1\"/>";
        }

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$width}" height="{$height}" role="img" aria-label="CAPTCHA question">
  <rect width="{$width}" height="{$height}" fill="{$bg}" rx="6"/>
  {$noise}
  <text x="50%" y="58%" dominant-baseline="middle" text-anchor="middle"
        font-family="monospace" font-size="22" fill="{$color}" font-weight="bold"
        letter-spacing="3">{$svgText}</text>
</svg>
SVG;
    }

    /**
     * Check whether captcha is enabled in settings.
     */
    public static function isEnabled(): bool
    {
        try {
            $db = Database::getInstance();
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = 'captcha_enabled'");
            return $row && $row['value'] === '1';
        } catch (\Exception $e) {
            return false;
        }
    }
}
