<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = [
            [
                'name' => 'Учебный комплекс №1',
                'short_name' => 'УК №1'
            ],
            [
                'name' => 'Учебный комплекс №2',
                'short_name' => 'УК №2'
            ],
            [
                'name' => 'Учебный комплекс №3',
                'short_name' => 'УК №3'
            ],
        ];

        foreach ($buildings as $building) {
            Building::create($building);
        }
    }
}
