<?php

namespace App\Services;

use App\Models\ClassRoom;
use Illuminate\Database\Eloquent\Collection;

class ClassRoomService
{
    public function all(): Collection
    {
        return ClassRoom::with('building')->get();
    }

    public function find(int $id): ClassRoom
    {
        return ClassRoom::with('building')->findOrFail($id);
    }

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

    public function store(array $data): ClassRoom
    {
        return ClassRoom::create($data);
    }

    public function update(ClassRoom $classRoom, array $data): ClassRoom
    {
        $classRoom->update($data);

        return $classRoom->fresh('building');
    }

    public function delete(ClassRoom $classRoom): void
    {
        $classRoom->delete();
    }
}
