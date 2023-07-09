<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostTeacherDescriptionRequest;
use App\Http\Resources\V1\TeacherDescription\TeacherDescriptionCollectionResource;
use App\Http\Resources\V1\TeacherDescription\TeacherDescriptionResource;
use App\Models\TeacherDescription;
use App\Services\TeacherDescriptionService;
use Illuminate\Http\Request;

class TeacherDescriptionController extends Controller
{
    public function index(Request $request): TeacherDescriptionCollectionResource
    {
        $teacherDescriptions = TeacherDescription::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? TeacherDescription::count());

        return new TeacherDescriptionCollectionResource($teacherDescriptions);
    }

    public function show(TeacherDescription $teacherDescription): TeacherDescriptionResource
    {
        return new TeacherDescriptionResource($teacherDescription);
    }

    public function store(PostTeacherDescriptionRequest $request): TeacherDescriptionResource
    {
        $data = $request->validated();
        $teacherDescription = TeacherDescriptionService::createTeacherDescription($data);

        return new TeacherDescriptionResource($teacherDescription);
    }

    public function update(PostTeacherDescriptionRequest $request, TeacherDescription $teacherDescription): TeacherDescriptionResource
    {
        $data = $request->validated();
        $teacher_description = TeacherDescriptionService::updateTeacherDescription($data, $teacherDescription);

        return new TeacherDescriptionResource($teacher_description);
    }

    public function delete(TeacherDescription $teacherDescription): TeacherDescriptionResource
    {
        $teacher_description = TeacherDescriptionService::deleteTeacherDescription($teacherDescription);

        return new TeacherDescriptionResource($teacher_description);
    }
}
