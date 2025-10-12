<?php

namespace App\Models;

use App\Enums\Day;
use App\Enums\WeekType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeSlot extends Model
{
    protected $fillable = [
        'week_type',
        'day',
        'day_partition_id',
    ];

    protected $casts = [
        'week_type' => WeekType::class,
        'day' => Day::class,
    ];

    /**
     * Раздел дня (время начала и окончания)
     */
    public function dayPartition(): BelongsTo
    {
        return $this->belongsTo(DayPartition::class);
    }

    /**
     * Занятия в этом временном слоте
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }
}
