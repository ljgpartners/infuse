<?php namespace Bpez\Infuse;

// Provides validation rules for all models
 // Also adds functionailty for files

class InfuseEloquent extends \Eloquent {

  protected $rules = array();

  protected $errors;
  
  protected $uploadFolder = "/uploads";

  public $timestamps = true;

  
  
}

?>