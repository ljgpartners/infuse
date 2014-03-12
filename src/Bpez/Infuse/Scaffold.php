<?php namespace Bpez\Infuse;

use Exception;
use Transit\Transit;
use Transit\Validator\ImageValidator;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Scaffold 
|--------------------------------------------------------------------------
| Contains all the logic for scaffolding a laravel/Infuse model
|
*/

class Scaffold {
	
	private $testing = false;
	private $model;
	private $columns;
	private $action;
	private $entries = array();
	private $header = array();
	private $name;
	private $columnNames = array();
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
	private $manyToMany = array();
	private $belongsToUser = false;
	private $onlyLoadSameChildren = false;
	private $associateToSameParent = false;
	private $deleteAction = true;

	protected $view;
	protected $user = false;
	protected static $db;

	public function __construct(\Illuminate\View\Environment $view, \InfuseUser $user, \Illuminate\Support\Facades\DB $db)
	{	
		$this->view = $view;
		$this->user = $user;
		self::$db = $db;
	}


	public function boot()
  {	
  	self::checkIfOverUploadLimit();
  	$this->action = Util::get("action"); 
		$this->name = get_class($this->model);
		$db = self::$db;
		$columns =  $db::select("SHOW COLUMNS FROM ".$this->model->getTable());
		
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
  	
  	return $this;
  }

  public static function checkIfOverUploadLimit()
  {
  	if(empty($_FILES) && empty($_POST) && isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD']) == 'post')	{ 
      $postMax = ini_get('post_max_size'); 
			Util::flash(array(
				"message" => "Maximum upload size exceeded on server. Please note files or total combined size of files larger than {$postMax} will result in this error!", 
				"type" => "error"
				)
			);

			header("Location: http://".$_SERVER['HTTP_HOST']."/admin/dashboard");
			exit();
		}
  }

