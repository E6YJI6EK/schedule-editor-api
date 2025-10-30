<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

class BuildingService
{
    public function search(array $filters): Collection
    {
        $query = Building::query();

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }
}


