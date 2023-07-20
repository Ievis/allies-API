<?php

namespace App\ModelFilters;

use App\Models\Course;
use App\Models\Subject;
use App\Models\User;
use App\Services\UserService;
use EloquentFilter\ModelFilter;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UserFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    private $user;
    protected $camel_cased_methods = false;

    public function name($name)
    {
        return $this->where('name', 'LIKE', "%$name%");
    }

    public function subject($subject)
    {
        $subject = Subject::findOrFail($subject);

        $users = $subject
            ->courses()
            ->with(['users' => function ($query) {
                $is_teacher = request('is_teacher') ?? true;
                $role_ids = $is_teacher ? [User::TEACHER, User::MAIN_TEACHER] : [User::STUDENT];
                $role_ids = UserService::isAdmin($this->user)
                    ? $role_ids
                    : [User::TEACHER, User::MAIN_TEACHER];

                $query->whereIn('role_id', $role_ids);
            }])
            ->get()
            ->pluck('users')
            ->reject(function ($user) {
                return empty($user->toArray());
            })
            ->collapse();

        $user_ids = $users->isEmpty()
            ? []
            : $users->pluck('id')->all();

        return $this->whereIn('id', $user_ids);
    }

    public function is_expired(bool $expired)
    {
        $now = ((array)Carbon::now()->toDateTime())['date'];

        $course_user_records = $expired
            ? DB::table('course_user')->whereDate('expires_at', '<', $now)
            : DB::table('course_user')->whereDate('expires_at', '>', $now);

        $user_ids = $course_user_records->where('is_teacher', false)
            ->get()
            ->pluck('user_id');

        return $this->whereIn('id', $user_ids);
    }

    public function course($course)
    {
        $course = Course::findOrFail($course);
        $user_ids = $course->users()->get()->pluck('id');

        return $this->whereIn('id', $user_ids);
    }

    public function is_teacher($is_teacher)
    {
        return $is_teacher
            ? $this
                ->whereHas('coursesTeacherOrMainTeacher')
                ->orderBy('role_id', 'DESC')
            : $this
                ->whereIn('role_id', [User::STUDENT])
                ->orderBy('role_id', 'DESC');
    }

    public function setup()
    {
        $this->query = User::with([
            'teacher_descriptions',
            'coursesTeacher.subject',
            'coursesTeacher.category',
            'coursesMainTeacher.subject',
            'coursesMainTeacher.category',
        ]);
        $this->user = auth()->user();
        if (UserService::isAdmin($this->user)) return $this->orderBy('role_id', 'DESC');

        return $this
            ->whereHas('coursesTeacherOrMainTeacher')
            ->orderBy('role_id', 'DESC');
    }
}
