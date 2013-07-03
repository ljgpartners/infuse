<?php

class AuthenticationController extends BaseController {

	public $layout = 'infuse::layouts.application'; 

	public function __construct()
	{
		//Set infuse Model for admin login
		Config::set('auth.model', 'InfuseAdminUser');
	}

	public function login()
	{
		$this->layout->title = "Login | Infuse";

		if (Input::has('infuseLoginSubmit')) {
		  $userdata = array(
		      'username'      => Input::get('infuseU'),
		      'password'      => Input::get('infuseP')
		  );

		  if (Auth::attempt($userdata)){
		  		$user = InfuseAdminUser::find(Auth::user()->id);
		  		$user->logins += 1;
		  		if (isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
		  			$user->last_login_ip = $_SERVER['REMOTE_ADDR'];
		  			$user->last_login_date = date("Y-m-d");
		  		}
		  		$user->save();
	        // we are now logged in, go to home
	        return Redirect::route('dashboard');
	    } else {
	        // auth failure! lets go back to the login
	        return Redirect::route('login')->with('login_errors', true);
	    }
	    $this->layout->content = View::make('infuse::authentication.login');
		} else if (Schema::hasTable('infuse_admin_users')) {
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

}