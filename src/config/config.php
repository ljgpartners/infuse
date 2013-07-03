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

	'admin_title' => 'Title of Website',

	'admin_title_link' => 'http://localhost:8888/',

	/*
	|--------------------------------------------------------------------------
	| Navigation
	|--------------------------------------------------------------------------
	|
	|	Manage the navagation here.
	|
	*/

	'navigation' => array(

		'Main' => array(
			'Shops' => 'shop',
			'Categories' => 'category',
			'Restaurants' => 'restaurant',
			'Cuisines' => 'cuisine'
		),

		'Play' => array(
				'Gallery' => 'gallery',
				'Event' => 'event'
			),

		'Other' => array(
				'ContactSubmission' => 'contact_submission',
				'News' => 'news',
				"HomeAds" => 'home_ads'
			)

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

		'shop' => array(
			'scaffold' => Scaffold::newInstance(new Shop, new DB)
											->modelDescription("Shops that are at the village.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->addCkeditor("details_text")
											->fileUpload("logo", "/uploads", array(
													array('maxWidth', 'Must have width of 249px', 249)
												))
											->fileUpload("detail_background_image", "/uploads", array(
													array('width', 'Must have width of 896px', 896),
													array('height', 'Must have height of 360px', 360)
												))
											->listColumns(array("name"))
											->describeColumn("store_hours", "To break to a new line use \",\" character. ")
											->hasMany(array(
													"special" => array("Special", array("name"))
												))
											->addSelect("category", Category::orderBy('name', 'asc')->get(array('id', 'name'))->toArray()),

			'children' => array(
					"special" =>  Scaffold::newInstance(new Special, new DB)
											->modelDescription("Specials are located in the specials section of the site.")
				)
		),

		'category' => array(
			'scaffold' => Scaffold::newInstance(new Category, new DB)
											->modelDescription("Categories are use to categorize the shops.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("name"))
		),

		'restaurant' => array(
			'scaffold' => Scaffold::newInstance(new Restaurant, new DB)
											->modelDescription("Restaurants that are located in the dine section of the site.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->addCkeditor("main_text")
											->fileUpload("logo", "/uploads", array(
													array('width', 'Must have width of 632px', 632),
													array('height', 'Must have height of 360px', 360)
												))
											->listColumns(array("name"))
											->describeColumn("side_bar_text", "To break to a new line use \"|\" character. ")
											->addSelect("cuisine", Cuisine::orderBy('name', 'asc')->get(array('id', 'name'))->toArray())
											
		),

		'cuisine' => array(
			'scaffold' => Scaffold::newInstance(new Cuisine, new DB)
											->modelDescription("Cuisines are use to categorize the restaurants in the dine section.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("name"))
		),
		
		'gallery' => array(
			'scaffold' => Scaffold::newInstance(new Gallery, new DB)
											->modelDescription("Galleries are located in the play section of the site.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("name"))
											->addSelect("restaurant", Restaurant::orderBy('name', 'asc')->get(array('id', 'name'))->toArray(), true)
											->addSelect("event", VamEvent::orderBy('name', 'asc')->get(array('id', 'name'))->toArray(), true)
											->hasMany(array(
													"photo" => array("Photo", array("photo", "display_order"))
												)),

			'children' => array(
					"photo" =>  Scaffold::newInstance(new Photo, new DB)
											->modelDescription("Photos belong to the galleries")
											->fileUpload("photo", "/uploads", array(
													array('width', 'Must have width of 710px', 710),
													array('height', 'Must have height of 470px', 470)
												))
				)
		),

		'event' => array(
			'scaffold' => Scaffold::newInstance(new VamEvent, new DB)
											->name("Event")
											->modelDescription("Events are in the play section of the site.")
											->addCkeditor("main_text")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("name", "date_time_start", "date_time_end"))

		),

		'contact_submission' => array(
			'scaffold' => Scaffold::newInstance(new ContactSubmission, new DB)
											->name("Contact Submissions")
											->modelDescription("Submissions from the contact page.")
											->limit(30)
											->addCkeditor("comments_questions")
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("name", "email", "created_at"))

		),


		'news' => array(
			'scaffold' => Scaffold::newInstance(new News, new DB)
											->modelDescription("New articles.")
											->limit(30)
											->addCkeditor("main_text")
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("title", "created_at"))
											->fileUpload("image", "/uploads", array(
													array('width', 'Must have width of 632px', 632),
													array('height', 'Must have height of 360px', 360)
												))

		),

		'home_ads' => array(
			'scaffold' => Scaffold::newInstance(new HomeAds, new DB)
											->name("Home Ad")
											->modelDescription("Bottom 3 ads that are displayed on the home page.")
											->limit(30)
											->order(array("order" => "desc", "column" => "created_at"))
											->listColumns(array("text", "display_order"))
											->fileUpload("image", "/uploads", array(
													array('width', 'Must have width of 98px', 98),
													array('height', 'Must have height of 98px', 98)
												))

		),
	
	)
	
	|--------------------------------------------------------------------------*/

	'resources' => array()
		



	

);
