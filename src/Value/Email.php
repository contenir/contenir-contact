<?php

declare(strict_types=1);

namespace Contenir\Contact\Value;

use Override;
use Stringable;

use function filter_var;
use function is_string;
use function trim;

use const FILTER_VALIDATE_EMAIL;

/**
 * An email address to contact a business or office at.
 *
 * @api
 */
final readonly class Email implements Stringable
{
    private function __construct(
        public string $address,
    ) {}

    /**
     * The address, or null when the value isn't a valid one.
     */
    public static function from(mixed $value): ?self
    {
        $address = is_string($value) ? trim($value) : '';

        return false === filter_var($address, FILTER_VALIDATE_EMAIL) ? null : new self($address);
    }

    /**
     * The address as a mailto: link.
     */
    public function uri(): string
    {
        return "mailto:{$this->address}";
    }

    #[Override]
    public function __toString(): string
    {
        return $this->address;
    }
}
