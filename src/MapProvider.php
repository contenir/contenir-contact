<?php

declare(strict_types=1);

namespace Contenir\Contact;

use Contenir\Contact\Internal\SettingsArray;

/**
 * The map provider Site Settings > Maps chooses, which draws the CMS's maps
 * and the links a site gives to an office's map and directions.
 *
 * @api
 */
enum MapProvider: string
{
    case OpenStreetMap = 'osm';
    case Google        = 'google';

    public const string SETTING = 'map.provider';

    /**
     * The provider a site's settings choose, or OpenStreetMap, which needs
     * no key.
     *
     * @param array<array-key, mixed> $settings
     */
    public static function fromSettings(array $settings): self
    {
        return self::tryFrom((new SettingsArray($settings))->text(self::SETTING)) ?? self::OpenStreetMap;
    }
}
