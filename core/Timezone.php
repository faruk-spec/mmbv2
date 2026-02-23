<?php
/**
 * Timezone utility
 *
 * Manages system-wide and per-user timezone preferences.
 *
 * Usage:
 *   // At the start of any authenticated page:
 *   Timezone::init(Auth::id());
 *
 *   // Format a stored UTC timestamp for display:
 *   echo Timezone::format($row['created_at']);          // e.g. "Feb 23, 2026 14:30"
 *   echo Timezone::format($row['created_at'], 'M j, Y'); // e.g. "Feb 23, 2026"
 *   echo Timezone::now();                               // current time in user TZ
 *
 * @package MMB\Core
 */

namespace Core;

class Timezone
{
    /** @var string|null Resolved user timezone (e.g. "Asia/Kolkata") */
    private static ?string $userTz = null;

    /** @var string System/default timezone read from settings table */
    private static string  $systemTz = 'UTC';

    // -------------------------------------------------------------------------

    /**
     * Initialise for the current request.
     *
     * Loads the system timezone (already applied by App.php) and optionally
     * the per-user display timezone from `user_settings.timezone`.
     *
     * @param int|null $userId  Authenticated user ID, or null for guests.
     */
    public static function init(?int $userId = null): void
    {
        // Capture the system TZ that App.php already set.
        self::$systemTz = date_default_timezone_get() ?: 'UTC';

        if (!$userId) {
            self::$userTz = self::$systemTz;
            return;
        }

        try {
            $db  = Database::getInstance();

            // Ensure `timezone` column exists in user_settings (idempotent).
            self::ensureTimezoneColumn($db);

            $row = $db->fetch(
                "SELECT timezone FROM user_settings WHERE user_id = ? LIMIT 1",
                [$userId]
            );

            $tz = $row['timezone'] ?? null;

            if ($tz && self::isValidTimezone($tz)) {
                self::$userTz = $tz;
            } else {
                self::$userTz = self::$systemTz;
            }
        } catch (\Exception $e) {
            self::$userTz = self::$systemTz;
        }
    }

    // -------------------------------------------------------------------------

    /**
     * Format a datetime string or Unix timestamp in the user's timezone.
     *
     * @param int|string|null $value  Unix timestamp or MySQL datetime string.
     * @param string          $format PHP date() format string.
     * @return string  Formatted string, or '—' if value is empty/invalid.
     */
    public static function format(int|string|null $value, string $format = 'M j, Y H:i'): string
    {
        if (empty($value)) {
            return '—';
        }

        $ts = is_int($value) ? $value : strtotime((string) $value);
        if ($ts === false) {
            return '—';
        }

        try {
            $dt = new \DateTime('@' . $ts);                       // UTC epoch
            $dt->setTimezone(new \DateTimeZone(self::getTz()));   // user TZ
            return $dt->format($format);
        } catch (\Exception $e) {
            return date($format, $ts);
        }
    }

    /**
     * Current time formatted in the user's timezone.
     */
    public static function now(string $format = 'Y-m-d H:i:s'): string
    {
        return self::format(time(), $format);
    }

    /**
     * Save a user's timezone preference.
     *
     * @param int    $userId
     * @param string $timezone  IANA timezone identifier (e.g. "Asia/Kolkata")
     * @return bool
     */
    public static function saveUserTimezone(int $userId, string $timezone): bool
    {
        if (!self::isValidTimezone($timezone)) {
            return false;
        }

        try {
            $db = Database::getInstance();
            self::ensureTimezoneColumn($db);

            $exists = $db->fetch(
                "SELECT user_id FROM user_settings WHERE user_id = ? LIMIT 1",
                [$userId]
            );

            if ($exists) {
                $db->update('user_settings', ['timezone' => $timezone], 'user_id = ?', [$userId]);
            } else {
                $db->insert('user_settings', ['user_id' => $userId, 'timezone' => $timezone]);
            }

            return true;
        } catch (\Exception $e) {
            Logger::error('Timezone::saveUserTimezone: ' . $e->getMessage());
            return false;
        }
    }

    // -------------------------------------------------------------------------
    // Getters

    /** Returns the active display timezone (user's if set, otherwise system). */
    public static function getTz(): string
    {
        return self::$userTz ?? self::$systemTz;
    }

