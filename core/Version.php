<?php
/**
 * Application Version Utilities
 *
 * @package MMB\Core
 */

namespace Core;

class Version
{
    private static ?array $cached = null;

    public static function app(): string
    {
        return defined('APP_VERSION') ? (string) APP_VERSION : '1.0.0';
    }

    public static function revision(): ?string
    {
        return self::metadata()['revision'];
    }

    public static function display(): string
    {
        return self::metadata()['display'];
    }

    public static function asset(): string
    {
        return self::revision() ?? self::app();
    }

    public static function metadata(): array
    {
        if (self::$cached !== null) {
            return self::$cached;
        }

        $appVersion = self::app();
        $revision = self::detectRevision();

        self::$cached = [
            'version' => $appVersion,
            'revision' => $revision,
            'display' => $revision ? "{$appVersion}+{$revision}" : $appVersion,
        ];

        return self::$cached;
    }

    private static function detectRevision(): ?string
    {
        $envRevision = getenv('APP_GIT_SHA') ?: getenv('GITHUB_SHA');
        if (is_string($envRevision) && preg_match('/^[a-f0-9]{7,40}$/i', $envRevision)) {
            return strtolower(substr($envRevision, 0, 7));
        }

        $gitDir = dirname(__DIR__) . '/.git';
        $headPath = $gitDir . '/HEAD';

        if (!is_file($headPath) || !is_readable($headPath)) {
            return null;
        }

        $head = trim((string) file_get_contents($headPath));

        if ($head === '') {
            return null;
        }

        if (str_starts_with($head, 'ref: ')) {
            $ref = trim(substr($head, 5));
            $refPath = $gitDir . '/' . $ref;

            if (is_file($refPath) && is_readable($refPath)) {
                $sha = trim((string) file_get_contents($refPath));
                if (preg_match('/^[a-f0-9]{40}$/i', $sha)) {
                    return strtolower(substr($sha, 0, 7));
                }
            }

            $packedRefs = $gitDir . '/packed-refs';
            if (is_file($packedRefs) && is_readable($packedRefs)) {
                $content = (string) file_get_contents($packedRefs);
                if (preg_match('/^([a-f0-9]{40})\s+' . preg_quote($ref, '/') . '$/mi', $content, $matches)) {
                    return strtolower(substr($matches[1], 0, 7));
                }
            }

            return null;
        }

        if (preg_match('/^[a-f0-9]{40}$/i', $head)) {
            return strtolower(substr($head, 0, 7));
        }

        return null;
    }
}
