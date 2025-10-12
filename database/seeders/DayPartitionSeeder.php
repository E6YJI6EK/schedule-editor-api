<?php

namespace Database\Seeders;

use App\Models\DayPartition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DayPartitionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partitions = [
            ['start_time' => '08:30:00', 'end_time' => '10:00:00'],
            ['start_time' => '10:10:00', 'end_time' => '11:40:00'],
            ['start_time' => '12:00:00', 'end_time' => '13:30:00'],
            ['start_time' => '13:40:00', 'end_time' => '15:10:00'],
            ['start_time' => '15:20:00', 'end_time' => '16:50:00'],
            ['start_time' => '17:00:00', 'end_time' => '18:30:00'],
        ];

        foreach ($partitions as $partition) {
            DayPartition::create($partition);
        }
    }
}
