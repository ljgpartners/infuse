<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| InfusePermission Scaffold config
	|--------------------------------------------------------------------------
	*/		

	'model' => new InfusePermission,
	'name' => 'Infuse Permission',
	'limit' => 100,
	'order' => array("order" => "desc", "column" => "created_at"),
	'listColumns' => array("name", "description"),
	'manyToMany' => 
		array(
			array("InfuseRole", "role_id", "InfusePermission", "permission_id", "permission_role", "name", "name")
		)

);

