<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LessonType extends Model
{
    use HasFactory;

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
