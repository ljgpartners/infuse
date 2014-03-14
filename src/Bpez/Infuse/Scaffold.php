<?php 

/*
 * This file is part of the Infuse package.
 *
 * (c) Bryan Perez <perezjbryan@gmail.com>
 *
 */

namespace Bpez\Infuse;

use Exception;
use Transit\Transit;
use Transit\Validator\ImageValidator;
use Illuminate\Support\Facades\Log;
use Bpez\Infuse\Exceptions\ScaffoldConfigurationException;
use Bpez\Infuse\Exceptions\ScaffoldModelNotRecognizedException;
use Bpez\Infuse\Exceptions\ScaffoldUnknownConfigurationIndexException;


/**
 * Scaffold class contains all the logic for configuring and generating a scaffold on a model.
 *
 * This class does  processes and ORM model by giving you the ability to manage a model. Uses api config methods for 
 * customizing the functionality of a scaffold. Also maps config files for api methods.
 * 
 * @category     	Infuse
 * @package      	Scaffold
 * @author       	Bryan Perez <perezjbryan@gmail.com>
 * @copyright  		2014
 * @version 		 	3.0.0
 * @since  				1.0.0
 * @api
 * @todo Implement dependency on request instance
 */
class Scaffold {
	
	/**
   * Allows unit testing to work.
   *
   * @access private
   * @var bool
   */
	private $testing = false;

	/**
   * Contains the ORM model.
   *
   * @access private
   * @var object
   */
	private $model;

	/**
   * Contains database table columns from the Model.
   *
   * @access private
   * @var array|null
   */
	private $columns = null;

	/**
   * Contains flag to determine how to proccess data.
   *
   * @access private
   * @var string
   */
	private $action;

	/**
   * Contains current instance of model that scaffold is working with or 
   * a collection of models represented by the laravel Collection Class or
   * can be model(s) representing in pure php array form.
   *
   * @access private
   * @var object|array
   */
	private $entries = array();

	/**
   * An array containing various configuration information for the templates.
   * (pagination, name, list, edit, onlyOne, columnNames, associations, manyToManyAssociations, 
   * hasOneAssociation, onlyOne, filters, description, deleteAction)
   *
   * @access private 
   * @var array
   */ 
	private $header = array();

	/**
   * Contains name of the model in string form.
   *
   * @access private
   * @var string
   */
	private $name;

	/**
   * Array contains new column names to overide ones gathered from the model table.
   *
   * @access private
   * @var array
   */
	private $columnNames = array();

	/**
   * Declares how instances of the model can be displayed on the listing page.
   *
   * @access private
   * @var integer
   */
	private $limit = 10;

	/**
   * Contains the display ordering on the listing page.
   *
   * @access private
   * @var array
   */
	private $order = array(
		"order" => "desc",
		"column" => "id"
		);

	/**
   * Contains the has many relationship configuration(s) for the model.
   *
   * @access private
   * @var array
   */
	private $hasMany = array();

	/**
   * Contains the has one relationship configuration(s) for the model.
   *
   * @access private
   * @var array|boolean
   */
	private $hasOne = false;

	/**
   * Contains the many to many relationship configuration(s) for the model.
   *
   * @access private
   * @var array
   */
	private $manyToMany = array();

	/**
   * Contains the description for the model.
   *
   * @access private
   * @var string
   */
	private $description = "";

	/**
   * Contains the list of columns to only display on listing page. If empty all shown.
   *
   * @access private
   * @var array
   */
	private $list = array();

	/**
   * Boolean for setting special rules for processing InfuseUser model.
   *
   * @access private
   * @var boolean
   */
	private $infuseLogin = false;

	/**
   * Flag for letting scaffold only allowing one instance of the model to be created.
   *
   * @access private
   * @var boolean
   */
	private $onlyOne = false;

	/**
   * Associates model to current user when a new instance is created .
   *
   * @access private
   * @var boolean
   */
	private $belongsToUser = false;

	/**
   * On the listing page only loads models instances that belong to the 
   * same parent of the foriegn key provided.
   *
   * @access private
   * @var boolean|string
   */
	private $onlyLoadSameChildren = false;

	/**
   * Associates model to the parent of the foreign key given. 
   *
   * @access private
   * @var boolean|string
   */
	private $associateToSameParent = false;

	/**
   * Disables the action delete action on an instance of the model. 
   *
   * @access private
   * @var boolean
   */
	private $deleteAction = true;

