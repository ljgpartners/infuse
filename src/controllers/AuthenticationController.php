<?php

class AuthenticationController extends BaseController {

	public $layout = 'infuse::layouts.application'; 

	public function __construct()
	{
		Config::set('auth.driver', 'verify');
		Config::set('auth.model', 'InfuseUser');
	}

	public function login()
	{
		$this->layout->title = "Login | Infuse";

		if (Input::has('infuseLoginSubmit')) {
		  $userdata = array(
		      'username'      => Input::get('infuseU'),
		      'password'      => Input::get('infuseP')
		  );

		  try {

		  	Auth::attempt($userdata);
		  	// we are now logged in, go to home
	      return Redirect::route('dashboard');

		  } catch (Toddish\Verify\UserNotFoundException $e)	{
				$error = "User can't be found";
			}	catch (Toddish\Verify\UserUnverifiedException $e)	{
				$error = "User isn't verified";
			}	catch (Toddish\Verify\UserDisabledException $e)	{
				$error = "User has been disabled";
			}	catch (Toddish\Verify\UserDeletedException $e)	{
				$error = "User has been deleted";
			}	catch (Toddish\Verify\UserPasswordIncorrectException $e)	{
				$error = "User has entered the wrong password";
			}

			// auth failure! lets go back to the login
	    $this->layout->content = View::make('infuse::authentication.login')->with("error", $error);

	    // Check if migrations have been ran
		} else if (Schema::hasTable('users') &&
							 Schema::hasTable('permissions') &&
							 Schema::hasTable('permission_role') &&
							 Schema::hasTable('roles') &&
							 Schema::hasTable('role_user') &&
							 Schema::hasTable('users') ) {

			if (Auth::check()) return Redirect::route('dashboard');
			$this->layout->content = View::make('infuse::authentication.login');
		} else {
			$this->layout->content = View::make('infuse::authentication.install');
		}
		
	}

	public function logout()
	{
		Auth::logout();
    return Redirect::route('login');
	}


	public function create_password()
	{
		$this->layout->title = "Create Password | Infuse";
		$this->layout->content = View::make('infuse::authentication.create_password');
	}

}