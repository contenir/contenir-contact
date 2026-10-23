<?php

declare(strict_types=1);

namespace Contenir\Contact;

use Contenir\Contact\Hour\OpeningHours;
use Contenir\Contact\Type\AdditionalType;
use Contenir\Contact\Type\BusinessType;
use Contenir\Contact\Type\TypeDetail;
use Contenir\Contact\Value\Email;
use Contenir\Contact\Value\TaxId;
use Contenir\Contact\Value\Telephone;

/**
 * The business: who it is, the values its offices have unless they say
 * otherwise, and its offices.
 *
 * @api
 */
final readonly class Business
{
    /**
     * @param string               $name    Its trading name
     * @param string               $country The site's country, as a two-letter code
     * @param list<AdditionalType> $additionalTypes
     *
     * @mago-expect lint:excessive-parameter-list The business is built with named arguments, one per value.
     */
    public function __construct(
        public string $name,
        public ?string $legalName,
        public ?TaxId $taxId,
        public BusinessType $type,
        public array $additionalTypes,
        public ?Telephone $telephone,
        public ?Email $email,
        public OpeningHours $hour,
        public TypeDetail $detail,
        public Offices $offices,
        public string $country = '',
    ) {}
}
