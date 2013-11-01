<?php namespace Bpez\Infuse;

use Exception;
use Transit\Transit;
use Transit\Validator\ImageValidator;
use Illuminate\Support\Facades\Hash;

class Scaffold {

	private $model;
	private $db;
	private $columns;
	private $action;
	private $entries = array();
	private $header = array();
	private $name;
	private $limit = 10;
	private $order = array(
		"order" => "desc",
		"column" => "id"
		);
	private $hasMany = array();
	private $hasOne = false;
	private $description = "";
	private $list = array();
	private $infuseLogin = false;
	private $onlyOne = false;


	public function __construct($model, $db)
	{	
		if (!isset($_SESSION)) session_start();
		$this->action = Util::get("action");
		$this->model = $model;
		$this->db = $db;
		$this->name = get_class($model);
		
		$columns = $db::select("SHOW COLUMNS FROM ".$model->getTable());
		
		foreach ($columns as $column) {
			if ($column->Field != 'id' ) {
				if (strlen(strstr($column->Type, "varchar")) > 0) {
					$type = "varchar";
				} else if (strlen(strstr($column->Type, "tinyint")) > 0) {
					$type = "tinyint";
				} else if (strlen(strstr($column->Type, "int")) > 0) {
					$type = "int";
				} else {
					$type = $column->Type;
				}


				array_push($this->list, $column->Field);
				$this->columns["{$column->Field}"] = array(
						"field" => $column->Field,
						"type"  => $type
					);
			}
		}
		
	}


	public static function newInstance($model, $db)
  {
  	return new self($model, $db);
  }


	private function route()
	{
		switch ($this->action) {
			case 'l':
				$this->listAll();
				break;
			case 'e':
				$this->edit();
				break;
			case 's':
				$this->show();
				break;
			case 'd':
				$this->delete();
				break;
			case 'c':
				$this->create();
				break;
			case 'cd':
				$this->createDuplicate();
				break;
			case 'u':
				$this->update();
				break;
			case 'f':
				$this->listAllFilter();
				break;

			default:
				$this->listAll();
				break;
		}
	}

