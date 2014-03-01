<?php

use Bpez\Infuse\Scaffold;
use Bpez\Infuse\Util;
use Bpez\Infuse\WebService;

// Debug
use Illuminate\Support\Facades\Log;

class InfuseController extends BaseController {

	public $layout = 'infuse::layouts.application';

	public function __construct()
	{	
		Config::set('auth.driver', 'verify');
		Config::set('auth.model', 'InfuseUser');
		$this->beforeFilter('InfuseAuth'); 

		if (!Auth::guest()) {
			$this->user = Auth::user();
			View::share("user", $this->user);
			View::share("superAdmin", $this->user->is('Super Admin'));
		}
		View::share('navigation', Config::get('infuse::navigation'));
		
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
		
		if (Request::ajax()) {
			$response = WebService::newInstance(new DB);
			return Response::json($response);
		}

		Util::stackReset();
		Util::stackPush($resource, Input::get('id', null), $uri);
		
		$data = Config::get('infuse::resources');
		$redirect = $data["{$resource}"]['scaffold']->checkPermissions($this->user, Util::childBackLink());
		if ($redirect)
			return Redirect::to($redirect);
		
		$data = $data["{$resource}"]['scaffold']->loadUser($this->user)->config();
		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold); 

		
	}

	public function child($resource)
	{
		$this->layout->title = "Resource | Infuse";
		View::share('manageActive', true);
		$uri = Request::path();
		$child = Input::get('stack');
		
		if (Request::ajax()) {
			$response = WebService::newInstance(new DB);
			return Response::json($response);
		}

		if (Input::has("pop"))
			Util::stackPop(); 
		Util::stackPush($child, Input::get('id', null), $uri); 

		$data = Config::get('infuse::resources');
		$redirect = $data["{$resource}"]["children"]["{$child}"]->checkPermissions($this->user, Util::childBackLink());

		if ($redirect)
			return Redirect::to($redirect);

		

		$data = $data["{$resource}"]["children"]["{$child}"]->loadUser($this->user)->config();
		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold);  
		
		
	}

	public function user()
	{	
		$this->layout->title = "User | Infuse";
		View::share('userActive', true);
		$uri = Request::path();

		$data = Config::get('infuse::user_resource');
		
		$redirect = $data['scaffold']->checkPermissions($this->user, $uri);
		if ($redirect)
			return Redirect::to($redirect);
			
		$data = $data['scaffold']->loadUser($this->user)->config();
		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold); 
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
		$data = Scaffold::newInstance(new InfusePermission, new DB)
			->name("Infuse Permission")
			->limit(100)
			->order(array("order" => "desc", "column" => "created_at"))
			->listColumns(array("name", "description"))
			->manyToMany(array(
				array("InfuseRole", "role_id", "InfusePermission", "permission_id", "permission_role", "name", "name")
			))
			->loadUser($this->user)->config();

		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold); 
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
		$data = Scaffold::newInstance(new InfuseRole, new DB)
			->name("Infuse Role")
			->limit(100)
			->order(array("order" => "desc", "column" => "created_at"))
			->listColumns(array("name", "description"))
			->manyToMany(array(
				array("InfusePermission", "permission_id", "InfuseRole", "role_id", "permission_role", "name", "name"),
				array("InfuseUser", "user_id", "InfuseRole", "role_id", "role_user", "username", "name")
			)) 
			->loadUser($this->user)->config();

		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold); 
	}

}