    /** Returns the system-wide timezone (from settings table). */
    public static function getSystemTz(): string
    {
        return self::$systemTz;
    }

    /**
     * Returns a grouped list of common IANA timezone identifiers for <select> elements.
     *
     * @return array<string, string[]>  ['Region' => ['Zone/City', ...], ...]
     */
    public static function getGroupedTimezones(): array
    {
        return [
            'UTC' => ['UTC'],
            'Americas' => [
                'America/New_York',
                'America/Chicago',
                'America/Denver',
                'America/Los_Angeles',
                'America/Anchorage',
                'America/Adak',
                'America/Toronto',
                'America/Vancouver',
                'America/Mexico_City',
                'America/Bogota',
                'America/Lima',
                'America/Caracas',
                'America/Santiago',
                'America/Buenos_Aires',
                'America/Sao_Paulo',
            ],
            'Europe' => [
                'Europe/London',
                'Europe/Dublin',
                'Europe/Lisbon',
                'Europe/Madrid',
                'Europe/Paris',
                'Europe/Berlin',
                'Europe/Rome',
                'Europe/Amsterdam',
                'Europe/Brussels',
                'Europe/Zurich',
                'Europe/Warsaw',
                'Europe/Vienna',
                'Europe/Prague',
                'Europe/Stockholm',
                'Europe/Oslo',
                'Europe/Copenhagen',
                'Europe/Helsinki',
                'Europe/Athens',
                'Europe/Bucharest',
                'Europe/Sofia',
                'Europe/Istanbul',
                'Europe/Kiev',
                'Europe/Moscow',
            ],
            'Africa' => [
                'Africa/Cairo',
                'Africa/Johannesburg',
                'Africa/Lagos',
                'Africa/Nairobi',
                'Africa/Casablanca',
                'Africa/Accra',
                'Africa/Tunis',
            ],
            'Asia' => [
                'Asia/Dubai',
                'Asia/Tehran',
                'Asia/Karachi',
                'Asia/Kolkata',
                'Asia/Colombo',
                'Asia/Kathmandu',
                'Asia/Dhaka',
                'Asia/Rangoon',
                'Asia/Bangkok',
                'Asia/Jakarta',
                'Asia/Singapore',
                'Asia/Kuala_Lumpur',
                'Asia/Manila',
                'Asia/Hong_Kong',
                'Asia/Shanghai',
                'Asia/Taipei',
                'Asia/Seoul',
                'Asia/Tokyo',
                'Asia/Riyadh',
                'Asia/Baghdad',
                'Asia/Beirut',
                'Asia/Jerusalem',
                'Asia/Tashkent',
                'Asia/Almaty',
                'Asia/Yekaterinburg',
                'Asia/Omsk',
                'Asia/Krasnoyarsk',
                'Asia/Irkutsk',
                'Asia/Yakutsk',
                'Asia/Vladivostok',
                'Asia/Magadan',
                'Asia/Kamchatka',
            ],
            'Pacific' => [
                'Pacific/Auckland',
                'Pacific/Fiji',
                'Pacific/Guam',
                'Pacific/Honolulu',
                'Pacific/Midway',
                'Australia/Sydney',
                'Australia/Melbourne',
                'Australia/Brisbane',
                'Australia/Adelaide',
                'Australia/Perth',
                'Australia/Darwin',
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Private helpers

    private static function isValidTimezone(string $tz): bool
    {
        if (empty($tz)) {
            return false;
        }
        try {
            new \DateTimeZone($tz);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    private static function ensureTimezoneColumn(Database $db): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            // Silently try to ensure user_settings table exists first.
            $db->query(
                "CREATE TABLE IF NOT EXISTS user_settings (
                    id         INT AUTO_INCREMENT PRIMARY KEY,
                    user_id    INT NOT NULL UNIQUE,
                    timezone   VARCHAR(60) DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
        } catch (\Exception $e) {
            // Table may already exist with different columns — that is fine.
        }

        try {
            $cols = $db->fetchAll("SHOW COLUMNS FROM user_settings LIKE 'timezone'");
            if (empty($cols)) {
                $db->query("ALTER TABLE user_settings ADD COLUMN timezone VARCHAR(60) DEFAULT NULL");
            }
        } catch (\Exception $e) {
            // ALTER failed or SHOW failed — proceed silently.
        }
    }
}
