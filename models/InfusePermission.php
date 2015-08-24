<?php
use Toddish\Verify\Models\Permission as VerifyPermission;

class InfusePermission extends VerifyPermission {

    use Bpez\Infuse\InfuseModelLibrary;

    const INTERFACE_MODEL = false;

    protected $table = 'permissions';

    protected $rules = array();

    protected $errors;

    public $uploadFolder = "/uploads";

    public $hstore = array();

    public function roles()
    {
        return $this->belongsToMany('InfuseRole', 'permission_role', 'permission_id', 'role_id');
    }

}
