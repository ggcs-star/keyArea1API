<?php

namespace App\Services\Project;

use App\Models\Project;
use App\Models\Configuration;

class ProjectFormatter
{


public function formatForSlider(Project $project): array
{
    $sizes = Configuration::where('project_id', $project->_id)
        ->pluck('size')
        ->filter()
        ->values()
        ->toArray();

    // ✅ Helper compatible structure
    $configurations = [
        'units' => collect($sizes)
            ->map(fn($size) => [
                'size' => $size
            ])
            ->toArray()
    ];

    return [

        'id' => (string) $project->_id,

        'project' => [
            'name' => $project->name,
            'type' => $project->type,
            'logo_image_url' => $project->logo_image,

            'location' => [
                'city' => data_get($project, 'location.city'),
                'area' => data_get($project, 'location.area'),
            ],
        ],

        'configuration' => [

            'price' => $project->price,

            'size' => ProjectSizeHelper::extractSizeRange(
                $configurations
            ),
        ],
    ];
}
}
