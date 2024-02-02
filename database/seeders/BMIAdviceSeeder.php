<?php

namespace Database\Seeders;

use App\Models\BMIAdvice;
use Illuminate\Database\Seeder;

class BMIAdviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create BMI advice records
        BMIAdvice::factory()->create([
            'key' => 'default',
            'advice' => 'Please consult with a healthcare professional for personalized advice.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'underweight',
            'advice' => 'You are underweight. Please consult with a healthcare professional for guidance on gaining weight safely.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'normal_weight',
            'advice' => 'Congratulations! Your BMI is within the normal weight range. Keep up the good work with your healthy lifestyle.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'overweight',
            'advice' => 'You are overweight. It is advisable to focus on a balanced diet and regular exercise to achieve a healthy weight.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'obesity_class_1',
            'advice' => 'You are classified as obese (Class 1). It is important to prioritize lifestyle changes such as diet and exercise to improve your health.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'obesity_class_2',
            'advice' => 'You are classified as obese (Class 2). Seeking support from healthcare professionals and making sustainable lifestyle changes are crucial for managing your weight.',
        ]);

        BMIAdvice::factory()->create([
            'key' => 'extreme_obesity_class_3',
            'advice' => 'You are classified as extremely obese (Class 3). Immediate action is necessary for improving your health. Consult with healthcare professionals for personalized advice and support.',
        ]);
    }
}