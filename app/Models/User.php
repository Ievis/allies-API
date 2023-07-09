<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;
    use Filterable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'image',
        'vk_id',
    ];

    public const ADMIN = 4;
    public const MAIN_TEACHER = 3;
    public const TEACHER = 2;
    public const STUDENT = 1;

    public static function getRoleById($role_id)
    {
        switch ($role_id) {
            case User::STUDENT:
                $role = 'student';
                break;
            case User::TEACHER:
                $role = 'teacher';
                break;
            case User::MAIN_TEACHER:
                $role = 'main_teacher';
                break;
            case User::ADMIN:
                $role = 'admin';
                break;
            default:
                return $role = 'student';
        }

        return $role;
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pivot'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function problems()
    {
        return $this->belongsToMany(Problem::class);
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

    public function coursesMainTeacher()
    {
        return $this->belongsToMany(Course::class)->where('is_main_teacher', true);
    }

    public function coursesTeacher()
    {
        return $this->belongsToMany(Course::class)->where('is_teacher', true)
            ->where('is_main_teacher', false);
    }

    public function teacher_descriptions()
    {
        return $this->hasMany(TeacherDescription::class);
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
}
