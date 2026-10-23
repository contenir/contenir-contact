<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\TestAsset\Factory;

use Contenir\Contact\Business;
use Contenir\Contact\ContactSettings;
use DateTimeZone;

/**
 * Builds a business the way a site's settings describe it.
 */
final class BusinessFactory
{
    /**
     * A row of opening hours.
     *
     * @param list<string> $days
     *
     * @return array{day: list<string>, opens: string, closes: string}
     */
    public static function hour(array $days, string $opens, string $closes): array
    {
        return ['day' => $days, 'opens' => $opens, 'closes' => $closes];
    }

    /**
     * @param array<string, mixed>       $business contact.business
     * @param list<array<string, mixed>> $offices  contact.office
     * @param array<string, mixed>       $site     site
     */
    public static function make(array $business = [], array $offices = [], array $site = []): Business
    {
        return ContactSettings::fromSettings(
            [
                'site'    => [...['title' => 'Studio Esteta', 'locale' => 'en_AU'], ...$site],
                'contact' => ['business' => $business, 'office' => $offices],
            ],
            new DateTimeZone('Australia/Melbourne'),
        );
    }

    /**
     * A row of contact.office with an address.
     *
     * @param array<string, mixed> $values
     *
     * @return array<string, mixed>
     */
    public static function office(string $id, string $name, array $values = []): array
    {
        return [
            'id'       => $id,
            'name'     => $name,
            'street'   => '402 Queens Parade',
            'locality' => 'Fitzroy North',
            'region'   => 'VIC',
            'postcode' => '3068',
            ...$values,
        ];
    }
}
