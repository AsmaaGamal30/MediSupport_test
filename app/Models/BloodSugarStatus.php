<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BloodSugarStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    public function bloodSugars(): HasMany
    {
        return $this->hasMany(BloodSugar::class);
    }
}
