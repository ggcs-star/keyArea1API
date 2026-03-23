<?php

if (!function_exists('priceToNumber')) {

    function priceToNumber($price)
    {
        if (!$price) {
            return 0;
        }

        $price = strtolower($price);
        $price = str_replace(',', '', $price);

        if (str_contains($price, 'lac')) {
            $value = (float) filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            return $value * 100000;
        }

        if (str_contains($price, 'cr')) {
            $value = (float) filter_var($price, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            return $value * 10000000;
        }

        return (float) $price;
    }
}