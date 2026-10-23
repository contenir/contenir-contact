<?php

declare(strict_types=1);

namespace Contenir\Contact;

use Contenir\Contact\Internal\Inherited;
use Contenir\Contact\Internal\OfficeRows;
use Contenir\Contact\Internal\Reader;
use Contenir\Contact\Internal\SettingsArray;
use Contenir\Contact\Type\BusinessType;
use DateTimeZone;

use function is_array;
use function preg_match;
use function strtoupper;

/**
 * Reads the business and its offices from a site's settings: the business
 * under contact.business, its offices as a list under contact.office.
 *
 * @api
 */
final class ContactSettings
{
    public const string BUSINESS = 'contact.business';

    public const string OFFICE = 'contact.office';

    public const string SITE_TITLE = 'site.title';

    public const string SITE_LOCALE = 'site.locale';

    public const string SITE_TIMEZONE = 'site.timezone';

    /**
     * The business, with its offices.
     *
     * Its name falls back on the site's title; its timezone on the site's,
     * then $timezone, then PHP's; its country comes from the site's locale
     * ("en_AU").
     *
     * @param array<array-key, mixed> $settings
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; the business's are checked here.
     */
    public static function fromSettings(array $settings, ?DateTimeZone $timezone = null): Business
    {
        $values   = new SettingsArray($settings);
        $found    = $values->value(self::BUSINESS);
        $business = is_array($found) ? $found : [];
        $country  = self::country($values->text(self::SITE_LOCALE));
        $reader   = new Reader(
            $country,
            $values->text(self::SITE_TITLE),
            Reader::zone($values->value(self::SITE_TIMEZONE))
            ?? $timezone
            ?? Reader::defaultZone(),
            BusinessType::from(null),
        );
        $details = $reader->details(new Inherited($business));
        $rows    = new OfficeRows(
            $business,
            new Reader($country, $details['name'], $details['hour']->timezone, $details['type']),
        );

        return new Business(
            name: $details['name'],
            legalName: $details['legalName'],
            taxId: $details['taxId'],
            type: $details['type'],
            additionalTypes: $details['additionalTypes'],
            telephone: $details['telephone'],
            email: $details['email'],
            hour: $details['hour'],
            detail: $details['detail'],
            offices: $rows->offices($values->value(self::OFFICE)),
            country: $country,
        );
    }

    /**
     * The country a locale names: "AU" for "en_AU", or an empty string.
     */
    private static function country(string $locale): string
    {
        $match = [];

        return 1 === preg_match('/_([A-Za-z]{2})\b/', $locale, $match) ? strtoupper($match[1] ?? '') : '';
    }
}
