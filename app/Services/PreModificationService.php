<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Problem;

class PreModificationService
{
    private $model_mapping;
    private $model_name;
    private $user;
    private $model;
    private $data;
    private $old_model;

    public function __construct($data, $model_name)
    {
        $this->data = $data;
        $this->user = auth()->user();

        $this->model_name = $model_name;
        $this->model_mapping = (new ModelMapping($model_name))->getModelMapping();
    }

    public function createPreparation()
    {
        $this->data[$this->model_mapping['model_number']] = $this->data[$this->model_mapping['model_number']] ?? $this->model_name::where($this->model_mapping['model_id'], $this->data[$this->model_mapping['model_id']])->where('is_modification', false)->count() + 1;
        $this->data['is_modification'] = true;
        $this->model = $this->model_name::find($this->model_name::create($this->data)->id);
    }

    public function updatePreparation(Lesson|Problem $old_model)
    {
        $this->old_model = $old_model;

        $this->data[$this->model_mapping['model_number']] =
            $this->data[$this->model_mapping['model_number']]
            ?? $this->old_model->{$this->model_mapping['model_number']};
        $this->data['is_modification'] = true;
        $this->model = $this->model_name::find($this->model_name::create($this->data)->id);
    }

    /**
     * @return mixed
     */
    public function getOldModel()
    {
        return $this->old_model;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
