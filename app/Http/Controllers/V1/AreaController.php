<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Project;
use App\Formatters\ProjectFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\City;
use App\Models\State;
class AreaController extends Controller
{

    private function paginateCollection($items, $perPage = 10)
    {
        $page = request()->get('page', 1);
        $items = collect($items)->values();

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage),
            $items->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );
    }

    public function getAreasWithProjectCount(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $searchKeyword = trim($request->get('search'));

            $query = Area::with([
                'state:id,name',
                'city:id,name',
                'projects' => function ($q) {
                    $q->select('_id', 'area_id')
                        ->where('status', true);
                }
            ])->where('status', true);

            if (!empty($searchKeyword)) {

                $normalSearch = "%{$searchKeyword}%";

                $chars = preg_split('//u', str_replace(' ', '', $searchKeyword), -1, PREG_SPLIT_NO_EMPTY);
                $fuzzySearch = '%' . implode('%', $chars) . '%';

                $stateIds = State::where('name', 'like', $normalSearch)
                    ->orWhere('name', 'like', $fuzzySearch)
                    ->pluck('id')
                    ->filter()
                    ->toArray();

                $cityIds = City::where('name', 'like', $normalSearch)
                    ->orWhere('name', 'like', $fuzzySearch)
                    ->pluck('id')
                    ->filter()
                    ->toArray();
                // dd([
//     'Search Keyword' => $searchKeyword,
//     'Found State IDs' => $stateIds,
//     'Found City IDs' => $cityIds
// ]);
                // 3. Apply main query
                $query->where(function ($q) use ($normalSearch, $fuzzySearch, $stateIds, $cityIds) {
                    $q->where('name', 'like', $normalSearch)
                        ->orWhere('name', 'like', $fuzzySearch);

                    if (!empty($stateIds)) {
                        $q->orWhereIn('state_id', $stateIds);
                    }

                    if (!empty($cityIds)) {
                        $q->orWhereIn('city_id', $cityIds);
                    }
                });
            }

            $areas = $query->get();

            $areas->each(function ($area) {
                $area->projects_count = $area->projects ? $area->projects->count() : 0;
                $area->state_name = $area->state ? $area->state->name : null;
                $area->city_name = $area->city ? $area->city->name : null;

                unset($area->projects);
                unset($area->state);
                unset($area->city);
            });

            $sortedAreas = $areas->sortByDesc('projects_count')->values();
            $paginatedAreas = $this->paginateCollection($sortedAreas, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Areas fetched successfully.',
                'data' => $paginatedAreas
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAreaDetails(Request $request, $areaId): JsonResponse
    {
        try {
            $area = Area::with(['state:id,name', 'city:id,name'])
                ->where('status', true)
                ->find($areaId);

            if (!$area) {
                return response()->json([
                    'success' => false,
                    'message' => 'Area not found.'
                ], 404);
            }

            $areaData = [
                '_id' => $area->_id,
                'name' => $area->name,
                'pincode' => $area->pincode,
                'latitude' => $area->latitude,
                'longitude' => $area->longitude,
                'image' => $area->image,
                'state_name' => optional($area->state)->name,
                'city_name' => optional($area->city)->name,
            ];

            $perPage = $request->get('per_page', 10);

            $projects = Project::with(['state', 'city', 'area'])
                ->where('area_id', $area->_id)
                ->where('status', true)
                ->get();

            $formatter = new ProjectFormatter();
            $formattedProjects = $formatter->collection($projects);

            $paginatedProjects = $this->paginateCollection($formattedProjects, $perPage);

            return response()->json([
                'success' => true,
                'message' => 'Area details and projects fetched successfully.',
                'data' => [
                    'area' => $areaData,
                    'projects' => $paginatedProjects
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}