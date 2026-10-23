<?php

declare(strict_types=1);

namespace Contenir\Contact\Hour;

use function array_map;
use function count;
use function in_array;
use function is_array;
use function sprintf;

/**
 * One entry of an office's opening hours: the days it covers, and the time
 * it opens and closes on each, as "09:00" and "17:30". A closing time
 * before the opening time runs past midnight into the next day, and one the
 * same as the opening time keeps it open round the clock.
 *
 * @api
 */
final readonly class OpeningHour
{
    /**
     * @param non-empty-list<Day> $days In week order, each once
     */
    private function __construct(
        public array $days,
        public string $opens,
        public string $closes,
    ) {}

    /**
     * The entry in a settings row (['day' => ['Mo', 'Tu'], 'opens' => '09:00',
     * 'closes' => '17:30']), or null unless it names a day and both times.
     * A closing time may be "24:00", for midnight.
     */
    public static function from(mixed $row): ?self
    {
        $days   = is_array($row) ? Days::from($row['day'] ?? null) : [];
        $opens  = is_array($row) ? Times::opening($row['opens'] ?? null) : null;
        $closes = is_array($row) ? Times::closing($row['closes'] ?? null) : null;

        return [] === $days || null === $opens || null === $closes ? null : new self($days, $opens, $closes);
    }

    /**
     * Whether the entry has the office open at $time ("14:05") on $day,
     * counting hours that run past midnight from the day before.
     */
    public function isOpenAt(Day $day, string $time): bool
    {
        $today = in_array($day, $this->days, strict: true) && $time >= $this->opens;
        if ($this->closes > $this->opens) {
            return $today && $time < $this->closes;
        }

        return $today || in_array($day->previous(), $this->days, strict: true) && $time < $this->closes;
    }

    /**
     * The entry as a person reads it: "Mon–Fri 9:00am–5:30pm".
     *
     * @param string $format The times' format, as DateTimeInterface::format() takes it
     */
    public function label(string $format = 'g:ia'): string
    {
        return sprintf(
            '%s %s–%s',
            Days::label($this->days),
            Times::format($this->opens, $format),
            Times::format($this->closes, $format),
        );
    }

    /**
     * The entry as schema.org's OpeningHoursSpecification.
     *
     * @return array{'@type': 'OpeningHoursSpecification', dayOfWeek: string|list<string>, opens: string, closes: string}
     */
    public function schema(): array
    {
        return [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => 1 === count($this->days)
                ? $this->days[0]->name
                : array_map(static fn(Day $day): string => $day->name, $this->days),
            'opens'     => $this->opens,
            'closes'    => $this->closes,
        ];
    }
}