  public function testing()
  {
  	$this->testing = true;
  	return $this;
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
			case 'cu':
				$this->update();
				break;
			case 'f':
				$this->listAllFilter();
				break;
			case 'rrpp':
				$this->sendRequestResetPasswordPage();
			case 'icsv':
				$this->importCSV();

			default:
				$this->listAll();
				break;
		}
	}


	public function checkPermissions($redirectBack)
  {	
  	switch ($this->action) {
			case 'l':
			case 's':
			case 'f':
				$action = "view";
				$redirect = "/admin/dashboard";
				break;
			case 'e':
			case 'u':
				$action = "update";
				$redirect = $redirectBack;
				break;
			case 'd':
				$action = "delete";
				$redirect = $redirectBack;
				break;
			case 'c':
			case 'cd':
			case 'cu':
			case 'icsv':
				$action = "create";
				$redirect = $redirectBack;
				break;

			default:
				$action = "view";
				$redirect = "/admin/dashboard";
				break;
		}

		$resource = Util::camel2under(get_class($this->model)); 
		
		if ($this->user->can($resource."_".$action)) {
			// User has permission let user continue action
			return false;

		} else {
			$name = Util::cleanName($this->name);
			Util::flash(array(
				"message" => "{$this->user->username} is not authorized to {$action} {$name}.", 
				"type" => "warning"
				)
			);

			// User does not have permission don't let user continue action
			return $redirect;
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
		
		
		if (!$this->belongsToUser || $this->user->is('Super Admin'))
			$pagination['count'] = $model::count(); 
		else
			$pagination['count'] = $model::where("infuse_user_id", "=", $this->user->id)->count();

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

		if ($this->infuseLogin)
			$prepareModel->where("id", "!=", 1)->where("username", "!=", 'super');

		if ($this->belongsToUser && !$this->user->is('Super Admin'))
			$prepareModel->where("infuse_user_id", "=", $this->user->id);

		if ($this->onlyLoadSameChildren)
			$prepareModel->where($this->onlyLoadSameChildren, "=", $this->user->{$this->onlyLoadSameChildren});
		

		if (Util::get("toCSV"))
			$this->toCSV($prepareModel);

		$this->entries = $prepareModel->get();
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"onlyOne" => $this->onlyOne,
				"columnNames" => $this->columnNames
			);
	}

	private function show()
	{	
		$model = $this->model;
		$this->header = array(
				"pagination" => array(),
				"name" => $this->name,
				"columnNames" => $this->columnNames
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
				"manyToManyAssociations" => $this->manyToMany,
				"hasOneAssociation" => $this->hasOne,
				"onlyOne" => $this->onlyOne,
				"columnNames" => $this->columnNames
			);

		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model::find(Util::get("id"));
		} else {
			$this->entries = $model::find($post["id"]);
			foreach ($this->columns as $column) {
				if (isset($post[$column['field']]))
					$this->entries->{$column['field']} = $post[$column['field']];
			}
		}
		
	}

	private function create()
	{
		$model = $this->model;
		$this->header = array(
				"name" => $this->name,
				"associations" => $this->hasMany,
				"manyToManyAssociations" => $this->manyToMany,
				"hasOneAssociation" => $this->hasOne,
				"columnNames" => $this->columnNames
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
				"associations" => $this->hasMany,
				"manyToManyAssociations" => $this->manyToMany,
				"columnNames" => $this->columnNames
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
		$entry = $model::find(Util::get("id"));

		if ($this->infuseLogin && $entry->id == 1 && $entry->username == 'super' ) {
			Util::flash(array(
				"message" => "Can't delete super.", 
				"type" => "error"
				)
			);

		} else {
			foreach ($this->columns as $column) {
				if (array_key_exists("upload", $column) && !empty($entry->{$column['field']})) 
					unlink($_SERVER['DOCUMENT_ROOT']."/".$entry->url($column['field']));
			}
			$entry->delete();
			Util::flash(array(
				"message" => "Deleted {$this->name} with id of ".Util::get("id").".", 
				"type" => "error"
				)
			);
		}
		 

		if (Util::get("stack")) {
			$redirect_path = Util::childBackLink(true);
		} else {
			$redirect_path = Util::redirectUrl();
		}
		
		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
	}

	private function importCSV()
	{
		$model = $this->model;
		$entry = $model::find(Util::get("parent_id"));
		$redirect_path = Util::get("back");
		$error = false;
		
		if (\Input::hasFile('csv_file')) {
			$rule = array('csv_file' => 'mimes:csv');
      $validator = \Validator::make(array('csv_file' => \Input::get('csv_file')), $rule);
      if($validator->fails()) {
        $error = true;
				$message = "File uploaded not valid.";
      } else {

	      $file            = \Input::file('csv_file');
        $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/';
        $filename        = time().'_'.$file->getClientOriginalName();
        $uploadSuccess   = $file->move($destinationPath, $filename);
	      
	      if (Util::get("child")) { 
					$child = Util::get("child");
					$childInstance = new $child;
					$db = self::$db;
					$columns = $db::select("SHOW COLUMNS FROM ".$childInstance->getTable());
					unset($db);

					$columns = array_map(function($n) {
						return $n->Field;
					}, $columns);

					$header = Util::importCSV($destinationPath.$filename, true);

					foreach ($header as $h) {
						if (!in_array($h, $columns)) {
							$error = true;
							$message = "Fields not correct in csv file.";
						}
					}
					unset($columns);

					if (!$error) {
						$data = Util::importCSV($destinationPath.$filename);
						$foreignKey = Util::getForeignKeyString($entry);
						foreach ($data as $row) {
							$childInstance = new $child;
							foreach($row as $key => $value)
								$childInstance->{$key} = $value;
							$childInstance->{$foreignKey} = $entry->id;
							$entry->hasMany($child)->save($childInstance);
						}

						$message = "Succesfully imported csv data.";
					}

				}
      }
			
		} else {
			$error = true;
			$message = "No file uploaded.";
		}
	
		Util::flash(array(
			"message" => $message, 
			"type" => ($error)? "error" : "success"
			)
		);
		
		
		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
	}
	

	private function sendRequestResetPasswordPage()
	{
		$model = $this->model;
		$user = $model::find(Util::get("id"));

		$user->sendRequestResetPasswordPage();
		Util::flash(array(
			"message" => "Sent email with link to reset password.", 
			"type" => "success"
			)
		);
		
		$redirect_path = Util::redirectUrl();
		
		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
	}


	private function update() 
	{	
		$model = $this->model;
		if (Util::get("id")) { 
			$entry = $model::find(Util::get("id"));

			$message = array("message" => "Updated {$this->name} user with id of ".Util::get("id").".", "type" => "success");
		} elseif (Util::get("stack")) {
			$entry = $model;
			$message = array("message" => "Added {$this->name}.", "type" => "success");
		} else {
			$entry = $model;
			$message = array("message" => "Created {$this->name}.", "type" => "success");
		}

		$fileErrors = array(); 
		
		foreach ($this->columns as $column) {

			if (array_key_exists("upload", $column) && array_key_exists($column['field'], $_FILES) && $_FILES["{$column['field']}"] != "") {
				
				

				/**************************************
				* Do uploading via image crop method
				****************************************/
				if (array_key_exists("imageCrop", $column['upload']) && $column['upload']['imageCrop']) {

					if ($_FILES["{$column['field']}"]['name'] != "") {
						
						$nw = $column['upload']['imageCrop']['width']; 
						$nh = $column['upload']['imageCrop']['height'];
						 
						$valid_exts = array('jpeg', 'JPEG', 'jpg', 'JPG', 'png', 'PNG', 'gif', 'GIF');
						$ext = strtolower(pathinfo($_FILES["{$column['field']}"]['name'], PATHINFO_EXTENSION));
							if (in_array($ext, $valid_exts)) {
									$filename = uniqid().'.'.$ext;
									$path = $model->uploadPath($column['field']).$filename;
									$size = getimagesize($_FILES["{$column['field']}"]['tmp_name']);

									$x = (int) Util::get("upload{$column['field']}x");
									$y = (int) Util::get("upload{$column['field']}y");
									$w = (int) Util::get("upload{$column['field']}w") ? Util::get("upload{$column['field']}w") : $size[0];
									$h = (int) Util::get("upload{$column['field']}h") ? Util::get("upload{$column['field']}h") : $size[1];

									$data = file_get_contents($_FILES["{$column['field']}"]['tmp_name']);
									$vImg = imagecreatefromstring($data);
									$dstImg = imagecreatetruecolor($nw, $nh);
									imagecopyresampled($dstImg, $vImg, 0, 0, $x, $y, $nw, $nh, $w, $h);
									imagejpeg($dstImg, $path);
									imagedestroy($dstImg);
									$entry->{$column['field']} = $filename;
									
							} else {
								$fileErrors["{$column['field']}"] = "Extension not allowed.";
							}
					}
						

				} else {
					/**************************************
					* Not croped image do regualr uploading 
					***************************************/
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

							if (!empty($entry->{$column['field']}))
								unlink($_SERVER['DOCUMENT_ROOT']."/".$entry->url($column['field']));
							$entry->{$column['field']} = end($fileName);
						}
					} catch (Exception $e) {
						$fileErrors["{$column['field']}"] = $e->getMessage();
					}
				}
				
				
				

				

			} else {
				if ($column['field'] != "created_at" && $column['field'] != "updated_at" && Util::checkInfuseLoginFields($this->infuseLogin, $column) ) {

					$inputsTemp = Util::get($column['field']);

					if ($this->belongsToUser && $column['field'] == "infuse_user_id" && !$this->user->is('Super Admin'))
						$inputsTemp = $this->user->id;


					if (isset($column['displayOrder']) && Util::get("stack") && $inputsTemp == "") {
						$count = $model::where(Util::foreignKeyString(Util::stackParent()), "=", Util::stackParentId())->count();
						$count = 1+(int)$count;
						$inputsTemp = $count;
					} 
					
					$entry->{$column['field']} = $inputsTemp;
					
				}
			}
		}

		$data = Util::getAll();



		// Remove any FALSE values. This includes NULL values, EMPTY arrays, etc.
		$data = array_filter($data);
		
		if ($entry->validate($data) && count($fileErrors) == 0) {
			
			
			// Check if brand new user
			if ($this->infuseLogin && !Util::get("id")) { 
				$entry->verified = 1;
				$entry->deleted_at = null;
				if ($this->associateToSameParent)
					$entry->{$this->associateToSameParent} = $this->user->{$this->associateToSameParent};
			}

			
			
			// Do many to many relationship saving
			if (Util::get("id") && count($this->manyToMany) > 0) {
				foreach ($this->manyToMany as $association) {
					$firstModel = $association[0];
					$secondModel = $association[2];
					$manyToManyTable = $association[4];
					$model = get_class($entry);

					if ($model == $firstModel) {
						$belongsToModel = $secondModel;
						$firstForeignId =	$association[1];
						$secondForeignId = $association[3] ;
					} else if ($model == $secondModel) {
						$belongsToModel = $firstModel;
						$firstForeignId =	$association[3];
						$secondForeignId = $association[1];
					}

					
					$idsForSync = Util::get($manyToManyTable); 
					if ($idsForSync) { 
						$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->detach();
						if ($this->infuseLogin && $entry->id == 1 && $entry->username == 'super' )
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->attach(1);
						foreach($idsForSync as $id) { 
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->attach($id);
						}
					} else {
						$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->detach();
					}
				}
			}

			
			
			$entry->save();
			Util::flash($message);


			
			if (Util::get("oneToOne")) { 
				$entry->belongsTo(ucfirst(Util::get("oneToOne")))->get()->{Util::getForeignKeyString($entry)} = $entry->id;
			}

			if (Util::get("stack")) {
				$redirect_path = Util::childBackLink(true);
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

			if (Util::get("stack")) {
				if (!Util::get("id")) { 
					$redirect_path = Util::redirectUrlChildSaveFailed();
				}	else {
					$redirect_path = Util::redirectUrlChildSaveFailed($entry->id);
				}
			} else { 
				$redirect_path = Util::redirectUrlSaveFailed(Util::get("id"));
			}

			
			
		}

		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
			
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
				"filters" => $filters,
				"onlyOne" => $this->onlyOne,
				"columnNames" => $this->columnNames
			);

	}

	

	/******************************
		Config methods
	*******************************/
	public function model($model)
	{
		$this->model = $model;
		return $this;
	}

	protected function name($name)
	{
		$this->name = $name;
		return $this;
	}

	protected function columnName($info) 
	{
		if (!is_array($info) && isset($info['column']) && isset($info['newName'])) 

		if (!is_string($info['column']) || !is_string($info['newName'])) 
			throw new Exception('columnName(array("column" => someColumn, "newName" => "someName")); First argument should name of column. Second argument should be replacement name.');
		if (array_key_exists($info['column'], $this->columns)) {
			$this->columnNames["{$info['column']}"] = $info['newName'];
			return $this;
		} else {
			throw new Exception('array("column" => someColumn, "newName" => "someName")); Column doesn\'t exist.');
		}
		return $this;
	}

	protected function limit($limit)
	{
		$this->limit = (is_int($limit))? $limit : $this->limit;
		return $this;
	}

	protected function infuseLogin()
	{
		$this->infuseLogin = true;
		unset($this->columns['password']);
		unset($this->columns['salt']);
		return $this;
	}


	protected function order($order)
	{
		if (is_array($order) && array_key_exists("order", $order) && array_key_exists("column", $order)) {
			$this->order["order"] = $order["order"];
			$this->order["column"] = $order["column"];
			return $this;
		} else {
			throw new Exception('order(array("order" => "desc", "column" => "name")); Array required with order and column. ');
		}
	}

	protected function addSelect($info) 
	{	
		if (!is_array($info) && isset($info['column']) && isset($info['array'])) 
			throw new Exception('addSelect(array("column" => $columnName, "array" => array(), "insertBlank" => false)); First argument must an array. column and array must be set. ');
		if (!is_string($info['column'])) 
			throw new Exception('addSelect(array("column" => $columnName, "array" => array(), "insertBlank" => false)); Column must be a string. ');
		if (!is_array($info['array'])) 
			throw new Exception('addSelect(array("column" => $columnName, "array" => array(), "insertBlank" => false)); Array index must be an array. ');
		if (array_key_exists($info['column'], $this->columns)) {
			$this->columns["{$info['column']}"]["select"] = $info['array'];
			if (isset($info['insertBlank']) && $info['insertBlank'] == true) {
				$this->columns["{$info['column']}"]["select_blank"] = true;
			}
			return $this;
		} else {
			throw new Exception('addSelect(array("column" => $columnName, "array" => array(), "insertBlank" => false)); Column doesn\'t exist.');
		}
	}

	protected function addMultiSelect($info)
	{	
		if (!is_array($info) && isset($info['column']) && isset($info['array'])) 
			throw new Exception('addMultiSelect(array("column" => $columnName, "array" => array())); First argument must an array. column and array must be set. ');
		if (!is_string($info['column'])) 
			throw new Exception('addMultiSelect(array("column" => $columnName, "array" => array())); First argument should name of column. ');
		if (!is_array($info['array'])) 
			throw new Exception('addMultiSelect(array("column" => $columnName, "array" => array())); Second argument can only be an array. ');
		if (array_key_exists($info['column'], $this->columns)) {
			$this->columns["{$column}"]["multi_select"] = $info['array'];
			return $this;
		} else {
			throw new Exception('addMultiSelect(array("column" => $columnName, "array" => array())); Column doesn\'t exist.');
		}
	}

	protected function addCkeditor($column)
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

	/* 
	To activate Image croping pass in the following
	$imageCrop = array("image_crop" => true, "width" => 98, "height" => 98) 
	Note if image crop is used then file validation will be applied
	*/
	protected function fileUpload($uploads)
	{
		if (!is_array($uploads)) {
			throw new Exception('fileUpload(array(array("column" => $columnName))); First argument must an array. column index must be set. ');
		} else {
			
			// go through all uploads 
			foreach ($uploads as $key => $info) {
				
				if (!is_array($info) && !isset($info['column'])) 
					throw new Exception('fileUpload(array(array("column" => $columnName))); First argument must an array. column index must be set. ');
				if (!is_string($info['column'])) 
					throw new Exception('fileUpload(array(array("column" => $columnName))); First argument should name of column. ');
				if (array_key_exists($info['column'], $this->columns)) { 
					$validations = (isset($info['validations']))? $info['validations'] : array();
					$imageCrop = (isset($info['imageCrop']))? $info['imageCrop'] : false; 
					$this->columns["{$info['column']}"]["upload"] = array("validations" => $validations, "imageCrop" => $imageCrop);
				} else {
					throw new Exception('fileUpload(array(array("column" => $columnName)));  Column doesn\'t exist.');
				}
			}
			return $this;
		}

		
	}

	protected function hasMany($models)
	{	
		if (!is_array($models)) 
			throw new Exception('hasMany( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array with all the info of the model. 
				First index in the array should be the model name, second should be the wanted model title and third should be the column names to list.');
		$this->hasMany = $models;
		return $this;
	}

	protected function hasOne($model)
	{	
		if (!is_array($model)) 
			throw new Exception('hasOne( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array of the model. 
				With name as the index and another array with the title as the first and the second array with columns to list.');
		$this->hasOne = $model;
		return $this;
	}

	protected function manyToMany($models)
	{
		if (!is_array($models)) 
			throw new Exception('manyToMany(array(array("FirstModelName", "FirstForeignId", "SecondModelName", "SecondForeignId", "many_to_many_table", "FirstColumnName", "SecondColumnName"))); ');
		$this->manyToMany = $models;
		return $this;
	}

	protected function modelDescription($desc)
	{
		$this->description = (is_string($desc))? $desc : "";
		return $this;
	}


	protected function describeColumn($describes)
	{	
		if (!is_array($describes))
			throw new Exception('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Must pass array in. ');

		foreach ($describes as $d) {
			if (!isset($d['column']) || !isset($d['desc'])) 
				throw new Exception('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Both argument are required');
			if (array_key_exists($d['column'], $this->columns)) { 
				$this->columns["{$d['column']}"]["description"] = (is_string($d['desc']))? $d['desc'] : ""; 	
			} else {
				throw new Exception('describeColumn(array(array("column" => "columnName", "desc" => "description here")));  Column doesn\'t exist.');
			}
		}
		
		return $this;
	}

	protected function displayOrder($column)
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

	protected function listColumns($list)
	{
		if (!is_array($list)) 
			throw new Exception('list(array("name", "count", "active")); First argument should be an array of the names of the columns wanted listed on landing page.');
		$this->list = $list;
		return $this;
	}

	protected function onlyOne()
	{
		$this->onlyOne = true;
		return $this;
	}

	protected function deleteAction($bool)
	{	
		$this->deleteAction = $bool;
		return $this;
	}

	protected function displayOrderColumn($column)
	{
		if (!is_string($column)) 
			throw new Exception('displayOrderColumn("name");  First argument should name of column.');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["displayOrder"] = true;
			return $this;
		} else {
			throw new Exception('displayOrderColumn("name");  Column doesn\'t exist.');
		}
	}
	/*
	public function loadUser($user)
	{
		if ($user instanceof InfuseUser)
			throw new Exception('loadUser($user);  User argument is required and should be an instance of InfuseUser.');
		$this->user = $user;
		return $this;
	}*/

	protected function belongsToUser()
	{
		$this->belongsToUser = true;
		return $this;
	}
	
	protected function associateToSameParent($foreignKey)
	{
		$this->associateToSameParent = $foreignKey;
		return $this;
	}

	protected function onlyLoadSameChildren($foreignKey)
	{
		$this->onlyLoadSameChildren = $foreignKey;
		return $this;
	}
	
	public function mapConfig($config)
	{
		foreach ($config as $key => $f) {
			
			switch ($key) {
				case 'model':
					$this->model($f);
					break;
				case 'name':
					$this->name($f);
					break;
				case 'columnName':
					$this->columnName($f);
					break;
				case 'limit':
					$this->limit($f);
					break;
				case 'infuseLogin':
					$this->infuseLogin();
					break;
				case 'order':
					$this->order($f);
					break;
				case 'hasMany':
					$this->hasMany($f);
					break;
				case 'hasOne':
					$this->hasOne($f);
					break;
				case 'manyToMany':
					$this->manyToMany($f);
					break;
				case 'modelDescription':
					$this->modelDescription($f);
					break;
				case 'displayOrder':
					$this->displayOrder($f);
					break;
				case 'listColumns':
					$this->listColumns($f);
					break;
				case 'onlyOne':
					$this->onlyOne();
					break;
				case 'deleteAction':
					$this->deleteAction($f);
					break;
				case 'displayOrderColumn':
					$this->displayOrderColumn($f);
					break;
				case 'loadUser':
					$this->loadUser($f);
					break;
				case 'belongsToUser':
					$this->belongsToUser();
					break;
				case 'associateToSameParent':
					$this->associateToSameParent($f);
					break;
				case 'onlyLoadSameChildren':
					$this->onlyLoadSameChildren($f);
					break;
				case 'addCkeditor':
					$this->addCkeditor($f);
					break;
				case 'addSelect':
					$this->addSelect($f);
					break;
				case 'addMultiSelect':
					$this->addMultiSelect($f['column'], $f['array']);
					break;
				case 'fileUpload':
					$this->fileUpload($f);
					break;
				case 'describeColumn':
					$this->describeColumn($f);
					break;
				case 'children':
					break;
				
				default:
					throw new Exception("Unknown {$key} index in config file.");
					break;
			}
		}

		return $this;
	}
	
	


	/******************************
		Final build scaffold
	*******************************/
	public function process()
	{	
		$this->route();
		$this->header['description'] = $this->description;
		$this->header['deleteAction'] = $this->deleteAction;

		$data = array(
			"action" => $this->action,
			"entries" => $this->entries,
			"header" => $this->header,
			"columns" => $this->columns,
			"infuseLogin" => $this->infuseLogin,
			"user" => $this->user,
			"db" => self::$db
		);

		return $this->view->make(self::getBladeTemplate(), $data);
	}

	// Used to test scaffold
	public function processDataOnly()
	{	
		$this->route();
		$this->header['description'] = $this->description;
		$this->header['deleteAction'] = $this->deleteAction;

		$data = array(
			"action" => $this->action,
			"entries" => $this->entries,
			"header" => $this->header,
			"columns" => $this->columns,
			"infuseLogin" => $this->infuseLogin,
			"user" => $this->user,
			"db" => self::$db
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