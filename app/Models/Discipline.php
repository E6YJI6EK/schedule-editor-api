<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Discipline extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Преподаватели, ведущие дисциплину
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'teacher_disciplines');
    }

    /**
     * Занятия по дисциплине
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
