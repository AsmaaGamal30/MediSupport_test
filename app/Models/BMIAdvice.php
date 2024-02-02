<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BMIAdvice extends Model
{
    use HasFactory;
    protected $table = 'bmi_advice';
    protected $fillable = [
        'key',
        'advice',
    ];

    public function bmi()
    {
        return $this->hasMany(BMI::class);
    }

}