<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit;

use Contenir\Contact\Tests\TestAsset\Factory\BusinessFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class OfficeTest extends TestCase
{
    #[Test]
    public function knowsWhichValuesItOverrides(): void
    {
        $office = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Perth', ['telephone' => '08 9000 0000', 'email' => '']),
        ])->offices->primary();

        static::assertSame(
            [true, true, false],
            [$office?->overrides('telephone'), $office?->overrides('email'), $office?->overrides('hour')],
        );
    }

    #[Test]
    public function writesItsNameForAddresses(): void
    {
        $offices = BusinessFactory::make(offices: [
            BusinessFactory::office('a', 'Fitzroy North — Studio & Gallery!'),
            BusinessFactory::office('b', 'ÉCOLE Zürich'),
        ])->offices;

        static::assertSame(['fitzroy-north-studio-gallery', 'école-zürich'], [
            $offices->byId('a')?->slug(),
            $offices->byId('b')?->slug(),
        ]);
    }
}
