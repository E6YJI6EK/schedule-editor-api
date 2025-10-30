<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function search(array $filters): Collection
    {
        $query = Group::query();

        if (!empty($filters['institute_id']) && method_exists(Group::class, 'institutes')) {
            $query->whereHas('institutes', function ($q) use ($filters) {
                $q->where('institutes.id', $filters['institute_id']);
            });
        }

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }
}


