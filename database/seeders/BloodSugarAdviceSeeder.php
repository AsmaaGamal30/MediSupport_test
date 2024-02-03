<?php

namespace Database\Seeders;

use App\Models\BloodSugarAdvice;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BloodSugarAdviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adviceData = [
            [
                'key' => 'low',
                'advice' => 'Your blood sugar level is low. It is recommended to consume a source of fast-acting carbohydrates, such as fruit juice, glucose tablets, or candy, to quickly raise your blood sugar. Remember to follow up with a balanced meal or snack to maintain stable levels. If symptoms persist or worsen, seek medical attention promptly.'
            ],
            [
                'key' => 'high',
                'advice' => 'Your blood sugar level is high. It is recommended to avoid high-sugar foods and beverages. Consider incorporating more vegetables, whole grains, and lean proteins into your diet. Regular exercise can also help regulate blood sugar levels. If you continue to experience high blood sugar, please consult with your healthcare provider.'
            ],
            [
                'key' => 'normal',
                'advice' => 'Great news! Your blood sugar level is within the normal range. Keep up the good work with your healthy lifestyle choices, including a balanced diet and regular physical activity. Monitoring your blood sugar regularly and maintaining a healthy lifestyle are key to overall well-being. If you have any concerns or questions, do not hesitate to consult with your healthcare provider.'
            ]
        ];

        foreach ($adviceData as $advice) {
            BloodSugarAdvice::firstOrCreate($advice);
        }
    }
}
