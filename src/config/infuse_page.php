<?php

$navigationSection = array();

for ($x = 1; $x <= count(Config::get('infuse::navigation')); $x++) {
  array_push($navigationSection, array("id" => $x, "name" => $x));
} 

if (empty($navigationSectio)) {
	array_push($navigationSection, array("id" => 1, "name" => 1));
}

return array(

	/*
	|--------------------------------------------------------------------------
	| InfuseUser Scaffold config
	|--------------------------------------------------------------------------
	*/		

	'model' => new InfusePage,
	'name' => 'Page',
	'modelDescription' => "Pages for infuse",
	'limit' => 10,
	'order' => array("order" => "desc", "column" => "created_at"),
	'listColumns' => array("title", "display_order"),
	'addSelect' => array("column" => 'navigation_section', "array" => $navigationSection),

);


