<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use function array_filter;
use function array_map;
use function array_values;
use function filter_var;
use function is_array;
use function is_bool;
use function is_numeric;
use function is_string;
use function trim;

use const FILTER_VALIDATE_URL;

/**
 * Reads single saved values, which settings keep untyped.
 *
 * @internal
 */
final class Parse
{
    /**
     * A yes or no saved as a boolean, 1 or 0, or null when it is neither.
     */
    public static function flag(mixed $value): ?bool
    {
        return match (true) {
            is_bool($value) => $value,
            '1' === $value, 1 === $value => true,
            '0' === $value, 0 === $value => false,
            default => null,
        };
    }

    /**
     * A star rating from 0 to 5, or null.
     */
    public static function rating(mixed $value): ?float
    {
        $number = is_numeric($value) ? (float) $value : null;

        return null === $number || $number < 0 || $number > 5 ? null : $number;
    }

    /**
     * Trimmed text, or null when the value is empty or isn't text.
     */
    public static function text(mixed $value): ?string
    {
        $text = is_string($value) ? trim($value) : '';

        return '' === $text ? null : $text;
    }

    /**
     * The texts in a list, or a single text as a list of one.
     *
     * @return list<string>
     */
    public static function texts(mixed $value): array
    {
        return array_values(array_filter(array_map(self::text(...), is_array($value) ? $value : [$value])));
    }

    /**
     * An absolute URL, or null.
     */
    public static function url(mixed $value): ?string
    {
        $url = self::text($value);

        return null === $url || false === filter_var($url, FILTER_VALIDATE_URL) ? null : $url;
    }
}
