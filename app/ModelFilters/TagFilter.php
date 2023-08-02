<?php

namespace App\ModelFilters;

use App\Models\Tag;
use EloquentFilter\ModelFilter;

class TagFilter extends ModelFilter
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

    public function setup()
    {
        //
    }
}
