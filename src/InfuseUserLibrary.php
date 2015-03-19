<?php namespace Bpez\Infuse;

trait InfuseUserLibrary {

	protected $rules = array(
	  'username' => "required|unique:users,username",
	  'email'  => 'required|email|unique:users,email'
  );

  /*
  protected $rules = array(
    'username' => "required|unique:users,username,[id]",
    'email'  => 'required|email|unique:users,email,[id]'
  );
  */

  protected $errors;

  // Added functionailty for files
  public $uploadFolder = "/uploads";

  public function roles()
  {
    return $this->belongsToMany('InfuseRole', 'role_user', 'user_id', 'role_id');
  }

	public function sendRequestResetPasswordPage()
  {
  	$email = $this->email;
  	$server = $_SERVER['SERVER_NAME'];

  	\Mail::send('infuse::emails.request_reset', array(), function($message)  use ($email, $server) {
	    $message->from("no-reply@{$server}");
	    $message->subject('[Infuse] Request Reset Password ');
	    $message->to($email); 
		});
  }

 

}
