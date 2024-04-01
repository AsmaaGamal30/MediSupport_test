<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'last_name',
        'email',
        'password',
        'avatar',
        'provider_id',
        'provider_name',
        'active_status'
    ];
    public function doctors()
    {
        return $this->belongsToMany(Doctor::class)->withPivot('rate')->withTimestamps();
    }
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function bloodSugars()
    {
        return $this->hasMany(BloodSugar::class);
    }

    public function BMIs()
    {
        return $this->hasMany(BMI::class);
    }

    public function bloodPressures()
    {
        return $this->hasMany(BloodPressure::class);
    }


    public function heartRates(): HasMany
    {
        return $this->hasMany(HeartRate::class);
    }

    public function rates(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function onlineBookings()
    {
        return $this->hasMany(OnlineBooking::class);
    }

    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'notifiable')->orderBy('created_at', 'desc');
    }

    public function doctorCashes()
    {
        return $this->hasMany(DoctorCash::class);
    }

    public function videoCalls()
    {
        return $this->hasMany(VideoCall::class, 'user_id');
    }
}
