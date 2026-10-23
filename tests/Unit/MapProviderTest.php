<?php

declare(strict_types=1);

namespace Contenir\Contact\Tests\Unit;

use Contenir\Contact\MapProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('unit')]
final class MapProviderTest extends TestCase
{
    #[Test]
    public function readsTheProviderSiteSettingsChoose(): void
    {
        static::assertSame(
            [MapProvider::Google, MapProvider::OpenStreetMap, MapProvider::OpenStreetMap],
            [
                MapProvider::fromSettings(['map' => ['provider' => 'google']]),
                MapProvider::fromSettings(['map' => ['provider' => 'bing']]),
                MapProvider::fromSettings([]),
            ],
        );
    }
}
