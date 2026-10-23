<?php

declare(strict_types=1);

namespace Contenir\Contact\Value;

use Contenir\Contact\MapProvider;

use function abs;
use function is_array;
use function is_numeric;
use function rtrim;
use function sprintf;

/**
 * Where an office is, as the Maps extension's coordinates field places it,
 * for the site's maps and links, and for search engines.
 *
 * @api
 */
final readonly class Geolocation
{
    private function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /**
     * The position, or null unless the value holds both a latitude between
     * -90 and 90 and a longitude between -180 and 180.
     */
    public static function from(mixed $value): ?self
    {
        $latitude  = is_array($value) ? self::number($value['latitude'] ?? null, limit: 90) : null;
        $longitude = is_array($value) ? self::number($value['longitude'] ?? null, limit: 180) : null;

        return null === $latitude || null === $longitude ? null : new self($latitude, $longitude);
    }

    private static function coordinate(float $value): string
    {
        return rtrim(rtrim(sprintf('%.7F', $value), characters: '0'), characters: '.');
    }

    /**
     * The value as a number no further from 0 than $limit, or null.
     */
    private static function number(mixed $value, int $limit): ?float
    {
        $number = is_numeric($value) ? (float) $value : null;

        return null === $number || abs($number) > $limit ? null : $number;
    }

    /**
     * A link to the provider's directions to the office, from wherever the
     * visitor is.
     */
    public function directionsUrl(MapProvider $provider): string
    {
        $latitude  = self::coordinate($this->latitude);
        $longitude = self::coordinate($this->longitude);

        return match ($provider) {
            MapProvider::OpenStreetMap
                => "https://www.openstreetmap.org/directions?route=%3B{$latitude}%2C{$longitude}",
            MapProvider::Google        => "https://www.google.com/maps/dir/?api=1&destination={$latitude},{$longitude}",
        };
    }

    /**
     * A link to the office on the provider's map.
     */
    public function mapUrl(MapProvider $provider): string
    {
        $latitude  = self::coordinate($this->latitude);
        $longitude = self::coordinate($this->longitude);

        return match ($provider) {
            MapProvider::OpenStreetMap => sprintf(
                'https://www.openstreetmap.org/?mlat=%1$s&mlon=%2$s#map=17/%1$s/%2$s',
                $latitude,
                $longitude,
            ),
            MapProvider::Google        => "https://www.google.com/maps/search/?api=1&query={$latitude},{$longitude}",
        };
    }

    /**
     * The position as schema.org's GeoCoordinates.
     *
     * @return array{'@type': 'GeoCoordinates', latitude: float, longitude: float}
     */
    public function schema(): array
    {
        return ['@type' => 'GeoCoordinates', 'latitude' => $this->latitude, 'longitude' => $this->longitude];
    }
}
