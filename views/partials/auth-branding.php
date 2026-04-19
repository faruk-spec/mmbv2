<?php
/**
 * Shared auth branding partial.
 *
 * Populates $authLogo, $authTagline, $authSiteName in the calling scope.
 * Safe to include multiple times — uses a flag to skip re-querying.
 */
if (!isset($authBrandingLoaded)) {
    try {
        $_authBrandDb  = \Core\Database::getInstance();
        $_authLogoRow  = $_authBrandDb->fetch("SELECT value FROM settings WHERE `key` = 'auth_logo'");
        $_authTagRow   = $_authBrandDb->fetch("SELECT value FROM settings WHERE `key` = 'auth_tagline'");
        $_siteNameRow  = $_authBrandDb->fetch("SELECT value FROM settings WHERE `key` = 'site_name'");
        $authLogo      = $_authLogoRow  ? trim($_authLogoRow['value'])  : '';
        $authTagline   = $_authTagRow   ? trim($_authTagRow['value'])   : '';
        $authSiteName  = $_siteNameRow  ? trim($_siteNameRow['value'])  : APP_NAME;
    } catch (\Exception $e) {
        $authLogo = ''; $authTagline = ''; $authSiteName = APP_NAME;
    }
    $authBrandingLoaded = true;
}
