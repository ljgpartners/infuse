<?php

// Provides validation rules for all models
 // Also adds functionailty for files

class InfuseEloquent extends Eloquent
{
    protected $rules = array();

    protected $errors;

    public $timestamps = true;

    public function validate($data)
    {
        // make a new validator object
        $v = Validator::make($data, $this->rules);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->messages();
            return false;
        }

        // validation pass
        return true;
    }

    public function errors()
    {
        return $this->errors;
    }

    


    // Added functionailty for files
    protected $uploadFolder = "/uploads";

    public function uploadPath($column)
    {
        return strtolower($_SERVER['DOCUMENT_ROOT'].$this->uploadFolder.DIRECTORY_SEPARATOR
                    .get_class($this).DIRECTORY_SEPARATOR
                    .$column.DIRECTORY_SEPARATOR);
    }

    public function url($column)
    {
        return strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                    .get_class($this).DIRECTORY_SEPARATOR
                    .$column.DIRECTORY_SEPARATOR).$this->{$column};
    }


}

?>