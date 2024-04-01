<?php

namespace App\Models;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Doctor extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'admin_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'phone',
        'specialization',
        'bio',
        'price',
        'clinic_location',
        'active_status',
        'working_hours',
    ];
    public function users()
    {
        return $this->belongsToMany(User::class, 'ratings')->withPivot('rate')->withTimestamps();
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

    public function rates(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function times(): HasMany
    {
        return $this->hasMany(Time::class);
    }

    public function dates(): HasMany
    {
        return $this->hasMany(Date::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
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
        return $this->hasMany(VideoCall::class, 'doctor_id');
    }
    public function activeVideoCall()
    {
        return $this->hasOne(VideoCall::class)
            ->where('status', 'accepted')
            ->whereNull('ended_at');
    }

}