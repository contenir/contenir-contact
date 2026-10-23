<?php

declare(strict_types=1);

namespace Contenir\Contact\Type;

use Override;
use Stringable;

use function array_key_exists;
use function in_array;
use function is_string;
use function preg_replace;
use function strtolower;
use function trim;
use function ucfirst;

/**
 * What kind of organisation a business or office is, as a schema.org type:
 * "LocalBusiness", "Restaurant", "Dentist". The type decides which values
 * search engines take for it: only a LocalBusiness has opening hours and a
 * position, and only a FoodEstablishment a menu.
 *
 * @api
 */
final readonly class BusinessType implements Stringable
{
    public const string ORGANIZATION = 'Organization';

    public const string LOCAL_BUSINESS = 'LocalBusiness';

    private function __construct(
        public string $name,
    ) {}

    /**
     * Whether $name is an organisation type in the bundled vocabulary,
     * which the CMS checks a type entered under Other against.
     */
    public static function exists(string $name): bool
    {
        return array_key_exists($name, Vocabulary::TYPES) && (new self($name))->isA(self::ORGANIZATION);
    }

    /**
     * The type a saved name gives, when it is an organisation type in the
     * bundled vocabulary, or else $default (Organization when none).
     */
    public static function from(mixed $value, ?self $default = null): self
    {
        $name = is_string($value) ? trim($value) : '';

        return self::exists($name) ? new self($name) : $default ?? new self(self::ORGANIZATION);
    }

    /**
     * Whether the type is $ancestor or one of its subtypes.
     */
    public function isA(string $ancestor): bool
    {
        if ($this->name === $ancestor) {
            return true;
        }

        foreach (Vocabulary::TYPES[$this->name] ?? [] as $parent) {
            if ((new self($parent))->isA($ancestor)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether schema.org has deprecated the type, as it has ProfessionalService.
     */
    public function isDeprecated(): bool
    {
        return in_array($this->name, Vocabulary::DEPRECATED, strict: true);
    }

    /**
     * Whether the type is a local business, which can have opening hours, a
     * position and a price range.
     */
    public function isLocalBusiness(): bool
    {
        return $this->isA(self::LOCAL_BUSINESS);
    }

    /**
     * The type's name as a person reads it: "Food establishment".
     */
    public function label(): string
    {
        return ucfirst(strtolower((string) preg_replace(
            '/(?<=[a-z])(?=[A-Z])/',
            replacement: ' ',
            subject: $this->name,
        )));
    }

    /**
     * Whether schema.org lets the type have $property: "openingHoursSpecification",
     * "servesCuisine". Only the properties a business or office is described
     * with are in the bundled vocabulary; any other is false.
     */
    public function takes(string $property): bool
    {
        foreach (Vocabulary::PROPERTIES[$property] ?? [] as $domain) {
            if ($this->isA($domain)) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function __toString(): string
    {
        return $this->name;
    }
}
