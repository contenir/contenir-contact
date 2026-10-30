<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use Contenir\Contact\Value\Email;

use function implode;
use function mb_ord;
use function mb_str_split;
use function ord;
use function sprintf;
use function str_split;

/**
 * Writes an email address as HTML entities, and its mailto: link as entities and a
 * percent-encoded address, so the markup carries no plain "name@host" for a scraper
 * to lift. It deters harvesting rather than hiding the address: anything that decodes
 * HTML still reads it.
 *
 * Framework-free, so page and email templates alike can use it:
 *
 *     <?php $escape = new EmailEscaper(); ?>
 *     <a href="<?= $escape->href($address) ?>"><?= $escape->text($address) ?></a>
 *
 * @api
 */
final readonly class EmailEscaper
{
    /**
     * A mailto: link for an href: "mailto:" as entities, then every byte of the address percent-encoded.
     */
    public function href(string|Email $address): string
    {
        $encoded = '';
        foreach (str_split((string) $address) as $byte) {
            $encoded .= sprintf('%%%02x', ord($byte));
        }

        return $this->text('mailto:') . $encoded;
    }

    /**
     * The address as decimal entities, one per character, for text in HTML.
     */
    public function text(string|Email $address): string
    {
        $entities = [];
        foreach (mb_str_split((string) $address, encoding: 'UTF-8') as $character) {
            $entities[] = '&#' . mb_ord($character, encoding: 'UTF-8') . ';';
        }

        return implode('', $entities);
    }
}
