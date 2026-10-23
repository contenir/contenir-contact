<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Internal;

use Contenir\Contact\Internal\Parse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class ParseTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?bool}>
     */
    public static function flagProvider(): array
    {
        return [
            'true'       => [true, true],
            'false'      => [false, false],
            'one'        => ['1', true],
            'one as int' => [1, true],
            'zero'       => ['0', false],
            'zero int'   => [0, false],
            'other'      => ['yes', null],
            'null'       => [null, null],
        ];
    }

    /**
     * @return array<string, array{mixed, ?float}>
     */
    public static function ratingProvider(): array
    {
        return [
            'an integer'   => [4, 4.0],
            'a float'      => [4.5, 4.5],
            'text'         => ['3.5', 3.5],
            'zero'         => [0, 0.0],
            'five'         => ['5', 5.0],
            'above five'   => [5.1, null],
            'below zero'   => [-0.1, null],
            'not a number' => ['four', null],
            'null'         => [null, null],
        ];
    }

    #[Test]
    public function readsAListOfTextsOrOneText(): void
    {
        static::assertSame(
            [['Italian', 'Cafe'], ['Italian'], []],
            [Parse::texts(['Italian', ' ', 3, ' Cafe ']), Parse::texts('Italian'), Parse::texts(null)],
        );
    }

    #[Test]
    #[DataProvider('ratingProvider')]
    public function readsAStarRatingFromZeroToFive(mixed $value, ?float $expected): void
    {
        static::assertSame($expected, Parse::rating($value));
    }

    #[Test]
    #[DataProvider('flagProvider')]
    public function readsAYesOrNo(mixed $value, ?bool $expected): void
    {
        static::assertSame($expected, Parse::flag($value));
    }

    #[Test]
    public function readsOnlyAbsoluteUrls(): void
    {
        static::assertSame(
            ['https://example.test/menu', null, null],
            [Parse::url(' https://example.test/menu '), Parse::url('/menu'), Parse::url(null)],
        );
    }

    #[Test]
    public function readsTextAndLeavesOutEmptyText(): void
    {
        static::assertSame(['$$', null, null], [Parse::text(' $$ '), Parse::text('  '), Parse::text(3)]);
    }
}
