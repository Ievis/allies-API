<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lesson extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;

    protected $guarded = false;

    public function modifications(): MorphMany
    {
        return $this->morphMany(Modification::class, 'modifiable');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function type()
    {
        return $this->belongsTo(LessonType::class);
    }

    public function status()
    {
        return $this->belongsTo(LessonStatus::class);
    }

    public function problems()
    {
        return $this->hasMany(Problem::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}
