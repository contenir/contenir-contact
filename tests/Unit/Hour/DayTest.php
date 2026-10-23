<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Hour;

use Contenir\Contact\Hour\Day;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

#[Group('unit')]
final class DayTest extends TestCase
{
    /**
     * @return array<string, array{int, Day}>
     */
    public static function numberProvider(): array
    {
        return [
            'Monday'         => [1, Day::Monday],
            'Tuesday'        => [2, Day::Tuesday],
            'Wednesday'      => [3, Day::Wednesday],
            'Thursday'       => [4, Day::Thursday],
            'Friday'         => [5, Day::Friday],
            'Saturday'       => [6, Day::Saturday],
            'Sunday'         => [7, Day::Sunday],
            '0 is Sunday'    => [0, Day::Sunday],
            '8 is Monday'    => [8, Day::Monday],
            '-1 is Saturday' => [-1, Day::Saturday],
        ];
    }

    #[Test]
    public function findsTheDayAMomentFallsOnInItsOwnTimezone(): void
    {
        $moment = new DateTimeImmutable('2026-10-25 23:30', new DateTimeZone('Australia/Perth'));

        static::assertSame(Day::Sunday, Day::of($moment));
    }

    #[Test]
    #[DataProvider('numberProvider')]
    public function findsTheDayAtAPlaceInTheWeek(int $number, Day $day): void
    {
        static::assertSame($day, Day::fromNumber($number));
    }

    #[Test]
    public function numbersEachDayFromMonday(): void
    {
        static::assertSame(
            [1, 2, 3, 4, 5, 6, 7],
            array_map(static fn(Day $day): int => $day->number(), Day::cases()),
        );
    }

    #[Test]
    public function theDayBeforeMondayIsSunday(): void
    {
        static::assertSame([Day::Sunday, Day::Monday], [Day::Monday->previous(), Day::Tuesday->previous()]);
    }

    #[Test]
    public function writesItsShortName(): void
    {
        static::assertSame(['Mon', 'Thu', 'Sun'], [Day::Monday->label(), Day::Thursday->label(), Day::Sunday->label()]);
    }
}
