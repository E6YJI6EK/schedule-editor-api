<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClassRoom extends Model
{
    protected $fillable = [
        'number',
        'building_id',
    ];

    /**
     * Здание аудитории
     */
    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    /**
     * Занятия в аудитории
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
