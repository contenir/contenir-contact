<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;
use function is_array;
use function is_string;
use function trim;

/**
 * An office's values over the business's: a key the office row has is the
 * office's own, even when empty, which means the office has none; a key it
 * lacks is the business's.
 *
 * @internal
 */
final readonly class Inherited
{
    /**
     * The values an office can override, by the key its row saves them
     * under, with the business's key where it differs.
     */
    public const array OVERRIDABLE = [
        'trading_name'    => 'name',
        'legal_name'      => 'legal_name',
        'tax_id'          => 'tax_id',
        'type'            => 'type',
        'additional_type' => 'additional_type',
        'telephone'       => 'telephone',
        'email'           => 'email',
        'hour'            => 'hour',
        'timezone'        => 'timezone',
        'price_range'     => 'price_range',
        'cuisine'         => 'cuisine',
        'menu'            => 'menu',
        'reservation'     => 'reservation',
        'star_rating'     => 'star_rating',
    ];

    /**
     * @param array<array-key, mixed>      $business
     * @param array<array-key, mixed>|null $office   Null for the business itself
     */
    public function __construct(
        private array $business,
        private ?array $office = null,
    ) {}

    private static function isEmpty(mixed $value): bool
    {
        return null === $value || [] === $value || is_string($value) && '' === trim($value);
    }

    /**
     * The keys the office overrides with none: an empty value, so it has
     * none where the business has one.
     *
     * @return list<string>
     */
    public function cleared(): array
    {
        return array_values(array_filter(
            array_keys(self::OVERRIDABLE),
            fn(string $key): bool => $this->overrides($key) && self::isEmpty($this->office[$key] ?? null),
        ));
    }

    /**
     * The keys the office overrides with a value of its own.
     *
     * @return list<string>
     */
    public function overridden(): array
    {
        return array_values(array_filter(
            array_keys(self::OVERRIDABLE),
            fn(string $key): bool => $this->overrides($key) && ! self::isEmpty($this->office[$key] ?? null),
        ));
    }

    /**
     * Whether the office's row overrides $key, with a value or with none.
     */
    public function overrides(string $key): bool
    {
        return (
            is_array($this->office)
                && array_key_exists($key, self::OVERRIDABLE)
                && array_key_exists($key, $this->office)
        );
    }

    /**
     * The value for an office key: the office's own when its row has the
     * key, or else the business's.
     *
     * @param key-of<self::OVERRIDABLE> $key
     */
    public function value(string $key): mixed
    {
        if (null !== $this->office && $this->overrides($key)) {
            return $this->office[$key] ?? null;
        }

        return $this->business[self::OVERRIDABLE[$key] ?? $key] ?? null;
    }
}
