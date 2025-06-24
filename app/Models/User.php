<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'type_document',
        'document_number',
        'phone',
        'status',
        'profile_photo_path',
        'city_id',
        'birth_date',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public function department()
    {
        return $this->belongsTo(Departament::class, 'department_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function getAgeAttribute()
    {
        return Carbon::parse($this->birth_date)->age;
    }

    public function events()
    {
        return $this->hasMany(EventAssistant::class);
    }

    public function eventParameters()
    {
        return $this->hasMany(UserEventParameter::class, 'user_id', 'id');
    }

    public function eventAssistantForEvent($eventId)
    {
        return $this->hasOne(EventAssistant::class)
            ->where('event_id', $eventId)
            ->first();
    }
}
