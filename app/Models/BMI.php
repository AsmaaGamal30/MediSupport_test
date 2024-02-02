<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMI extends Model
{
    use HasFactory;
    protected $table = 'bmi';


    protected $fillable = [
        'user_id',
        'bmi_advice_id',
        'gender',
        'age',
        'height',
        'weight',
        'result',
    ];
    public function bmiAdvice()
    {
        return $this->belongsTo(BMIAdvice::class, 'bmi_advice_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
