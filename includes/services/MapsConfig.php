<?php

declare(strict_types=1);

/**
 * Google Maps — placeholder for future integration.
 */
final class MapsConfig
{
    public static function apiKey(): string
    {
        return GOOGLE_MAPS_API_KEY;
    }

    public static function embedUrl(float $lat, float $lng, int $zoom = 14): string
    {
        $key = self::apiKey();
        return 'https://www.google.com/maps/embed/v1/place?key=' . rawurlencode($key)
            . '&q=' . rawurlencode((string) $lat . ',' . (string) $lng);
    }
}
