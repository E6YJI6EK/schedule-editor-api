<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DayPartition extends Model
{
    protected $fillable = [
        'start_time',
        'end_time',
    ];

    protected $casts = [
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    /**
     * Временные слоты
     */
    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }
}
