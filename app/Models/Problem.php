<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Problem extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Filterable;

    protected $guarded = false;

    public function modifications(): MorphMany
    {
        return $this->morphMany(Modification::class, 'modifiable');
    }

    public function solution()
    {
        return $this->belongsTo(Solution::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function problems()
    {
        return $this->belongsToMany(User::class);
    }
}
