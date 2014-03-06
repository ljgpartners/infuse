<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| ExampleResource Scaffold config
	|--------------------------------------------------------------------------
	*/

	'scaffold' => 
		Scaffold::newInstance(new ExampleResource, new DB)
			->modelDescription("Our neighbors speak spotlights")
			->limit(30)
			->order(array("order" => "desc", "column" => "created_at"))
			->listColumns(array("update_type", "created_at"))
			->addCkeditor("content")
			->hasMany(array(
				// Use the @image to display photo
				array("CollectionAsset", "Collection Asset", array("image@image", "youtube_video")),
				array("SpotlightImage", "Spotlight Images", array("image")),
				// category column is a select for use this method for filling in 
				array("ShopCategory", "Shop Category", array( array("category" => Category::orderBy('name', 'asc')->get(array('id', 'name'))->toArray())))
				// Add display order to children
				array("Photo", "Photo", array("photo@image", "display_order"), array("order_column" => "display_order", "order_direction" => "ASC"))
			)),

	'children' => array(

		"spotlight_image" =>  
			Scaffold::newInstance(new SpotlightImage, new DB)
				 ->name("Splotlight Image")
				 ->fileUpload("image", "/uploads", array(
						array('width', 'Must have width of 490px', 490),
						array('height', 'Must have height of 250px', 250)
				 )),

		"shop_category" => 
			Scaffold::newInstance(new ShopCategory, new DB)
				->name("Shop Category")
				->modelDescription("")
				->addSelect("category", Category::orderBy('name', 'asc')->get(array('id', 'name'))->toArray())
								
	)

);


/////////////////////////////////////////////////////////////////////////////


return array(

	/*
	|--------------------------------------------------------------------------
	| ExampleResource2 Scaffold config
	|--------------------------------------------------------------------------
	*/

	'scaffold' => 
		Scaffold::newInstance(new ExampleResource2, new DB)
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

);


/////////////////////////////////////////////////////////////////////////////


return array(

	/*
	|--------------------------------------------------------------------------
	| ExampleResource3 Scaffold config
	|--------------------------------------------------------------------------
	*/

	'scaffold' => 
		Scaffold::newInstance(new ExampleResource3, new DB)
			->modelDescription("Construction Coffee Meetings")
			->limit(30)
			->order(array("order" => "desc", "column" => "created_at"))
			->listColumns(array("title"))
			->addSelect("category", Category::orderBy('name', 'asc')->get(array('id', 'name'))->toArray()),
			->addSelect("restaurant", Restaurant::orderBy('name', 'asc')->get(array('id', 'name'))->toArray(), true)

);

