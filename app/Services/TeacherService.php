<?php

namespace App\Services;

use App\Models\Teacher;
use Illuminate\Database\Eloquent\Collection;

class TeacherService
{
    public function all(): Collection
    {
        return Teacher::with('disciplines')->orderBy('name')->get();
    }

    public function find(int $id): Teacher
    {
        return Teacher::with('disciplines')->findOrFail($id);
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

    public function store(array $data): Teacher
    {
        $teacher = Teacher::create(['name' => $data['name']]);

        if (!empty($data['discipline_ids'])) {
            $teacher->disciplines()->sync($data['discipline_ids']);
        }

        return $teacher->load('disciplines');
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $teacher->update(['name' => $data['name'] ?? $teacher->name]);

        if (array_key_exists('discipline_ids', $data)) {
            $teacher->disciplines()->sync($data['discipline_ids']);
        }

        return $teacher->load('disciplines');
    }

    public function delete(Teacher $teacher): void
    {
        $teacher->delete();
    }
}
