<?php namespace Bpez\Infuse;

/****************************************************
 * Interface for importFromModel API fuction for class
 * to still work with infuse. Use with ImportFromServiceModel
 ****************************************************/

interface ImportFromServiceModelInterface {

    public function where($column, $operator, $value);

    public function orWhere($column, $operator, $value);

    public function get();

    public function count();

    public function orderBy($orderByColumn, $orderByDirection);

    public function take($limit);

    public function skip($offset);

    public function whereIn($column, $value);

    public function getColumns();
}
