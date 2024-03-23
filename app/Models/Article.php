<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Article extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'body', 'doctor_id', 'image'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}
