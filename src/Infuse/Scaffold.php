<?php 
namespace Infuse;

use Infuse\Util;
use Exception;
use Transit\Transit;
use Transit\Validator\ImageValidator;

class Scaffold {

	private $model;
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
	private $selects = array();
	private $hasMany = array();


	public function __construct($model, $db)
	{	
		if (!isset($_SESSION)) session_start();
		$this->action = Util::get("action");
		$this->model = $model;
		$this->name = get_class($model);

		$columns = $db::query("SHOW COLUMNS FROM ".$model->table());
		foreach ($columns as $column) {
			if ($column->field != 'id' ) {
				if (strlen(strstr($column->type, "varchar")) > 0) {
					$type = "varchar";
				} else if (strlen(strstr($column->type, "tinyint")) > 0) {
					$type = "tinyint";
				} else if (strlen(strstr($column->type, "int")) > 0) {
					$type = "int";
				} else {
					$type = $column->type;
				}
				$this->columns["{$column->field}"] = array(
						"field" => $column->field,
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
			case 'u':
				$this->update();
				break;
			case 'cc':
				$this->childCreate();
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
			$this->entries = $model::order_by($this->order["column"], $this->order["order"])->get();
		} else {
			$this->entries = $model::order_by($this->order["column"], $this->order["order"])->take($pagination['limit'])->skip($offset)->get();
		}
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name
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
				"associations" => $this->hasMany
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model::find(Util::get("id"));
		} else {
			$this->entries = Util::arrayToObject($post);
		}
	}

	private function create()
	{
		$model = $this->model;
		$this->header = array(
				"name" => $this->name,
				"associations" => $this->hasMany
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model;
		} else {
			$this->entries = Util::arrayToObject($post);
		}
		
	}

	private function childCreate()
	{

	}

	private function delete()
	{
		$model = $this->model;
		$model::find(Util::get("id"))->delete();
		Util::flash(array(
			"message" => "Deleted {$this->name} with id = ".Util::get("id").".", 
			"type" => "error"
			)
		); 
		$redirect_path = str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		header("Location: {$redirect_path}");
		exit();
	}

	private function update()
	{	
		$model = $this->model;
		if (Util::get("id")) {
			$entry = $model::find(Util::get("id"));
			$message = array("message" => "Updated {$this->name} user with id = ".Util::get("id").".", "type" => "success");
		} else {
			$entry = $model;
			$message = array("message" => "Created {$this->name}.", "type" => "success");
		}

		$fileErrors = array();

		foreach ($this->columns as $column) {

			if (array_key_exists("upload", $column) && array_key_exists($column['field'], $_FILES) && $_FILES["{$column['field']}"] != "") {
				$validations = $column['upload']['validations'];
				if (count($validations) > 0) {
					$validator = new ImageValidator();
					foreach ($validations as $val) {
						$validator->addRule($val[0], $val[1], $val[2]);
					}
				}

				$transit = new Transit($_FILES["{$column['field']}"]); 
				$transit->setDirectory($model->uploadPath($column['field'])) 
								->setValidator($validator);

				try { 
					if ($_FILES["{$column['field']}"]['name'] != "" && $transit->upload()) {
						$fileName = explode(DIRECTORY_SEPARATOR, $transit->getOriginalFile());
						$entry->{$column['field']} = end($fileName);
					}
				} catch (Exception $e) {
					$fileErrors["{$column['field']}"] = $e->getMessage();
				}

			} else {
				$entry->{$column['field']} = Util::get($column['field']);
			}
		}


		if ($entry->validate(Util::getAll()) && count($fileErrors) == 0) {
			$entry->save();
			Util::flash($message);
			$redirect_path = str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		} else {
			Util::flash(array(
				"message" => "Failed to save {$this->name}.", 
				"type" => "error"
				)
			);
			Util::flashArray("errors", $entry->errors());
			Util::flashArray("file_errors", $fileErrors);
			$action = (Util::get("id"))? "?action=e&id=".Util::get("id") : "?action=c";
			Util::flashArray("post", Util::getAll());
			$redirect_path = str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])."{$action}";
		}
		
		header("Location: {$redirect_path}");
		exit();
	}

	

	/******************************
		Alter methods
	*******************************/

	public function limit($limit)
	{
		$this->limit = (is_int($limit))? $limit : $this->limit;
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

	public function addSelect($column, $array)
	{	
		if (!is_string($column)) 
			throw new Exception('addSelect("name", array()); First argument should name of column. ');
		if (!is_array($array)) 
			throw new Exception('addSelect("name", array()); Second argument can only be an array. ');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["select"] = $array;
			return $this;
		} else {
			throw new Exception('addSelect("name", array()); Column doesn\'t exist.');
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
			throw new Exception('hasMany(array("name" => new User)); First argument should is_array of the model. 
				With name as the index and new instance of the Model');
		array_push($this->hasMany, $model);
		return $this;
	}


	/******************************
		Final build scaffold
	*******************************/
	public function build()
	{	
		$this->route();
		$data = array(
				"action" => $this->action,
				"enrties" => $this->entries,
				"header" => $this->header,
				"columns" => $this->columns
			);

		return View::fuse($data);
	}

}

?>