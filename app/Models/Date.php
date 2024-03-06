<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Date extends Model
{
    use HasFactory;

    protected $fillable =[
        'doctor_id',
         'date',
       ];
   
       public function doctor(): BelongsTo
       {
           return $this->belongsTo(Doctor::class);
       }
   
       public function times(): HasMany
       {
           return $this->hasMany(Time::class);
       }
}