	/**
   * \Illuminate\View\Environment instance for proccessing blade templates
   *
   * @access protected
   * @var object
   */
	protected $view;

	/**
   * \InfuseUser current instance of user logged into infuse
   *
   * @access protected
   * @var object
   */
	protected $user = false;

	/**
   * \Illuminate\Support\Facades\DB instance of current request
   *
   * @access protected
   * @var object
   */
	protected static $db;

	/**
   * \Illuminate\Http\Request instance of current request
   *
   * @access protected
   * @var object
   */
	protected $request;
	

	/**
	 * Constructor
	 *
	 * @param \Illuminate\View\Environment $view An Environment instance
	 * @param \InfuseUser $user A InfuseUser instance
	 * @param \Illuminate\Support\Facades\DB $db An DB instance
	 * @param \Illuminate\Http\Request $request A Request instance
	 *
	 * @api
	 */
	public function __construct(\Illuminate\View\Environment $view, \InfuseUser $user, \Illuminate\Support\Facades\DB $db, \Illuminate\Http\Request $request)
	{	
		$this->view = $view;
		$this->user = $user;
		$this->request = $request;
		self::$db = $db;
	}

	/**
	 * Loads model.
	 *
   * Loads in external model to be processed by the scaffold class.
   *
   * @api
   *
   * @uses $this->boot().
   *
   * @return void
   */
	public function model($model)
	{
		$this->model = $model;
		if (is_subclass_of($this->model, 'InfuseEloquent') 
				|| is_subclass_of($this->model, 'Toddish\Verify\Models\User') 
				|| is_subclass_of($this->model, 'Toddish\Verify\Models\Role')  
				|| is_subclass_of($this->model, 'Toddish\Verify\Models\Permission') ) {

			$this->name = get_class($this->model);

			$this->boot();

			return $this;
		} else {
			throw new ScaffoldModelNotRecognizedException(get_parent_class($this->model)." is the wrong model to inherit from. Extend from InfuseEloquent.");
		}
	}


	/**
   * Boot sets up base configuration for model right after model is loaded into the instance.
   *
   * @uses self::checkIfOverUploadLimit().
   *
   * @return void
   */
	protected function boot()
  {	
  	self::checkIfOverUploadLimit();
  	
  	$this->action = Util::get("action");
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
  }

  /**
   * Checks if post request overflowed limit and so exits class and redirects. (post_max_size and/or upload_max_filesize)
   *
   * @return void
   */
  protected static function checkIfOverUploadLimit()
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

  /**
   * Set testing flag.
   *
   * Set testing to true so that redirects stop within class so that unit testing can take place.
   *
   * @api
   *
   * @return $this
   */
  public function testing()
  {
  	$this->testing = true;
  	return $this;
  }




  /** ******** Public Configuration API methods below.  ********** */


  /**
   * Replaces default model string name.
   *
   * @api
   *
   * @param string $name
   *
   * @return $this Returns this so that it can be easily chanined.
   */
	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	/**
	 * Replaces column name.
	 *
   * Pass in an array with the following indexes 
   * array("column" => "someColumn", "newName" => "someName"). Multiple 
   * changes to different columns can be passed in at the same time just
   * wrap all of them with in an array then pass into method.
   *
   * @api
   *
   * @example columnName($info) 
   <pre>
   	// Single
		$scaffold->columnName(array("column" => "someColumn", "newName" => "someName"));</br></br>
		// Multiple
		$scaffold->columnName(array(
		&nbsp;array("column" => "someColumn", "newName" => "someName"),
		&nbsp;array("column" => "someColumn2", "newName" => "someName2")
		));
   </pre>
   *
   * @param array $info array("column" => "someColumn", "newName" => "someName")
   *
   * @return $this Returns this so that it can be easily chanined.
   *
   * @throws ScaffoldConfigurationException When parameter is not valid
   */
	public function columnName($info) 
	{
		$base = 'columnName(array(array("column" => someColumn, "newName" => "someName")));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		/** If only one. */
		if ( isset($info['column']) && isset($info['newName']) ) {

			if (!is_string($info['column']) || !is_string($info['newName'])) 
				throw new ScaffoldConfigurationException($base.' First aand second argument should be strings');
			if (array_key_exists($info['column'], $this->columns)) {
				$this->columnNames["{$info['column']}"] = $info['newName'];
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}
			

		/** If more then one. */
		} else {

			foreach ($info as $i) {

				if (!isset($i['column']) && !isset($i['column'])) 
					throw new ScaffoldConfigurationException($base.' First argument should name of column. Second argument should be replacement name.');
				if (!is_string($i['column']) || !is_string($i['newName'])) 
					throw new ScaffoldConfigurationException($base.' First aand second argument should be strings');
				if (array_key_exists($i['column'], $this->columns)) {
					$this->columnNames["{$i['column']}"] = $i['newName'];
				} else {
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
				}
			}
		}

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
		unset($this->columns['password']);
		unset($this->columns['salt']);
		return $this;
	}


