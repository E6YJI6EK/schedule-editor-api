<?php

namespace App\Services;

use App\Models\DayPartition;
use Illuminate\Database\Eloquent\Collection;

class DayPartitionService
{
    public function all(): Collection
    {
        return DayPartition::orderBy('start_time')->get();
    }

    public function find(int $id): DayPartition
    {
        return DayPartition::findOrFail($id);
    }

    public function store(array $data): DayPartition
    {
        return DayPartition::create($data);
    }

    public function update(DayPartition $dayPartition, array $data): DayPartition
    {
        $dayPartition->update($data);

        return $dayPartition->fresh();
    }

    public function delete(DayPartition $dayPartition): void
    {
        $dayPartition->delete();
    }
}
