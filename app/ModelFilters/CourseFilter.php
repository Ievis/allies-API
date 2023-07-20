<?php

namespace App\ModelFilters;

use App\Models\Category;
use App\Models\Course;
use App\Models\Tag;
use App\Services\UserService;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Facades\DB;

class CourseFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    protected $camel_cased_methods = false;

    public function name($name)
    {
        return $this->where('name', 'LIKE', "%$name%");
    }

    public function tags($tags)
    {
        $tag_query = Tag::query();

        foreach ((array)$tags as $tag) {
            $tag_query->when(request()->input('tags_id'), function ($query) use ($tag) {
                return $query->orWhere('id', $tag);
            });
            $tag_query->when(request()->input('tags'), function ($query) use ($tag) {
                return $query->orWhere('name', $tag);
            });
        }

        $tags_id = $tag_query
            ->get()
            ->pluck('id')
            ->toArray();

        $course_ids = Tag::whereIn('id', $tags_id)
            ->with('courses')
            ->get()
            ->pluck('courses')
            ->first()
            ->pluck('id')
            ->toArray();

        return $this->whereIn('id', $course_ids);
    }

    public function category($category)
    {
        return $this->where('category_id', $category);
    }

    public function subject($subject)
    {
        return $this->where('subject_id', $subject);
    }

    public function is_visible($is_visible)
    {
        $user = auth()->user();
        if (empty($user)) return $this;
        if (UserService::isAdmin($user)) return $this->where('is_visible', $is_visible);

        $course_ids = DB::table('course_user')->where('user_id', $user->id)
            ->where('is_teacher', true)
            ->pluck('course_id')
            ->toArray();

        return $this->where('is_visible', $is_visible)
            ->whereIn('id', $course_ids);
    }

    public function setup()
    {
        $route_name = request()->route()->getName();
        $this->query = $route_name === 'courses.index'
            ? Course::whereHas('usersMainTeacher')
                ->with([
                    'users',
                    'tags',
                    'subject',
                    'category',
                ])
            : Course::with('lessons', 'lessons.problems', 'tags');

        if (!is_null(request()->input('is_visible'))) return;
        $this->query = $this->query->where('is_visible', true);
    }
}
