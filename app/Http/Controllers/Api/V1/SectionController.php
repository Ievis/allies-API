<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostSectionRequest;
use App\Http\Resources\V1\Section\SectionCollectionResource;
use App\Http\Resources\V1\Section\SectionResource;
use App\Models\Section;
use App\Services\SectionService;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request): SectionCollectionResource
    {
        $sections = Section::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Section::count());

        return new SectionCollectionResource($sections);
    }

    public function store(PostSectionRequest $request): SectionResource
    {
        $data = $request->validated();
        $section = SectionService::createSection($data);

        return new SectionResource($section);
    }

    public function show(Section $section): SectionResource
    {
        return new SectionResource($section);
    }

    public function update(PostSectionRequest $request, Section $section): SectionResource
    {
        $data = $request->validated();
        $section = SectionService::updateSection($data, $section);

        return new SectionResource($section);
    }

    public function delete(Section $section): SectionResource
    {
        $section = SectionService::deleteSection($section);

        return new SectionResource($section);
    }
}
