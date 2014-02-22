<?php

use Bpez\Infuse\Scaffold;

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
		"logo" => "", // /packages/bpez/infuse/images/infuseLogo.png
		"login_page_background" => "/packages/bpez/infuse/images/infuseBackgroundNew.jpg",
		"top_nav_background_snipe" => ""
	), 

	"colors" => array(
		/*
		| Hex colors
		*/
		"top_nav_background_color" => "#333333",
		"nav_border_color" => "#000000",
		"nav_highlight" => "#A01A27",
		"button_color" => "#A01A27",
		"button_alt_color" => "#33352E",

		"side_menu_background" => "#FFFFFF",
		"side_menu_open_background" => "#E4E4E4",
		"side_menu_section_title" => "#0093CF",
		"side_menu_sub_section_title" => "#000000",
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

	/*
	|--------------------------------------------------------------------------
	| Resources (Models)
	|--------------------------------------------------------------------------
	|
	|	Resources are the models that you want to be managed. Example below:
	|
	|--------------------------------------------------------------------------

	'resources' => array(

		'meetings' => array(
					'scaffold' => Scaffold::newInstance(new Meeting, new DB)
												->modelDescription("Construction Coffee Meetings")
												->limit(30)
												->order(array("order" => "desc", "column" => "created_at"))
												->listColumns(array("title"))
												->addSelect("category", Category::orderBy('name', 'asc')->get(array('id', 'name'))->toArray()),
												->addSelect("restaurant", Restaurant::orderBy('name', 'asc')->get(array('id', 'name'))->toArray(), true)

				),

			'constuction_updates' => array(
					'scaffold' => Scaffold::newInstance(new ConstructionUpdate, new DB)
												->modelDescription("Construction Updates")
												->limit(30)
												->order(array("order" => "desc", "column" => "created_at"))
												->listColumns(array("update_type", "created_at"))
												->addCkeditor("details_text")
												->describeColumn("store_hours", "To break to a new line use \",\" character. ")
												->addSelect("update_type", array(
														array("id" => "building_projects", "name" => "Major NBCU Building Projects"),
														array("id" => "infrastructure_projects", "name" => "Infrastructure Projects"),
														array("id" => "metro_pedestrian_bridge", "name" => "Metro Pedestrian Bridge"),
													))
												->addCkeditor("update_text")
				),

			'spotlights' => array(
					'scaffold' => Scaffold::newInstance(new Spotlight, new DB)
												->modelDescription("Our neighbors speak spotlights")
												->limit(30)
												->order(array("order" => "desc", "column" => "created_at"))
												->listColumns(array("update_type", "created_at"))
												->addCkeditor("content")
												->hasMany(array(
													array("SpotlightImage", "Spotlight Images", array("image")),
												)),
					'children' => array(
						"spotlight_image" =>  Scaffold::newInstance(new SpotlightImage, new DB)
																	 ->name("Splotlight Image")
																	 ->fileUpload("image", "/uploads", array(
																			array('width', 'Must have width of 490px', 490),
																			array('height', 'Must have height of 250px', 250)
																	 ))
												
					)
				)

		
	
	|--------------------------------------------------------------------------*/

	'resources' => array(


	)
		



	

);
