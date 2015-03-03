<?php
use Toddish\Verify\Models\Role as VerifyRole;

class InfuseRole extends VerifyRole {

  use Bpez\Infuse\InfuseModelLibrary;

	protected $table = 'roles';

  protected $rules = array();

  protected $errors;
  
  protected $uploadFolder = "/uploads";

	public function users()
  {
    return $this->belongsToMany('InfuseUser', 'role_user', 'role_id', 'user_id');
  }

  public function permissions()
  {
    return $this->belongsToMany('InfusePermission', 'permission_role', 'role_id', 'permission_id');
  }
	
  
}