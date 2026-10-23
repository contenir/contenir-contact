<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Type;

use Contenir\Contact\Internal\Inherited;
use Contenir\Contact\Type\BusinessType;
use Contenir\Contact\Type\TypeDetail;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class TypeDetailTest extends TestCase
{
    private const array VALUES = [
        'price_range' => '$$',
        'cuisine'     => ['Italian', 'Cafe'],
        'menu'        => 'https://example.test/menu',
        'reservation' => '1',
        'star_rating' => '4',
    ];

    #[Test]
    public function dropsTheValuesATypeDoesNotTake(): void
    {
        $dentist      = TypeDetail::from(new Inherited(self::VALUES), BusinessType::from('Dentist'));
        $organisation = TypeDetail::from(new Inherited(self::VALUES), BusinessType::from('Organization'));

        static::assertSame([['priceRange' => '$$'], []], [$dentist->schema(), $organisation->schema()]);
    }

    #[Test]
    public function keepsAReservationsNo(): void
    {
        $detail = TypeDetail::from(new Inherited(['reservation' => false]), BusinessType::from('Restaurant'));

        static::assertSame(['acceptsReservations' => false], $detail->schema());
    }

    #[Test]
    public function keepsTheValuesAFoodBusinessTakes(): void
    {
        $detail = TypeDetail::from(new Inherited(self::VALUES), BusinessType::from('Restaurant'));

        static::assertSame(
            [
                'priceRange'          => '$$',
                'servesCuisine'       => ['Italian', 'Cafe'],
                'hasMenu'             => 'https://example.test/menu',
                'acceptsReservations' => true,
                'starRating'          => ['@type' => 'Rating', 'ratingValue' => 4.0],
            ],
            $detail->schema(),
        );
    }

    #[Test]
    public function writesNothingItLacks(): void
    {
        static::assertSame([], (new TypeDetail())->schema());
    }
}
