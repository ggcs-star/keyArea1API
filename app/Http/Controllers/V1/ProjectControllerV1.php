<?php

namespace App\Http\Controllers\V1;
use Illuminate\Http\Request;
use App\Formatters\ProjectFormatter;
use App\Http\Controllers\Controller;
use App\Models\Project;
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
use App\Http\Resources\ProjectDetailResource;
use Illuminate\Support\Facades\Log;

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

            $bhk = (int) filter_var($bhk, FILTER_SANITIZE_NUMBER_INT);

            $unitTypeIds = UnitType::where('bhk', 'like', "%{$bhk}%")
                ->get()
                ->map(fn($item) => (string) $item->id)
                ->toArray();
            // dd([
//     'unitTypeIds' => $unitTypeIds,
// ]);
            if (empty($unitTypeIds)) {
                return response()->json([
                    'data' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 10,
                        'total' => 0,
                    ]
                ]);
            }

            $projects = Project::where('status', true)
                ->get()
                ->filter(function ($project) use ($unitTypeIds) {

                    $ids = $project->unit_type_ids ?? [];

                    if (!is_array($ids))
                        return false;

                    $ids = array_map('strval', $ids);

                    return count(array_intersect($ids, $unitTypeIds)) > 0;
                })
                ->values();

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

            \Log::error('BHK Projects Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

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




    public function projectDetails($slug)
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

            return response()->json([
                'data' => new ProjectDetailResource($project)
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
                        ->first(['_id', 'name', 'bhk']);
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


    public function mapProjects(Request $request)
    {

        try {

            $query = Project::query();

            $query->whereNotNull('latitude')
                ->whereNotNull('longitude');

            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }

            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            if ($request->filled('project_type')) {
                $query->where('project_type', $request->project_type);
            }

            if ($request->filled('project_status')) {
                $query->where('project_status', $request->project_status);
            }

            if ($request->filled('min_price')) {
                $query->where('price', '>=', (float) $request->min_price);
            }
            if ($request->filled('max_price')) {
                $query->where('price', '<=', (float) $request->max_price);
            }

            if ($request->filled('is_featured')) {
                $query->where('is_featured', true);
            }

            $projects = $query->get();

            $data = $projects->map(function ($project) {

                return [
                    'id' => (string) $project->_id,
                    'name' => $project->name,
                    'slug' => $project->slug,
                    'carpet_area' => $project->carpet_area,
                    'latitude' => (float) $project->latitude,
                    'longitude' => (float) $project->longitude,
                    'price' => $project->price,

                    'city_name' => optional($project->city)->name,
                    'area_name' => optional($project->area)->name,

                    'project_type' => $project->project_type,
                    'project_status' => $project->project_status,

                    'cover_image' => $project->cover_image_url,
                ];
            });

            return response()->json([
                'status' => true,
                'count' => $data->count(),
                'projects' => $data->values()
            ]);

        } catch (\Throwable $e) {

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

   public function getAllProjects(Request $request, ProjectFormatter $formatter)
    {
        try {
            $perPage = $request->get('per_page', 10);

            // ==========================================
            // STEP 1: DATABASE LEVEL FILTERS (Fast Query)
            // ==========================================
            $query = Project::with(['state', 'city', 'area'])->where('status', true);

            // 1. Keyword Search (Project Name)
            if ($request->filled('search')) {
                $search = trim($request->get('search'));
                $regex = '/.*' . preg_quote($search, '/') . '.*/i';
                $query->where('name', 'regexp', $regex);
            }

            // 2. Location Filters (City & Area)
            if ($request->filled('city_id')) {
                $query->where('city_id', $request->city_id);
            }
            if ($request->filled('area_id')) {
                $query->where('area_id', $request->area_id);
            }

            // 3. Builder / Promoter Filter
            if ($request->filled('builder_id')) {
                $query->where('builder_id', $request->builder_id);
            }

            // 4. Project Type & Status (Exact Match)
            if ($request->filled('project_type')) {
                $query->where('project_type', $request->project_type);
            }
            if ($request->filled('project_status')) {
                $query->where('project_status', $request->project_status);
            }

            // 5. Property Type Array Filter (e.g., Apartment, Villa)
            if ($request->filled('property_type_id')) {
                // Agar frontend comma-separated bhej raha hai (e.g., id1,id2)
                $propIds = explode(',', $request->property_type_id);
                $query->whereIn('property_type_ids', $propIds);
            }

            // 6. Category Array Filter (e.g., Luxury, Affordable)
            if ($request->filled('category_id')) {
                $catIds = explode(',', $request->category_id);
                $query->whereIn('category_ids', $catIds);
            }

            // 7. Boolean Flags (Featured, Trending, New Launch, Emerging)
            $booleanFlags = ['is_featured', 'is_trending', 'is_new_launch', 'is_emerging_property'];
            foreach ($booleanFlags as $flag) {
                if ($request->filled($flag)) {
                    $boolValue = filter_var($request->$flag, FILTER_VALIDATE_BOOLEAN);
                    $query->where($flag, $boolValue);
                }
            }

            // 8. Year Filters (Possession / Launch Date)
            // Example format from DB: "2026-12-31"
            if ($request->filled('possession_year')) {
                $query->where('possession_date', 'like', $request->possession_year . '%');
            }
            if ($request->filled('launch_year')) {
                $query->where('launch_date', 'like', $request->launch_year . '%');
            }

            // 9. BHK Filter
            if ($request->filled('bhk')) {
                $bhk = (int) filter_var($request->bhk, FILTER_SANITIZE_NUMBER_INT);
                
                $unitTypeIds = UnitType::where('bhk', 'like', "%{$bhk}%")
                    ->pluck('id')
                    ->map(fn($id) => (string) $id)
                    ->toArray();

                if (empty($unitTypeIds)) {
                    $query->where('id', null);
                } else {
                    $query->whereIn('unit_type_ids', $unitTypeIds);
                }
            }

            // Query Execute Karein
            $projects = $query->latest()->get();

            // ==========================================
            // STEP 2: COLLECTION LEVEL FILTERS (Price logic)
            // ==========================================
            if ($request->filled('min_price') || $request->filled('max_price')) {
                
                $min = $request->filled('min_price') ? (float) $request->min_price : 0;
                $max = $request->filled('max_price') ? (float) $request->max_price : PHP_INT_MAX;

                $projects = $projects->filter(function ($project) use ($min, $max) {
                    $price = function_exists('priceToNumber') 
                                ? priceToNumber($project->price) 
                                : (float) $project->price;

                    return $price >= $min && $price <= $max;
                })->values();
            }

            // ==========================================
            // STEP 3: PAGINATION & RESPONSE
            // ==========================================
            $paginated = $this->paginateCollection($projects, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Projects fetched successfully.',
                'data' => $formatter->collection(collect($paginated->items())),
                'pagination' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page' => $paginated->lastPage(),
                    'per_page' => $paginated->perPage(),
                    'total' => $paginated->total(),
                    'next_page_url' => $paginated->nextPageUrl(),
                    'prev_page_url' => $paginated->previousPageUrl(),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Get All Projects Error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
