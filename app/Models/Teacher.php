<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Дисциплины, которые ведет преподаватель
     */
    public function disciplines(): BelongsToMany
    {
        return $this->belongsToMany(Discipline::class, 'teacher_disciplines');
    }

    /**
     * Занятия преподавателя
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
