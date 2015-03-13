<?php
use Toddish\Verify\Models\User as VerifyUser;
use Illuminate\Auth\Passwords\TokenRepositoryInterface;

use Illuminate\Support\Facades\Password;

class InfuseUser extends VerifyUser {

  use Bpez\Infuse\InfuseModelLibrary, Bpez\Infuse\InfuseUserLibrary;

	protected $table = 'users';

	public $timestamps = true;

	public static function boot()
  {
  	parent::boot();

  	self::created(function($user)
		{
			// Check if infuse super skip if inital create
		  if ($user->id != 1) {
		    
        $server = (isset($_SERVER['SERVER_NAME']))? $_SERVER['SERVER_NAME'] : "localhost";

        $tempPass = str_random(10);
        $user->setPasswordAttribute($tempPass);
        $user->save();

        $data = array("user" => $user, "password" => $tempPass);
        $email = $user->email;

        \Mail::send('infuse::emails.created_user', $data, function($message)  use ($email, $server) {
          $message->from("no-reply@{$server}");
          $message->subject('[Infuse] User Account Created');
          $message->to($email); 
        });
       
		  }

		});
  }


  
}