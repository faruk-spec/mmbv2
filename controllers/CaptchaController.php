<?php
/**
 * Captcha Controller
 *
 * Serves the CAPTCHA SVG image and refreshes the challenge.
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Captcha;

class CaptchaController extends BaseController
{
    /**
     * GET /captcha
     * Returns a fresh SVG captcha image and stores the answer in the session.
     */
    public function image(): void
    {
        // Generate new challenge
        $data = Captcha::generate();

        // No caching
        header('Content-Type: image/svg+xml; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo Captcha::renderSvg($data['question']);
        exit;
    }
}
