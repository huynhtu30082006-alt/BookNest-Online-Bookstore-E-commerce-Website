<?php

namespace App\Support;

class Phone
{
    /** Stored / validated format: "+84" followed by exactly 9 digits (e.g. +84901234567). */
    public const PATTERN = '/^\+84[0-9]{9}$/';

    public static function normalize(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $phone = preg_replace('/[\s.\-()]/', '', $input);

        if (str_starts_with($phone, '0')) {
            return '+84'.substr($phone, 1);
        }
        if (str_starts_with($phone, '84') && strlen($phone) === 11) {
            return '+'.$phone;
        }

        return $phone;
    }
}