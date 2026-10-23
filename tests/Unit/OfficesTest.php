<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit;

use Contenir\Contact\Office;
use Contenir\Contact\OfficeChoice;
use Contenir\Contact\Offices;
use Contenir\Contact\Tests\TestAsset\Factory\BusinessFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_map;
use function count;
use function iterator_to_array;

#[Group('unit')]
final class OfficesTest extends TestCase
{
    /**
     * @return array<string, array{mixed, list<string>}>
     */
    public static function choiceProvider(): array
    {
        return [
            'every office'         => [['choose' => 'all'], ['k3f9q2', 'p8d1x7', 's2']],
            'the primary office'   => [['choose' => 'primary'], ['s2']],
            'some, in their order' => [['choose' => 'some', 'id' => ['s2', 'gone', 'k3f9q2', 3]], ['s2', 'k3f9q2']],
            'some, with no ids'    => [['choose' => 'some'], []],
            'an unknown choice'    => [['choose' => 'nearest'], ['k3f9q2', 'p8d1x7', 's2']],
            'no choice'            => [null, ['k3f9q2', 'p8d1x7', 's2']],
        ];
    }

    /**
     * @return list<string>
     */
    private static function ids(Offices $offices): array
    {
        return array_map(static fn(Office $office): string => $office->id, $offices->all());
    }

    private static function offices(): Offices
    {
        return BusinessFactory::make(offices: [
            BusinessFactory::office('k3f9q2', 'Melbourne studio'),
            BusinessFactory::office('p8d1x7', 'Perth', ['region' => 'WA', 'search' => false]),
            BusinessFactory::office('s2', 'Sydney', ['region' => 'NSW', 'primary' => true]),
        ])->offices;
    }

    #[Test]
    public function countsAndListsTheOfficesInOrder(): void
    {
        $offices = self::offices();

        static::assertSame(
            [3, 3, ['k3f9q2', 'p8d1x7', 's2']],
            [count($offices), count(iterator_to_array($offices)), self::ids($offices)],
        );
    }

    #[Test]
    public function findsAnOfficeByItsIdOrSlug(): void
    {
        static::assertSame(
            ['p8d1x7', null, 'k3f9q2', null],
            [
                self::offices()->byId('p8d1x7')?->id,
                self::offices()->byId('gone')?->id,
                self::offices()->bySlug('melbourne-studio')?->id,
                self::offices()->bySlug('perth-cbd')?->id,
            ],
        );
    }

    #[Test]
    public function keepsTheOfficesAskedFor(): void
    {
        static::assertSame(
            [['k3f9q2', 'p8d1x7'], ['k3f9q2', 's2'], ['p8d1x7'], []],
            [
                self::ids(self::offices()->where(static fn(Office $office): bool => ! $office->primary)),
                self::ids(self::offices()->searchable()),
                self::ids(self::offices()->inRegion('Wa')),
                self::ids(self::offices()->inRegion('QLD')),
            ],
        );
    }

    #[Test]
    public function namesTheChosenOfficesSinceRemoved(): void
    {
        static::assertSame(
            [['gone'], [], []],
            [
                OfficeChoice::from(['choose' => 'some', 'id' => ['s2', 'gone']])->missing(self::offices()),
                OfficeChoice::from(['choose' => 'all', 'id' => ['gone']])->missing(self::offices()),
                OfficeChoice::from('some')->missing(self::offices()),
            ],
        );
    }

    #[Test]
    public function noOfficesHaveNoPrimary(): void
    {
        static::assertNull((new Offices())->primary());
    }

    /**
     * @param list<string> $ids
     */
    #[Test]
    #[DataProvider('choiceProvider')]
    public function picksTheOfficesAContactBlockChose(mixed $choice, array $ids): void
    {
        static::assertSame($ids, self::ids(self::offices()->pick($choice)));
    }

    #[Test]
    public function readsTheChoiceItSaved(): void
    {
        $choice = OfficeChoice::from(['choose' => 'some', 'id' => ['a', 'b']]);

        static::assertSame(['some', ['a', 'b'], 'all', []], [
            $choice->choose,
            $choice->ids,
            OfficeChoice::from(['choose' => 'all', 'id' => ['a']])->choose,
            OfficeChoice::from(['choose' => 'primary', 'id' => ['a']])->ids,
        ]);
    }

    #[Test]
    public function thePrimaryOfNoOfficesIsNone(): void
    {
        static::assertSame([], (new Offices())->pick(['choose' => OfficeChoice::PRIMARY])->all());
    }
}
