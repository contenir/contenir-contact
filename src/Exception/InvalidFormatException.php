<?php

declare(strict_types=1);

namespace Contenir\Contact\Exception;

use InvalidArgumentException;

use function get_debug_type;
use function is_string;
use function sprintf;

/**
 * A format in contenir_contact.format the site's config gets wrong.
 *
 * @api
 */
final class InvalidFormatException extends InvalidArgumentException
{
    public static function notAnArray(string $name, mixed $value): self
    {
        return new self(sprintf('The contact format "%s" must be an array, %s given.', $name, get_debug_type($value)));
    }

    public static function unknownPart(string $name, mixed $part): self
    {
        return new self(sprintf(
            'The contact format "%s" names a part that doesn\'t exist: %s. Parts are name, trading_name, legal_name,'
                . ' tax_id, address, telephone, email, hour and map.',
            $name,
            is_string($part) ? "\"{$part}\"" : get_debug_type($part),
        ));
    }

    public static function unknownSubject(string $name, mixed $subject): self
    {
        return new self(sprintf(
            'The contact format "%s" has a subject that doesn\'t exist: %s. Subjects are office and business.',
            $name,
            is_string($subject) ? "\"{$subject}\"" : get_debug_type($subject),
        ));
    }
}
