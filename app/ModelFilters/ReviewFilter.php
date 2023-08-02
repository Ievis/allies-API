<?php

namespace App\ModelFilters;

use App\Models\Review;
use EloquentFilter\ModelFilter;

class ReviewFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function setup()
    {
        $this->query = Review::with([
            'user',
            'results',
        ]);
    }
}
