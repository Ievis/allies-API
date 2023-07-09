<?php

namespace App\Services;

use App\Models\Lesson;
use App\Models\Modification;
use App\Models\Problem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ModificationService
{
    private $data;
    private $user;
    private $model;
    private $model_id;
    private $modification;
    private $models;
    private $modification_type;
    private $model_mapping;

    public function __construct(array $data, User $user, Lesson|Problem $model, int|null $model_id = null)
    {
        $model_mapping = new ModelMapping($model);
        $this->model_mapping = $model_mapping->getModelMapping();

        $this->data = $data;
        $this->modification_type = empty($data) ? 'delete' : null;
        if (is_null($this->modification_type)) $this->data[$this->model_mapping['model_number']] = (int)$this->data[$this->model_mapping['model_number']];
        $this->user = $user;
        $this->model = $model;
        $this->model_id = $model_id;

        $this->models = $model::where($this->model_mapping['model_id'], $this->data[$this->model_mapping['model_id']] ?? $model->{$this->model_mapping['model_id']})
            ->where('is_modification', false)
            ->orderBy($this->model_mapping['model_number'])
            ->get();
    }

    public function setModification(Modification $modification)
    {
        $this->modification = $modification;
        $this->modification_type = $modification->modification_type;
    }

    public function createModification(): self
    {
        $this->modification_type = $this->modification_type ?? (empty($this->model_id) ? 'create' : 'update');

        $this->modification = Modification::create([
            'modifiable_type' => get_class($this->model),
            'modification_type' => $this->modification_type,
            'modifiable_id' => $this->model->id,
            'current_id' => $this->model_id ?? $this->model->id,
            'user_id' => $this->user->id,
        ]);

        return $this;
    }

    public function resolveModification(bool $decision = true): Lesson|Problem
    {
        $this->modification->update([
            'is_resolved' => true,
            'is_applied' => $decision
        ]);

        return $decision
            ? $this->applyModification()
            : $this->misuseModification();
    }

    private function applyModification(): Lesson|Problem
    {
        return $this->{$this->modification_type}();
    }

    private function create(): Lesson|Problem
    {
        $this->models->push($this->model);
        $this->upsertModel();

        $this->model->is_modification = false;
        $this->model->save();
        $this->model->is_applied = true;
        return $this->model;
    }

    private $number = 1;

    private function update(): Lesson|Problem
    {
        $old_model = (new (get_class($this->model)))::find($this->model_id);
        $this->data[$this->model_mapping['model_number']] = $this->data[$this->model_mapping['model_number']] ?? $old_model->{$this->model_mapping['model_number']};

        $this->models = $this->models->map(function ($item) use ($old_model) {
            if ($item->id === $old_model->id) {
                $item = $this->model;
                $item->id = $old_model->id;
                $item->is_modification = true;
                $item->{$this->model_mapping['model_number']} = $this->data[$this->model_mapping['model_number']];
            }
            return $item;
        });

        $this->models = $this->models->map(function ($item) use ($old_model) {
            if ($item->id !== $old_model->id) {
                $item->{$this->model_mapping['model_number']} = $this->number;
                $this->number++;
            }
            return $item;
        });

        $this->upsertModel();

        $this->model->is_applied = true;
        return $this->model;
    }

    private function delete()
    {
        $this->models = $this->models->reject(function ($item) {
            return $item->id === $this->model->id;
        });
        $this->model->delete();

        $data = $this->models->map(function ($item) {
            $item->{$this->model_mapping['model_number']} = $this->number;
            $this->number++;

            return $item->getAttributes();
        })->toArray();

        DB::table($this->model_mapping['table'])->upsert(
            $data, ['id', $this->model_mapping['model_number']]);

        return $this->model;
    }

    private function misuseModification(): Lesson|Problem
    {
        $this->model->is_applied = false;

        return $this->model;
    }

    private function upsertModel()
    {
        $data = collect();
        foreach ($this->models as $model) {
            $number_in_group = $model->{$this->model_mapping['model_number']};
            if ($number_in_group >= $this->data[$this->model_mapping['model_number']]) {
                $number_in_group = $model->is_modification
                    ? $number_in_group
                    : $number_in_group + 1;
            }
            $model->{$this->model_mapping['model_number']} = $number_in_group;

            $data->push($model);
        }

        $data = $data->map(function ($item) {
            if ($this->modification_type === 'delete') return false;
            $item->is_modification = false;
            return $item->getAttributes();
        })->toArray();

        DB::table($this->model_mapping['table'])->upsert(
            $data, ['id', $this->model_mapping['model_number']]);
    }
}
