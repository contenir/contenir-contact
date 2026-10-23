<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Value;

use Contenir\Contact\MapProvider;
use Contenir\Contact\Value\Geolocation;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class GeolocationTest extends TestCase
{
    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'not a group'        => ['-37.7787,144.9874'],
            'no longitude'       => [['latitude' => '-37.7787']],
            'no latitude'        => [['longitude' => '144.9874']],
            'latitude past 90'   => [['latitude' => '90.0001', 'longitude' => '0']],
            'latitude past -90'  => [['latitude' => -90.5, 'longitude' => 0]],
            'longitude past 180' => [['latitude' => 0, 'longitude' => 180.0001]],
            'not numbers'        => [['latitude' => 'north', 'longitude' => 'east']],
            'not text'           => [['latitude' => [1], 'longitude' => [2]]],
        ];
    }

    /**
     * @return array<string, array{MapProvider, string, string}>
     */
    public static function linkProvider(): array
    {
        return [
            'OpenStreetMap' => [
                MapProvider::OpenStreetMap,
                'https://www.openstreetmap.org/?mlat=-37.7787&mlon=144.98#map=17/-37.7787/144.98',
                'https://www.openstreetmap.org/directions?route=%3B-37.7787%2C144.98',
            ],
            'Google Maps'   => [
                MapProvider::Google,
                'https://www.google.com/maps/search/?api=1&query=-37.7787,144.98',
                'https://www.google.com/maps/dir/?api=1&destination=-37.7787,144.98',
            ],
        ];
    }

    #[Test]
    public function describesItselfAsGeoCoordinates(): void
    {
        static::assertSame(
            ['@type' => 'GeoCoordinates', 'latitude' => -37.7787, 'longitude' => 144.9874],
            Geolocation::from(['latitude' => -37.7787, 'longitude' => 144.9874])?->schema(),
        );
    }

    #[Test]
    #[DataProvider('invalidProvider')]
    public function leavesOutAnythingButTwoCoordinatesOnEarth(mixed $value): void
    {
        static::assertNull(Geolocation::from($value));
    }

    #[Test]
    #[DataProvider('linkProvider')]
    public function linksToTheProvidersMapAndDirections(MapProvider $provider, string $map, string $directions): void
    {
        $position = Geolocation::from(['latitude' => -37.7787, 'longitude' => 144.98]);

        static::assertSame([$map, $directions], [$position?->mapUrl($provider), $position?->directionsUrl($provider)]);
    }

    #[Test]
    public function readsBothCoordinatesAsTheMapsFieldSavesThem(): void
    {
        $position = Geolocation::from(['latitude' => ' -37.7787 ', 'longitude' => '144.9874']);

        static::assertSame([-37.7787, 144.9874], [$position?->latitude, $position?->longitude]);
    }

    #[Test]
    public function readsNumbersAndTheEdgesOfTheWorld(): void
    {
        $position = Geolocation::from(['latitude' => -90, 'longitude' => 180.0]);

        static::assertSame([-90.0, 180.0], [$position?->latitude, $position?->longitude]);
    }

    #[Test]
    public function writesWholeAndLongCoordinatesShort(): void
    {
        $position = Geolocation::from(['latitude' => 10, 'longitude' => 144.123_456_789]);

        static::assertSame(
            'https://www.google.com/maps/search/?api=1&query=10,144.1234568',
            $position?->mapUrl(MapProvider::Google),
        );
    }
}
