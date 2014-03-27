<?php
use Toddish\Verify\Models\Role as VerifyRole;

class InfuseRole extends VerifyRole {

	protected $table = 'roles';

	public function users()
  {
    return $this->belongsToMany('InfuseUser', 'role_user', 'role_id', 'user_id');
  }

  public function permissions()
  {
    return $this->belongsToMany('InfusePermission', 'permission_role', 'role_id', 'permission_id');
  }


	/*
  |--------------------------------------------------------------------------
  | InfuseEloquent declarations below
  |--------------------------------------------------------------------------
  */

	protected $rules = array();

  protected $errors;

  public $timestamps = true;

  public function validate($data)
  {
      $replace = ($this->getKey() > 0) ? $this->getKey() : '';
      foreach ($this->rules as $key => $rule) {
          $this->rules[$key] = str_replace('[id]', $replace, $rule);
      }

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

  protected function processRules(array $rules)
  {
      $id = $this->getKey();
      array_walk($rules, function(&$item) use ($id)
      {
          // Replace placeholders
          $item = stripos($item, ':id:') !== false ? str_ireplace(':id:', $id, $item) : $item;
      });

      return $rules;
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
    if (filter_var($this->{$column}, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
      return $this->{$column};
    } else { 
    return strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR).$this->{$column};
    }
      
  }
  
}