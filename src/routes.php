<?php

Route::filter('InfuseAuth', function()
{
	if (Auth::guest()) return Redirect::route('login'); 
});

Route::group(array('prefix' => 'admin'), function()
{
		Route::get('/', function(){ return Redirect::route('login'); });
    Route::any('login', array('as' => 'login', 'uses' => 'AuthenticationController@login') );
    Route::get('logout', array('as' => 'logout', 'uses' => 'AuthenticationController@logout') );
    Route::any('create-password', array('as' => 'create_password', 'uses' => 'AuthenticationController@create_password') );

		Route::get('dashboard', array('as' => 'dashboard', 'uses' => 'InfuseController@dashboard') );
		Route::any('user', array('as' => 'user', 'uses' => 'InfuseController@user') );
    Route::any('permission', array('as' => 'permission', 'uses' => 'InfuseController@permission') );
    Route::any('role', array('as' => 'role', 'uses' => 'InfuseController@role') );
		Route::any('resource/{resource}', array('as' => 'resource', 'uses' => 'InfuseController@resource'));
    Route::any('resource/{resource}/child', array('as' => 'child', 'uses' => 'InfuseController@child'));
    

});


Route::controller('password', 'RemindersController');


/*
Route::get('password/reset', array(
  'uses' => 'PasswordController@remind',
  'as' => 'password.remind'
));

Route::post('password/reset', array(
  'uses' => 'PasswordController@request',
  'as' => 'password.request'
));

Route::get('password/reset/{token}', array(
  'uses' => 'PasswordController@reset',
  'as' => 'password.reset'
));


*/

?>