<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Format;

use Contenir\Contact\Format\EmailEscaper;
use Contenir\Contact\Value\Email;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class EmailEscaperTest extends TestCase
{
    private const string MAILTO = '&#109;&#97;&#105;&#108;&#116;&#111;&#58;';

    /**
     * @return array<string, array{string, string}>
     */
    public static function hrefProvider(): array
    {
        return [
            'empty address'     => ['', self::MAILTO],
            'plain address'     => ['a@b.au', self::MAILTO . '%61%40%62%2e%61%75'],
            'multibyte address' => ['é@b', self::MAILTO . '%c3%a9%40%62'],
            'low byte'          => ["\n", self::MAILTO . '%0a'],
        ];
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function textProvider(): array
    {
        return [
            'empty address'     => ['', ''],
            'plain address'     => ['a@b.au', '&#97;&#64;&#98;&#46;&#97;&#117;'],
            'multibyte address' => ['é@b.au', '&#233;&#64;&#98;&#46;&#97;&#117;'],
        ];
    }

    #[Test]
    public function escapesAnEmailValueAsItsAddress(): void
    {
        $email = Email::from('a@b.au');
        static::assertNotNull($email);

        static::assertSame(
            ['&#97;&#64;&#98;&#46;&#97;&#117;', self::MAILTO . '%61%40%62%2e%61%75'],
            [(new EmailEscaper())->text($email), (new EmailEscaper())->href($email)],
        );
    }

    #[Test]
    #[DataProvider('textProvider')]
    public function writesEachCharacterOfTheTextAsADecimalEntity(string $address, string $expected): void
    {
        static::assertSame($expected, (new EmailEscaper())->text($address));
    }

    #[Test]
    #[DataProvider('hrefProvider')]
    public function writesTheHrefAsAnEntityMailtoAndAPercentEncodedAddress(string $address, string $expected): void
    {
        static::assertSame($expected, (new EmailEscaper())->href($address));
    }
}
