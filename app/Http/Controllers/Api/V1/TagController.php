<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostTagRequest;
use App\Http\Resources\V1\Tag\TagCollectionResource;
use App\Http\Resources\V1\Tag\TagResource;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request): TagCollectionResource
    {
        $tags = Tag::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Tag::count());

        return new TagCollectionResource($tags);
    }

    public function show(Tag $tag): TagResource
    {
        return new TagResource($tag);
    }

    public function store(PostTagRequest $request): TagResource
    {
        $data = $request->validated();
        $tag = TagService::createTag($data);

        return new TagResource($tag);
    }

    public function update(PostTagRequest $request, Tag $tag): TagResource
    {
        $data = $request->validated();
        $tag = TagService::updateTag($data, $tag);

        return new TagResource($tag);
    }

    public function delete(Tag $tag): TagResource
    {
        $tag = TagService::deleteTag($tag);

        return new TagResource($tag);
    }
}
