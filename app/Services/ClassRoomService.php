<?php

namespace App\Services;

use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Collection;

class ClassRoomService
{
    public function search(array $filters): Collection
    {
        $query = ClassRoom::query();

        if (!empty($filters['building_id'])) {
            $query->whereHas('building', function ($q) use ($filters) {
                $q->where('buildings.id', $filters['building_id']);
            });
        }

        if (!empty($filters['number'])) {
            return $query->where('number', 'like', '%' . $filters['number'] . '%')->get();
        }

        return $query->limit(10)->get();
    }
}


