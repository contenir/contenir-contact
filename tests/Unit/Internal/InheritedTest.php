<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Internal;

use Contenir\Contact\Internal\Inherited;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class InheritedTest extends TestCase
{
    private const array BUSINESS = ['name' => 'Studio Esteta', 'telephone' => '03 9428 0228', 'email' => 'a@b.test'];

    #[Test]
    public function anOfficeHasTheBusinessesValuesItDoesNotOverride(): void
    {
        $values = new Inherited(self::BUSINESS, ['name' => 'Perth', 'telephone' => '08 9000 0000']);

        static::assertSame(
            ['Studio Esteta', '08 9000 0000', 'a@b.test'],
            [$values->value('trading_name'), $values->value('telephone'), $values->value('email')],
        );
    }

    #[Test]
    public function anOfficeOverridingWithNothingHasNone(): void
    {
        $values = new Inherited(self::BUSINESS, ['email' => null, 'telephone' => ' ', 'hour' => []]);

        static::assertSame(
            [null, ' ', [], ['telephone', 'email', 'hour'], []],
            [
                $values->value('email'),
                $values->value('telephone'),
                $values->value('hour'),
                $values->cleared(),
                $values->overridden(),
            ],
        );
    }

    #[Test]
    public function namesTheValuesAnOfficeOverrides(): void
    {
        $values = new Inherited(self::BUSINESS, [
            'name'         => 'Perth',
            'street'       => '1 Hay St',
            'trading_name' => 'Esteta West',
            'timezone'     => 'Australia/Perth',
            'email'        => '',
        ]);
        $cleared = new Inherited(self::BUSINESS, ['trading_name' => '']);

        static::assertSame(
            [['trading_name', 'timezone'], ['email'], true, false, false, ['trading_name']],
            [
                $values->overridden(),
                $values->cleared(),
                $values->overrides('timezone'),
                $values->overrides('street'),
                $values->overrides('telephone'),
                $cleared->cleared(),
            ],
        );
    }

    #[Test]
    public function theBusinessReadsItsOwnValues(): void
    {
        $values = new Inherited(self::BUSINESS);

        static::assertSame(
            ['Studio Esteta', '03 9428 0228', null, [], []],
            [
                $values->value('trading_name'),
                $values->value('telephone'),
                $values->value('hour'),
                $values->overridden(),
                $values->cleared(),
            ],
        );
    }
}
