<?php

declare(strict_types=1);

namespace Contenir\Contact\Hour;

use DateTimeInterface;

use function substr;

/**
 * A day of the week, saved as schema.org's two-letter code.
 *
 * @api
 */
enum Day: string
{
    case Monday    = 'Mo';
    case Tuesday   = 'Tu';
    case Wednesday = 'We';
    case Thursday  = 'Th';
    case Friday    = 'Fr';
    case Saturday  = 'Sa';
    case Sunday    = 'Su';

    /**
     * The day at a place in the week, Monday being 1; 0 is Sunday and 8 Monday, so the days run on.
     */
    public static function fromNumber(int $number): self
    {
        return match ((($number % 7) + 7) % 7) {
            1       => self::Monday,
            2       => self::Tuesday,
            3       => self::Wednesday,
            4       => self::Thursday,
            5       => self::Friday,
            6       => self::Saturday,
            default => self::Sunday,
        };
    }

    /**
     * The day a moment falls on, in the moment's own timezone.
     */
    public static function of(DateTimeInterface $moment): self
    {
        return self::fromNumber((int) $moment->format('N'));
    }

    /**
     * Its short name: "Mon".
     */
    public function label(): string
    {
        return substr($this->name, offset: 0, length: 3);
    }

    /**
     * Its place in the week, Monday being 1.
     */
    public function number(): int
    {
        return match ($this) {
            self::Monday => 1,
            self::Tuesday => 2,
            self::Wednesday => 3,
            self::Thursday => 4,
            self::Friday => 5,
            self::Saturday => 6,
            self::Sunday => 7,
        };
    }

    /**
     * The day before.
     */
    public function previous(): self
    {
        return self::fromNumber($this->number() - 1);
    }
}
