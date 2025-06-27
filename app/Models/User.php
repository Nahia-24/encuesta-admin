<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

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

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // 🔁 Relaciones geográficas
    public function department()
    {
        return $this->belongsTo(Departament::class, 'department_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    // 👤 Edad calculada
    public function getAgeAttribute()
    {
        return Carbon::parse($this->birth_date)->age;
    }

    // 📝 Encuestas creadas por este usuario
    public function surveys()
    {
        return $this->hasMany(Survey::class, 'created_by');
    }

    // ✅ Respuestas del usuario a encuestas
    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }
}
