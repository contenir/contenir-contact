<?php

declare(strict_types=1);

namespace Contenir\Contact\Value;

use Override;
use Stringable;

use function is_string;
use function ltrim;
use function preg_match;
use function str_replace;
use function str_starts_with;
use function strlen;
use function substr;
use function trim;

/**
 * A telephone number, as it was entered, written for the site's country.
 *
 * A number entered internationally ("+61 3 9428 0228") is written the way
 * its own country writes it ("03 9428 0228") when that is the site's
 * country and the country dials a 0 before local numbers. A number entered
 * the local way is written as it is, and turned international for its link.
 *
 * @api
 */
final readonly class Telephone implements Stringable
{
    /**
     * The calling codes of the countries that dial a 0 before a number
     * within the country and drop it when dialling from outside.
     */
    private const array TRUNK_ZERO = [
        'AU' => '61',
        'DE' => '49',
        'FR' => '33',
        'GB' => '44',
        'IE' => '353',
        'IN' => '91',
        'JP' => '81',
        'NZ' => '64',
        'ZA' => '27',
    ];

    /**
     * @param string $number  As entered
     * @param string $country The site's country, as a two-letter code
     */
    private function __construct(
        public string $number,
        public string $country,
    ) {}

    /**
     * The number, or null when the value holds no digits.
     *
     * @param string $country The site's country, as a two-letter code
     */
    public static function from(mixed $value, string $country = ''): ?self
    {
        $number = is_string($value) ? trim($value) : '';

        return 1 === preg_match('/\d/', $number) ? new self($number, $country) : null;
    }

    /**
     * The number as dialled from another country: "+61 3 9428 0228".
     */
    public function international(): string
    {
        $code = self::TRUNK_ZERO[$this->country] ?? null;
        if (null === $code || ! str_starts_with($this->number, '0')) {
            return $this->number;
        }

        return "+{$code} " . substr($this->number, offset: 1);
    }

    /**
     * The number as dialled within the site's country: "03 9428 0228".
     */
    public function national(): string
    {
        $code = self::TRUNK_ZERO[$this->country] ?? null;
        if (null === $code || ! str_starts_with($this->number, "+{$code}")) {
            return $this->number;
        }

        return '0' . ltrim(substr($this->number, offset: strlen($code) + 1));
    }

    /**
     * The number as a tel: link, international and without spaces.
     */
    public function uri(): string
    {
        return 'tel:' . str_replace([' ', '-', '.', '(', ')'], replace: '', subject: $this->international());
    }

    #[Override]
    public function __toString(): string
    {
        return $this->national();
    }
}
