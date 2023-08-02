<?php

namespace App\ModelFilters;

use App\Models\Category;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Problem;
use App\Services\UserService;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ProblemFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    protected $camel_cased_methods = false;

    public function title($title)
    {
        return $this->where('title', 'LIKE', "%$title%");
    }

    public function condition($condition)
    {
        return $this->where('condition', 'LIKE', "%$condition%");
    }

    public function lesson($lesson)
    {
        $lesson_id = Lesson::where('title', $lesson)?->first('id')?->id;

        return empty($lesson_id)
            ? $this->where('lesson_id', $lesson)
            : $this->where('lesson_id', $lesson_id);
    }

    public function course($course)
    {
        $course_id = Course::where('name', $course)?->first('id')?->id;

        return empty($course_id)
            ? $this->where('course_id', $course)
            : $this->where('course_id', $course_id);
    }

    public function setup()
    {
        $user = auth()->user();
        if (UserService::isAdmin($user)) {
            $this->query = Lesson::where('is_modification', false)
                ->orderBy('course_id')
                ->orderBy('number_in_course');
            return;
        }

        $course_ids = DB::table('course_user')->where('user_id', $user->id)
            ->where('expires_at', '>', ((array)Carbon::now()->toDateTime())['date'])
            ->orWhere('is_teacher', true)
            ->where('user_id', $user->id)
            ->orderBy('course_id')
            ->pluck('course_id')
            ->toArray();

        $lesson_ids = Lesson::whereIn('course_id', $course_ids)
            ->where('is_modification', false)
            ->orderBy('course_id')
            ->orderBy('number_in_course')
            ->pluck('id')
            ->toArray();

        $this->query = Problem::whereIn('lesson_id', $lesson_ids)
            ->where('is_modification', false)
            ->orderBy('lesson_id')
            ->orderBy('number_in_lesson');
    }
}
