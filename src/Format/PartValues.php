<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use Contenir\Contact\Business;
use Contenir\Contact\Office;
use Contenir\Contact\Value\Geolocation;
use Contenir\Contact\Value\PostalAddress;

/**
 * The values a format's parts show that an office and the business keep
 * differently: the trading name, and the address and position, which for
 * the business are its primary office's.
 *
 * @internal
 */
final readonly class PartValues
{
    public function __construct(
        private Office|Business $subject,
    ) {}

    public function address(): ?PostalAddress
    {
        return $this->office()?->address;
    }

    public function geolocation(): ?Geolocation
    {
        return $this->office()?->geolocation;
    }

    public function tradingName(): string
    {
        return $this->subject instanceof Office ? $this->subject->tradingName : $this->subject->name;
    }

    private function office(): ?Office
    {
        return $this->subject instanceof Office ? $this->subject : $this->subject->offices->primary();
    }
}
