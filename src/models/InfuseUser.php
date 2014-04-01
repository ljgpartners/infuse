<?php
use Toddish\Verify\Models\User as VerifyUser;
use Illuminate\Support\Facades\Password;

class InfuseUser extends VerifyUser {

	protected $table = 'users';

  public function roles()
  {
    return $this->belongsToMany('InfuseRole', 'role_user', 'user_id', 'role_id');
  }

  /*
  |--------------------------------------------------------------------------
  | InfuseEloquent declarations below
  |--------------------------------------------------------------------------
  */

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
    if (filter_var($this->{$column}, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
      return $this->{$column};
    } else { 
    return strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR).$this->{$column};
    }
      
  }


  public function sendRequestResetPasswordPage()
  {
  	$email = $this->email;
  	$server = $_SERVER['SERVER_NAME'];

  	Mail::send('infuse::emails.request_reset', array(), function($message)  use ($email, $server) {
	    $message->from("no-reply@{$server}");
	    $message->subject('[Infuse] Request Reset Password ');
	    $message->to($email); 
		});
  }


  public function save(array $options = array())
  {
    if (!$this->exists) { 
      $saved = parent::save($options);

      // Check if infuse super skip if inital create
      if ($saved->id != 1) {
        $email = $this->email;
        $server = $_SERVER['SERVER_NAME'];
        $data = array("full_name" => $this->full_name, "username" => $this->username, "email" => $email, "create" => true);

        
        Config::set('auth.reminder.email', 'infuse::emails.reminder');

        View::composer('infuse::emails.reminder', function($view) use ($data) {
          $view->with($data);
        });

        Password::remind(array("email" => $email), function($message)  use ($server)  {
          $message->subject('[Infuse] User Account Created');
          $message->from("no-reply@{$server}");
        });
      }
      

      return $saved;

    } else {
      return parent::save($options);
    }
    
  }
  
}