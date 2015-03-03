<?php

use Bpez\Infuse\Util;

/*
|--------------------------------------------------------------------------
| InfuseController 
|--------------------------------------------------------------------------
| Main logic for infuse combined together in this controller
|
*/

class InfuseController extends Bpez\Infuse\BaseController {


	public function __construct(\InfuseUser $user)
	{	
		$this->user = $user;
		View::share("user", $this->user);
		View::share("superAdmin", $this->user->is('Super Admin'));
		View::share('navigation', Config::get('infuse::config.navigation'));
		$rolePermission = (\Config::get("infuse::config.role_permission"))? true : false;
		View::share('rolePermission', $rolePermission);
		View::share('databaseConnectionType', \Config::get('database.default')); 
	}


	public function dashboard()
	{	//\Session::flush();
		$this->layout->title = "Dashboard | Infuse";
		View::share('dashboardActive', true);
		$content = (Config::get('infuse::config.dashboard_template') != "")? view(Config::get('infuse::config.dashboard_template')): view('infuse::infuse.dashboard');
		$this->layout->content =  $content;
	}



	public function resource($firstNavLevel, $secondNavLevel, $resource) 
	{
		$this->layout->title =  "Resource | Infuse"; 
		View::share('manageActive', true);
		$uri = Request::path();

		Util::stackReset();
		Util::stackPush($resource, Input::get('id', null), $uri);
		
		$this->loadResource($resource);
		$config = Config::get("infuse::{$resource}");
		
			$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);


		$redirect = $scaffold->checkPermissions($uri);
		if ($redirect)
			return Redirect::to($redirect);
		

		if ($resource != 'infuse_page') {
			$this->layout->firstNavLevel = $firstNavLevel; 
			$this->layout->secondNavLevel = $secondNavLevel;
		}
		$this->layout->resource = $resource;

		$this->layout->content =  $scaffold->process();
	}

	public function child($firstNavLevel, $secondNavLevel, $resource)
	{
		$this->layout->title =  "Resource | Infuse";
		View::share('manageActive', true);
		$uri = Request::path();
		$child = Input::get('stack');
		
		if (Util::stackFixBrowserBack($child))
			Util::stackPop(); 
		Util::stackPush($child, Input::get('id', null), $uri); 
		
		$this->loadResource($resource);
		$config = Config::get("infuse::{$resource}.children.{$child}");
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions(Util::childBackLink());
		if ($redirect)
			return Redirect::to($redirect);

		$this->layout->firstNavLevel = $firstNavLevel; 
		$this->layout->secondNavLevel = $secondNavLevel;
		$this->layout->resource = $resource;
			
		$this->layout->content =  $scaffold->process();
	}

	public function user()
	{	
		$this->layout->title =  "User | Infuse";
		View::share('userActive', true);
		$uri = Request::path();
		
		$this->loadResource("infuse_user");
		$config = Config::get('infuse::infuse_user');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions($uri);
		if ($redirect)
			return Redirect::to($redirect);
			
		$this->layout->content =  $scaffold->process();
	}


	public function permission()
	{	
		if (!$this->user->is('Super Admin')) {
			Util::flash(array(
				"message" => "{$this->user->username} is not authorized to manage permissions.", 
				"type" => "warning"
				)
			);
			return Redirect::route('dashboard');
		}
		
		$this->layout->title =  "Permissions | Infuse";

		$this->loadResource("permission");
		$config = Config::get('infuse::permission');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);
			
		$this->layout->content =  $scaffold->process();
	}

	public function role()
	{	
		if (!$this->user->is('Super Admin')) {
			Util::flash(array(
				"message" => "{$this->user->username} is not authorized to manage roles.", 
				"type" => "warning"
				)
			);
			return Redirect::route('dashboard');
		}
		
		$this->layout->title =  "Roles | Infuse";
		$this->loadResource("role");
		$config = Config::get('infuse::role');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);
			
		$this->layout->content =  $scaffold->process();
	}

	public function call_function()
	{	
		set_time_limit(1800);
		$callFunction = Util::get("cf");
    $callClass = Util::get("cc");

    try {
    	$success = $callClass::$callFunction();
    } catch (Exception $e) {
    	Util::flash(array(
				"message" => "Failed to call {$callFunction} action. {$e->getMessage()}", 
				"type" => "error"
				)
			);
			return Redirect::route('dashboard');
    }
		

		if ($success) {
			Util::flash(array(
				"message" => "Succesfully called {$callFunction} action.", 
				"type" => "success"
				)
			);
		} else {
			Util::flash(array(
				"message" => "Failed to call {$callFunction} action.", 
				"type" => "error"
				)
			);
		}
			
			
		return Redirect::route('dashboard');
	}


}