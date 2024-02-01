<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PressureAdvice extends Model
{
    use HasFactory;
    protected $fillable = [
        'key',
        'advice',
    ];

    public function bloodPressures()
    {
        return $this->hasMany(BloodPressure::class);
    }
}
