<?php

namespace App\Services\Project;

class ProjectBhkHelper
{
   
public static function hasBHK(array $configurations, string $bhk): bool
{
    $bhk = strtolower($bhk);

    foreach ($configurations as $unitGroup) {

        if (!is_array($unitGroup)) {
            continue;
        }

        foreach ($unitGroup as $key => $value) {
            if (strtolower($key) === $bhk) {
                return true;
            }
        }
    }

    return false;
}



}
