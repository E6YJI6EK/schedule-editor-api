<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TeacherService
{
    public function paginateLatest(int $perPage = 10): LengthAwarePaginator
    {
        return Teacher::latest()->paginate($perPage);
    }

    public function search(array $filters): Collection
    {
        $query = Teacher::query();

        if (!empty($filters['discipline_id'])) {
            $query->whereHas('disciplines', function ($q) use ($filters) {
                $q->where('disciplines.id', $filters['discipline_id']);
            });
        }

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }
}


