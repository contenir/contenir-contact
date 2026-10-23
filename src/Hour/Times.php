<?php

declare(strict_types=1);

namespace Contenir\Contact\Hour;

use DateTimeImmutable;

use function is_string;
use function preg_match;
use function trim;

/**
 * Reads and writes the times of an opening-hours entry, saved as "09:00".
 *
 * @internal
 */
final class Times
{
    private const string TIME = '/^([01]\d|2[0-3]):[0-5]\d$/';

    /**
     * The value as a closing time, which may also be "24:00", or null.
     */
    public static function closing(mixed $value): ?string
    {
        $time = is_string($value) ? trim($value) : '';

        return '24:00' === $time ? $time : self::opening($time);
    }

    /**
     * A saved time written with a DateTimeInterface::format() format.
     */
    public static function format(string $time, string $format): string
    {
        return (new DateTimeImmutable("2000-01-01 {$time}"))->format($format);
    }

    /**
     * The value as an opening time, or null.
     */
    public static function opening(mixed $value): ?string
    {
        $time = is_string($value) ? trim($value) : '';

        return 1 === preg_match(self::TIME, $time) ? $time : null;
    }
}
