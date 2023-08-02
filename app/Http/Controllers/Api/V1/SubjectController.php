<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostSubjectRequest;
use App\Http\Resources\V1\Subject\SubjectCollectionResource;
use App\Http\Resources\V1\Subject\SubjectResource;
use App\Models\Subject;
use App\Services\SubjectService;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request): SubjectCollectionResource
    {
        $subjects = Subject::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Subject::count());

        return new SubjectCollectionResource($subjects);
    }

    public function show(Subject $subject): SubjectResource
    {
        return new SubjectResource($subject);
    }

    public function store(PostSubjectRequest $request): SubjectResource
    {
        $data = $request->validated();
        $subject = SubjectService::createSubject($data);

        return new SubjectResource($subject);
    }

    public function update(PostSubjectRequest $request, Subject $subject): SubjectResource
    {
        $data = $request->validated();
        $subject = SubjectService::updateSubject($subject, $data);

        return new SubjectResource($subject);
    }

    public function delete(Subject $subject): SubjectResource
    {
        $subject->delete();

        return new SubjectResource($subject);
    }
}
