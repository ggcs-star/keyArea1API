<?php
namespace App\Services;

class PriceParser
{
    
    public static function parseToRupees(string $priceStr): ?float
    {
        try {
            if (!$priceStr || trim($priceStr) === '') {
                return null;
            }

            $s = strtolower(trim($priceStr));
            $s = str_replace(['₹', ','], '', $s);

           
            if (strpos($s, '-') !== false && preg_match('/(.+)-(.+)/', $s, $m)) {
                $a = self::parseToRupees(trim($m[1]));
                $b = self::parseToRupees(trim($m[2]));
                if ($a && $b) return ($a + $b) / 2;
            }

          
            $s = preg_replace('/(\/.*$)/', '', $s);

            
            if (preg_match('/([\d.]+)\s*(l|lac|lakh|lakhs)\b/', $s, $m)) {
                return floatval($m[1]) * 100000;
            }

            if (preg_match('/([\d.]+)\s*(cr|crore|crores)\b/', $s, $m)) {
                return floatval($m[1]) * 10000000;
            }

            if (preg_match('/([\d.]+)\s*k\b/', $s, $m)) {
                return floatval($m[1]) * 1000;
            }

          
            if (preg_match('/^[\d.]+$/', $s)) {
                return floatval($s);
            }

           
            if (preg_match('/([\d.]+)/', $s, $m)) {
                return floatval($m[1]);
            }

            return null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function formatIndian(float $value): string
    {
        try {
            if ($value >= 10000000) {
                return round($value / 10000000, 2) . ' Cr';
            }
            if ($value >= 100000) {
                return round($value / 100000, 2) . ' Lac';
            }
            if ($value >= 1000) {
                return round($value / 1000, 2) . ' K';
            }
            return (int)$value . ' ₹';
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }
}
