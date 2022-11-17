<?php

namespace Koffin\Enums;

use Exception;

enum Spacer: int
{
    case NORMAL = 4;
    case TIGH = 2;
    case WIDE = 8;

    public function space(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '&nbsp;&nbsp;&nbsp;&nbsp;',
                self::TIGH => '&nbsp;&nbsp;',
                self::WIDE => '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
            };
        } catch (Exception $e) {
            return '';
        }
    }

    public function dot(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '.. ..',
                self::TIGH => '..',
                self::WIDE => '.. .. .. ..',
            };
        } catch (Exception $e) {
            return '';
        }
    }

    public function arrow(): string
    {
        try {
            return match ($this) {
                self::NORMAL => '--->',
                self::TIGH => '->',
                self::WIDE => '------->',
            };
        } catch (Exception $e) {
            return '';
        }
    }
}
