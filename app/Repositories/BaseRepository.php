<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    protected $model;
    
    public function __construct(Model $model)
    {
        $this->model = $model;
    }
    
    public function getAll($columns = ['*'], $relations = [])
    {
        return $this->model->with($relations)->get($columns);
    }
    
    public function getPaginated($perPage = 15, $columns = ['*'], $relations = [])
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }
    
    public function getById($id, $columns = ['*'], $relations = [])
    {
        return $this->model->with($relations)->findOrFail($id, $columns);
    }
    
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }
    
    public function update($id, array $attributes)
    {
        $record = $this->getById($id);
        $record->update($attributes);
        return $record;
    }
    
    public function delete($id)
    {
        return $this->getById($id)->delete();
    }
} 