	public function order($order)
	{
		if (is_array($order) && array_key_exists("order", $order) && array_key_exists("column", $order)) {
			$this->order["order"] = $order["order"];
			$this->order["column"] = $order["column"];
			return $this;
		} else {
			throw new ScaffoldConfigurationException('order(array("order" => "desc", "column" => "name")); Array required with order and column. ');
		}
	}


	public function addSelect($info) 
	{	
		$base = 'addSelect(array(array("column" => $columnName, "array" => array(), "insertBlank" => false)));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['column']) && isset($info['array']) ) {

			if ( array_key_exists($info['column'], $this->columns) ) {
				$this->columns["{$info['column']}"]["select"] = $info['array'];
				if (isset($info['insertBlank']) && $info['insertBlank'] == true) {
					$this->columns["{$info['column']}"]["select_blank"] = true;
				}
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}

		// If more then one
		} else {
			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['column']) || !isset($i['array']))) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				if (!is_string($i['column']) || !is_array($i['array'])) 
					throw new ScaffoldConfigurationException($base.' Column must be a string. Array index must be an array.');
				if (array_key_exists($i['column'], $this->columns)) {
					$this->columns["{$i['column']}"]["select"] = $i['array'];
					if (isset($i['insertBlank']) && $i['insertBlank'] == true) {
						$this->columns["{$i['column']}"]["select_blank"] = true;
					}
				} else {
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
				}
			}
		}
		
		return $this;
	}

	public function addMultiSelect($info)
	{	
		if (!is_array($info) && isset($info['column']) && isset($info['array'])) 
			throw new ScaffoldConfigurationException('addMultiSelect(array("column" => $columnName, "array" => array())); First argument must an array. column and array must be set. ');
		if (!is_string($info['column'])) 
			throw new ScaffoldConfigurationException('addMultiSelect(array("column" => $columnName, "array" => array())); First argument should name of column. ');
		if (!is_array($info['array'])) 
			throw new ScaffoldConfigurationException('addMultiSelect(array("column" => $columnName, "array" => array())); Second argument can only be an array. ');
		if (array_key_exists($info['column'], $this->columns)) {
			$this->columns["{$column}"]["multi_select"] = $info['array'];
			return $this;
		} else {
			throw new ScaffoldConfigurationException('addMultiSelect(array("column" => $columnName, "array" => array())); Column doesn\'t exist.');
		}
	}

	public function addCkeditor($info)
	{
		$base = 'addCkeditor(array("column1", "column2"));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		foreach ($info as $i) {
			if (!is_string($i)) 
				throw new ScaffoldConfigurationException($base.' Column must be a string. Array index must be an array.');
			if (array_key_exists($i, $this->columns)) {
				$this->columns["{$i}"]["ckeditor"] = $i;
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}
		}
		
		return $this;
	}

	/* 
	To activate Image croping pass in the following
	$imageCrop = array("image_crop" => true, "width" => 98, "height" => 98) 
	Note if image crop is used then file validation will be applied
	*/
	public function fileUpload($uploads) 
	{
		if (!is_array($uploads)) {
			throw new ScaffoldConfigurationException('fileUpload(array(array("column" => $columnName))); First argument must an array. column index must be set. ');
		} else {
			
			// go through all uploads 
			foreach ($uploads as $key => $info) {
				
				if (!is_array($info) && !isset($info['column'])) 
					throw new ScaffoldConfigurationException('fileUpload(array(array("column" => $columnName))); First argument must an array. column index must be set. ');
				if (!is_string($info['column'])) 
					throw new ScaffoldConfigurationException('fileUpload(array(array("column" => $columnName))); First argument should name of column. ');
				if (array_key_exists($info['column'], $this->columns)) { 
					$validations = (isset($info['validations']))? $info['validations'] : array();
					$imageCrop = (isset($info['imageCrop']))? $info['imageCrop'] : false; 
					$this->columns["{$info['column']}"]["upload"] = array("validations" => $validations, "imageCrop" => $imageCrop);
				} else {
					throw new ScaffoldConfigurationException('fileUpload(array(array("column" => $columnName)));  Column doesn\'t exist.');
				}
			}
			return $this;
		}

		
	}

	public function hasMany($models)
	{	
		if (!is_array($models)) 
			throw new ScaffoldConfigurationException('hasMany( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array with all the info of the model. 
				First index in the array should be the model name, second should be the wanted model title and third should be the column names to list.');
		$this->hasMany = $models;
		return $this;
	}

	public function hasOne($model)
	{	
		if (!is_array($model)) 
			throw new ScaffoldConfigurationException('hasOne( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array of the model. 
				With name as the index and another array with the title as the first and the second array with columns to list.');
		$this->hasOne = $model;
		return $this;
	}

	public function manyToMany($models)
	{
		if (!is_array($models)) 
			throw new ScaffoldConfigurationException('manyToMany(array(array("FirstModelName", "FirstForeignId", "SecondModelName", "SecondForeignId", "many_to_many_table", "FirstColumnName", "SecondColumnName"))); ');
		$this->manyToMany = $models;
		return $this;
	}

	public function modelDescription($desc)
	{
		$this->description = (is_string($desc))? $desc : "";
		return $this;
	}


	public function describeColumn($describes) 
	{	
		if (!is_array($describes))
			throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Must pass array in. ');

		foreach ($describes as $d) {
			if (!isset($d['column']) || !isset($d['desc'])) 
				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Both argument are required');
			if (array_key_exists($d['column'], $this->columns)) { 
				$this->columns["{$d['column']}"]["description"] = (is_string($d['desc']))? $d['desc'] : ""; 	
			} else {
				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here")));  Column doesn\'t exist.');
			}
		}
		
		return $this;
	}

	public function displayOrder($column)
	{
		if (!is_string($column)) 
			throw new ScaffoldConfigurationException('displayOrder("name"); First argument should name of column. ');
		if (array_key_exists($column, $this->columns)) { 
			if ($this->columns["{$column}"]["type"] != "int") 
				throw new ScaffoldConfigurationException('displayOrder("name"); Column type should be an integer. ');
			$this->columns["{$column}"]["display_order"] = true;
			return $this;
		} else {
			throw new ScaffoldConfigurationException('displayOrder("name");  Column doesn\'t exist.');
		}
	}

	public function listColumns($list)
	{
		if (!is_array($list)) 
			throw new ScaffoldConfigurationException('list(array("name", "count", "active")); First argument should be an array of the names of the columns wanted listed on landing page.');
		$this->list = $list;
		return $this;
	}

	public function onlyOne()
	{
		$this->onlyOne = true;
		return $this;
	}

	public function deleteAction($bool)
	{	
		$this->deleteAction = $bool;
		return $this;
	}

	public function displayOrderColumn($column)
	{
		if (!is_string($column)) 
			throw new ScaffoldConfigurationException('displayOrderColumn("name");  First argument should name of column.');
		if (array_key_exists($column, $this->columns)) {
			$this->columns["{$column}"]["displayOrder"] = true;
			return $this;
		} else {
			throw new ScaffoldConfigurationException('displayOrderColumn("name");  Column doesn\'t exist.');
		}
	}

	public function belongsToUser()
	{
		$this->belongsToUser = true;
		return $this;
	}

	public function readOnly($info)
	{
		$base = 'readOnly(array("columnOne", "columnTwo", "columnThree"));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		foreach ($info as $i) {
			if (array_key_exists($i, $this->columns)) {
				$this->columns["{$i}"]["readOnly"] = true;
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}
		}
		return $this;
	}
	
	public function associateToSameParent($foreignKey)
	{
		$this->associateToSameParent = $foreignKey;
		return $this;
	}

	public function onlyLoadSameChildren($foreignKey)
	{
		$this->onlyLoadSameChildren = $foreignKey;
		return $this;
	}
	
	public function mapConfig($config)
	{
		if ($this->columns == null)
			throw new ScaffoldConfigurationException("Model must be loaded before mapConfig method is called.");

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
				case 'readOnly':
					$this->readOnly($f);
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
					throw new ScaffoldUnknownConfigurationIndexException("Unknown {$key} index in config.");
					break;
			}
		}

		return $this;
	}

	/** ******** End of Public Configuration API methods ********** */


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