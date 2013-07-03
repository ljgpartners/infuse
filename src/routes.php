<?php

Route::filter('InfuseAuth', function()
{
	Config::set('auth.model', 'InfuseAdminUser');
	if (Auth::guest()) return Redirect::route('login');
	 
});

Route::group(array('prefix' => 'admin'), function()
{
		Route::get('/', function(){ return Redirect::route('login'); });
    Route::any('login', array('as' => 'login', 'uses' => 'AuthenticationController@login') );
    Route::get('logout', array('as' => 'logout', 'uses' => 'AuthenticationController@logout') );

		Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'InfuseController@dashboard') );
		Route::any('admin-user', array('as' => 'admin_user', 'uses' => 'InfuseController@admin_user') );
		Route::any('resource/{resource}/{child?}', array('as' => 'resource', 'uses' => 'InfuseController@resource'));

});




?>