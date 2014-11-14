<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Miscellaneous Admin Options
	|--------------------------------------------------------------------------
	|
	| Miscellaneous options for admin tool.
	|
	*/

	/*
	| Toggles role/permission authentication. 
	*/
	'role_permission' => false,

	/*
	| Toggles to have only one role per user otherwise if false many can be assigned to user. 
	*/
	'one_role_per_user' => false,

	/*
	| Format Carbon object updated_at & created_at timestamp. 
	*/
	'format_laravel_timestamp' => "n/d/Y",

	/*
	| Base url for uploaded assets only on local environment. 
	| On production assets are served locally. Leave blank to 
	| serve locally on local environemnt.
	*/
	'base_url_uploaded_assets_local' => "",

	/*
	| Link to main site
	*/
	'admin_site_link' => url('/'),

	/*
	| Compnay logo
	*/
	"company_logo" => "",

	/*
	| Compnay logo
	*/
	"site_title" => "",

	/*
	| Overide dashboard template a custom template from your views
	*/
	'dashboard_template' => '', 

	/*
	| Add external javascript
	*/
	'add_javascript' => '', 


	/*
	| The "expire" time is the number of minutes that the reminder should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	*/
	'reminder_expire' => 60,
	
		
	/*
	|--------------------------------------------------------------------------
	| Navigation
	|--------------------------------------------------------------------------
	|
	|	Manage the navagation here.
	|
	*/

	'navigation' => array(

		'Section 1' => array(
			'Model1' => 'model1_snake_case',
		),

		'Section 2 ' => array(
			'Model2' => 'model2_snake_case',
			'Model3' => 'model3_snake_case',
			'Model4' => 'model4_snake_case'
		),

		'Section 3' => array(
			'Model5' => 'model5_snake_case'
		),


	),

	

);
