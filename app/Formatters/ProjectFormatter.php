<?php

namespace App\Formatters;

class ProjectFormatter
{
    public function format($project)
    {
        return [
            'name' => $project->name,
            'slug' => $project->slug,
            'cover_image_url' => $project->cover_image_url,
            'state' => optional($project->state)->name,
            'city'  => optional($project->city)->name,
            'area'  => optional($project->area)->name,
            'project_type' => $project->project_type,
            'price' => $project->price,
            'carpet_area' => $project->carpet_area,
        ];
    }

    public function collection($projects)
    {
        return $projects->map(function ($project) {
            return $this->format($project);
        })->values();
    }
}