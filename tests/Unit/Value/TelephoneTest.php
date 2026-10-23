<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Value;

use Contenir\Contact\Value\Telephone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TelephoneTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string, string, string}>
     */
    public static function numberProvider(): array
    {
        return [
            'entered internationally, in its country' => [
                '+61 3 9428 0228',
                'AU',
                '03 9428 0228',
                '+61 3 9428 0228',
                'tel:+61394280228',
            ],
            'entered locally'                         => [
                '03 9428 0228',
                'AU',
                '03 9428 0228',
                '+61 3 9428 0228',
                'tel:+61394280228',
            ],
            'another country\'s number'               => [
                '+64 9 300 0000',
                'AU',
                '+64 9 300 0000',
                '+64 9 300 0000',
                'tel:+6493000000',
            ],
            'a country without a 0 to drop'           => [
                '+1 212 555 0100',
                'US',
                '+1 212 555 0100',
                '+1 212 555 0100',
                'tel:+12125550100',
            ],
            'entered without spaces'                  => [
                '+61394280228',
                'AU',
                '0394280228',
                '+61394280228',
                'tel:+61394280228',
            ],
            'written with punctuation'                => [
                '(03) 9428-0228',
                'AU',
                '(03) 9428-0228',
                '(03) 9428-0228',
                'tel:0394280228',
            ],
            'no country'                              => [
                '1300 000 000',
                '',
                '1300 000 000',
                '1300 000 000',
                'tel:1300000000',
            ],
            'a three-digit code'                      => [
                '+353 1 000 0000',
                'IE',
                '01 000 0000',
                '+353 1 000 0000',
                'tel:+35310000000',
            ],
        ];
    }

    #[Test]
    public function leavesOutAValueWithoutDigits(): void
    {
        static::assertSame([null, null, null], [Telephone::from('call us'), Telephone::from(''), Telephone::from(394)]);
    }

    #[Test]
    #[DataProvider('numberProvider')]
    public function writesTheNumberForTheSitesCountry(
        string $entered,
        string $country,
        string $national,
        string $international,
        string $uri,
    ): void {
        $telephone = Telephone::from(" {$entered} ", $country);

        static::assertSame(
            [$national, $international, $uri, $national, $entered],
            [
                $telephone?->national(),
                $telephone?->international(),
                $telephone?->uri(),
                (string) $telephone,
                $telephone?->number,
            ],
        );
    }
}
