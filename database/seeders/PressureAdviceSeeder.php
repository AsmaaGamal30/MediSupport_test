<?php

namespace Database\Seeders;

use App\Models\PressureAdvice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PressureAdviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PressureAdvice::factory()->create([
            'key' => 'normal',
            'advice' => 'Maintain a healthy lifestyle by eating a balanced diet, engaging in regular physical activity, managing stress, and getting adequate sleep. Regular check-ups with your healthcare provider are important for monitoring your overall health.',
        ]);

        PressureAdvice::factory()->create([
            'key' => 'high',
            'advice' => 'Consult with a healthcare professional for personalized advice and potential treatment options. Lifestyle modifications, including a low-sodium diet, regular exercise, weight management, and stress reduction, may be recommended. Medication may be prescribed based on the severity.',
        ]);

        PressureAdvice::factory()->create([
            'key' => 'low',
            'advice' => 'If you are not experiencing symptoms such as dizziness or fainting, low blood pressure may not necessarily require treatment. However, if you have symptoms or concerns, consult with a healthcare professional. Increasing fluid and salt intake, wearing compression stockings, and avoiding alcohol and caffeine may be recommended.',
        ]);
    }
}
