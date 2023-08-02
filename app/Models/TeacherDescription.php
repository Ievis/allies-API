<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherDescription extends Model
{
    use HasFactory;
    use Filterable;

    protected $guarded = false;
}
