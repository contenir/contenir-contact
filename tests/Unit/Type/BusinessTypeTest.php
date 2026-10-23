<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Type;

use Contenir\Contact\Type\BusinessType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class BusinessTypeTest extends TestCase
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function propertyProvider(): array
    {
        return [
            'a bakery has cuisine'             => ['Bakery', 'servesCuisine', true],
            'a bakery has opening hours'       => ['Bakery', 'openingHoursSpecification', true],
            'a hotel has a star rating'        => ['Hotel', 'starRating', true],
            'a dentist has a price range'      => ['Dentist', 'priceRange', true],
            'a dentist has no menu'            => ['Dentist', 'hasMenu', false],
            'an organisation has a legal name' => ['Organization', 'legalName', true],
            'an organisation has no geo'       => ['Organization', 'geo', false],
            'a property not in the vocabulary' => ['Bakery', 'foundingDate', false],
        ];
    }

    #[Test]
    public function anUnknownTypeIsTheDefaultOrOrganization(): void
    {
        $restaurant = BusinessType::from('Restaurant');

        static::assertSame(
            ['Organization', 'Restaurant', 'Organization', 'Organization'],
            [
                BusinessType::from('Spaceship')->name,
                BusinessType::from('Spaceship', $restaurant)->name,
                BusinessType::from(null)->name,
                BusinessType::from('Place')->name,
            ],
        );
    }

    #[Test]
    public function knowsWhatItIs(): void
    {
        $bakery = BusinessType::from('Bakery');

        static::assertSame(
            [true, true, true, true, false],
            [
                $bakery->isA('Bakery'),
                $bakery->isA('FoodEstablishment'),
                $bakery->isA('Place'),
                $bakery->isLocalBusiness(),
                BusinessType::from('Corporation')->isLocalBusiness(),
            ],
        );
    }

    #[Test]
    #[DataProvider('propertyProvider')]
    public function knowsWhichPropertiesItTakes(string $type, string $property, bool $takes): void
    {
        static::assertSame($takes, BusinessType::from($type)->takes($property));
    }

    #[Test]
    public function knowsWhichTypesSchemaOrgDeprecated(): void
    {
        static::assertSame(
            [true, false],
            [
                BusinessType::from('ProfessionalService')->isDeprecated(),
                BusinessType::from('LegalService')->isDeprecated(),
            ],
        );
    }

    #[Test]
    public function onlyOrganisationTypesExist(): void
    {
        static::assertSame(
            [true, true, false, false],
            [
                BusinessType::exists('Organization'),
                BusinessType::exists('Winery'),
                BusinessType::exists('Place'),
                BusinessType::exists('Spaceship'),
            ],
        );
    }

    #[Test]
    public function readsAnOrganisationTypeFromTheVocabulary(): void
    {
        static::assertSame(['Bakery', 'Bakery'], [
            BusinessType::from(' Bakery ')->name,
            (string) BusinessType::from('Bakery'),
        ]);
    }

    #[Test]
    public function writesItsNameAsAPersonReadsIt(): void
    {
        static::assertSame(
            ['Food establishment', 'Organization', 'Ngo'],
            [
                BusinessType::from('FoodEstablishment')->label(),
                BusinessType::from('Organization')->label(),
                BusinessType::from('NGO')->label(),
            ],
        );
    }
}
