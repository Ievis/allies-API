<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\PostCategoryRequest;
use App\Http\Resources\V1\Category\CategoryCollectionResource;
use App\Http\Resources\V1\Category\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): CategoryCollectionResource
    {
        $categories = Category::filter($request->all())->simplePaginateFilter($request->input('per_page') ?? Category::count());

        return new CategoryCollectionResource($categories);
    }

    public function show(Category $category): CategoryResource
    {
        return new CategoryResource($category);
    }

    public function store(PostCategoryRequest $request): CategoryResource
    {
        $data = $request->validated();
        $category = CategoryService::createCategory($data);

        return new CategoryResource($category);
    }

    public function update(PostCategoryRequest $request, Category $category): CategoryResource
    {
        $data = $request->validated();
        $category = CategoryService::updateCategory($category, $data);

        return new CategoryResource($category);
    }

    public function delete(Category $category): CategoryResource
    {
        $category->delete();

        return new CategoryResource($category);
    }
}
