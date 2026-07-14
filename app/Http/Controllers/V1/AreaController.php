<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Project;
use App\Formatters\ProjectFormatter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

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
            ['path' => request()->url()]
        );
    }

    public function getAreasWithProjectCount(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);

            $areas = Area::with([
                'state:id,name',
                'city:id,name',
                'projects' => function ($query) {
                    $query->select('_id', 'area_id');
                }
            ])
                ->where('status', true)
                ->get();

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