<?php
use Illuminate\Support\Facades\Password;

/*
|--------------------------------------------------------------------------
| RemindersController 
|--------------------------------------------------------------------------
*/

class RemindersController extends BaseController {

	public $layout = 'infuse::layouts.application'; 

	public function __construct()
	{
		Config::set('auth.driver', 'verify');
		Config::set('auth.model', 'InfuseUser');
		Config::set('auth.reminder.email', 'infuse::emails.reminder');
		Config::set('auth.reminder.expire', Config::get('infuse::reminder_expire'));
		View::share("superAdmin", false);
	}

	/**
	 * Display the password reminder view.
	 *
	 * @return Response
	 */
	public function getRemind()
	{
		$this->layout->title = "Get reminder | Infuse";

		$response = array();

		if (Session::has('error')) {
			$response['error'] = Session::get('error');
			if (Session::has('reason'))
				$response['reason'] = Session::get('reason');
		}

		if (Session::has('status')) 
			$response['success'] = Session::get('status');

		$this->layout->content = View::make('infuse::password.remind', $response);
	}

	/**
	 * Handle a POST request to remind a user of their password.
	 *
	 * @return Response
	 */
	public function postRemind()
	{
		$server = $_SERVER['SERVER_NAME'];

		try {

			$response = Password::remind(Input::only('email'), function($message)  use ($server)	{
		    $message->subject('[Infuse] Password Reset');
		    $message->from("no-reply@{$server}");
			});

	  } catch (Toddish\Verify\UserNotFoundException $e)	{
			$error = "User can't be found";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserUnverifiedException $e)	{
			$error = "User isn't verified";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserDisabledException $e)	{
			$error = "User has been disabled";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserDeletedException $e)	{
			$error = "User has been deleted";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserPasswordIncorrectException $e)	{
			$error = "User has entered the wrong password";
			return Redirect::back()->with('error', $error);
		}

		switch ($response)
		{
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::REMINDER_SENT:
				return Redirect::back()->with('status', Lang::get($response));
		}
	}

	/**
	 * Display the password reset view for the given token.
	 *
	 * @param  string  $token
	 * @return Response
	 */
	public function getReset($token = null)
	{
		$this->layout->title = "Reset Password | Infuse";
		if (is_null($token)) App::abort(404);

		$response = array();

		if (Session::has('error'))
			$response['error'] = Session::get('error');

		$reset = DB::table('password_reminders')->where('token', '=', $token)->first();
		
		$response['token'] = $token;

		if (count($reset) > 0) {
			$user = InfuseUser::where("email", "=", $reset->email)->first();
			if (count($user) > 0 && ($user->password == "" || $user->password == null)) {
				$response['create'] = true;
			} else {
				$response['create'] = false;
			}
		} else {
			$response['create'] = false; 
		}
		

		$this->layout->content = View::make('infuse::password.reset', $response);
	}

	/**
	 * Handle a POST request to reset a user's password.
	 *
	 * @return Response
	 */
	public function postReset() 
	{
		$credentials = Input::only('email', 'password', 'password_confirmation', 'token');

		try {

			$response = Password::reset($credentials, function($user, $password)	{
				$user->setPasswordAttribute($password);
				$user->save();
			});


	  } catch (Toddish\Verify\UserNotFoundException $e)	{
			$error = "User can't be found";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserUnverifiedException $e)	{
			$error = "User isn't verified";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserDisabledException $e)	{
			$error = "User has been disabled";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserDeletedException $e)	{
			$error = "User has been deleted";
			return Redirect::back()->with('error', $error);
		}	catch (Toddish\Verify\UserPasswordIncorrectException $e)	{
			$error = "User has entered the wrong password";
			return Redirect::back()->with('error', $error);
		}

		

		switch ($response)
		{
			case Password::INVALID_PASSWORD:
			case Password::INVALID_TOKEN:
			case Password::INVALID_USER:
				return Redirect::back()->with('error', Lang::get($response));

			case Password::PASSWORD_RESET:
				return Redirect::to('/admin/login');
		}
	}

}