<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use Contenir\Contact\Exception\InvalidFormatException;

use function array_values;
use function is_array;
use function is_string;

/**
 * The formats a site shows its contact details in: the package's three
 * (full, address and compact), with the site's own from its config laid
 * over them by name.
 *
 * @api
 */
final readonly class Formats
{
    /**
     * The format a Contact block uses when its choice is missing or names
     * a format the site no longer has.
     */
    public const string DEFAULT = 'full';

    /**
     * The Contact block's choice of its own parts.
     */
    public const string CUSTOM = 'custom';

    /**
     * The full format, which a Contact block falls back on.
     *
     * @var array{part: list<string>, label: string}
     */
    private const array FULL = [
        'part'  => ['name', 'address', 'telephone', 'email', 'hour', 'map'],
        'label' => 'Full contact details',
    ];

    /**
     * The package's formats.
     *
     * @var array<string, array{part: list<string>, label: string}>
     */
    public const array PACKAGE = [
        'full'    => self::FULL,
        'address' => ['part' => ['name', 'address'], 'label' => 'Address'],
        'compact' => ['part' => ['name', 'telephone', 'email'], 'label' => 'Telephone and email'],
    ];

    /**
     * @param array<string, Format> $formats By name
     * @param Format                $default The full format, which a Contact block falls back on
     */
    private function __construct(
        private array $formats,
        private Format $default,
    ) {}

    /**
     * The package's formats with the site's laid over them, from
     * contenir_contact.format in its config.
     *
     * @throws InvalidFormatException When a format in the config is wrong.
     *
     * @mago-expect analysis:mixed-assignment Config is untyped; each format is checked as it is read.
     */
    public static function fromConfig(mixed $config): self
    {
        $merged  = [...self::PACKAGE, ...(is_array($config) ? $config : [])];
        $formats = [];
        foreach ($merged as $name => $format) {
            $formats[(string) $name] = Format::fromConfig((string) $name, $format);
        }

        return new self(
            $formats,
            $formats[self::DEFAULT] ?? Format::fromConfig(self::DEFAULT, self::FULL),
        );
    }

    /**
     * Every format, for the Contact block's Format list.
     *
     * @return list<Format>
     */
    public function all(): array
    {
        return array_values($this->formats);
    }

    /**
     * The format a Contact block chose, as it saves the choice:
     * ['format' => 'footer'], or ['format' => 'custom', 'part' => ['address', 'map']].
     * A missing choice or a format the site no longer has is the full one.
     *
     * @throws InvalidFormatException When a custom choice names a part that doesn't exist.
     */
    public function chosen(mixed $fields): Format
    {
        $name = is_array($fields) && is_string($fields['format'] ?? null) ? $fields['format'] : self::DEFAULT;
        if (self::CUSTOM === $name && is_array($fields)) {
            return Format::fromConfig(self::CUSTOM, ['part' => $fields['part'] ?? [], 'label' => 'Custom']);
        }

        return $this->formats[$name] ?? $this->default;
    }

    public function get(string $name): ?Format
    {
        return $this->formats[$name] ?? null;
    }
}
