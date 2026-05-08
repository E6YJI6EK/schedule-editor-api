<?php

namespace App\Services;

use App\Models\Building;
use Illuminate\Database\Eloquent\Collection;

class BuildingService
{
    public function all(): Collection
    {
        return Building::with('classRooms')->get();
    }

    public function find(int $id): Building
    {
        return Building::with('classRooms')->findOrFail($id);
    }

    public function search(array $filters): Collection
    {
        $query = Building::query();

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }

    public function store(array $data): Building
    {
        return Building::create($data);
    }

    public function update(Building $building, array $data): Building
    {
        $building->update($data);

        return $building->fresh();
    }

    public function delete(Building $building): void
    {
        $building->delete();
    }
}
