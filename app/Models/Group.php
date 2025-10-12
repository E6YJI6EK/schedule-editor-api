<?php

namespace App\Models;

use App\Enums\Course;
use App\Enums\EducationForm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name',
        'course',
        'education_form',
        'institute_id',
    ];

    protected $casts = [
        'course' => Course::class,
        'education_form' => EducationForm::class,
    ];

    /**
     * Институт группы
     */
    public function institute(): BelongsTo
    {
        return $this->belongsTo(Institute::class);
    }

    /**
     * Занятия группы
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
