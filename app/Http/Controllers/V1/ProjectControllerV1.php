<?php

namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use App\Formatters\ProjectFormatter;
use App\Http\Controllers\Controller;
use App\Models\Project;
// use App\Services\Project\ProjectFormatter;
use App\Services\Project\ProjectPriceHelper;
use App\Services\Project\ProjectBhkHelper;
use App\Models\Unit;
use App\Models\BuilderUser;
use App\Models\Configuration;
use App\Models\Amenity;
use App\Models\UnitType;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\PropertyType;
use App\Models\Tower;
class ProjectControllerV1 extends Controller
{
    private function paginateCollection($items, $perPage = 10)
    {
        $page = request()->get('page', 1);

        $items = $items->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            ['path' => request()->url()]
        );
    }
    public function budgetSlider(ProjectFormatter $formatter)
    {
        try {

            $min = 5000000;
            $max = 10000000;

            $projects = Project::where('status', true)->get();

            $filtered = $projects->filter(function ($project) use ($min, $max) {

                $price = priceToNumber($project->price);

                return $price >= $min && $price <= $max;
            });

            $paginated = $this->paginateCollection($filtered, 10);

            return response()->json([
                'data' => $formatter->collection(collect($paginated->items())),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function bhkProjects($bhk, ProjectFormatter $formatter)
    {
        try {

            $bhk = (string) filter_var($bhk, FILTER_SANITIZE_NUMBER_INT);

            $unitTypeIds = UnitType::where('bhk', $bhk)
                ->pluck('id')
                ->toArray();

            $projects = Project::where('status', true)
                ->get()
                ->filter(function ($project) use ($unitTypeIds) {

                    $ids = $project->unit_type_ids;

                    if (is_string($ids)) {
                        $ids = json_decode($ids, true);
                    }

                    if (!is_array($ids)) {
                        return false;
                    }

                    return count(array_intersect($ids, $unitTypeIds)) > 0;
                });

            $paginated = $this->paginateCollection($projects, 10);

            return response()->json([
                'data' => $formatter->collection(collect($paginated->items())),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function twoBhkProjects(ProjectFormatter $formatter)
    {
        return $this->bhkProjects(2, $formatter);
    }

    public function threeBhkProjects(ProjectFormatter $formatter)
    {
        return $this->bhkProjects(3, $formatter);
    }

    public function fourBhkProjects(ProjectFormatter $formatter)
    {
        return $this->bhkProjects(4, $formatter);
    }

    public function fiveBhkProjects(ProjectFormatter $formatter)
    {
        return $this->bhkProjects(5, $formatter);
    }

    

public function projectDetails($slug, ProjectFormatter $formatter)
{
    try {

        $project = Project::where('slug', $slug)
            ->where('status', true)
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }

        $builder = $project->builder;
        $promoters = BuilderUser::whereIn('_id', $project->promoter_ids ?? [])
             ->get(['_id','name','email','phone','company_name','logo']);

        $unitTypes = UnitType::whereIn('_id', $project->unit_type_ids ?? [])
            ->get(['_id','name','slug','bhk']);

        $amenities = Amenity::whereIn('_id', $project->amenity_ids ?? [])
            ->get(['_id','name','icon_url']);

        $categories = Category::whereIn('_id', $project->category_ids ?? [])
            ->get(['_id','name','slug']);

        $towers = $project->towers()->get();

        return response()->json([
            'data' => [

                'name' => $project->name,
                'slug' => $project->slug,

                'builder' => $builder ? [
                    'id' => $builder->_id,
                    'name' => $builder->name,
                    'company_name' => $builder->company_name,
                    'email' => $builder->email,
                    'phone' => $builder->phone,
                    'address' => $builder->address,
                    'logo' => $builder->logo,
                ] : null,
                'promoters' => $promoters,
                'price' => $project->price,
                'project_type' => $project->project_type,
                'project_status' => $project->project_status,

                'description' => $project->description,
                'short_description' => $project->short_description,
                'address' => $project->address,

                'state' => optional($project->state)->name,
                'city' => optional($project->city)->name,
                'area' => optional($project->area)->name,

                'latitude' => $project->latitude,
                'longitude' => $project->longitude,

                'carpet_area' => $project->carpet_area,
                'rera_number' => $project->rera_number,

                'total_units' => $project->total_units,
                'total_towers' => $project->total_towers,

                'launch_date' => $project->launch_date,
                'possession_date' => $project->possession_date,

                'cover_image' => $project->cover_image_url,
                'slider_images' => $project->slider_image_url,
                'gallery_images' => $project->gallery_images_url,
                'floor_plans' => $project->floorPlans_images_url,

                'brochure' => $project->brochure_url,
                'reel' => $project->reel_url,

                'unit_types' => $unitTypes,
                'amenities' => $amenities,
                'categories' => $categories,
                // 'towers' => $towers,

                'meta_title' => $project->meta_title,
                'meta_description' => $project->meta_description,
                'meta_keywords' => $project->meta_keywords,
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function projectTowers(Request $request, $slug)
{
    try {
        $project = Project::where('slug', $slug)
            ->where('status', true)
            ->first();

        if (!$project) {
            return response()->json([
                'message' => 'Project not found'
            ], 404);
        }

        if (!$request->has('tower_id')) {

            $towers = $project->towers()
                ->where('status', true)
                ->get(['_id', 'name']);

            return response()->json([
                'data' => [
                    'project' => [
                        'id' => $project->_id,
                        'name' => $project->name,
                        'slug' => $project->slug,
                    ],
                    'view_type' => 'tower',
                    'towers' => $towers->map(fn($t) => [
                        'id' => $t->_id,
                        'name' => $t->name,
                    ])
                ]
            ]);
        }

        $tower = Tower::where('_id', $request->tower_id)
            ->where('status', true)
            ->first();

        if (!$tower) {
            return response()->json([
                'message' => 'Tower not found'
            ], 404);
        }

        if ($request->has('unit_number')) {

            $unitNumber = $request->unit_number;

            $unit = collect($tower->units ?? [])
                ->firstWhere('unit_number', $unitNumber);

            if (!$unit) {
                return response()->json([
                    'message' => 'Unit not found'
                ], 404);
            }

            $unitType = null;
            if (!empty($unit['unit_type_id'])) {
                $unitType = UnitType::where('_id', $unit['unit_type_id'])
                    ->first(['_id','name','bhk']);
            }

            return response()->json([
                'data' => [
                    'project' => [
                        'id' => $project->_id,
                        'name' => $project->name,
                    ],
                    'tower' => [
                        'id' => $tower->_id,
                        'name' => $tower->name,
                    ],
                    'view_type' => 'unit_detail',
                    'unit' => [
                        'unit_number' => $unit['unit_number'],
                        'unit_size' => $unit['unit_size'] ?? null,
                        'status' => $unit['status'] ?? null,

                        'unit_type' => $unitType ? [
                            'id' => $unitType->_id,
                            'name' => $unitType->name,
                            'bhk' => $unitType->bhk,
                        ] : null,

                        'property_type_id' => $unit['property_type_id'] ?? null,
                        'room_sizes' => $unit['room_sizes'] ?? [],
                    ]
                ]
            ]);
        }

        if ($request->has('floor')) {

            $floor = (int) $request->floor;

            $units = collect($tower->units ?? [])
                ->where('floor_number', $floor)
                ->values()
                ->map(fn($u) => [
                    'unit_number' => $u['unit_number'] ?? null,
                    'unit_size' => $u['unit_size'] ?? null,
                    'status' => $u['status'] ?? null,
                ]);

            return response()->json([
                'data' => [
                    'project' => [
                        'id' => $project->_id,
                        'name' => $project->name,
                    ],
                    'tower' => [
                        'id' => $tower->_id,
                        'name' => $tower->name,
                    ],
                    'view_type' => 'unit',
                    'floor' => $floor,
                    'units' => $units
                ]
            ]);
        }

        if ($tower->type === 'apartment') {

            $floors = collect($tower->floor_designs ?? [])
                ->flatMap(fn($d) => range($d['from_floor'], $d['to_floor']))
                ->unique()
                ->values();

            return response()->json([
                'data' => [
                    'project' => [
                        'id' => $project->_id,
                        'name' => $project->name,
                    ],
                    'tower' => [
                        'id' => $tower->_id,
                        'name' => $tower->name,
                        'type' => $tower->type,
                    ],
                    'view_type' => 'floor',
                    'floors' => $floors
                ]
            ]);
        }

        if ($tower->type === 'villa/bungalow') {

            return response()->json([
                'data' => [
                    'project' => [
                        'id' => $project->_id,
                        'name' => $project->name,
                    ],
                    'tower' => [
                        'id' => $tower->_id,
                        'name' => $tower->name,
                        'type' => $tower->type,
                    ],
                    'view_type' => 'unit',
                    'units' => collect($tower->units ?? [])->map(fn($u) => [
                        'unit_number' => $u['unit_number'] ?? null,
                        'unit_size' => $u['unit_size'] ?? null,
                        'status' => $u['status'] ?? null,
                    ])
                ]
            ]);
        }

        return response()->json([
            'data' => [
                'message' => 'Unknown tower type'
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}
}
