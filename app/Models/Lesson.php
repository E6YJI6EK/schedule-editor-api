<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lesson extends Model
{
    protected $fillable = [
        'teacher_id',
        'class_room_id',
        'time_slot_id',
        'discipline_id',
        'group_id',
    ];

    /**
     * Преподаватель занятия
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Аудитория занятия
     */
    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class);
    }

    /**
     * Временной слот занятия
     */
    public function timeSlot(): BelongsTo
    {
        return $this->belongsTo(TimeSlot::class);
    }

    /**
     * Дисциплина занятия
     */
    public function discipline(): BelongsTo
    {
        return $this->belongsTo(Discipline::class);
    }

    /**
     * Группа занятия
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }
}
