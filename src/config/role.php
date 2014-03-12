<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| InfuseRole Scaffold config
	|--------------------------------------------------------------------------
	*/		

	'model' => new InfuseRole,
	'name' => 'Infuse Role',
	'limit' => 100,
	'order' => array("order" => "desc", "column" => "created_at"),
	'listColumns' => array("name", "description"),
	'manyToMany' => 
		array(
			array("InfusePermission", "permission_id", "InfuseRole", "role_id", "permission_role", "name", "name"),
			array("InfuseUser", "user_id", "InfuseRole", "role_id", "role_user", "username", "name")
		)

);
