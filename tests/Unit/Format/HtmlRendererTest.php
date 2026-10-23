<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Format;

use Contenir\Contact\Business;
use Contenir\Contact\Format\Format;
use Contenir\Contact\Format\Formats;
use Contenir\Contact\Format\HtmlRenderer;
use Contenir\Contact\MapProvider;
use Contenir\Contact\Tests\TestAsset\Factory\BusinessFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class HtmlRendererTest extends TestCase
{
    private static function business(): Business
    {
        return BusinessFactory::make(
            [
                'legal_name' => 'Studio <Esteta> Pty Ltd',
                'tax_id'     => '12345678901',
                'telephone'  => '+61 3 9428 0228',
                'email'      => 'studio@example.test',
                'hour'       => [BusinessFactory::hour(['Mo', 'Tu', 'We', 'Th', 'Fr'], '09:00', '17:30')],
            ],
            [
                BusinessFactory::office('a', 'Melbourne & Co', [
                    'trading_name' => 'Esteta North',
                    'geolocation'  => ['latitude' => -37.7787, 'longitude' => 144.9874],
                ]),
            ],
        );
    }

    #[Test]
    public function anOfficeShowsItsTradingName(): void
    {
        $office = self::business()->offices->primary();
        static::assertNotNull($office);

        static::assertStringContainsString(
            '<span class="contact__trading-name">Esteta North</span>',
            (new HtmlRenderer())->render($office, Format::fromConfig('x', ['part' => ['trading_name']])),
        );
    }

    #[Test]
    public function drawsAnOfficeInTheFullFormat(): void
    {
        $office = self::business()->offices->primary();
        static::assertNotNull($office);

        static::assertSame(
            <<<'HTML'
                <address class="contact contact--full">
                <strong class="contact__name">Melbourne &amp; Co</strong>
                <span class="contact__address">402 Queens Parade<br>Fitzroy North VIC 3068</span>
                <a class="contact__telephone" href="tel:+61394280228">03 9428 0228</a>
                <a class="contact__email" href="mailto:studio@example.test">studio@example.test</a>
                <span class="contact__hour">Mon–Fri 9:00am–5:30pm</span>
                <a class="contact__map" href="https://www.openstreetmap.org/?mlat=-37.7787&amp;mlon=144.9874#map=17/-37.7787/144.9874">Map</a>
                </address>
                HTML,
            (new HtmlRenderer())->render($office, Formats::fromConfig(null)->chosen(null)),
        );
    }

    #[Test]
    public function drawsTheBusinessWithItsPrimaryOfficesAddressAndMap(): void
    {
        $format = Format::fromConfig('legal', ['part' => ['trading_name', 'legal_name', 'tax_id', 'address', 'map']]);

        static::assertSame(
            <<<'HTML'
                <address class="contact contact--legal">
                <span class="contact__trading-name">Studio Esteta</span>
                <span class="contact__legal-name">Studio &lt;Esteta&gt; Pty Ltd</span>
                <span class="contact__tax-id">ABN 12 345 678 901</span>
                <span class="contact__address">402 Queens Parade<br>Fitzroy North VIC 3068</span>
                <a class="contact__map" href="https://www.google.com/maps/search/?api=1&amp;query=-37.7787,144.9874">View on a map</a>
                </address>
                HTML,
            (new HtmlRenderer(MapProvider::Google, 'View on a map'))->render(self::business(), $format),
        );
    }

    #[Test]
    public function escapesQuotesAddressesAndBrokenText(): void
    {
        $business = BusinessFactory::make(
            ['legal_name' => "O'Brien \xC3 Pty Ltd"],
            [BusinessFactory::office('a', 'Melbourne', ['street' => '1 <Main> St'])],
        );
        $format = Format::fromConfig('legal', ['part' => ['legal_name', 'address']]);

        static::assertSame(
            "<address class=\"contact contact--legal\">\n"
                . "<span class=\"contact__legal-name\">O&apos;Brien \u{FFFD} Pty Ltd</span>\n"
                . "<span class=\"contact__address\">1 &lt;Main&gt; St<br>Fitzroy North VIC 3068</span>\n"
                . '</address>',
            (new HtmlRenderer())->render($business, $format),
        );
    }

    #[Test]
    public function leavesOutThePartsWithoutAValue(): void
    {
        $business = BusinessFactory::make();
        $format   = Format::fromConfig('all', [
            'part' => ['legal_name', 'tax_id', 'address', 'telephone', 'email', 'hour', 'map'],
        ]);

        static::assertSame(
            [
                '',
                "<address class=\"contact contact--all\">\n<strong class=\"contact__name\">Studio Esteta</strong>\n</address>",
            ],
            [
                (new HtmlRenderer())->render($business, $format),
                (new HtmlRenderer())->render($business, Format::fromConfig('all', ['part' => ['name', 'hour', 'map']])),
            ],
        );
    }
}
