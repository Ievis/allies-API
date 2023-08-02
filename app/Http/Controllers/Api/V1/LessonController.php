<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostLessonRequest;
use App\Http\Resources\V1\Lesson\LessonCollectionResource;
use App\Http\Resources\V1\Lesson\LessonResource;
use App\Models\Lesson;
use App\Services\LessonService;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function index(Request $request): LessonCollectionResource
    {
        $lessons = Lesson::filter($request->all())->simplePaginateFilter($request->per_page ?? Lesson::count());

        return new LessonCollectionResource($lessons);
    }

    public function store(PostLessonRequest $request): LessonResource
    {
        $data = $request->validated();
        $lesson = LessonService::createLesson($data);

        return new LessonResource($lesson);
    }

    public function show(Lesson $lesson): LessonResource
    {
        return new LessonResource($lesson);
    }

    public function update(PostLessonRequest $request, Lesson $lesson): LessonResource
    {
        $data = $request->validated();
        $lesson = LessonService::updateLesson($lesson, $data);

        return new LessonResource($lesson);
    }

    public function delete(Lesson $lesson): LessonResource
    {
        $deleted_lesson = LessonService::deleteLesson($lesson);

        return new LessonResource($deleted_lesson);
    }
}
