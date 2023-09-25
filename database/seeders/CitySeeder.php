<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cities = require 'cities.php';
        $cities = array_map(function ($city) {
            return [
                'name' => $city['city']
            ];
        }, $cities);

        DB::table('cities')->upsert($cities, ['id']);
    }
}
