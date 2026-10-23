<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

/**
 * A value a format shows, by the name the site's config gives it.
 *
 * @api
 */
enum Part: string
{
    /**
     * An office's name, or the business's trading name.
     */
    case Name        = 'name';
    case TradingName = 'trading_name';
    case LegalName   = 'legal_name';
    case TaxId       = 'tax_id';
    case Address     = 'address';
    case Telephone   = 'telephone';
    case Email       = 'email';
    case Hour        = 'hour';
    case Map         = 'map';
}
