<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use function array_filter;
use function array_key_exists;
use function is_array;
use function is_string;
use function trim;

/**
 * An office row as the CMS's Office details saves it, made like a row written
 * by hand.
 *
 * The CMS saves each row of a list as ['fields' => [...]], with every input
 * posted, so a value left empty there means the office inherits the
 * business's, never that it has none, and is dropped. Its position is saved
 * as flat latitude and longitude, and becomes geolocation. A row written by
 * hand, without the wrapper, is kept as it is.
 *
 * @internal
 */
final class CmsRow
{
    /**
     * @return array<array-key, mixed>
     */
    public static function flat(mixed $row): array
    {
        if (! is_array($row)) {
            return [];
        }

        if (! is_array($row['fields'] ?? null)) {
            return $row;
        }

        $fields = array_filter(
            $row['fields'],
            static fn(mixed $value): bool => (
                null !== $value && [] !== $value && ! (is_string($value) && '' === trim($value))
            ),
        );

        if (array_key_exists('latitude', $fields) || array_key_exists('longitude', $fields)) {
            $fields['geolocation'] = [
                'latitude'  => $fields['latitude'] ?? null,
                'longitude' => $fields['longitude'] ?? null,
            ];
            unset($fields['latitude'], $fields['longitude']);
        }

        return $fields;
    }
}
