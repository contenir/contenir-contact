<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use Contenir\Contact\Exception\InvalidFormatException;

use function array_map;
use function array_values;
use function in_array;
use function is_array;
use function is_string;
use function str_replace;
use function trim;
use function ucfirst;

/**
 * A set of values shown together, in order, such as a footer's name and
 * address, with the template that draws it when the site has one.
 *
 * @api
 */
final readonly class Format
{
    /**
     * @param string     $name     How templates and Contact blocks ask for it: "footer"
     * @param list<Part> $parts
     * @param ?string    $template The site's template for it, or null for the package's markup
     * @param string     $label    What the Contact block's Format list calls it
     */
    public function __construct(
        public string $name,
        public array $parts,
        public Subject $subject = Subject::Office,
        public ?string $template = null,
        public string $label = '',
    ) {}

    /**
     * A format as the site's config gives it:
     * ['part' => ['name', 'address'], 'subject' => 'office', 'template' => 'partials/office-footer',
     * 'label' => 'Footer']. Only part is needed.
     *
     * @throws InvalidFormatException When it isn't an array or names a part or subject that doesn't exist.
     *
     * @mago-expect analysis:mixed-assignment Config is untyped; the subject is checked here.
     */
    public static function fromConfig(string $name, mixed $config): self
    {
        if (! is_array($config)) {
            throw InvalidFormatException::notAnArray($name, $config);
        }

        $subject  = $config['subject'] ?? Subject::Office->value;
        $template = is_string($config['template'] ?? null) ? trim($config['template']) : '';
        $label    = is_string($config['label'] ?? null) ? trim($config['label']) : '';

        return new self(
            $name,
            self::parts($name, $config['part'] ?? []),
            (is_string($subject) ? Subject::tryFrom($subject) : null)
            ?? throw InvalidFormatException::unknownSubject($name, $subject),
            '' === $template ? null : $template,
            '' === $label
                ? ucfirst(str_replace(
                    search: '_',
                    replace: ' ',
                    subject: $name,
                ))
                : $label,
        );
    }

    /**
     * @return list<Part>
     *
     * @throws InvalidFormatException When a part doesn't exist.
     */
    private static function parts(string $name, mixed $parts): array
    {
        return array_map(
            static fn(mixed $part): Part => (
                (is_string($part) ? Part::tryFrom($part) : null)
                ?? throw InvalidFormatException::unknownPart($name, $part)
            ),
            is_array($parts) ? array_values($parts) : [$parts],
        );
    }

    /**
     * Whether the format shows $part.
     */
    public function shows(Part $part): bool
    {
        return in_array($part, $this->parts, strict: true);
    }
}
