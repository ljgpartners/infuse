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
			$data = Config::get('infuse::resources');
			$data = $data["{$resource}"]['scaffold']->config();
			$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
			$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold); 
		} else { 
			$data = Config::get('infuse::resources');
			$data = $data["{$resource}"]["children"]["{$child}"]->config();
			$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
			$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold);  
		}
		
	}

	public function admin_user()
	{	
		$this->layout->title = "Admin User | Infuse";
		$data = Scaffold::newInstance(new InfuseAdminUser, new DB)
			->name("Admin User")
			->infuseLogin()
			->limit(10)
			->order(array("order" => "desc", "column" => "last_login_date"))
			->listColumns(array("username", "email", "logins", "last_login_date"))
			->config();

		$scaffold = View::make(Scaffold::getBladeTemplate())->with('data', $data);
		$this->layout->content = View::make('infuse::infuse.resource')->with('scaffold', $scaffold);
	}

}