<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public static function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public static function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }
}
