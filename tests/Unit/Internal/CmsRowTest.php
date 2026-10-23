<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit\Internal;

use Contenir\Contact\Internal\CmsRow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class CmsRowTest extends TestCase
{
    #[Test]
    public function keepsARowWrittenByHandAsItIs(): void
    {
        static::assertSame(
            [['name' => 'Perth', 'email' => null], [], ['fields' => 'x']],
            [
                CmsRow::flat(['name' => 'Perth', 'email' => null]),
                CmsRow::flat('Perth'),
                CmsRow::flat(['fields' => 'x']),
            ],
        );
    }

    #[Test]
    public function turnsTheCmsFlatPositionIntoGeolocation(): void
    {
        static::assertSame(
            [
                ['name' => 'Melbourne', 'geolocation' => ['latitude' => '-37.7787', 'longitude' => '144.9874']],
                ['name' => 'Perth', 'geolocation' => ['latitude' => '-31.95', 'longitude' => null]],
                ['name' => 'Darwin'],
            ],
            [
                CmsRow::flat(['fields' => [
                    'name'      => 'Melbourne',
                    'latitude'  => '-37.7787',
                    'longitude' => '144.9874',
                ]]),
                CmsRow::flat(['fields' => ['name' => 'Perth', 'latitude' => '-31.95', 'longitude' => '']]),
                CmsRow::flat(['fields' => ['name' => 'Darwin', 'latitude' => '', 'longitude' => '']]),
            ],
        );
    }

    #[Test]
    public function unwrapsARowTheCmsSavedAndDropsWhatWasLeftEmpty(): void
    {
        static::assertSame(
            ['id' => 'k3f9q2', 'name' => 'Melbourne', 'primary' => '1', 'search' => '0'],
            CmsRow::flat([
                'fields' => [
                    'id'        => 'k3f9q2',
                    'name'      => 'Melbourne',
                    'primary'   => '1',
                    'search'    => '0',
                    'telephone' => ' ',
                    'email'     => '',
                    'type'      => null,
                    'cuisine'   => [],
                ],
            ]),
        );
    }
}
