<?php

class ORMResultsSet extends ArrayObject
{

    public function __construct($results, $model)
    {
        if (!class_exists($model))
        {
            throw new ErrorException("model class $model not found");
        }
        while ($row = $results->fetch())
        {
            $temp = $this->hydrate_model($row, $model);
            $pk = $model::get_primary_key();
            $this[$temp->$pk] = $temp;
        }
    }

    public function create_sub_model($fields, $model, string $name)
    {
        if (!class_exists($model))
        {
            throw new ErrorException("model class $model not found");
        }
        foreach ($this as $row_key => $row)
        {
            $sub_model = new $model();
            foreach ($fields as $key)
            {
                $sub_model->$key = $row->$key;
                unset($this[$row_key]->$key);
            }
            $row->$name = $sub_model;
        }
        
    }



    public function fieldExists($field)
    {
        if (empty($this))
        {
            return false;
        }
        foreach ($this as $row)
        {
            if (isset($row->$field))
            {
                return true;
            }
        }
        return false;
    }

    protected function hydrate_model($row, $model):ORMModel
    {
        $model = new $model();
        foreach ($row as $key => $value)
        {
            $model->$key = $value;
        }
        return $model;
    }
}