<?php

/*
|--------------------------------------------------------------------------
| InfuseController 
|--------------------------------------------------------------------------
| Main logic for infuse combined together in this controller
|
*/

class InfuseController extends BaseController {

	public $layout = 'infuse::layouts.application';

	public function __construct(\InfuseUser $user)
	{	
		$this->user = $user;
		View::share("user", $this->user);
		View::share("superAdmin", $this->user->is('Super Admin'));
		View::share('navigation', Config::get('infuse::navigation'));
		$rolePermission = (\Config::get("infuse::role_permission"))? true : false;
		View::share('rolePermission', $rolePermission);
	}


	public function dashboard()
	{
		$this->layout->title = "Dashboard | Infuse";
		View::share('dashboardActive', true);
		$content = (Config::get('infuse::dashboard_template') != "")? View::make(Config::get('infuse::dashboard_template')): View::make('infuse::infuse.dashboard');
		$this->layout->content = $content;
	}

	public function resource($resource) 
	{
		$this->layout->title = "Resource | Infuse"; 
		View::share('manageActive', true);
		$uri = Request::path();

		Util::stackReset();
		Util::stackPush($resource, Input::get('id', null), $uri);
		
		$config = Config::get("infuse::{$resource}");

		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions(Util::childBackLink());
		if ($redirect)
			return Redirect::to($redirect);
			
		$this->layout->content = $scaffold->process(); 
	}

	public function child($resource)
	{
		$this->layout->title = "Resource | Infuse";
		View::share('manageActive', true);
		$uri = Request::path();
		$child = Input::get('stack');
		
		if (Util::stackFixBrowserBack($child))
			Util::stackPop(); 
		Util::stackPush($child, Input::get('id', null), $uri); 
		
		
		$config = Config::get("infuse::{$resource}.children.{$child}");
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions(Util::childBackLink());
		if ($redirect)
			return Redirect::to($redirect);
			
		$this->layout->content = $scaffold->process();
	}

	public function user()
	{	
		$this->layout->title = "User | Infuse";
		View::share('userActive', true);
		$uri = Request::path();
		
		$config = Config::get('infuse::infuse_user');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions($uri);
		if ($redirect)
			return Redirect::to($redirect);
			
		$this->layout->content = $scaffold->process();
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
		
		$this->layout->title = "Permissions | Infuse";
		$config = Config::get('infuse::permission');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);
			
		$this->layout->content = $scaffold->process(); 
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
		
		$this->layout->title = "Roles | Infuse";
		$config = Config::get('infuse::role');
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);
			
		$this->layout->content = $scaffold->process(); 
	}

}