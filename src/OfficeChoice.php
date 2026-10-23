<?php

declare(strict_types=1);

namespace Contenir\Contact;

use function array_filter;
use function array_map;
use function array_values;
use function in_array;
use function is_array;
use function is_string;

/**
 * Which offices a Contact block shows, as the block saves its choice:
 * ['choose' => 'all'], ['choose' => 'primary'], or ['choose' => 'some',
 * 'id' => ['k3f9q2', …]], the last in the editor's order. Anything else
 * chooses every office.
 *
 * @api
 */
final readonly class OfficeChoice
{
    public const string ALL = 'all';

    public const string PRIMARY = 'primary';

    public const string SOME = 'some';

    /**
     * @param list<string> $ids The offices chosen, when $choose is SOME
     */
    private function __construct(
        public string $choose,
        public array $ids,
    ) {}

    /**
     * @mago-expect analysis:mixed-assignment A saved choice is untyped; it is checked here.
     */
    public static function from(mixed $choice): self
    {
        $choose = is_array($choice) ? $choice['choose'] ?? null : null;
        $ids    = is_array($choice) && is_array($choice['id'] ?? null) ? $choice['id'] : [];

        return new self(
            in_array($choose, [self::PRIMARY, self::SOME], strict: true) ? $choose : self::ALL,
            self::SOME === $choose ? array_values(array_filter($ids, is_string(...))) : [],
        );
    }

    /**
     * The chosen ids no office has any more, for the CMS to mark.
     *
     * @return list<string>
     */
    public function missing(Offices $offices): array
    {
        return array_values(array_filter($this->ids, static fn(string $id): bool => null === $offices->byId($id)));
    }

    /**
     * The chosen offices; any chosen since removed are skipped.
     */
    public function pick(Offices $offices): Offices
    {
        return match ($this->choose) {
            self::PRIMARY => new Offices(array_values(array_filter([$offices->primary()]))),
            self::SOME => new Offices(array_values(array_filter(array_map($offices->byId(...), $this->ids)))),
            default => $offices,
        };
    }
}
