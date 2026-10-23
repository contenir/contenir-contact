<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Hour;

use Contenir\Contact\Hour\Times;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TimesTest extends TestCase
{
    /**
     * @return array<string, array{mixed, ?string, ?string}>
     */
    public static function timeProvider(): array
    {
        return [
            'a time'                         => [' 09:00 ', '09:00', '09:00'],
            'just before midnight'           => ['23:59', '23:59', '23:59'],
            'midnight'                       => ['00:00', '00:00', '00:00'],
            'the end of the day'             => ['24:00', null, '24:00'],
            'the end of the day, with space' => [' 24:00 ', null, '24:00'],
            'no leading zero'                => ['9:00', null, null],
            'past 23'                        => ['25:00', null, null],
            'past 59 minutes'                => ['09:60', null, null],
            'seconds'                        => ['09:00:00', null, null],
            'not text'                       => [900, null, null],
        ];
    }

    #[Test]
    #[DataProvider('timeProvider')]
    public function readsOpeningAndClosingTimes(mixed $value, ?string $opening, ?string $closing): void
    {
        static::assertSame([$opening, $closing], [Times::opening($value), Times::closing($value)]);
    }

    #[Test]
    public function writesATimeInAFormat(): void
    {
        static::assertSame(['5:30pm', '17:30', '12:00am'], [
            Times::format('17:30', 'g:ia'),
            Times::format('17:30', 'H:i'),
            Times::format('24:00', 'g:ia'),
        ]);
    }
}
