<?php

declare(strict_types=1);

namespace Contenir\Contact\Hour;

use ArrayIterator;
use Countable;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use IteratorAggregate;
use Override;
use Traversable;

use function array_filter;
use function array_map;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_array;

/**
 * When an office is open: its entries, in the order they were entered, in
 * the office's timezone. No entries is "by appointment" or "closed"; the
 * site words it.
 *
 * @implements IteratorAggregate<int, OpeningHour>
 *
 * @api
 */
final readonly class OpeningHours implements Countable, IteratorAggregate
{
    /**
     * @param list<OpeningHour> $entries
     */
    public function __construct(
        public array $entries,
        public DateTimeZone $timezone,
    ) {}

    /**
     * The entries in a settings list, leaving out any that don't name a day
     * and both times.
     */
    public static function from(mixed $rows, DateTimeZone $timezone): self
    {
        return new self(
            array_values(array_filter(array_map(
                OpeningHour::from(...),
                is_array($rows) ? $rows : [],
            ))),
            $timezone,
        );
    }

    #[Override]
    public function count(): int
    {
        return count($this->entries);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->entries);
    }

    /**
     * Whether no entries were given.
     */
    public function isEmpty(): bool
    {
        return [] === $this->entries;
    }

    /**
     * Whether the office is open at $moment, read in the office's timezone,
     * so "Open now" is right for a Perth office whoever reads it.
     */
    public function isOpenAt(DateTimeInterface $moment): bool
    {
        $local = DateTimeImmutable::createFromInterface($moment)->setTimezone($this->timezone);
        $day   = Day::of($local);
        $time  = $local->format('H:i');

        foreach ($this->entries as $entry) {
            if ($entry->isOpenAt($day, $time)) {
                return true;
            }
        }

        return false;
    }

    /**
     * The entries covering $day.
     *
     * @return list<OpeningHour>
     */
    public function on(Day $day): array
    {
        return array_values(array_filter(
            $this->entries,
            static fn(OpeningHour $entry): bool => in_array($day, $entry->days, strict: true),
        ));
    }

    /**
     * The entries as schema.org's OpeningHoursSpecification, one each.
     *
     * @return list<array{'@type': 'OpeningHoursSpecification', dayOfWeek: string|list<string>, opens: string, closes: string}>
     */
    public function specifications(): array
    {
        $specifications = [];
        foreach ($this->entries as $entry) {
            $specifications[] = $entry->schema();
        }

        return $specifications;
    }

    /**
     * The entries as a person reads them: "Mon–Fri 9:00am–5:30pm, Sat
     * 10:00am–2:00pm".
     *
     * @param string $format The times' format, as DateTimeInterface::format() takes it
     */
    public function summary(string $format = 'g:ia'): string
    {
        return implode(', ', array_map(static fn(OpeningHour $entry): string => $entry->label(
            $format,
        ), $this->entries));
    }

    /**
     * The entries covering the day $now falls on in the office's timezone.
     *
     * @return list<OpeningHour>
     */
    public function today(DateTimeInterface $now): array
    {
        return $this->on(Day::of(DateTimeImmutable::createFromInterface($now)->setTimezone($this->timezone)));
    }
}
