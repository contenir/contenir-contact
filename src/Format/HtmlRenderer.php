<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use Contenir\Contact\Business;
use Contenir\Contact\MapProvider;
use Contenir\Contact\Office;

use function array_filter;
use function array_map;
use function implode;
use function sprintf;

/**
 * Draws a format as plain markup, for a format with no template of the
 * site's own: an <address> element with a class per part, the telephone and
 * email as links, every value escaped. A part with no value is left out.
 *
 * For the business, the address and map are its primary office's.
 *
 * @api
 */
final readonly class HtmlRenderer
{
    /**
     * @param string $mapText The map link's text
     */
    public function __construct(
        private MapProvider $maps = MapProvider::OpenStreetMap,
        private string $mapText = 'Map',
    ) {}

    /**
     * The format's markup for an office or the business, or an empty string
     * when none of its parts has a value.
     */
    public function render(Office|Business $subject, Format $format): string
    {
        $parts = array_filter(array_map(fn(Part $part): ?string => $this->part($part, $subject), $format->parts));
        if ([] === $parts) {
            return '';
        }

        return sprintf(
            "<address class=\"contact contact--%s\">\n%s\n</address>",
            Html::escape($format->name),
            implode("\n", $parts),
        );
    }

    private function part(Part $part, Office|Business $subject): ?string
    {
        $values = new PartValues($subject);

        return match ($part) {
            Part::Name        => Html::element('strong', 'name', $subject->name),
            Part::TradingName => Html::element('span', 'trading-name', $values->tradingName()),
            Part::LegalName   => Html::element('span', 'legal-name', $subject->legalName),
            Part::TaxId => Html::element('span', 'tax-id', null === $subject->taxId ? null : (string) $subject->taxId),
            Part::Address     => Html::lines('address', $values->address()?->lines() ?? []),
            Part::Telephone   => Html::link('telephone', $subject->telephone?->uri(), $subject->telephone?->national()),
            Part::Email       => Html::link('email', $subject->email?->uri(), $subject->email?->address),
            Part::Hour        => Html::element('span', 'hour', $subject->hour->summary()),
            Part::Map         => Html::link('map', $values->geolocation()?->mapUrl($this->maps), $this->mapText),
        };
    }
}
