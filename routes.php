<?php

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

Route::any('login', array('as' => 'login', 'prefix' => "admin", 'uses' => 'AuthenticationController@login') );

Route::group(array('prefix' => "admin", 'before' => array('InfuseAuth', 'WebServiceFilter')), function()
{  
  Route::get('logout', array('as' => 'logout', 'uses' => 'AuthenticationController@logout') );
  Route::get('/', function(){ return Redirect::route('login'); });
  
  Route::any('dashboard', array('as' => 'dashboard', 'uses' => 'InfuseController@dashboard') );
  Route::any('user', array('as' => 'user', 'uses' => 'InfuseController@user') );
  Route::any('permission', array('as' => 'permission', 'uses' => 'InfuseController@permission') );
  Route::any('role', array('as' => 'role', 'uses' => 'InfuseController@role') );
  Route::resource('page', 'InfusePageController');
  Route::any('resource/{firstNavLevel}/{secondNavLevel}/{resource}/child', array('as' => 'child', 'uses' => 'InfuseController@child'));
  Route::any('resource/{firstNavLevel}/{secondNavLevel}/{resource}', array('as' => 'resource', 'uses' => 'InfuseController@resource'));
  Route::any('call-function', array('as' => 'call_function', 'uses' => 'InfuseController@call_function') );

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


Route::filter('WebServiceFilter', function()
{
  if (Request::ajax()) return Response::json(WebService::process());
});








?>