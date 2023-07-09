<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Modification extends Model
{
    use HasFactory;
    use Filterable;

    protected $guarded = false;

    public const NAMING_RU = [
        Lesson::class => 'Урок',
        Problem::class => 'Задача',
    ];

    public const NAMING_EN = [
        Lesson::class => 'lesson',
        Problem::class => 'problem',
    ];

    public function modifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
