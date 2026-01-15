<?php

namespace App\Util;

class StringNormalizer
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower($value, 'UTF-8');

        $value = iconv('UTF-8', 'ASCII//TRANSLIT', $value);

        $value = preg_replace('/[^a-z]/', '', $value);

        return $value;
    }
}
