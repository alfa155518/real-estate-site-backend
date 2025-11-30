<?php

namespace App\Utils;


class ConvertNumbers
{
    /**
     * Convert Arabic/Indic digits to English digits
     *
     * @param mixed $number
     * @return string
     */
    public static function convertToEnglishDigits($number): string
    {
        if (is_null($number)) {
            return '';
        }

        $arabic = [
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
            '.' => '.',
            ',' => ','
        ];

        return strtr((string) $number, $arabic);
    }

    /**
     * Convert a number to Arabic digits.
     *
     * @param mixed $number
     * @return string
     */
    public static function toArabicDigits($number): string
    {
        if (is_null($number)) {
            return '';
        }

        $arabic = [
            '0' => '٠',
            '1' => '١',
            '2' => '٢',
            '3' => '٣',
            '4' => '٤',
            '5' => '٥',
            '6' => '٦',
            '7' => '٧',
            '8' => '٨',
            '9' => '٩',
            '.' => '.',
            ',' => ','
        ];

        return strtr((string) $number, $arabic);
    }
}