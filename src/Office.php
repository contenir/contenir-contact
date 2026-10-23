<?php

declare(strict_types=1);

namespace Contenir\Contact;

use Contenir\Contact\Hour\OpeningHours;
use Contenir\Contact\Type\AdditionalType;
use Contenir\Contact\Type\BusinessType;
use Contenir\Contact\Type\TypeDetail;
use Contenir\Contact\Value\Email;
use Contenir\Contact\Value\Geolocation;
use Contenir\Contact\Value\PostalAddress;
use Contenir\Contact\Value\TaxId;
use Contenir\Contact\Value\Telephone;

use function in_array;
use function mb_strtolower;
use function preg_replace;
use function trim;

/**
 * One office of the business, with every value as it applies to the office:
 * its own where it overrides the business's, the business's where it
 * doesn't, or none where it overrides with nothing.
 *
 * @api
 */
final readonly class Office
{
    /**
     * @param string               $id              Set when the office is added and never changed
     * @param string               $name            How the office is known: "Melbourne studio"
     * @param bool                 $search          Whether search engines are told of it
     * @param string               $tradingName     The name the office trades under
     * @param list<AdditionalType> $additionalTypes
     * @param list<string>         $overridden      The values it sets itself, by settings key
     * @param list<string>         $cleared         The values it has none of though the business has, by settings key
     *
     * @mago-expect lint:excessive-parameter-list Offices are built with named arguments, one per value.
     */
    public function __construct(
        public string $id,
        public string $name,
        public bool $primary,
        public bool $search,
        public ?PostalAddress $address,
        public ?Geolocation $geolocation,
        public string $tradingName,
        public ?string $legalName,
        public ?TaxId $taxId,
        public BusinessType $type,
        public array $additionalTypes,
        public ?Telephone $telephone,
        public ?Email $email,
        public OpeningHours $hour,
        public TypeDetail $detail,
        public array $overridden = [],
        public array $cleared = [],
    ) {}

    /**
     * Whether the office overrides the business's value under $key, with
     * a value of its own or with none.
     */
    public function overrides(string $key): bool
    {
        return in_array($key, $this->overridden, strict: true) || in_array($key, $this->cleared, strict: true);
    }

    /**
     * The office's name for addresses: "melbourne-studio".
     */
    public function slug(): string
    {
        return trim(
            (string) preg_replace('/[^\p{L}\p{N}]+/u', replacement: '-', subject: mb_strtolower($this->name)),
            characters: '-',
        );
    }
}
