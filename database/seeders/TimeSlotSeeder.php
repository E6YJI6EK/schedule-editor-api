<?php

namespace Database\Seeders;

use App\Enums\Day;
use App\Enums\WeekType;
use App\Models\TimeSlot;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeSlotSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weekTypes = WeekType::cases();
        $days = Day::cases();
        $dayPartitionIds = range(1, 6); // 6 day partitions

        foreach ($weekTypes as $weekType) {
            foreach ($days as $day) {
                foreach ($dayPartitionIds as $dayPartitionId) {
                    TimeSlot::create([
                        'week_type' => $weekType->value,
                        'day' => $day->value,
                        'day_partition_id' => $dayPartitionId,
                    ]);
                }
            }
        }
    }
}
