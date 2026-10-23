<?php

declare(strict_types=1);

namespace Contenir\Contact\Type;

use Override;
use Stringable;

use function is_string;
use function preg_match;
use function trim;

/**
 * An outside definition of what a business is, for the kinds schema.org has
 * no type for, such as an architecture firm: a Wikidata entry, written as
 * its address for schema.org's additionalType.
 *
 * @api
 */
final readonly class AdditionalType implements Stringable
{
    /**
     * @param string $id    The Wikidata entry, such as "Q4387609"
     * @param string $label What the CMS calls it, when it is in the list Office details offers
     */
    private function __construct(
        public string $id,
        public string $label,
    ) {}

    /**
     * The entry a saved value names, as its id ("Q4387609") or its address
     * ("https://www.wikidata.org/wiki/Q4387609"), or null. The value is
     * checked for its shape only; whether the entry exists is for the CMS
     * to check when it is entered.
     */
    public static function from(mixed $value): ?self
    {
        $text    = is_string($value) ? trim($value) : '';
        $matches = [];
        if (
            1 !== preg_match(
                '~^(?:https?://(?:www\.|m\.)?wikidata\.org/(?:wiki|entity)/)?(Q[1-9]\d*)$~',
                $text,
                $matches,
            )
        ) {
            return null;
        }

        $id = $matches[1] ?? '';

        return new self($id, AdditionalTypes::CATALOGUE[$id] ?? '');
    }

    /**
     * The entry's address, as schema.org's additionalType takes it.
     */
    public function url(): string
    {
        return "https://www.wikidata.org/wiki/{$this->id}";
    }

    #[Override]
    public function __toString(): string
    {
        return $this->url();
    }
}
