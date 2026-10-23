<?php

declare(strict_types=1);

namespace Contenir\Contact\Value;

use Override;
use Stringable;

use function is_string;
use function preg_replace;
use function sprintf;
use function strlen;
use function substr;
use function trim;

/**
 * A business's tax or business number, written as its country writes it:
 * "ABN 12 345 678 901" for an Australian business.
 *
 * @api
 */
final readonly class TaxId implements Stringable
{
    /**
     * What each country calls the number.
     */
    private const array LABEL = [
        'AU' => 'ABN',
        'NZ' => 'NZBN',
    ];

    /**
     * @param string $value   As entered
     * @param string $country The site's country, as a two-letter code
     */
    private function __construct(
        public string $value,
        public string $country,
    ) {}

    /**
     * The number, or null when the value is empty.
     */
    public static function from(mixed $value, string $country = ''): ?self
    {
        $number = is_string($value) ? trim($value) : '';

        return '' === $number ? null : new self($number, $country);
    }

    /**
     * What the country calls the number, such as "ABN", or null.
     */
    public function label(): ?string
    {
        return self::LABEL[$this->country] ?? null;
    }

    /**
     * The number alone, grouped as the country groups it: an Australian
     * ABN's 11 digits as "12 345 678 901".
     */
    public function number(): string
    {
        $digits = (string) preg_replace('/\D/', replacement: '', subject: $this->value);
        if ('AU' !== $this->country || 11 !== strlen($digits)) {
            return $this->value;
        }

        return sprintf(
            '%s %s %s %s',
            substr($digits, offset: 0, length: 2),
            substr($digits, offset: 2, length: 3),
            substr($digits, offset: 5, length: 3),
            substr($digits, offset: 8),
        );
    }

    #[Override]
    public function __toString(): string
    {
        $label = $this->label();

        return null === $label ? $this->number() : "{$label} {$this->number()}";
    }
}
