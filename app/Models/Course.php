<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Course extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;

    protected $guarded = false;

    protected $hidden = ['pivot'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }

    public function lesson_problems()
    {
        return $this->hasManyThrough(Problem::class, Lesson::class);
    }

    public function course_problems()
    {
        return $this->hasMany(Problem::class);
    }

    public function students()
    {
        $ids = DB::table('course_user')
            ->where('course_id', '=', $this->id)
            ->where('is_teacher', '=', false)
            ->pluck('user_id');

        return User::whereIn('id', $ids)->get();
    }

    public function teachers()
    {
        $ids = DB::table('course_user')
            ->where('course_id', '=', $this->id)
            ->where('is_teacher', '=', true)
            ->pluck('user_id');

        return $this->belongsToMany(User::class)->whereIn('id', $ids);
    }

    public function mainTeacher()
    {
        $id = DB::table('course_user')
            ->where('course_id', '=', $this->id)
            ->where('is_teacher', '=', true)
            ->where('is_main_teacher', '=', true)
            ->pluck('user_id')
            ->first();

        return $this->belongsToMany(User::class)->whereIn('id', [$id]);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(Section::class);
    }

    public function payment_plans(): HasMany
    {
        return $this->hasMany(PaymentPlan::class);
    }
}
