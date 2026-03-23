<?php

namespace App\Services\Project;

class ProjectSizeHelper
{
    public static function extractSizeRange(array $configurations): ?string
    {
        $sizes = [];

        foreach ($configurations as $unitGroup) {

            if (!is_array($unitGroup)) continue;

            foreach ($unitGroup as $unit) {

                if (!is_array($unit)) continue;

                if (empty($unit['size'])) continue;

                $sizeText = strtolower($unit['size']);

                if (str_contains($sizeText, '-')) {

                    [$min, $max] = array_map('trim', explode('-', $sizeText));

                    $sizes[] = (int) filter_var($min, FILTER_SANITIZE_NUMBER_INT);
                    $sizes[] = (int) filter_var($max, FILTER_SANITIZE_NUMBER_INT);

                } else {
                    $sizes[] = (int) filter_var($sizeText, FILTER_SANITIZE_NUMBER_INT);
                }
            }
        }

        if (empty($sizes)) {
            return null;
        }

        $minSize = min($sizes);
        $maxSize = max($sizes);

        if ($minSize === $maxSize) {
            return $minSize . ' Sq. ft.';
        }

        return $minSize . ' - ' . $maxSize . ' Sq. ft.';
    }
}
