<?php

namespace App\ModelFilters;

use App\Models\Category;
use EloquentFilter\ModelFilter;

class CategoryFilter extends ModelFilter
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

    public function category($category)
    {
        $category_id = Category::where('slug', $category)?->first('id')?->id;

        return empty($category_id)
            ? $this->where('category_id', $category)
            : $this->where('category_id', $category_id);
    }

    public function setup()
    {
        //
    }
}
