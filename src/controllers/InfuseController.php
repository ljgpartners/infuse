<?php

use Bpez\Infuse\Scaffold;

class InfuseController extends BaseController {

	public $layout = 'infuse::layouts.application';

	public function __construct()
	{	
		$this->beforeFilter('InfuseAuth');
		View::share('navigation', Config::get('infuse::navigation'));
		View::share('adminTitle', Config::get('infuse::admin_title'));
		View::share('adminLink', Config::get('infuse::admin_title_link'));
	}

	public function dashboard()
	{
		$this->layout->title = "Dashboard | Infuse";
		$this->layout->content = View::make('infuse::infuse.dashboard');
	}

	public function resource($resource, $child = "")
	{
		$this->layout->title = "Resource | Infuse";
		if ($child == "") { 
			$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', Config::get('infuse::resources')["$resource"]['scaffold']->build()); 
		} else { 
			$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', Config::get('infuse::resources')["$resource"]["children"]["$child"]->build()); 
		}
		
	}

	public function admin_user()
	{	
		$this->layout->title = "Admin User | Infuse";
		$scaffold = Scaffold::newInstance(new InfuseAdminUser, new DB)
			->name("Admin User")
			->infuseLogin()
			->limit(10)
			->order(array("order" => "desc", "column" => "last_login_date"))
			->listColumns(array("username", "email", "logins", "last_login_date"))
			->build();

		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold);
	}

}