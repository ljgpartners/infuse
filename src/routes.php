<?php

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

Route::any('login', array('as' => 'login', 'prefix' => "admin", 'uses' => 'AuthenticationController@login') );

Route::group(array('prefix' => "admin", 'before' => array('InfuseAuth', 'WebService')), function()
{  
  Route::get('logout', array('as' => 'logout', 'uses' => 'AuthenticationController@logout') );
  Route::get('/', function(){ return Redirect::route('login'); });
  
  Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'InfuseController@dashboard') );
  Route::any('user', array('as' => 'user', 'uses' => 'InfuseController@user') );
  Route::any('permission', array('as' => 'permission', 'uses' => 'InfuseController@permission') );
  Route::any('role', array('as' => 'role', 'uses' => 'InfuseController@role') );
  Route::any('resource/{resource}', array('as' => 'resource', 'uses' => 'InfuseController@resource'));
  Route::any('resource/{resource}/child', array('as' => 'child', 'uses' => 'InfuseController@child'));   

});

Route::controller('password', 'RemindersController');



/*
|--------------------------------------------------------------------------
| Filters
|--------------------------------------------------------------------------
*/

Route::filter('InfuseAuth', function()
{
	if (Auth::guest()) return Redirect::route('login'); 
});


Route::filter('WebService', function()
{
  if (Request::ajax()) return Response::json(WebService::process());
});








?>