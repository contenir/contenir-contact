<?php

declare(strict_types=1);

namespace Contenir\Contact\Hour;

use function array_filter;
use function array_map;
use function array_unique;
use function implode;
use function is_array;
use function is_string;
use function sort;
use function trim;

/**
 * Reads the days an opening-hours entry covers, and writes them as a person
 * reads them: runs of three or more as a range ("Mon–Fri"), shorter runs one
 * by one ("Sat, Sun").
 *
 * @internal
 */
final class Days
{
    /**
     * The days a saved value names by their two-letter codes, in week order,
     * each once; codes that aren't days are left out.
     *
     * @return list<Day>
     */
    public static function from(mixed $value): array
    {
        $numbers = array_unique(array_map(
            static fn(Day $day): int => $day->number(),
            array_filter(array_map(
                static fn(mixed $code): ?Day => is_string($code) ? Day::tryFrom(trim($code)) : null,
                is_array($value) ? $value : [$value],
            )),
        ));
        sort($numbers);

        return array_map(Day::fromNumber(...), $numbers);
    }

    /**
     * @param list<Day> $days In week order, each once
     */
    public static function label(array $days): string
    {
        $labels = [];
        $first  = null;
        $last   = null;
        foreach ($days as $day) {
            if (null !== $last && ($last->number() + 1) === $day->number()) {
                $last = $day;
                continue;
            }

            if (null !== $first && null !== $last) {
                $labels[] = self::run($first, $last);
            }

            $first = $day;
            $last  = $day;
        }

        if (null !== $first && null !== $last) {
            $labels[] = self::run($first, $last);
        }

        return implode(', ', $labels);
    }

    private static function run(Day $first, Day $last): string
    {
        return match ($last->number() - $first->number()) {
            0       => $first->label(),
            1       => "{$first->label()}, {$last->label()}",
            default => "{$first->label()}–{$last->label()}",
        };
    }
}
