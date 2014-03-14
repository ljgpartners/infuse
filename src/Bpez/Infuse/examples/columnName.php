<?php

$scaffold = new Scafffold(new \Illuminate\View\Environment, new \InfuseUser, new DB, \Illuminate\Http\Request $request);

$scaffold->model($config['model']);

$scaffold->columnName(array("column" => someColumn, "newName" => "someName"));

$scaffold->columnName(array(
	array("column" => someColumn, "newName" => "someName"),
	array("column" => someColumn2, "newName" => "someName2")
));

?>