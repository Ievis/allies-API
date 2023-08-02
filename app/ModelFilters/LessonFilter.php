<?php

namespace App\ModelFilters;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Services\TokenService;
use App\Services\UserService;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LessonFilter extends ModelFilter
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

    public function course($course_id)
    {
        $course_id = Course::findOrFail($course_id)->id;
        return $this->where('course_id', $course_id);
    }

    public function tags($tags)
    {
        $tag_query = Lesson::query();

        foreach ((array)$tags as $tag) {
            $tag_query->when(request()->input('tags_id'), function ($query) use ($tag) {
                return $query->orWhere('id', $tag);
            });
            $tag_query->when(request()->input('tags'), function ($query) use ($tag) {
                return $query->orWhere('name', $tag);
            });
        }

        $tags = $tag_query->get();
        $lessons = collect();
        foreach ($tags as $tag) {
            $lessons->push($tag->lessons);
        }
        $lesson_ids = $lessons->collapse()->unique('id')->pluck('id');

        return $this->whereIn('id', $lesson_ids);
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

        $this->query = Lesson::whereIn('course_id', $course_ids)
            ->where('is_modification', false)
            ->orderBy('course_id')
            ->orderBy('number_in_course');
    }
}
