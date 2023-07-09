<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCourseRequest;
use App\Http\Resources\V1\Course\CourseResource;
use App\Http\Resources\V1\Course\CourseCollectionResource;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request): CourseCollectionResource
    {
        $courses = Course::filter($request->all())->simplePaginateFilter($request->per_page ?? 10);

        return new CourseCollectionResource($courses);
    }

    public function store(PostCourseRequest $request): CourseResource
    {
        $data = $request->validated();
        $course = CourseService::createCourse($data);

        return new CourseResource($course);
    }

    public function show(Course $course): CourseResource
    {
        CourseService::ensureIsVisible($course);
        return new CourseResource($course);
    }

    public function update(PostCourseRequest $request, Course $course): CourseResource
    {
        $data = $request->validated();
        $course = CourseService::updateCourse($course, $data);

        return new CourseResource($course);
    }

    public function delete(Course $course): CourseResource
    {
        $course->delete();

        return new CourseResource($course);
    }
}
