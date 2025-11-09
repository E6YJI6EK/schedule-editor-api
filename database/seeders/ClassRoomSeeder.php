<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\ClassRoom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;


class ClassRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = Building::all();

        foreach ($buildings as $building) {
            for ($i = 1; $i < 600; $i++) {
                ClassRoom::create([
                    'number' => $i,
                    'building_id' => $building->id,
                ]);
            }

        }
    }
}
