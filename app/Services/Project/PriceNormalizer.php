<?php

namespace App\Services\Project;

class PriceNormalizer
{
    public static function normalize(string $price): ?int
    {
        $price = strtolower(trim($price));

        $price = str_replace(
            ['₹', 'rs', 'rs.', ',', ' '],
            '',
            $price
        );

        preg_match('/\d+(\.\d+)?/', $price, $matches);

        if (empty($matches)) {
            return null;
        }

        $number = (float) $matches[0];

        if (
            str_contains($price, 'cr') ||
            str_contains($price, 'crore')
        ) {
            return (int) round($number * 10000000);
        }

        if (
            str_contains($price, 'l') ||
            str_contains($price, 'lac') ||
            str_contains($price, 'lakh')
        ) {
            return (int) round($number * 100000);
        }

        return (int) round($number);
    }
}
