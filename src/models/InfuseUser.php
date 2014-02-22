<?php
use Toddish\Verify\Models\User as VerifyUser;

class InfuseUser extends VerifyUser {

	protected $table = 'users';

  public function roles()
  {
    return $this->belongsToMany('InfuseRole', 'role_user', 'user_id', 'role_id');
  }

  ///////////////////////////////////////////////////////////
  // InfuseEloquent declarations below
  ///////////////////////////////////////////////////////////
  protected $rules = array(
        'username' => "required|unique:users,username,[id]",
        'email'  => 'required|email|unique:users,email,[id]"'
    );

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
      return strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR).$this->{$column};
  }


  public function sendPasswordCreateEmail()
  {
  	$email = $this->email;
  	$server = $_SERVER['SERVER_NAME'];
		$data = array("full_name" => $this->full_name, "username" => $this->username);
  	
  	Mail::send('infuse::emails.user_created', $data  , function($message)  use ($email, $server) {
	    $message->from("no-reply@{$server}");
	    $message->subject('[Infuse] New User Created');
	    $message->to($email); 
		});
  }

}