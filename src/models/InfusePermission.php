<?php
use Toddish\Verify\Models\Permission as VerifyPermission;

class InfusePermission extends VerifyPermission {

  use InfuseEloquentLibrary;

  protected $table = 'permissions';

  protected $rules = array();

  protected $errors;
  
  protected $uploadFolder = "/uploads";

  public function roles()
  {
    return $this->belongsToMany('InfuseRole', 'permission_role', 'permission_id', 'role_id');
  }

}