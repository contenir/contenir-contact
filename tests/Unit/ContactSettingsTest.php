<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit;

use Contenir\Contact\ContactSettings;
use Contenir\Contact\Office;
use Contenir\Contact\Tests\TestAsset\Factory\BusinessFactory;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function count;
use function date_default_timezone_get;

#[Group('unit')]
final class ContactSettingsTest extends TestCase
{
    /**
     * @param iterable<Office> $offices
     *
     * @return list<string>
     */
    private static function ids(iterable $offices): array
    {
        $ids = [];
        foreach ($offices as $office) {
            $ids[] = $office->id;
        }

        return $ids;
    }

    #[Test]
    public function aBusinessWithoutANameIsTheSitesTitleAndAnOrganization(): void
    {
        $business = BusinessFactory::make();

        static::assertSame(['Studio Esteta', 'Organization', 0], [
            $business->name,
            $business->type->name,
            count($business->offices),
        ]);
    }

    #[Test]
    public function anOfficeCanBeKeptFromSearchEnginesButNotThePrimary(): void
    {
        $business = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Melbourne', ['primary' => true, 'search' => '0']),
            BusinessFactory::office('b', 'Warehouse', ['search' => '0']),
            BusinessFactory::office('c', 'Perth'),
        ]);

        static::assertSame(
            [true, false, true],
            array_map(static fn(Office $office): bool => $office->search, $business->offices->all()),
        );
    }

    #[Test]
    public function anOfficeHasTheBusinessesValuesUnlessItOverridesThem(): void
    {
        $business = BusinessFactory::make(
            [
                'legal_name' => 'Studio Esteta Pty Ltd',
                'telephone'  => '+61 3 9428 0228',
                'email'      => 'studio@example.test',
                'type'       => 'Restaurant',
                'cuisine'    => ['Italian'],
                'hour'       => [BusinessFactory::hour(['Mo', 'Tu', 'We', 'Th', 'Fr'], '09:00', '17:30')],
            ],
            [
                BusinessFactory::office('a', 'Melbourne'),
                BusinessFactory::office('b', 'Perth', [
                    'region'       => 'WA',
                    'trading_name' => 'Esteta West',
                    'telephone'    => '08 9000 0000',
                    'email'        => null,
                    'type'         => 'Bakery',
                    'timezone'     => 'Australia/Perth',
                    'hour'         => [BusinessFactory::hour(['Tu', 'We', 'Th'], '10:00', '16:00')],
                ]),
            ],
        );
        [$melbourne, $perth] = $business->offices->all() + [null, null];

        static::assertSame(
            [
                [
                    'Studio Esteta',
                    '03 9428 0228',
                    'studio@example.test',
                    'Restaurant',
                    'Mon–Fri 9:00am–5:30pm',
                    'Australia/Melbourne',
                    ['Italian'],
                    [],
                    [],
                ],
                [
                    'Esteta West',
                    '08 9000 0000',
                    null,
                    'Bakery',
                    'Tue–Thu 10:00am–4:00pm',
                    'Australia/Perth',
                    ['Italian'],
                    ['trading_name', 'type', 'telephone', 'hour', 'timezone'],
                    ['email'],
                ],
            ],
            array_map(
                static fn(?Office $office): array => [
                    $office?->tradingName,
                    (string) $office?->telephone,
                    $office?->email?->address,
                    $office?->type->name,
                    $office?->hour->summary(),
                    $office?->hour->timezone->getName(),
                    $office?->detail->cuisine,
                    $office?->overridden,
                    $office?->cleared,
                ],
                [$melbourne, $perth],
            ),
        );
    }

    #[Test]
    public function readsEachOfficesAddressAndPosition(): void
    {
        $office = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Melbourne', ['geolocation' => [
                'latitude'  => '-37.7787',
                'longitude' => '144.9874',
            ]]),
        ])->offices->primary();

        static::assertSame(
            ['402 Queens Parade, Fitzroy North VIC 3068', 'AU', -37.7787],
            [$office?->address?->oneLine(), $office?->address?->country, $office?->geolocation?->latitude],
        );
    }

    #[Test]
    public function readsNoCountryFromALocaleWithoutOne(): void
    {
        static::assertSame(['', 'NZ'], [
            BusinessFactory::make(site: ['locale' => 'en'])->country,
            BusinessFactory::make(site: ['locale' => 'en_nz.UTF-8'])->country,
        ]);
    }

    #[Test]
    public function readsOfficesAsTheCmsSavesThem(): void
    {
        $business = BusinessFactory::make(['telephone' => '03 9428 0228'], [
            ['fields' => [
                'id'        => 'a',
                'name'      => 'Melbourne',
                'primary'   => '0',
                'telephone' => '',
                'latitude'  => '-37.7787',
                'longitude' => '144.9874',
            ]],
            ['fields' => [
                'id'        => 'b',
                'name'      => 'Perth',
                'primary'   => '1',
                'search'    => '1',
                'telephone' => '08 9000 0000',
            ]],
        ]);
        $melbourne = $business->offices->byId('a');

        static::assertSame(
            ['b', '03 9428 0228', [], -37.7787, '08 9000 0000'],
            [
                $business->offices->primary()?->id,
                (string) $melbourne?->telephone,
                $melbourne?->overridden,
                $melbourne?->geolocation?->latitude,
                (string) $business->offices->byId('b')?->telephone,
            ],
        );
    }

    #[Test]
    public function readsTheBusiness(): void
    {
        $business = BusinessFactory::make([
            'name'       => 'Esteta',
            'legal_name' => 'Studio Esteta Pty Ltd',
            'tax_id'     => '12345678901',
            'type'       => 'LocalBusiness',
            'telephone'  => '+61 3 9428 0228',
            'email'      => 'studio@example.test',
        ]);

        static::assertSame(
            [
                'Esteta',
                'Studio Esteta Pty Ltd',
                'ABN 12 345 678 901',
                'LocalBusiness',
                '03 9428 0228',
                'studio@example.test',
                'AU',
            ],
            [
                $business->name,
                $business->legalName,
                (string) $business->taxId,
                $business->type->name,
                (string) $business->telephone,
                (string) $business->email,
                $business->country,
            ],
        );
    }

    #[Test]
    public function readsTheOfficesInOrderLeavingOutRowsWithoutANameOrWithAnIdSeen(): void
    {
        $business = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Melbourne'),
            BusinessFactory::office('b', ''),
            'not a row',
            BusinessFactory::office('a', 'Melbourne again'),
            BusinessFactory::office('', 'Perth'),
            BusinessFactory::office('7', 'Darwin'),
        ]);

        static::assertSame(['a', 'Perth', '7'], self::ids($business->offices));
    }

    #[Test]
    public function theFirstOfficeMarkedPrimaryIsPrimary(): void
    {
        $business = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Melbourne'),
            BusinessFactory::office('b', 'Sydney', ['primary' => true]),
            BusinessFactory::office('c', 'Perth', ['primary' => '1']),
        ]);

        static::assertSame(
            ['b', [false, true, false]],
            [
                $business->offices->primary()?->id,
                array_map(static fn(Office $office): bool => $office->primary, $business->offices->all()),
            ],
        );
    }

    #[Test]
    public function theTimezoneIsTheBusinessesThenTheSitesThenTheOneGivenThenPhps(): void
    {
        $given = new DateTimeZone('Asia/Tokyo');

        static::assertSame(
            ['Australia/Perth', 'Australia/Darwin', 'Asia/Tokyo', date_default_timezone_get()],
            [
                ContactSettings::fromSettings(
                    [
                        'contact' => ['business' => ['timezone' => 'Australia/Perth']],
                        'site'    => ['timezone' => 'Australia/Darwin'],
                    ],
                    $given,
                )->hour->timezone->getName(),
                ContactSettings::fromSettings(['site' => [
                    'timezone' => 'Australia/Darwin',
                ]], $given)->hour->timezone->getName(),
                ContactSettings::fromSettings(['contact' => [
                    'business' => 'not a group',
                ]], $given)->hour->timezone->getName(),
                ContactSettings::fromSettings([])->hour->timezone->getName(),
            ],
        );
    }

    #[Test]
    public function withoutAnOfficeMarkedPrimaryTheFirstIs(): void
    {
        $business = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Melbourne', ['search' => false]),
            BusinessFactory::office('b', 'Sydney'),
        ]);

        static::assertSame(['a', true], [$business->offices->primary()?->id, $business->offices->primary()?->search]);
    }
}
