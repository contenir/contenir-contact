<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Format;

use Contenir\Contact\Exception\InvalidFormatException;
use Contenir\Contact\Format\Format;
use Contenir\Contact\Format\Part;
use Contenir\Contact\Format\Subject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class FormatTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     */
    public static function invalidProvider(): array
    {
        return [
            'not an array'            => [
                'name, address',
                'The contact format "footer" must be an array, string given.',
            ],
            'an unknown part'         => [
                ['part' => ['name', 'fax']],
                'The contact format "footer" names a part that doesn\'t exist: "fax". Parts are name, trading_name,'
                    . ' legal_name, tax_id, address, telephone, email, hour and map.',
            ],
            'a part that isn\'t text' => [['part' => [3]], 'names a part that doesn\'t exist: int.'],
            'an unknown subject'      => [
                ['part' => ['name'], 'subject' => 'team'],
                'The contact format "footer" has a subject that doesn\'t exist: "team". Subjects are office and business.',
            ],
            'a subject not text'      => [
                ['part' => ['name'], 'subject' => 1],
                'has a subject that doesn\'t exist: int.',
            ],
        ];
    }

    #[Test]
    public function aFormatNeedsOnlyItsParts(): void
    {
        $format = Format::fromConfig('office_footer', ['part' => 'address']);

        static::assertSame(
            [[Part::Address], Subject::Office, null, 'Office footer'],
            [$format->parts, $format->subject, $format->template, $format->label],
        );
    }

    #[Test]
    public function aFormatWithoutPartsShowsNothing(): void
    {
        static::assertSame([], Format::fromConfig('empty', ['template' => ''])->parts);
    }

    #[Test]
    public function knowsWhichPartsItShows(): void
    {
        $format = Format::fromConfig('footer', ['part' => ['name', 'address']]);

        static::assertSame([true, false], [$format->shows(Part::Address), $format->shows(Part::Map)]);
    }

    #[Test]
    public function readsAFormatFromTheSitesConfig(): void
    {
        $format = Format::fromConfig('legal', [
            'part'     => ['legal_name', 'tax_id'],
            'subject'  => 'business',
            'template' => ' partials/legal ',
            'label'    => ' The fine print ',
        ]);

        static::assertSame(
            ['legal', [Part::LegalName, Part::TaxId], Subject::Business, 'partials/legal', 'The fine print'],
            [$format->name, $format->parts, $format->subject, $format->template, $format->label],
        );
    }

    #[Test]
    #[DataProvider('invalidProvider')]
    public function saysWhatIsWrongWithAFormat(mixed $config, string $message): void
    {
        $this->expectException(InvalidFormatException::class);
        $this->expectExceptionMessage($message);

        Format::fromConfig('footer', $config);
    }
}
