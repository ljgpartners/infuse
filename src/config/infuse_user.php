<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| InfuseUser Scaffold config
	|--------------------------------------------------------------------------
	*/		

	'scaffold' => 
		Scaffold::newInstance(new InfuseUser, new DB)
			->name("Infuse User")
			->infuseLogin()
			->limit(10)
			->order(array("order" => "desc", "column" => "created_at"))
			->listColumns(array("username", "email"))
			->manyToMany(array(
				array("InfuseRole", "role_id", "InfuseUser", "user_id", "role_user", "name", "username")
			))					

);
