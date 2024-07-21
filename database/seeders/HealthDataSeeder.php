<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class HealthDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $csvFile = File::get(storage_path('app/balanced_dataset.csv'));
        $rows = array_map('str_getcsv', explode("\n", $csvFile));
        $header = array_shift($rows);

        foreach ($rows as $row) {
            DB::table('data')->insert(array_combine($header, $row));
        }
    }
}
