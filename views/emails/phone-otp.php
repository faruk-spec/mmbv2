<?php
/**
 * Phone OTP verification email (fallback view).
 * Variables: $name, $otp, $phone, $expires_minutes
 */
$name = htmlspecialchars($name ?? 'User', ENT_QUOTES);
$otp  = htmlspecialchars($otp  ?? '', ENT_QUOTES);
$phone = htmlspecialchars($phone ?? '', ENT_QUOTES);
$expMin = (int) ($expires_minutes ?? 10);
?>
<h2>Hi <?= $name ?>,</h2>
<p>You requested to verify your phone number <strong><?= $phone ?></strong>.</p>
<p>Use the code below to complete verification. This code expires in <strong><?= $expMin ?> minutes</strong>.</p>
<div style="text-align:center;margin:28px 0;">
    <span style="display:inline-block;font-size:40px;font-weight:800;letter-spacing:12px;background:#f4f7fb;border:2px dashed #0077cc;border-radius:12px;padding:18px 36px;color:#1a1a2e;"><?= $otp ?></span>
</div>
<p style="color:#888;font-size:.85em;">If you did not request this, you can safely ignore this email.</p>
