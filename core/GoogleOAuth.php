<?php
/**
 * Google OAuth compatibility wrapper
 *
 * @package MMB\Core
 */

namespace Core;

class GoogleOAuth
{
    public static function isEnabled(): bool
    {
        return OAuthProvider::isEnabled('google');
    }

    public static function getAuthUrl(?string $returnUrl = null): string
    {
        return OAuthProvider::getAuthUrl('google', $returnUrl);
    }

    public static function handleCallback(string $code, string $state): array|false
    {
        return OAuthProvider::handleCallback('google', $code, $state);
    }

    public static function linkAccount(int $userId, array $oauthData): bool
    {
        return OAuthProvider::linkAccount('google', $userId, $oauthData);
    }

    public static function findOrCreateUser(array $oauthData): int|false
    {
        return OAuthProvider::findOrCreateUser('google', $oauthData);
    }

    public static function revokeConnection(int $userId): bool
    {
        return OAuthProvider::revokeConnection('google', $userId);
    }
}
