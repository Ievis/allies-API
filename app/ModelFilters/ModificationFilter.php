<?php

namespace App\ModelFilters;

use App\Models\Modification;
use EloquentFilter\ModelFilter;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModificationFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    protected $camel_cased_methods = false;

    public function for($for)
    {
        if (!in_array($for, Modification::NAMING_EN)) throw new NotFoundHttpException();

        return $this->where('modifiable_type', Modification::NAMING_EN[$for]);
    }

    public function user_id($user_id)
    {
        return $this->where('user_id', $user);
    }

    public function setup()
    {
        //
    }
}
