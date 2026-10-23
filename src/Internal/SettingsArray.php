<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use function array_key_exists;
use function array_pop;
use function explode;
use function in_array;
use function is_array;
use function is_bool;
use function is_float;
use function is_int;
use function is_string;
use function strtolower;
use function trim;

/**
 * Reads values from a site's settings, or from one row of a list in them, by
 * dotted key.
 *
 * @internal
 */
final readonly class SettingsArray
{
    /**
     * Values that switch a setting on, as settings files store them.
     */
    private const array ON = ['1', 'true', 'on', 'yes'];

    /**
     * Values that switch a setting off.
     */
    private const array OFF = ['0', 'false', 'off', 'no', ''];

    /**
     * @param array<array-key, mixed> $settings
     */
    public function __construct(
        private array $settings,
    ) {}

    /**
     * The value at $key as a switch, or $default when it is missing or
     * can't be read as one.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; the value is checked here.
     */
    public function flag(string $key, bool $default): bool
    {
        $value = $this->value($key);
        if (is_bool($value)) {
            return $value;
        }

        $text = is_string($value) || is_int($value) ? strtolower(trim((string) $value)) : null;

        return match (true) {
            in_array($text, self::ON, strict: true) => true,
            in_array($text, self::OFF, strict: true) => false,
            default => $default,
        };
    }

    /**
     * Whether $key is there at all, even holding null or an empty value.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; each level is checked here.
     */
    public function has(string $key): bool
    {
        $parent = $this->settings;
        $parts  = explode('.', $key);
        $last   = array_pop($parts);
        foreach ($parts as $segment) {
            $parent = is_array($parent) ? $parent[$segment] ?? null : null;
        }

        return is_array($parent) && array_key_exists($last, $parent);
    }

    /**
     * The value at $key as trimmed text, or an empty string when it isn't
     * text or a number.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; the value is checked here.
     */
    public function text(string $key): string
    {
        $value = $this->value($key);

        return is_string($value) || is_int($value) || is_float($value) ? trim((string) $value) : '';
    }

    /**
     * The value at $key, or null when it is missing.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; each level is checked here.
     */
    public function value(string $key): mixed
    {
        $value = $this->settings;
        foreach (explode('.', $key) as $segment) {
            if (! is_array($value) || ! array_key_exists($segment, $value)) {
                return null;
            }

            $value = $value[$segment];
        }

        return $value;
    }
}
