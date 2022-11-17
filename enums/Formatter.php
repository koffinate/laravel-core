<?php

namespace Koffin\Enums;

use Exception;

enum Formatter: string
{
    case DATE_SHORT = 'L';
    case DATE_MEDIUM = 'll';
    case DATE_LONG = 'LL';
    case DATE_FULL = 'LLL';
    case DATE_FULL_SHORT = 'lll';
    case TIME = 'LT';
    case DATETIME = 'LL LT';
    case DATETIME_MEDIUM = 'll LT';
    case DATETIME_SHORT = 'L LT';
}
