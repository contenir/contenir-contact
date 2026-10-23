<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Type;

use Contenir\Contact\Type\AdditionalType;
use Contenir\Contact\Type\AdditionalTypes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_combine;
use function array_map;
use function count;

#[Group('unit')]
final class AdditionalTypeTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function entryProvider(): array
    {
        return [
            'its id'             => ['Q4387609'],
            'its address'        => ['https://www.wikidata.org/wiki/Q4387609'],
            'its entity address' => ['http://wikidata.org/entity/Q4387609'],
            'its mobile address' => [' https://m.wikidata.org/wiki/Q4387609 '],
        ];
    }

    /**
     * @return array<string, array{mixed}>
     */
    public static function invalidProvider(): array
    {
        return [
            'another site'   => ['https://example.test/wiki/Q4387609'],
            'a property'     => ['P31'],
            'a leading zero' => ['Q0123'],
            'text after'     => ['Q4387609 architecture'],
            'not text'       => [4_387_609],
        ];
    }

    #[Test]
    public function anEntryNotOfferedHasNoLabel(): void
    {
        static::assertSame('', AdditionalType::from('Q42')?->label);
    }

    #[Test]
    #[DataProvider('invalidProvider')]
    public function leavesOutAnythingButAWikidataEntry(mixed $value): void
    {
        static::assertNull(AdditionalType::from($value));
    }

    #[Test]
    public function offersEachEntryInItsList(): void
    {
        static::assertSame(
            AdditionalTypes::CATALOGUE,
            array_combine(
                array_map(static fn(AdditionalType $type): string => $type->id, AdditionalTypes::all()),
                array_map(static fn(AdditionalType $type): string => $type->label, AdditionalTypes::all()),
            ),
        );
    }

    #[Test]
    public function readsASavedListEachEntryOnce(): void
    {
        static::assertSame(
            [['Q4387609', 'Q42'], ['Q42'], []],
            [
                array_map(
                    static fn(AdditionalType $type): string => $type->id,
                    AdditionalTypes::fromList([
                        'Q4387609',
                        'nonsense',
                        'https://www.wikidata.org/wiki/Q4387609',
                        'Q42',
                    ]),
                ),
                array_map(static fn(AdditionalType $type): string => $type->id, AdditionalTypes::fromList('Q42')),
                AdditionalTypes::fromList(null),
            ],
        );
    }

    #[Test]
    #[DataProvider('entryProvider')]
    public function readsAWikidataEntry(string $value): void
    {
        $type = AdditionalType::from($value);

        static::assertSame(
            [
                'Q4387609',
                'Architecture firm',
                'https://www.wikidata.org/wiki/Q4387609',
                'https://www.wikidata.org/wiki/Q4387609',
            ],
            [$type?->id, $type?->label, $type?->url(), (string) $type],
        );
    }

    #[Test]
    public function searchesTheListByName(): void
    {
        static::assertSame(
            [['Architecture firm', 'Landscape architecture firm'], [], 13],
            [
                array_map(
                    static fn(AdditionalType $type): string => $type->label,
                    AdditionalTypes::search(' ARCHITECT '),
                ),
                AdditionalTypes::search('bakery'),
                count(AdditionalTypes::search('')),
            ],
        );
    }
}
