<?php

declare(strict_types=1);

namespace Contenir\Contact\Type;

use function array_map;

/**
 * The business types Office details offers, grouped by the kinds of
 * business Contenir's clients are. Any other organisation type in the
 * bundled vocabulary can still be entered under Other.
 *
 * @api
 */
final class BusinessTypes
{
    /**
     * @var array<string, list<string>>
     */
    public const array GROUP = [
        'Organisations'      => ['Organization', 'Corporation', 'NGO', 'GovernmentOrganization', 'LocalBusiness'],
        'Food and drink'     => [
            'Restaurant',
            'CafeOrCoffeeShop',
            'Bakery',
            'BarOrPub',
            'Brewery',
            'Winery',
            'FastFoodRestaurant',
            'IceCreamShop',
        ],
        'Health and beauty'  => [
            'Dentist',
            'Physician',
            'MedicalClinic',
            'Optician',
            'Pharmacy',
            'VeterinaryCare',
            'DaySpa',
            'HairSalon',
            'BeautySalon',
        ],
        'Legal and finance'  => [
            'LegalService',
            'Notary',
            'AccountingService',
            'FinancialService',
            'InsuranceAgency',
            'RealEstateAgent',
        ],
        'Retail'             => [
            'Store',
            'ClothingStore',
            'BookStore',
            'FurnitureStore',
            'HomeGoodsStore',
            'JewelryStore',
            'Florist',
            'HardwareStore',
        ],
        'Accommodation'      => ['Hotel', 'Motel', 'BedAndBreakfast', 'Hostel', 'Resort', 'Campground'],
        'Trades'             => [
            'HomeAndConstructionBusiness',
            'GeneralContractor',
            'Electrician',
            'Plumber',
            'HousePainter',
            'RoofingContractor',
            'Locksmith',
        ],
        'Education and care' => ['School', 'Preschool', 'CollegeOrUniversity', 'ChildCare'],
        'Arts and leisure'   => [
            'ArtGallery',
            'MovieTheater',
            'NightClub',
            'EntertainmentBusiness',
            'SportsActivityLocation',
            'ExerciseGym',
        ],
    ];

    /**
     * The groups, each with its types.
     *
     * @return array<string, list<BusinessType>>
     */
    public static function groups(): array
    {
        $groups = [];
        foreach (self::GROUP as $group => $names) {
            $groups[$group] = array_map(BusinessType::from(...), $names);
        }

        return $groups;
    }
}
