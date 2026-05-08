<?php

namespace App\Services;

use App\Models\Group;
use Illuminate\Database\Eloquent\Collection;

class GroupService
{
    public function search(array $filters): Collection
    {
        $query = Group::query();

        if (!empty($filters['institute_id'])) {
            $query->where('institute_id', $filters['institute_id']);
        }

        if (!empty($filters['course'])) {
            $query->where('course', $filters['course']);
        }

        if (!empty($filters['education_form'])) {
            $query->where('education_form', $filters['education_form']);
        }

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        return $query->limit(50)->get();
    }
}
