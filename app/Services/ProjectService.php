<?php

namespace App\Services;
  use Symfony\Component\Process\Exception\ProcessTimedOutException;
use App\Models\Project;
use Illuminate\Support\Collection;
class ProjectService
{
  

public function formatProjects($projects)
{
    return $projects->map(function ($project) {
        try {
            set_time_limit(30); 

            $sizes = [];
            $price = null;

            foreach ($project->configuration ?? [] as $configGroup) {
                if (is_array($configGroup) && isset($configGroup['price'])) {
                    $price = $configGroup['price'];
                    break;
                } 
            }

            foreach ($project->configuration ?? [] as $configGroup) {
                if (is_array($configGroup)) {
                    if (isset($configGroup['size'])) {
                        $sizes[] = (int) str_replace([' Sq. ft.', ','], '', $configGroup['size']);
                    } else {
                        foreach ($configGroup as $subConfig) {
                            if (is_array($subConfig) && isset($subConfig['size'])) {
                                $sizes[] = (int) str_replace([' Sq. ft.', ','], '', $subConfig['size']);
                            }
                        }
                    }
                }
            }

            $sizeRange = count($sizes) > 0
                ? min($sizes) . ' - ' . max($sizes) . ' Sq. ft.'
                : null;

            $logoImageUrl = $project->project['logo_image_id'] ?? null;
            $visualImageUrl = $project->project['visual_image_id'] ?? null;

            return [
                'id' => (string) ($project->_id ?? ''),
                'project' => [
                    'name' => $project->project['name'] ?? 'Unknown',
                    'type' => $project->project['type'] ?? null,
                    'logo_image_url' => $logoImageUrl,
                    'visual_image_url' => $visualImageUrl,
                    'location' => [
                        'city' => $project->project['location']['city'] ?? null,
                        'area' => $project->project['location']['area'] ?? null,
                    ],
                ],
                'configuration' => [
                    'price' => $price,
                    'size' => $sizeRange,
                ],
            ];
        } 
        catch (ProcessTimedOutException $e) {
            \Log::error("Project processing timeout: " . $e->getMessage(), [
                'project_id' => $project->_id ?? null
            ]);

            return [
                'id' => (string) ($project->_id ?? ''),
                'project' => [
                    'name' => $project->project['name'] ?? 'Unknown',
                    'type' => $project->project['type'] ?? null,
                    'logo_image_url' => $project->project['logo_image_id'] ?? null,
                    'visual_image_url' => $project->project['visual_image_id'] ?? null,
                    'location' => [
                        'city' => $project->project['location']['city'] ?? null,
                        'area' => $project->project['location']['area'] ?? null,
                    ],
                ],
                'configuration' => [
                    'price' => null,
                    'size' => null,
                ],
                'message' => 'Project processing timed out.',
            ];
        }
        catch (\Throwable $e) {
            \Log::error("Project processing error: " . $e->getMessage(), [
                'project_id' => $project->_id ?? null
            ]);

            return [
                'id' => (string) ($project->_id ?? ''),
                'project' => [
                    'name' => $project->project['name'] ?? 'Unknown',
                    'type' => $project->project['type'] ?? null,
                    'logo_image_url' => $project->project['logo_image_id'] ?? null,
                    'visual_image_url' => $project->project['visual_image_id'] ?? null,
                    'location' => [
                        'city' => $project->project['location']['city'] ?? null,
                        'area' => $project->project['location']['area'] ?? null,
                    ],
                ],
                'configuration' => [
                    'price' => null,
                    'size' => null,
                ],
                'message' => 'Some project details could not be processed safely.',
            ];
        }
    })->values();
}



    public function extractAvailableFilters(Collection $projects): array
{
    try {
        $cities = [];
        $bhks = [];

        foreach ($projects as $project) {
            $city = $project->project['location']['city'] ?? null;
            if (!empty($city)) {
                $cities[] = $city;
            }

            if (!empty($project->configuration) && is_iterable($project->configuration)) {
                foreach ($project->configuration as $type => $units) {
                    if (is_array($units)) {
                        foreach (array_keys($units) as $bhkType) {
                            if (preg_match('/BHK$/i', $bhkType)) {
                                $bhks[] = $bhkType;
                            }
                        }
                    }
                }
            }
        }

        return [
            'city' => array_values(array_unique($cities)),
            'bhk' => array_values(array_unique($bhks)),
            'featured' => [true, false],
            'emerging_property' => [true, false],
            'emerging_area' => [true, false],
        ];
    } catch (\Throwable $e) {
        return [
            'city' => [],
            'bhk' => [],
            'featured' => [true, false],
            'emerging_property' => [true, false],
            'emerging_area' => [true, false],
            'message' => 'Some filters could not be processed safely.' 
        ];
    }
}


    public function convertPriceToNumber($priceString)
    {
        $priceString = strtolower(str_replace(',', '', $priceString));
        $priceString = str_replace(['lca', 'lc', 'lack', 'lacks'], 'lac', $priceString);

        if (str_contains($priceString, '-')) {
            [$from, $to] = explode('-', $priceString);
            $fromNumber = $this->extractSinglePrice(trim($from));
            $toNumber = $this->extractSinglePrice(trim($to));
            return ($fromNumber + $toNumber) / 2;
        }

        return $this->extractSinglePrice(trim($priceString));
    }

    private function extractSinglePrice($pricePart)
    {
        if (str_contains($pricePart, 'lac')) {
            preg_match('/(\d+\.?\d*)\s*lac/', $pricePart, $matches);
            return isset($matches[1]) ? floatval($matches[1]) * 1e5 : 0;
        }

        if (str_contains($pricePart, 'cr')) {
            preg_match('/(\d+\.?\d*)\s*cr/', $pricePart, $matches);
            return isset($matches[1]) ? floatval($matches[1]) * 1e7 : 0;
        }

        if (preg_match('/(\d+\.?\d*)/', $pricePart, $matches)) {
            return isset($matches[1]) ? floatval($matches[1]) : 0;
        }

        return 0;
    }
}
