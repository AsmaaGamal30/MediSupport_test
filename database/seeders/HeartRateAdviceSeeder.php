<?php

namespace Database\Seeders;

use App\Models\HeartRateAdvice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeartRateAdviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adviceData = [
            [
                'key' => 'low',
                'advice' => 'ensure you are well-rested and hydrated. Avoid sudden movements and consider gentle exercise to improve circulation. Consult your doctor if it persists or if you feel dizzy or faint.'
            ],
            [
                'key' => 'high',
                'advice' => 'try deep breathing exercises and relaxation techniques. Avoid caffeine, alcohol, and stress. Stay hydrated and consider light exercise if appropriate. Seek medical attention if it persists or if you experience chest pain or shortness of breath.'
            ],
            [
                'key' => 'normal',
                'advice' => 'continue to prioritize your overall well-being. Maintain a balanced lifestyle with proper nutrition, regular exercise, and stress management. Stay hydrated, get quality sleep, and avoid excessive caffeine and alcohol intake. Remember to monitor your heart rate periodically and seek medical advice if you experience any unusual symptoms or concerns.'
            ]
        ];

        foreach ($adviceData as $advice) {
            HeartRateAdvice::firstOrCreate($advice);
        }
    }
}
