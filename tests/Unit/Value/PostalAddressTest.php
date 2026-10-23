<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Value;

use Contenir\Contact\Value\PostalAddress;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class PostalAddressTest extends TestCase
{
    #[Test]
    public function aRowsOwnCountryWins(): void
    {
        static::assertSame('NZ', PostalAddress::fromRow(['locality' => 'Auckland', 'country' => 'nz'], 'AU')?->country);
    }

    #[Test]
    public function aRowWithoutAStreetOrTownHasNoAddress(): void
    {
        static::assertNull(PostalAddress::fromRow(['region' => 'VIC', 'postcode' => '3068']));
    }

    #[Test]
    public function describesItselfAsAPostalAddressWithoutTheMissingParts(): void
    {
        static::assertSame(
            [
                '@type'           => 'PostalAddress',
                'streetAddress'   => '1 Hay St',
                'addressLocality' => 'Perth',
                'addressCountry'  => 'AU',
            ],
            (new PostalAddress('1 Hay St', 'Perth', country: 'AU'))->schema(),
        );
    }

    #[Test]
    public function leavesOutTheLinesItLacks(): void
    {
        static::assertSame(
            [['Perth WA'], ['1 Hay St']],
            [
                (new PostalAddress(
                    locality: 'Perth',
                    region: 'WA',
                ))->lines(),
                (new PostalAddress('1 Hay St'))->lines(),
            ],
        );
    }

    #[Test]
    public function readsTheAddressFromAnOfficesRow(): void
    {
        $address = PostalAddress::fromRow([
            'street'   => ' 402 Queens Parade ',
            'locality' => 'Fitzroy North',
            'region'   => 'VIC',
            'postcode' => 3068,
        ], country: 'au');

        static::assertSame(
            [['402 Queens Parade', 'Fitzroy North VIC 3068'], '402 Queens Parade, Fitzroy North VIC 3068', 'AU'],
            [$address?->lines(), $address?->oneLine(), $address?->country],
        );
    }
}
