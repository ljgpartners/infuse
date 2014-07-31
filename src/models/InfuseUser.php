<?php
use Toddish\Verify\Models\User as VerifyUser;
use Illuminate\Support\Facades\Password;

class InfuseUser extends VerifyUser {

  use Bpez\Infuse\InfuseEloquentLibrary, Bpez\Infuse\InfuseUserLibrary;

	protected $table = 'users';

	public $timestamps = true;

	public static function boot()
  {
  	parent::boot();

  	InfuseUser::created(function($user)
		{
			// Check if infuse super skip if inital create
		  if ($user->id != 1) {
		    $email = $user->email;
		    $server = $_SERVER['SERVER_NAME'];
		    $data = array("full_name" => $user->full_name, "username" => $user->username, "email" => $email, "create" => true);

		    
		    Config::set('auth.reminder.email', 'infuse::emails.reminder');

		    View::composer('infuse::emails.reminder', function($view) use ($data) {
		      $view->with($data);
		    });

		    Password::remind(array("email" => $email), function($message)  use ($server)  {
		      $message->subject('[Infuse] User Account Created');
		      $message->from("no-reply@{$server}");
		    });
		  }

		});
  }



  /**
   * Is the User a certain Level
   *
   * @param  integer $level
   * @param  string $modifier [description]
   * @return boolean
   */ /*
  public function level($level, $modifier = '>=')
  {
      $to_check = $this->getToCheck();

      $max = -1;
      $min = 100;
      $levels = array();

      foreach ($to_check->roles as $role)
      {
          $max = $role->level > $max
              ? $role->level
              : $max;

          $min = $role->level < $min
              ? $role->level
              : $min;

          $levels[] = $role->level;
      }
      
      switch ($modifier)
      {
          case '=':
              return in_array($level, $levels);
              break;

          case '>=':
              return $max >= $level;
              break;

          case '>':
              return $max > $level;
              break;

          case '<=':
              return $min <= $level;
              break;

          case '<':
              return $min < $level;
              break;

          case '!=':
              return !in_array($level, $levels);
              break;

          default:
              return false;
              break;
      }
  } */
  
}