<?php

namespace App\Services\Project;

class ProjectPriceHelper
{
    public static function extractPrice(array $configurations): ?string
    {
        foreach ($configurations as $unitType => $data) {

            if (!is_array($data))
                continue;

            if (!empty($data['price'])) {
                return $data['price'];
            }
        }

        return null;
    }

    public static function isInBudget(
        array $configurations,
        int $min,
        int $max
    ): bool {
        foreach ($configurations as $data) {

            if (empty($data['price']))
                continue;

            $priceText = $data['price'];

            if (str_contains($priceText, '-')) {

                [$pMin, $pMax] = array_map('trim', explode('-', $priceText));

                $minVal = PriceNormalizer::normalize($pMin);
                $maxVal = PriceNormalizer::normalize($pMax);

                if ($minVal === null || $maxVal === null) {
                    continue;
                }

                if ($minVal >= $min && $maxVal <= $max) {
                    return true;
                }


            } else {

                $price = PriceNormalizer::normalize($priceText);

                if ($price >= $min && $price <= $max) {
                    return true;
                }
            }
        }

        return false;
    }
}
