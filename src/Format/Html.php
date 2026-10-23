<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

use function array_map;
use function htmlspecialchars;
use function implode;
use function sprintf;

use const ENT_HTML5;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Writes the escaped elements HtmlRenderer draws, each with its BEM class.
 *
 * @internal
 */
final class Html
{
    /**
     * An element holding $text, or null when there is none.
     */
    public static function element(string $tag, string $part, ?string $text): ?string
    {
        return null === $text || '' === $text
            ? null
            : sprintf('<%1$s class="contact__%2$s">%3$s</%1$s>', $tag, $part, self::escape($text));
    }

    public static function escape(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, encoding: 'UTF-8');
    }

    /**
     * A span holding $lines, one per line, or null when there are none.
     *
     * @param list<string> $lines
     */
    public static function lines(string $part, array $lines): ?string
    {
        return (
            [] === $lines
                ? null
                : sprintf(
                    '<span class="contact__%s">%s</span>',
                    $part,
                    implode('<br>', array_map(self::escape(...), $lines)),
                )
        );
    }

    /**
     * A link, or null without both an address and text.
     */
    public static function link(string $part, ?string $href, ?string $text): ?string
    {
        return null === $href || null === $text
            ? null
            : sprintf('<a class="contact__%s" href="%s">%s</a>', $part, self::escape($href), self::escape($text));
    }
}
