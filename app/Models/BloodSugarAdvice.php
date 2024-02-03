<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodSugarAdvice extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'advice',
    ];

    
    public function bloodSugars(): HasMany
    {
        return $this->hasMany(BloodSugar::class);
    }
}
