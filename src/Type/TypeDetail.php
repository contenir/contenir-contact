<?php

declare(strict_types=1);

namespace Contenir\Contact\Type;

use Contenir\Contact\Internal\Inherited;
use Contenir\Contact\Internal\Parse;

use function array_filter;

/**
 * The values only some business types have, kept only when the type takes
 * them: a price range for a local business; cuisine, a menu and whether it
 * takes reservations for a food business; a star rating for food and
 * lodging.
 *
 * @api
 */
final readonly class TypeDetail
{
    /**
     * @param list<string> $cuisine
     */
    public function __construct(
        public ?string $priceRange = null,
        public array $cuisine = [],
        public ?string $menu = null,
        public ?bool $reservation = null,
        public ?float $starRating = null,
    ) {}

    /**
     * The values a business or office has, as far as its type takes them.
     *
     * @internal
     */
    public static function from(Inherited $values, BusinessType $type): self
    {
        return new self(
            priceRange: $type->takes('priceRange') ? Parse::text($values->value('price_range')) : null,
            cuisine: $type->takes('servesCuisine') ? Parse::texts($values->value('cuisine')) : [],
            menu: $type->takes('hasMenu') ? Parse::url($values->value('menu')) : null,
            reservation: $type->takes('acceptsReservations') ? Parse::flag($values->value('reservation')) : null,
            starRating: $type->takes('starRating') ? Parse::rating($values->value('star_rating')) : null,
        );
    }

    /**
     * The values as schema.org properties, without those not set.
     *
     * @return array<string, mixed>
     */
    public function schema(): array
    {
        return array_filter(
            [
                'priceRange'          => $this->priceRange,
                'servesCuisine'       => [] === $this->cuisine ? null : $this->cuisine,
                'hasMenu'             => $this->menu,
                'acceptsReservations' => $this->reservation,
                'starRating'          => null === $this->starRating
                    ? null
                    : ['@type' => 'Rating', 'ratingValue' => $this->starRating],
            ],
            static fn(mixed $value): bool => null !== $value,
        );
    }
}
