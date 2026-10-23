<?php

declare(strict_types=1);

namespace Contenir\Contact\Value;

use Contenir\Contact\Internal\SettingsArray;

use function array_filter;
use function array_values;
use function implode;
use function strtoupper;
use function trim;

/**
 * An office's street address, in its parts.
 *
 * @api
 */
final readonly class PostalAddress
{
    /**
     * @param string $street   Street number and name, unit included
     * @param string $locality Suburb or town
     * @param string $region   State, as written locally: "VIC"
     * @param string $country  Two-letter code
     */
    public function __construct(
        public string $street = '',
        public string $locality = '',
        public string $region = '',
        public string $postcode = '',
        public string $country = '',
    ) {}

    /**
     * The address in an office's settings row, or null when it has neither
     * a street nor a suburb or town.
     *
     * @param array<array-key, mixed> $row
     * @param string                  $country The country when the row gives none
     */
    public static function fromRow(array $row, string $country = ''): ?self
    {
        $values  = new SettingsArray($row);
        $address = new self(
            $values->text('street'),
            $values->text('locality'),
            $values->text('region'),
            $values->text('postcode'),
            strtoupper('' === $values->text('country') ? $country : $values->text('country')),
        );

        return '' === $address->street && '' === $address->locality ? null : $address;
    }

    /**
     * The address as it is written on an envelope, without the country:
     * ["402 Queens Parade", "Fitzroy North VIC 3068"].
     *
     * @return list<string>
     */
    public function lines(): array
    {
        return array_values(array_filter(
            [$this->street, trim("{$this->locality} {$this->region} {$this->postcode}")],
            static fn(string $line): bool => '' !== $line,
        ));
    }

    /**
     * The address on one line: "402 Queens Parade, Fitzroy North VIC 3068".
     */
    public function oneLine(): string
    {
        return implode(', ', $this->lines());
    }

    /**
     * The address as schema.org's PostalAddress, without the parts it lacks.
     *
     * @return array<string, string>
     */
    public function schema(): array
    {
        return array_filter(
            [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $this->street,
                'addressLocality' => $this->locality,
                'addressRegion'   => $this->region,
                'postalCode'      => $this->postcode,
                'addressCountry'  => $this->country,
            ],
            static fn(string $part): bool => '' !== $part,
        );
    }
}
