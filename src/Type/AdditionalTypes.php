<?php

declare(strict_types=1);

namespace Contenir\Contact\Type;

use function array_filter;
use function array_keys;
use function array_values;
use function is_array;
use function mb_stripos;
use function trim;

/**
 * The additional types Office details offers: kinds of business schema.org
 * has no type for, each matched to its Wikidata entry, so editors pick a
 * name rather than paste a link. Kinds schema.org has a type for, such as a
 * law firm (LegalService), are chosen as the business type instead.
 *
 * @api
 */
final class AdditionalTypes
{
    /**
     * Each Wikidata entry and what the CMS calls it. Checked against
     * Wikidata on 23 October 2026.
     *
     * @var array<string, string>
     */
    public const array CATALOGUE = [
        'Q4387609'   => 'Architecture firm',
        'Q123024699' => 'Interior design firm',
        'Q98446764'  => 'Landscape architecture firm',
        'Q1200676'   => 'Design studio',
        'Q672070'    => 'Photography studio',
        'Q216931'    => 'Advertising agency',
        'Q9592701'   => 'Marketing agency',
        'Q921773'    => 'Engineering firm',
        'Q2089936'   => 'Consultancy',
        'Q811930'    => 'Construction company',
        'Q746369'    => 'Recording studio',
        'Q27043907'  => 'Yoga studio',
        'Q27038997'  => 'Tattoo studio',
    ];

    /**
     * Every entry Office details offers, in its order.
     *
     * @return list<AdditionalType>
     */
    public static function all(): array
    {
        return self::fromList(array_keys(self::CATALOGUE));
    }

    /**
     * The entries a saved list names, each once, leaving out values that
     * aren't Wikidata entries.
     *
     * @mago-expect analysis:mixed-assignment Settings are untyped; each item is checked here.
     *
     * @return list<AdditionalType>
     */
    public static function fromList(mixed $value): array
    {
        $types = [];
        foreach (is_array($value) ? $value : [$value] as $item) {
            $type = AdditionalType::from($item);
            if (null !== $type) {
                $types[$type->id] = $type;
            }
        }

        return array_values($types);
    }

    /**
     * The entries whose name holds $query, for the picker's search.
     *
     * @return list<AdditionalType>
     */
    public static function search(string $query): array
    {
        $query = trim($query);

        return array_values(array_filter(
            self::all(),
            static fn(AdditionalType $type): bool => '' === $query || false !== mb_stripos($type->label, $query),
        ));
    }
}
