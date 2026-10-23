<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Hour;

use Contenir\Contact\Hour\Day;
use Contenir\Contact\Hour\OpeningHour;
use Contenir\Contact\Hour\OpeningHours;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function count;
use function iterator_to_array;

#[Group('unit')]
final class OpeningHoursTest extends TestCase
{
    private static function hours(string $zone = 'Australia/Perth'): OpeningHours
    {
        return OpeningHours::from([
            ['day' => ['Mo', 'Tu', 'We', 'Th', 'Fr'], 'opens' => '09:00', 'closes' => '17:30'],
            'not an entry',
            ['day' => ['Sa'], 'opens' => '10:00', 'closes' => '14:00'],
            ['day' => ['Sa'], 'opens' => '15:00', 'closes' => '17:00'],
        ], new DateTimeZone($zone));
    }

    #[Test]
    public function describesEachEntryAsAnOpeningHoursSpecification(): void
    {
        $specifications = self::hours()->specifications();

        static::assertSame(
            [3, 'Saturday', '15:00'],
            [count($specifications), $specifications[2]['dayOfWeek'] ?? null, $specifications[2]['opens'] ?? null],
        );
    }

    #[Test]
    public function findsTheEntriesForADay(): void
    {
        static::assertSame(
            [['10:00', '15:00'], [], ['09:00']],
            [
                array_map(static fn(OpeningHour $entry): string => $entry->opens, self::hours()->on(Day::Saturday)),
                self::hours()->on(Day::Sunday),
                array_map(static fn(OpeningHour $entry): string => $entry->opens, self::hours()->on(Day::Monday)),
            ],
        );
    }

    #[Test]
    public function findsTodaysEntriesInTheOfficesTimezone(): void
    {
        $sundayInPerth  = new DateTimeImmutable('2026-10-25 23:30', new DateTimeZone('Australia/Perth'));
        $mondayInSydney = $sundayInPerth->setTimezone(new DateTimeZone('Australia/Sydney'));

        static::assertSame([], self::hours()->today($mondayInSydney));
    }

    #[Test]
    public function isOpenAtAMomentReadInTheOfficesTimezone(): void
    {
        $sydney = new DateTimeZone('Australia/Sydney');

        static::assertSame(
            [false, true, false],
            [
                self::hours()->isOpenAt(new DateTimeImmutable('2026-10-27 10:00', $sydney)),
                self::hours()->isOpenAt(new DateTimeImmutable('2026-10-27 13:00', $sydney)),
                self::hours()->isOpenAt(new DateTimeImmutable('2026-10-31 17:30', $sydney)),
            ],
        );
    }

    #[Test]
    public function noEntriesIsEmpty(): void
    {
        $hours = OpeningHours::from('by appointment', new DateTimeZone('UTC'));

        static::assertSame([true, 0, ''], [$hours->isEmpty(), count($hours), $hours->summary()]);
    }

    #[Test]
    public function readsTheEntriesLeavingOutAnyThatArentOne(): void
    {
        $hours = self::hours();

        static::assertSame(
            [3, 3, false, 'Australia/Perth'],
            [count($hours), count(iterator_to_array($hours)), $hours->isEmpty(), $hours->timezone->getName()],
        );
    }

    #[Test]
    public function writesItsEntriesAsAPersonReadsThem(): void
    {
        static::assertSame(
            [
                'Mon–Fri 9:00am–5:30pm, Sat 10:00am–2:00pm, Sat 3:00pm–5:00pm',
                'Mon–Fri 09:00–17:30, Sat 10:00–14:00, Sat 15:00–17:00',
            ],
            [self::hours()->summary(), self::hours()->summary('H:i')],
        );
    }
}
