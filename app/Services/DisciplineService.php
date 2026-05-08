<?php

namespace App\Services;

use App\Models\Discipline;
use Illuminate\Database\Eloquent\Collection;

class DisciplineService
{
    public function all(): Collection
    {
        return Discipline::with('teachers')->get();
    }

    public function find(int $id): Discipline
    {
        return Discipline::with('teachers')->findOrFail($id);
    }

    public function search(array $filters): Collection
    {
        $query = Discipline::query();

        if (!empty($filters['name'])) {
            return $query->where('name', 'like', '%' . $filters['name'] . '%')->get();
        }

        return $query->limit(10)->get();
    }

    public function store(array $data): Discipline
    {
        return Discipline::create($data);
    }

    public function update(Discipline $discipline, array $data): Discipline
    {
        $discipline->update($data);

        return $discipline->fresh();
    }

    public function delete(Discipline $discipline): void
    {
        $discipline->delete();
    }
}
