<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Hour;

use Contenir\Contact\Hour\Day;
use Contenir\Contact\Hour\Days;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class DaysTest extends TestCase
{
    /**
     * @return array<string, array{list<string>, string}>
     */
    public static function labelProvider(): array
    {
        return [
            'a run'           => [['Mo', 'Tu', 'We', 'Th', 'Fr'], 'Mon–Fri'],
            'a run and a day' => [['Mo', 'Tu', 'We', 'Fr'], 'Mon–Wed, Fri'],
            'two days'        => [['Sa', 'Su'], 'Sat, Sun'],
            'one day'         => [['We'], 'Wed'],
            'days apart'      => [['Mo', 'We', 'Fr'], 'Mon, Wed, Fri'],
            'two runs'        => [['Mo', 'Tu', 'We', 'Fr', 'Sa', 'Su'], 'Mon–Wed, Fri–Sun'],
            'the whole week'  => [['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'], 'Mon–Sun'],
            'none'            => [[], ''],
        ];
    }

    #[Test]
    public function readsCodesWithSpaceAround(): void
    {
        static::assertSame([Day::Tuesday], Days::from([' Tu ']));
    }

    #[Test]
    public function readsOneDayOrNone(): void
    {
        static::assertSame([[Day::Friday], []], [Days::from('Fr'), Days::from(null)]);
    }

    #[Test]
    public function readsTheDaysInWeekOrderEachOnce(): void
    {
        static::assertSame(
            [Day::Monday, Day::Wednesday, Day::Sunday],
            Days::from(['Su', ' We ', 'Mo', 'We', 'Xx', 3]),
        );
    }

    /**
     * @param list<string> $codes
     */
    #[Test]
    #[DataProvider('labelProvider')]
    public function writesTheDaysAsAPersonReadsThem(array $codes, string $label): void
    {
        static::assertSame($label, Days::label(Days::from($codes)));
    }
}
