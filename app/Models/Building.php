<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{
    protected $fillable = [
        'name',
        'short_name',
    ];

    /**
     * Аудитории здания
     */
    public function classRooms(): HasMany
    {
        return $this->hasMany(ClassRoom::class);
    }
}
