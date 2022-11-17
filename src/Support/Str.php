<?php

namespace Koffin\Core\Support;

use Illuminate\Support\Str as BaseStr;

class Str extends BaseStr
{
    public static function forceSnake($string = '')
    {
        return preg_replace('/\W+/', '_', $string);
    }
}
