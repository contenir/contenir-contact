<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Internal;

use Contenir\Contact\Internal\Inherited;
use Contenir\Contact\Internal\Reader;
use Contenir\Contact\Type\BusinessType;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

use function date_default_timezone_get;

#[Group('unit')]
final class ReaderTest extends TestCase
{
    #[Test]
    public function fallsBackOnItsNameTimezoneAndType(): void
    {
        $reader = new Reader(
            'AU',
            'Studio Esteta',
            new DateTimeZone('Australia/Melbourne'),
            BusinessType::from('Bakery'),
        );
        $details = $reader->details(new Inherited(['type' => 'Spaceship', 'timezone' => 'nowhere']));

        static::assertSame(
            ['Studio Esteta', 'Bakery', 'Australia/Melbourne', null, null, null, null, []],
            [
                $details['name'],
                $details['type']->name,
                $details['hour']->timezone->getName(),
                $details['legalName'],
                $details['taxId'],
                $details['telephone'],
                $details['email'],
                $details['additionalTypes'],
            ],
        );
    }

    #[Test]
    public function readsEachValueForTheSitesCountry(): void
    {
        $reader  = new Reader('AU', 'Studio Esteta', new DateTimeZone('UTC'), BusinessType::from(null));
        $details = $reader->details(new Inherited([
            'name'            => 'Esteta',
            'legal_name'      => 'Studio Esteta Pty Ltd',
            'tax_id'          => '12345678901',
            'type'            => 'Restaurant',
            'additional_type' => ['Q4387609'],
            'telephone'       => '+61 3 9428 0228',
            'email'           => 'studio@example.test',
            'hour'            => [['day' => 'Mo', 'opens' => '09:00', 'closes' => '17:00']],
            'timezone'        => 'Australia/Perth',
            'cuisine'         => ['Italian'],
        ]));

        static::assertSame(
            [
                'Esteta',
                'Studio Esteta Pty Ltd',
                'ABN 12 345 678 901',
                'Restaurant',
                'Architecture firm',
                '03 9428 0228',
                'studio@example.test',
                'Mon 9:00am–5:00pm',
                'Australia/Perth',
                ['Italian'],
            ],
            [
                $details['name'],
                $details['legalName'],
                (string) $details['taxId'],
                $details['type']->name,
                $details['additionalTypes'][0]->label ?? null,
                (string) $details['telephone'],
                (string) $details['email'],
                $details['hour']->summary(),
                $details['hour']->timezone->getName(),
                $details['detail']->cuisine,
            ],
        );
    }

    #[Test]
    public function readsOnlyTimezonesIanaNames(): void
    {
        static::assertSame(
            ['Australia/Perth', null, null],
            [Reader::zone(' Australia/Perth ')?->getName(), Reader::zone('Mars/Olympus'), Reader::zone(8)],
        );
    }

    #[Test]
    public function theDefaultTimezoneIsPhps(): void
    {
        static::assertSame(date_default_timezone_get(), Reader::defaultZone()->getName());
    }
}
