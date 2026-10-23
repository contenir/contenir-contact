<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Type;

use Contenir\Contact\Type\BusinessType;
use Contenir\Contact\Type\BusinessTypes;
use Contenir\Contact\Type\Vocabulary;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function array_keys;
use function array_map;

#[Group('unit')]
final class BusinessTypesTest extends TestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function offeredProvider(): array
    {
        $types = [];
        foreach (BusinessTypes::GROUP as $names) {
            foreach ($names as $name) {
                $types[$name] = [$name];
            }
        }

        return $types;
    }

    #[Test]
    public function groupsTheTypesItOffers(): void
    {
        $groups = BusinessTypes::groups();

        static::assertSame(
            [
                array_keys(BusinessTypes::GROUP),
                ['Organization', 'Corporation', 'NGO', 'GovernmentOrganization', 'LocalBusiness'],
            ],
            [
                array_keys($groups),
                array_map(static fn(BusinessType $type): string => $type->name, $groups['Organisations'] ?? []),
            ],
        );
    }

    #[Test]
    #[DataProvider('offeredProvider')]
    public function offersOnlyCurrentOrganisationTypes(string $name): void
    {
        static::assertSame(
            [true, false],
            [BusinessType::exists($name), BusinessType::from($name)->isDeprecated()],
        );
    }

    #[Test]
    public function theVocabularyIsAReleaseOfSchemaOrg(): void
    {
        static::assertSame(['30.1', ['Organization', 'Place']], [
            Vocabulary::VERSION,
            Vocabulary::TYPES['LocalBusiness'] ?? [],
        ]);
    }
}
