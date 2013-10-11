<?php
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableInterface;

class InfuseAdminUser extends InfuseEloquent implements UserInterface, RemindableInterface {

	protected $rules = array(
        'username' => "required|unique:infuse_admin_users,username,[id]",
        'email'  => 'required|email|unique:infuse_admin_users,email,[id]"',
        'password' => 'required_without:id|confirmed'
    );

	protected $fillable = array('fname','lname','email','password','create_at','updated_at');

  /**
  * The attributes excluded from the model's JSON form.
  *
  * @var array
  */
  protected $hidden = array('password');

  /**
  * Get the unique identifier for the user.
  *
  * @return mixed
  */
  public function getAuthIdentifier()
  {
      return $this->getKey();
  }

  /**
  * Get the password for the user.
  *
  * @return string
  */
  public function getAuthPassword()
  {
      return $this->password;
  }

  /**
  * Get the e-mail address where password reminders are sent.
  *
  * @return string
  */
  public function getReminderEmail()
  {
      return $this->email;
  }

}

?>
