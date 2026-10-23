<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Internal;

use Contenir\Contact\Internal\SettingsArray;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class SettingsArrayTest extends TestCase
{
    /**
     * @return array<string, array{mixed, bool}>
     */
    public static function flagProvider(): array
    {
        return [
            'true'         => [true, true],
            'false'        => [false, false],
            'one'          => ['1', true],
            'zero as int'  => [0, false],
            'yes'          => [' Yes ', true],
            'off'          => ['off', false],
            'empty'        => ['', false],
            'not a switch' => ['maybe', false],
            'a list'       => [['1'], false],
        ];
    }

    /**
     * @return array<string, array{mixed, string}>
     */
    public static function textProvider(): array
    {
        return [
            'text, trimmed' => [' Studio ', 'Studio'],
            'an integer'    => [3068, '3068'],
            'a float'       => [1.5, '1.5'],
            'a list'        => [['a'], ''],
            'null'          => [null, ''],
        ];
    }

    #[Test]
    public function aMissingSwitchIsTheDefault(): void
    {
        static::assertSame(
            [false, true],
            [(new SettingsArray([]))->flag('a.b', default: false), (new SettingsArray([]))->flag('a.b', default: true)],
        );
    }

    #[Test]
    #[DataProvider('flagProvider')]
    public function readsASwitchOrFallsBackOnTheDefault(mixed $value, bool $expected): void
    {
        static::assertSame($expected, (new SettingsArray(['a' => ['b' => $value]]))->flag('a.b', default: false));
    }

    #[Test]
    public function readsAValueByDottedKey(): void
    {
        $settings = new SettingsArray(['a' => ['b' => ['c' => 1]], 'x' => 'text']);

        static::assertSame(
            [['c' => 1], 1, null, null],
            [$settings->value('a.b'), $settings->value('a.b.c'), $settings->value('a.z'), $settings->value('x.y')],
        );
    }

    #[Test]
    #[DataProvider('textProvider')]
    public function readsText(mixed $value, string $expected): void
    {
        static::assertSame($expected, (new SettingsArray(['a' => ['b' => $value]]))->text('a.b'));
    }

    #[Test]
    public function tellsAKeyHoldingNothingFromAMissingOne(): void
    {
        $settings = new SettingsArray(['a' => ['b' => null, 'c' => ''], 'x' => 'text']);

        static::assertSame(
            [true, true, false, false, false, true],
            [
                $settings->has('a.b'),
                $settings->has('a.c'),
                $settings->has('a.d'),
                $settings->has('x.y'),
                $settings->has('z.y'),
                $settings->has('x'),
            ],
        );
    }
}
