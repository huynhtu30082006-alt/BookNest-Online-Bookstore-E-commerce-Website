<?php

namespace App\Support;

class Money
{
    /** Format a whole-VND amount, e.g. 125000 => "125.000 ₫". */
    public static function vnd(int|float|null $amount): string
    {
        return number_format((int) $amount, 0, ',', '.').' ₫';
    }
}
