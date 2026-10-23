<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Hour;

use Contenir\Contact\Hour\Day;
use Contenir\Contact\Hour\OpeningHour;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class OpeningHourTest extends TestCase
{
    /**
     * @return array<string, array{Day, string, bool}>
     */
    public static function daytimeProvider(): array
    {
        return [
            'as it opens'         => [Day::Monday, '09:00', true],
            'during'              => [Day::Wednesday, '12:00', true],
            'just before closing' => [Day::Friday, '17:29', true],
            'as it closes'        => [Day::Friday, '17:30', false],
            'before it opens'     => [Day::Monday, '08:59', false],
            'on another day'      => [Day::Saturday, '12:00', false],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'not a row'        => ['Mo-Fr 09:00-17:30'],
            'no days'          => [['day' => [], 'opens' => '09:00', 'closes' => '17:30']],
            'no opening time'  => [['day' => ['Mo'], 'closes' => '17:30']],
            'no closing time'  => [['day' => ['Mo'], 'opens' => '09:00']],
            'opening at 24:00' => [['day' => ['Mo'], 'opens' => '24:00', 'closes' => '17:30']],
        ];
    }

    /**
     * @return array<string, array{Day, string, bool}>
     */
    public static function overnightProvider(): array
    {
        return [
            'in the evening'              => [Day::Friday, '22:00', true],
            'after midnight'              => [Day::Saturday, '01:00', true],
            'as it closes'                => [Day::Saturday, '02:00', false],
            'before it opens'             => [Day::Friday, '17:00', false],
            'after midnight on a day off' => [Day::Friday, '01:00', false],
            'on another evening'          => [Day::Thursday, '22:00', false],
        ];
    }

    #[Test]
    public function aClosingTimeOfMidnightRunsToTheEndOfTheDay(): void
    {
        $entry = OpeningHour::from(['day' => ['Sa'], 'opens' => '18:00', 'closes' => '24:00']);

        static::assertSame(
            [true, false],
            [$entry?->isOpenAt(Day::Saturday, '23:59'), $entry?->isOpenAt(Day::Sunday, '00:30')],
        );
    }

    #[Test]
    public function anEntryClosingAsItOpensIsOpenRoundTheClock(): void
    {
        $entry = OpeningHour::from(['day' => ['Mo'], 'opens' => '00:00', 'closes' => '00:00']);

        static::assertSame(
            [true, true, true, false],
            [
                $entry?->isOpenAt(Day::Monday, '00:00'),
                $entry?->isOpenAt(Day::Monday, '12:00'),
                $entry?->isOpenAt(Day::Monday, '23:59'),
                $entry?->isOpenAt(Day::Tuesday, '00:00'),
            ],
        );
    }

    #[Test]
    public function describesItselfAsAnOpeningHoursSpecification(): void
    {
        static::assertSame(
            [
                [
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday'],
                    'opens'     => '09:00',
                    'closes'    => '17:30',
                ],
                [
                    '@type'     => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'Saturday',
                    'opens'     => '10:00',
                    'closes'    => '14:00',
                ],
            ],
            [
                OpeningHour::from(['day' => ['Mo', 'Tu'], 'opens' => '09:00', 'closes' => '17:30'])?->schema(),
                OpeningHour::from(['day' => 'Sa', 'opens' => '10:00', 'closes' => '14:00'])?->schema(),
            ],
        );
    }

    #[Test]
    #[DataProvider('overnightProvider')]
    public function hoursClosingAfterMidnightRunIntoTheNextDay(Day $day, string $time, bool $open): void
    {
        $entry = OpeningHour::from(['day' => ['Fr'], 'opens' => '18:00', 'closes' => '02:00']);

        static::assertSame($open, $entry?->isOpenAt($day, $time));
    }

    #[Test]
    #[DataProvider('daytimeProvider')]
    public function isOpenFromItsOpeningUntilItsClosingTime(Day $day, string $time, bool $open): void
    {
        $entry = OpeningHour::from(['day' => ['Mo', 'Tu', 'We', 'Th', 'Fr'], 'opens' => '09:00', 'closes' => '17:30']);

        static::assertSame($open, $entry?->isOpenAt($day, $time));
    }

    #[Test]
    #[DataProvider('invalidProvider')]
    public function leavesOutAnEntryWithoutDaysAndBothTimes(mixed $row): void
    {
        static::assertNull(OpeningHour::from($row));
    }

    #[Test]
    public function readsAnEntryFromItsRow(): void
    {
        $entry = OpeningHour::from(['day' => ['Fr', 'Mo'], 'opens' => '09:00', 'closes' => '17:30']);

        static::assertSame(
            [[Day::Monday, Day::Friday], '09:00', '17:30'],
            [$entry?->days, $entry?->opens, $entry?->closes],
        );
    }

    #[Test]
    public function writesItselfAsAPersonReadsIt(): void
    {
        $entry = OpeningHour::from(['day' => ['Mo', 'Tu', 'We'], 'opens' => '09:00', 'closes' => '17:30']);

        static::assertSame(
            ['Mon–Wed 9:00am–5:30pm', 'Mon–Wed 09:00–17:30'],
            [$entry?->label(), $entry?->label('H:i')],
        );
    }
}