	private function listAll()
	{	
		$model = $this->model;
		$pagination = array(
			"limit" => $this->limit,
			"active_page" => 1,
			"count" => 0
		);
		
		$pagination['count'] = $model::count();
		$offset = 0;
		$page = Util::get("pg");
		if ($page && $page != 1 && $page != 'a') {
			$offset =  ($page-1) * $pagination['limit'];
			$pagination['active_page'] = $page;
		}

		if ($page == "a") {
			$prepareModel = $model::orderBy($this->order["column"], $this->order["order"]);
		} else {
			$prepareModel = $model::orderBy($this->order["column"], $this->order["order"])->take($pagination['limit'])->skip($offset);
		}

		if (Util::get("toCSV"))	{
			$this->toCSV($prepareModel);
		}

		$this->entries = $prepareModel->get();
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"onlyOne" => $this->onlyOne
			);
	}

	private function show()
	{	
		$model = $this->model;
		$this->header = array(
				"pagination" => array(),
				"name" => $this->name
			);
		$this->entries = $model::find(Util::get("id"));
	}

	private function edit()
	{
		$model = $this->model;
		$this->header = array(
				"edit" => true,
				"name" => $this->name,
				"associations" => $this->hasMany,
				"hasOneAssociation" => $this->hasOne,
				"onlyOne" => $this->onlyOne
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model::find(Util::get("id"));
		} else {
			$this->entries = Util::arrayToObject($post);
			$this->header['actualModel'] = $model::find($post["id"]);
		}
		
	}

	private function create()
	{
		$model = $this->model;
		$this->header = array(
				"name" => $this->name,
				"associations" => $this->hasMany,
				"hasOneAssociation" => $this->hasOne
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model;
		} else {
			$this->entries = Util::arrayToObject($post);
		}
		
	}

	private function createDuplicate()
	{
		$model = $this->model;
		$this->header = array(
				"name" => $this->name,
				"associations" => $this->hasMany
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model::find(Util::get("id"));
		} else {
			$this->entries = Util::arrayToObject($post);
		}
		
	}

	private function delete()
	{
		$model = $this->model;
		$model::find(Util::get("id"))->delete();
		Util::flash(array(
			"message" => "Deleted {$this->name} with id of ".Util::get("id").".", 
			"type" => "error"
			)
		); 

		if (Util::get("parent") && Util::get("pid")) {
			$redirect_path = Util::redirectBackToParentUrl(Util::classToString($model), Util::get("pid"));
		} else {
			$redirect_path = Util::redirectUrl();
		}
		header("Location: {$redirect_path}");
		exit();
	}

	private function update()
	{	
		$model = $this->model;
		if (Util::get("id")) {
			$entry = $model::find(Util::get("id"));
			$message = array("message" => "Updated {$this->name} user with id of ".Util::get("id").".", "type" => "success");
		} elseif (Util::get("parent") && Util::get("pid")) {
			$entry = $model;
			$message = array("message" => "Added {$this->name} to ".Util::cleanName(Util::get("parent"))." with id of ".Util::get("pid").".", "type" => "success");
		} else {
			$entry = $model;
			$message = array("message" => "Created {$this->name}.", "type" => "success");
		}

		$fileErrors = array();

		foreach ($this->columns as $column) {

			if (array_key_exists("upload", $column) && array_key_exists($column['field'], $_FILES) && $_FILES["{$column['field']}"] != "") {
				
				$transit = new Transit($_FILES["{$column['field']}"]); 

				$validations = $column['upload']['validations'];
				if (count($validations) > 0) {
					$validator = new ImageValidator();
					foreach ($validations as $val) {
						$validator->addRule($val[0], $val[1], $val[2]);
					}

					$transit->setDirectory($model->uploadPath($column['field'])) 
								->setValidator($validator);
				} else {
					$transit->setDirectory($model->uploadPath($column['field']));
				}

				
				

				try { 
					if ($_FILES["{$column['field']}"]['name'] != "" && $transit->upload()) {
						$fileName = explode(DIRECTORY_SEPARATOR, $transit->getOriginalFile());
						$entry->{$column['field']} = end($fileName);
					}
				} catch (Exception $e) {
					$fileErrors["{$column['field']}"] = $e->getMessage();
				}

			} else {
				if ($column['field'] != "created_at" && $column['field'] != "updated_at" ) {

					$inputsTemp = Util::get($column['field']);
					if ($this->infuseLogin && $column['field'] == "password") {
						$inputsTemp = Hash::make(Util::get($column['field']));
					}

					$entry->{$column['field']} = $inputsTemp;

					if ($this->infuseLogin && $column['field'] == "password_confirmation") {
						unset($entry->{$column['field']});
					}
						
					
				}
			}
		}

		$data = Util::getAll();

		// Remove any FALSE values. This includes NULL values, EMPTY arrays, etc.
		$data = array_filter($data);

		if ($entry->validate($data) && count($fileErrors) == 0) {

			$entry->save();
			Util::flash($message);


			
			if (Util::get("oneToOne")) { 
				$entry->belongsTo(ucfirst(Util::get("oneToOne")))->get()->{Util::getForeignKeyString($entry)} = $entry->id;
			}

			if (Util::get("parent") && Util::get("pid")) {
				$redirect_path = Util::redirectBackToParentUrl(Util::classToString($entry), Util::get("pid"));
			} else {
				$redirect_path = Util::redirectUrl();
			}
			
		} else {
			Util::flash(array(
				"message" => "Failed to save {$this->name}.", 
				"type" => "error"
				)
			);
			Util::flashArray("errors", $entry->errors());
			Util::flashArray("file_errors", $fileErrors);
			Util::flashArray("post", Util::getAll());

			if (Util::get("parent") && Util::get("pid")) {
				$redirect_path = Util::redirectUrlChildSaveFailed(Util::get("parent"), Util::get("pid"));
			} else {
				$redirect_path = Util::redirectUrlSaveFailed(Util::get("id"));
			}

			
			
		}
		
		header("Location: {$redirect_path}");
		exit();
	}


	private function toCSV($prepareModel)
	{
		$model = $this->model;
		$columnNames = array_keys($this->columns);
		array_unshift($columnNames, "id");
		$data = $prepareModel->select($columnNames)->get()->toArray();
		foreach ($columnNames as $key => $column) {
		 	$columnNames[$key] = Util::cleanName($column);
		}
		array_unshift($data, $columnNames);
		Util::returnCSVDataAsFile(Util::classToString($model), $data);
		exit();
	}


	private function listAllFilter()
	{
		$model = $this->model;
		$pagination = array(
			"limit" => $this->limit,
			"active_page" => 1,
			"count" => 0
		);
		

		$filterCount = Util::get("filter_count");
		$filters = array();

		for ($x=1; $x <= $filterCount; $x++)	{
			$filter = json_decode(Util::get("filter_".$x));

			if (count($filter) == 3) {
				if (!isset($filter[0]) && !in_array($filter[0], $columnNames))
					continue;
				if (!isset($filter[1]) && !array_key_exists($filter[1], $comparisons))
					continue;
				if (!isset($filter[2]))
					continue;
				array_push($filters, $filter);
			}
	  } 

		
		
		
		$offset = 0;
		$page = Util::get("pg");
		if ($page && $page != 1 && $page != 'a') {
			$offset =  ($page-1) * $pagination['limit'];
			$pagination['active_page'] = $page;
		}




		if ($page == "a") { 
			$prepareModel = $model::orderBy($this->order["column"], $this->order["order"]);
		} else {
			$prepareModel = $model::orderBy($this->order["column"], $this->order["order"])->take($pagination['limit'])->skip($offset);
		}

		$columnNames = array_keys($this->columns);
		$comparisons = array("equals" => "=", "less than" => "<", "greater than" => ">", "not equal to" => "!=");

		foreach($filters as $filter) {
				$prepareModel = $prepareModel->where($filter[0], $comparisons[$filter[1]], $filter[2]);
		}

		if (Util::get("toCSV"))	{
			$this->toCSV($prepareModel);
		}

		$this->entries = $prepareModel->get();


		$prepareModelForCount = $model::orderBy($this->order["column"], $this->order["order"]);
		foreach($filters as $filter) {
				$prepareModelForCount = $prepareModelForCount->where($filter[0], $comparisons[$filter[1]], $filter[2]);
		}
		$pagination['count'] = $prepareModelForCount->count();
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"filters" => $filters
			);

	}

	

	/******************************
		Config methods
	*******************************/

	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	public function limit($limit)
	{
		$this->limit = (is_int($limit))? $limit : $this->limit;
		return $this;
	}

	public function infuseLogin()
	{
		$this->infuseLogin = true;
		Util::insertAfter($this->columns, "password", array( 
			"password_confirmation" => array("field" => "password_confirmation", "type"  => "varchar")
			)
		);
		return $this;
	}


	public function order($order)
	{
		if (is_array($order) && array_key_exists("order", $order) && array_key_exists("column", $order)) {
			$this->order["order"] = $order["order"];
			$this->order["column"] = $order["column"];
			return $this;
		} else {
			throw new Exception('order(array("order" => "desc", "column" => "name")); Array required with order and column. ');
		}
	}

	public function addSelect($column, $array, $insertBlank = false)
	{	
		if (!is_string($column)) 
			throw new Exception('addSelect("name", array()); First argument should name of column. ');
		if (!is_array($array)) 
			throw new Exception('addSelect("name", array()); Second argument can only be an array. ');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["select"] = $array;
			if ($insertBlank == true) {
				$this->columns["{$column}"]["select_blank"] = true;
			}
			return $this;
		} else {
			throw new Exception('addSelect("name", array()); Column doesn\'t exist.');
		}
	}

	public function addCkeditor($column)
	{
		if (!is_string($column)) 
			throw new Exception('addCkeditor("name"); First argument should name of column. ');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["ckeditor"] = $column;
			return $this;
		} else {
			throw new Exception('addCkeditor("name"); Column doesn\'t exist.');
		}
	}


	public function fileUpload($column, $uploadFolder, $validations = array())
	{
		if (!is_string($column)) 
			throw new Exception('fileUpload("name", "/path/to/files"); First argument should name of column. ');
		if (!is_string($uploadFolder)) 
			throw new Exception('fileUpload("name", "/path/to/files"); Second argument should be the path to the uploads folder. ');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["upload"] = array("uploadFolder" => $uploadFolder, "validations" => $validations);
			return $this;
		} else {
			throw new Exception('fileUpload("name", "/path/to/files");  Column doesn\'t exist.');
		}
	}

	public function hasMany($model)
	{	
		if (!is_array($model)) 
			throw new Exception('hasMany(array("SomeModelName", "model_title", array("column_1", "column_2"))); First argument should be an array with all the info of the model. 
				First index in the array should be the model name, second should be the wanted model title and third should be the column names to list.');
		$this->hasMany = $model;
		return $this;
	}

	public function hasOne($model)
	{	
		if (!is_array($model)) 
			throw new Exception('hasOne(array("model_name" => array("model_title", array("column_1", "column_2")))); First argument should be an array of the model. 
				With name as the index and another array with the title as the first and the second array with columns to list.');
		$this->hasOne = $model;
		return $this;
	}

	public function modelDescription($desc)
	{
		$this->description = (is_string($desc))? $desc : "";
		return $this;
	}


	public function describeColumn($column, $desc)
	{
		if (!is_string($column)) 
			throw new Exception('describeColumn("columnName", "description here"); First argument should name of column. ');
		if (array_key_exists($column, $this->columns)) { 
			$this->columns["{$column}"]["description"] = (is_string($desc))? $desc : "";
			return $this;
		} else {
			throw new Exception('describeColumn("columnName", "description here");  Column doesn\'t exist.');
		}
		return $this;
	}

	public function displayOrder($column)
	{
		if (!is_string($column)) 
			throw new Exception('displayOrder("name"); First argument should name of column. ');
		if (array_key_exists($column, $this->columns)) { 
			if ($this->columns["{$column}"]["type"] != "int") 
				throw new Exception('displayOrder("name"); Column type should be an integer. ');
			$this->columns["{$column}"]["display_order"] = true;
			return $this;
		} else {
			throw new Exception('displayOrder("name");  Column doesn\'t exist.');
		}
	}

	public function listColumns($list)
	{
		if (!is_array($list)) 
			throw new Exception('list(array("name", "count", "active")); First argument should be an array of the names of the columns wanted listed on landing page.');
		$this->list = $list;
		return $this;
	}

	public function onlyOne()
	{
		$this->onlyOne = true;
		return $this;
	}



	/******************************
		Final build scaffold
	*******************************/
	public function config()
	{	

		$this->route();
		$this->header['description'] = $this->description;
		$data = array(
				"action" => $this->action,
				"enrties" => $this->entries,
				"header" => $this->header,
				"columns" => $this->columns,
				"infuseLogin" => $this->infuseLogin
			);

		return $data;
	}


	public static function getBladeTemplate()
	{
		switch (Util::get("action")) {
			case 'l':
				return "infuse::scaffold.list";
			case 'e':
			case 'c':
			case 'cd':
				return "infuse::scaffold.create_edit";
			case 's':
				return "infuse::scaffold.show";
			default:
				return "infuse::scaffold.list";
		}

	}

}

?>