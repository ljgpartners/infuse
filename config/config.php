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
	| Hstore allow key removal when hstore schema.
	*/
	'hstore_allow_key_removal' => false,

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
	| Uses base_url_uploaded_assets_local for uploaded assets
	| base url in all evnvironments.
	*/
	'base_url_for_uploaded_assets_always' => false,

	/*
	| Link to main site
	*/
	'admin_site_link' => "/",

	/*
	| Compnay logo
	*/
	"company_logo" => "",

	/*
	| Compnay logo
	*/
	"site_title" => "Admin",

	/*
	| Overide dashboard template a custom template from your views
	*/
	'dashboard_template' => '',

	/*
	| Add external javascript
	*/
	'add_javascript' => '',

    /*
	| CKFinder Configuration
	*/
	'licenseName' => '',
	'licenseKey' => '',
	's3bucket' => '',
	's3key' => '',
	's3secret' => '',
	's3baseUrl' => '',
	's3rootPath' => '',



	/*
	|--------------------------------------------------------------------------
	| Navigation
	|--------------------------------------------------------------------------
	|
	|	Manage the navagation here.
	|
	*/

	'navigation' => array(


		'group_1' => array(
			"name" => "Group 1",

			'sub_group_1' => array(
				"name" => "Sub group 1",
				"description" => "Aliquam facilisis leo et aliquam fermentum. Vestibulum mattis purus sed magna ullamcorper ornare. Morbi sit amet orci non nulla euismod facilisis. Aenean aliquet elit rhoncus quam iaculis, non feugiat urna posuere.",
				'3rd Lvl sub section 1' => 'map_to_some_file',
				'3rd Lvl sub section 2' => 'map_to_some_file_2',
			),

		),

		'group_2' => array(
			"name" => "Group 2",
		),

		'group_3' => array(
			"name" => "Group 3",
		),


		'group_4' => array(
			"name" => "Group 4",
		),










	),



);
