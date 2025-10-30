<?php

namespace App\Services;

use App\Models\Discipline;
use Illuminate\Database\Eloquent\Collection;

class DisciplineService
{
    public function search(array $filters): Collection
    {
        $query = Discipline::query();

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }
}


