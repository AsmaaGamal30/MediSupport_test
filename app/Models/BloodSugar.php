<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BloodSugar extends Model
{
    use HasFactory;

    protected $fillable=[
        'level',
        'user_id',
        'blood_sugar_advice_id',
        'blood_sugar_statuses_id',
     ];
 
    public function user(): BelongsTo
     {
        return $this->belongsTo(User::class);
     }
 
    public function bloodSugarAdvice(): BelongsTo
     {
        return $this->belongsTo(BloodSugarAdvice::class, 'blood_sugar_advice_id');
     }
 
    public function bloodSugarStatus(): BelongsTo
    {
        return $this->belongsTo(BloodSugarStatus::class ,'blood_sugar_statuses_id');
    }
}
