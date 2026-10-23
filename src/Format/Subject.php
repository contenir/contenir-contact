<?php

declare(strict_types=1);

namespace Contenir\Contact\Format;

/**
 * What a format shows: each office, or the business.
 *
 * @api
 */
enum Subject: string
{
    case Office   = 'office';
    case Business = 'business';
}
