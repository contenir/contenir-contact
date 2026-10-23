<?php

declare(strict_types=1);

namespace Contenir\Contact;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Override;
use Traversable;

use function array_filter;
use function array_values;
use function count;
use function strtolower;

/**
 * The business's offices, in the order Office details lists them, with
 * exactly one primary office when there are any.
 *
 * @implements IteratorAggregate<int, Office>
 *
 * @api
 */
final readonly class Offices implements Countable, IteratorAggregate
{
    /**
     * @param list<Office> $offices
     */
    public function __construct(
        private array $offices = [],
    ) {}

    /**
     * @return list<Office>
     */
    public function all(): array
    {
        return $this->offices;
    }

    public function byId(string $id): ?Office
    {
        foreach ($this->offices as $office) {
            if ($office->id === $id) {
                return $office;
            }
        }

        return null;
    }

    public function bySlug(string $slug): ?Office
    {
        foreach ($this->offices as $office) {
            if ($office->slug() === $slug) {
                return $office;
            }
        }

        return null;
    }

    #[Override]
    public function count(): int
    {
        return count($this->offices);
    }

    #[Override]
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->offices);
    }

    /**
     * The offices in a state or region, as their addresses write it: "VIC".
     */
    public function inRegion(string $region): self
    {
        return $this->where(
            static fn(Office $office): bool => strtolower($office->address->region ?? '') === strtolower($region),
        );
    }

    /**
     * The offices a Contact block chose, from the choice it saved; see
     * OfficeChoice.
     */
    public function pick(mixed $choice): self
    {
        return OfficeChoice::from($choice)->pick($this);
    }

    /**
     * The primary office, or null when there are no offices.
     */
    public function primary(): ?Office
    {
        foreach ($this->offices as $office) {
            if ($office->primary) {
                return $office;
            }
        }

        return null;
    }

    /**
     * The offices search engines are told of.
     */
    public function searchable(): self
    {
        return $this->where(static fn(Office $office): bool => $office->search);
    }

    /**
     * The offices $keep keeps, such as every office but the primary:
     * where(fn(Office $office): bool => ! $office->primary).
     *
     * @param callable(Office): bool $keep
     */
    public function where(callable $keep): self
    {
        return new self(array_values(array_filter($this->offices, $keep)));
    }
}
