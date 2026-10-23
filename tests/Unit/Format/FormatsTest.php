<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Format;

use Contenir\Contact\Format\Format;
use Contenir\Contact\Format\Formats;
use Contenir\Contact\Format\Part;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;

#[Group('unit')]
final class FormatsTest extends TestCase
{
    #[Test]
    public function aCustomChoiceShowsThePartsTheBlockTicked(): void
    {
        $format = Formats::fromConfig(null)->chosen(['format' => 'custom', 'part' => ['address', 'map']]);

        static::assertSame(['custom', 'Custom', [Part::Address, Part::Map]], [
            $format->name,
            $format->label,
            $format->parts,
        ]);
    }

    #[Test]
    public function aCustomChoiceWithoutPartsShowsNothing(): void
    {
        static::assertSame([], Formats::fromConfig(null)->chosen(['format' => 'custom'])->parts);
    }

    #[Test]
    public function aSiteCanReplaceTheFullFormatTheBlockFallsBackOn(): void
    {
        static::assertSame(
            [Part::Name],
            Formats::fromConfig(['full' => ['part' => ['name']]])->chosen(['format' => 'removed'])->parts,
        );
    }

    #[Test]
    public function aSitesFormatsAreAddedAndReplaceThePackagesByName(): void
    {
        $formats = Formats::fromConfig([
            'footer'  => ['part' => ['name', 'address']],
            'compact' => ['part' => ['telephone']],
        ]);

        static::assertSame(
            [['full', 'address', 'compact', 'footer'], [Part::Telephone], [Part::Name, Part::Address], null],
            [
                array_map(static fn(Format $format): string => $format->name, $formats->all()),
                $formats->get('compact')?->parts,
                $formats->get('footer')?->parts,
                $formats->get('header'),
            ],
        );
    }

    #[Test]
    public function findsTheFormatAContactBlockChose(): void
    {
        $formats = Formats::fromConfig(['footer' => ['part' => ['address']]]);

        static::assertSame(
            ['footer', 'full', 'full', 'full'],
            [
                $formats->chosen(['format' => 'footer'])->name,
                $formats->chosen(['format' => 'removed'])->name,
                $formats->chosen(['format' => 3])->name,
                $formats->chosen(null)->name,
            ],
        );
    }

    #[Test]
    public function thePackagesFormatsComeFirst(): void
    {
        static::assertSame(
            [['full', 'address', 'compact'], 'Full contact details'],
            [
                array_map(static fn(Format $format): string => $format->name, Formats::fromConfig(null)->all()),
                Formats::fromConfig([])->get('full')?->label,
            ],
        );
    }
}
