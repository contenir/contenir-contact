<?php

declare(strict_types=1);

namespace Contenir\Contact\Internal;

use Contenir\Contact\Hour\OpeningHours;
use Contenir\Contact\Type\AdditionalType;
use Contenir\Contact\Type\AdditionalTypes;
use Contenir\Contact\Type\BusinessType;
use Contenir\Contact\Type\TypeDetail;
use Contenir\Contact\Value\Email;
use Contenir\Contact\Value\TaxId;
use Contenir\Contact\Value\Telephone;
use DateTimeZone;

use function date_default_timezone_get;
use function in_array;
use function is_string;
use function timezone_identifiers_list;
use function trim;

/**
 * Reads the values a business and its offices share, the same way for both.
 *
 * @internal
 */
final readonly class Reader
{
    /**
     * @param string       $country  The site's country, as a two-letter code
     * @param string       $name     The name to fall back on
     * @param DateTimeZone $timezone The timezone to fall back on
     * @param BusinessType $type     The type to fall back on
     */
    public function __construct(
        public string $country,
        public string $name,
        public DateTimeZone $timezone,
        public BusinessType $type,
    ) {}

    /**
     * PHP's own timezone.
     *
     * @mago-expect analysis:unhandled-thrown-type PHP only accepts a valid timezone as its default.
     */
    public static function defaultZone(): DateTimeZone
    {
        return new DateTimeZone(date_default_timezone_get());
    }

    /**
     * A timezone's name, as IANA writes it, or null when it isn't one.
     *
     * @mago-expect analysis:unhandled-thrown-type The name was checked against timezone_identifiers_list() first.
     */
    public static function zone(mixed $value): ?DateTimeZone
    {
        $name = is_string($value) ? trim($value) : '';

        return in_array($name, timezone_identifiers_list(), strict: true) ? new DateTimeZone($name) : null;
    }

    /**
     * @return array{
     *     name: string,
     *     legalName: ?string,
     *     taxId: ?TaxId,
     *     type: BusinessType,
     *     additionalTypes: list<AdditionalType>,
     *     telephone: ?Telephone,
     *     email: ?Email,
     *     hour: OpeningHours,
     *     detail: TypeDetail,
     * }
     */
    public function details(Inherited $values): array
    {
        $type = BusinessType::from($values->value('type'), $this->type);

        return [
            'name'            => Parse::text($values->value('trading_name')) ?? $this->name,
            'legalName'       => Parse::text($values->value('legal_name')),
            'taxId'           => TaxId::from($values->value('tax_id'), $this->country),
            'type'            => $type,
            'additionalTypes' => AdditionalTypes::fromList($values->value('additional_type')),
            'telephone'       => Telephone::from($values->value('telephone'), $this->country),
            'email'           => Email::from($values->value('email')),
            'hour'            => OpeningHours::from(
                $values->value('hour'),
                self::zone($values->value('timezone')) ?? $this->timezone,
            ),
            'detail'          => TypeDetail::from($values, $type),
        ];
    }
}
