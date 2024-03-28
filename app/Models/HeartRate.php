<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HeartRate extends Model
{
    use HasFactory;
    protected $fillable=[
        'heart_rate',
        'user_id',
        'heart_rate_advice_id',
     ];

     public function user(): BelongsTo
     {
        return $this->belongsTo(User::class);
     }

     public function heartRateAdvice(): BelongsTo
     {
        return $this->belongsTo(HeartRateAdvice::class, 'heart_rate_advice_id');
     }



}
