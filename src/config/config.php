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
	| Base url for uploaded assets only on local environment. 
	| On production assets are served locally.  Leave blank to 
	| serve locally on local environemnt.
	*/
	'base_url_uploaded_assets_local' => "",

	/*
	| Link to main site
	*/
	'admin_site_link' => url('/'),

	/*
	| Overide dashboard template a custom template from your views
	*/
	'dashboard_template' => '', 


	/*
	| The "expire" time is the number of minutes that the reminder should be
	| considered valid. This security feature keeps tokens short-lived so
	| they have less time to be guessed. You may change this as needed.
	*/
	'reminder_expire' => 60,


	/*
	| Theme customization (css & images)
	*/
	"titles" => array(
		"login_above_logo" => "",
		"login_site_title" => "Site Title Here"
	),

	"logo_margin_top" => 0,
	"snipe_css" => "background-repeat: no-repeat; background-position: 100% 0px;",

	"images" => array( 
		/*
		| Url path
		*/
		"logo" => "", 
		"login_page_background" => "/packages/bpez/infuse/images/infuseBackgroundNew.jpg",
		"top_nav_background_snipe" => ""
	), 

	"colors" => array(
		/*
		| Hex colors
		*/
		"login_text" => "#CBCECD", 
		"login_input_text" => "#CBCECD", 
		"top_nav_background_color" => "#333333",
		"nav_border_color" => "#000000",
		"nav_highlight" => "#A01A27",
		"button_color" => "#A01A27",
		"button_alt_color" => "#33352E",

		"side_menu_background" => "#FFFFFF",
		"side_menu_open_background" => "#E4E4E4",
		"side_menu_section_title" => "#0093CF",
		"side_menu_sub_section_title" => "#000000",
		"side_menu_border" => "#F8F8F8"
	), 
	
		
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
			'Model' => 'model_snake_case'
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
