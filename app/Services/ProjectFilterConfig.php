<?php
namespace App\Services;
use Illuminate\Support\Collection;
class ProjectFilterConfig

{
    public static function filters(): array
    {
        return [
            'featured' => 'featured',
            'emerging_property' => 'emerging_property',
            'emerging_area' => 'emerging_area',
            'city' => 'project.location.city',
            'bhk' => 'configuration',
        ];
    }

   
}
