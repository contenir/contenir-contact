<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Value;

use Contenir\Contact\Value\TaxId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TaxIdTest extends TestCase
{
    /**
     * @return array<string, array{string, string, ?string, string, string}>
     */
    public static function numberProvider(): array
    {
        return [
            'an ABN'               => ['12345678901', 'AU', 'ABN', '12 345 678 901', 'ABN 12 345 678 901'],
            'an ABN with spaces'   => ['12 3456 78901', 'AU', 'ABN', '12 345 678 901', 'ABN 12 345 678 901'],
            'not eleven digits'    => ['1234', 'AU', 'ABN', '1234', 'ABN 1234'],
            'a New Zealand number' => ['9429000000000', 'NZ', 'NZBN', '9429000000000', 'NZBN 9429000000000'],
            'another country'      => ['GB123456789', 'GB', null, 'GB123456789', 'GB123456789'],
        ];
    }

    #[Test]
    public function leavesOutAnEmptyValue(): void
    {
        static::assertSame([null, null], [TaxId::from(' '), TaxId::from(123)]);
    }

    #[Test]
    #[DataProvider('numberProvider')]
    public function writesTheNumberAsItsCountryDoes(
        string $value,
        string $country,
        ?string $label,
        string $number,
        string $written,
    ): void {
        $taxId = TaxId::from(" {$value} ", $country);

        static::assertSame([$label, $number, $written], [$taxId?->label(), $taxId?->number(), (string) $taxId]);
    }
}
