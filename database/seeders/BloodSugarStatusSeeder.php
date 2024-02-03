<?php

namespace Database\Seeders;

use App\Models\BloodSugarStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BloodSugarStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['status' => 'During Fasting'],
            ['status' => 'Before Eating'],
            ['status' => 'After Eating (1h)'],
            ['status' => 'After Eating (2h)'],
            ['status' => 'Before bedtime'],
            ['status' => 'Before workout'],
            ['status' => 'After workout'],
        ];
    
        foreach ($statuses as $status) {
          BloodSugarStatus::firstOrCreate($status);
        }
    }
}
