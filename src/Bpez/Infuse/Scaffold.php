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
use Transit\Transformer\Image\ResizeTransformer;
use Transit\File;
use Illuminate\Support\Facades\Log;
use Bpez\Infuse\Exceptions\ScaffoldConfigurationException;
use Bpez\Infuse\Exceptions\ScaffoldModelNotRecognizedException;
use Bpez\Infuse\Exceptions\ScaffoldUnknownConfigurationIndexException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
class Scaffold
{

	/**
   * Toggles role/permission authentication.
   *
   * @access private
   * @var bool
   */
	private $rolePermission = false; 
	
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
   * Contains the ORM model primary key.
   *
   * @access private
   * @var object
   */
	private $primaryKey = null;

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
   * Associates model to current user when a new instance is created and only load ones that belong to user.
   *
   * @access private
   * @var boolean
   */
	private $belongsToUser = false;

	/**
   * Associates model to current user when a new instance is created and only load ones that belong to user with the many to many relationship.
   *
   * @access private
   * @var boolean
   */
	private $belongsToUserManyToMany = false;

	/**
   * On the listing page only loads model instances that are siblings of the user
   * of the foriegn key provided.
   *
   * @access private
   * @var boolean|string
   */
	private $onlyLoadSiblingsOfUserRelatedBy = false;

	/**
   * Associates model to the same parent of the user by the foreign key given. 
   *
   * @access private
   * @var boolean|string
   */
	private $associateToSameParentOfUserRelatedBy = false;

	/**
   * Only load model if sibling of user's parent. Specify parent 
   * foreign id and current model  foreign id.
   *
   * @access private
   * @var boolean|array
   */
	private $siblingOfUserParentOnly = false;

	/**
   * Disables the action delete action on an instance of the model. 
   *
   * @access private
   * @var boolean
   */
	private $deleteAction = true;

	/**
   * Adds elequent function calls list as an action on the listing page under Edit, Show, Delete
   *
   * @access private
   * @var array
   */
	private $callFunctions = array();

	/**
   * Adds other function calls to the model in the Other Action dropdown on the listing page. static function call to the model.
   *
   * @access private
   * @var array
   */
	private $addOtherActions = array();

   /**
   * Call function before edit page.
   *
   * @access private
   * @var array
   */
   private $beforeEdit;

	/**
   * Attach elequent where method calls to the main filter
   *
   * @access private
   * @var array
   */
	private $permanentFilters = array();

	/**
   * Contains the model configuration to what models to import from. 
   *
   * @access private
   * @var boolean|array
   */
	private $importFromModel = false;


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
   * \Illuminate\Events\Dispatchet instance of current event
   *
   * @access protected
   * @var object
   */
	protected $event;

   /**
   * Date format for laravel timestamps (updated_at, created_at) displayed infuse
   *
   * @access private
   * @var string
   */
   private $formatLaravelTimestamp;
   
	

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
	public function __construct(\Illuminate\View\Environment $view, \InfuseUser $user, \Illuminate\Support\Facades\DB $db, \Illuminate\Http\Request $request, \Event $event)
	{	
		$this->view = $view;
		$this->user = $user;
		$this->request = $request;
		$this->event = $event;
		self::$db = $db; 
		$this->rolePermission = (\Config::get("infuse::role_permission"))? true : false;
      $this->formatLaravelTimestamp = \Config::get("infuse::format_laravel_timestamp");
      $this->beforeEdit = function() { return true; };
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
		if (is_subclass_of($this->model, 'Bpez\Infuse\InfuseEloquent') 
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

		$this->order['column'] = $this->model->getKeyName();

		$this->primaryKey = $this->model->getKeyName();
		
		foreach ($columns as $column) {
			if ($column->Field != $this->primaryKey ) {
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

	/**
	 * Set the limit for the listing page
	 *
   * Pass in an integer to set the limit for the page
   *
   * @api
   *
   * @example limit($limit) 
   <pre>
   	// Single
		$scaffold->limit($limit);</br></br>
		// Multiple
		$scaffold->columnName(array(
		&nbsp;array("column" => "someColumn", "newName" => "someName"),
		&nbsp;array("column" => "someColumn2", "newName" => "someName2")
		));
   </pre>
   *
   * @param integer $limit 
   *
   * @return $this Returns this so that it can be easily chanined.
   *
   */
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
		$base = 'addSelect(array(array("column" => $columnName, "array" => array(), "insertBlank" => false, "topSelect" => false, "nested" => array("Model1", "Model2"), "nestedLastArray" => array() )));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['column']) && isset($info['array']) ) {

			if ( array_key_exists($info['column'], $this->columns) ) {
				$this->columns["{$info['column']}"]["select"] = $info['array'];
				if (isset($info['insertBlank']) && $info['insertBlank'] == true)
					$this->columns["{$info['column']}"]["select_blank"] = true;
				if (isset($info['topSelect']) && $info['topSelect'] == true)
					$this->columns["{$info['column']}"]["top_select"] = true;
				if (isset($info['nested']) && $info['nested'] == true)
					$this->columns["{$info['column']}"]["nested"] = $info['nested'];
				if (isset($info['nestedLastArray']) && $info['nestedLastArray'] == true)
					$this->columns["{$info['column']}"]["nested_last_array"] = $info['nestedLastArray'];
				
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}

		// If more then one roles
		} else {
			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['column']) || !isset($i['array']))) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				if (!is_string($i['column']) || !is_array($i['array'])) 
					throw new ScaffoldConfigurationException($base.' Column must be a string. Array index must be an array.');
				if (array_key_exists($i['column'], $this->columns)) {
					$this->columns["{$i['column']}"]["select"] = $i['array'];
					if (isset($i['insertBlank']) && $i['insertBlank'] == true)
						$this->columns["{$i['column']}"]["select_blank"] = true;
					if (isset($i['topSelect']) && $i['topSelect'] == true)
						$this->columns["{$i['column']}"]["top_select"] = true;
					if (isset($i['nested']) && $i['nested'] == true)
						$this->columns["{$i['column']}"]["nested"] = $i['nested'];
					if (isset($i['nestedLastArray']) && $i['nestedLastArray'] == true)
						$this->columns["{$i['column']}"]["nested_last_array"] = $i['nestedLastArray'];
					
				} else {
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
				}
			}
		}
		
		return $this;
	}

	public function importFromModel($info)
	{
		$base = 'importFromModel(array("child_resource_to_use", "ParentModelOfChild", array("description" => "Some description", "name" => "Some Name", "map" = array(), "list" => array())) );';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		if (!is_string($info[0]) || !is_string($info[1])) 
				throw new ScaffoldConfigurationException($base.' First and second argument should name of type string in each import array. ');
		if (!is_array($info[2])) 
			throw new ScaffoldConfigurationException($base.' Third argument can only be an array in each import array. ');
		/*
		foreach ($info as $i) {
			if (!is_string($i[0]) || !is_string($i[1])) 
				throw new ScaffoldConfigurationException($base.' First and second argument should name of type string in each import array. ');
			if (!is_array($i[2])) 
				throw new ScaffoldConfigurationException($base.' Third argument can only be an array in each import array. ');
		}*/

		$this->importFromModel = $info;
		
		return $this;
	}

	public function addMultiSelect($info)
	{	
		$base = 'addMultiSelect(array("column" => $columnName, "array" => array()));';

		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['column']) && isset($info['array'])) {

			if (is_string($info['column']) && is_array($info['array'])) {
				if (array_key_exists($info['column'], $this->columns))
					$this->columns["{$info['column']}"]["multi_select"] = $info['array'];
				else
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			} else {
				throw new ScaffoldConfigurationException($base.' column index must be a string and array index must be an array.');
			}

		// If more then one
		} else {
			foreach ($info as $i) {
				if (!is_array($i) && isset($i['column']) && isset($i['array'])) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				if (!is_string($i['column']) || !is_array($i['array']))
					throw new ScaffoldConfigurationException($base.' column index must be a string and array index must be an array.');
				if (array_key_exists($i['column'], $this->columns))
					$this->columns["{$i['column']}"]["multi_select"] = $i['array'];
				else
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}
		}
		return $this;
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
		$base = 'fileUpload(array(array("column" => $columnName)));';

		if (!is_array($uploads)) 
			throw new ScaffoldConfigurationException($base.' First argument must an array. column index must be set. ');
		
			
		// If only one
		if ( is_array($uploads) && isset($uploads['column']) && is_string($uploads['column'])) {

			if (array_key_exists($uploads['column'], $this->columns)) { 
				$validations = (isset($uploads['validations']))? $uploads['validations'] : array();
				$imageCrop = (isset($uploads['imageCrop']))? $uploads['imageCrop'] : false; 
				$this->columns["{$uploads['column']}"]["upload"] = array("validations" => $validations, "imageCrop" => $imageCrop);
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}

		// If more then one
		} else {

			foreach ($uploads as $key => $info) {
				
				if (!is_array($info) && !isset($info['column'])) 
					throw new ScaffoldConfigurationException($base.'  First argument must an array. column index must be set. ');
				if (!is_string($info['column'])) 
					throw new ScaffoldConfigurationException($base.' First argument should name of column. ');
				if (array_key_exists($info['column'], $this->columns)) { 
					$validations = (isset($info['validations']))? $info['validations'] : array();
					$imageCrop = (isset($info['imageCrop']))? $info['imageCrop'] : false; 
					$this->columns["{$info['column']}"]["upload"] = array("validations" => $validations, "imageCrop" => $imageCrop);
				} else {
					throw new ScaffoldConfigurationException($base.'  Column doesn\'t exist.');
				}
			}
		}

		return $this;
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

      // If only one
      if ( is_array($describes) && isset($describes['column']) && isset($describes['desc'])) {
         if (array_key_exists($describes['column'], $this->columns)) { 
            $this->columns["{$describes['column']}"]["description"] = (is_string($describes['desc']))? $describes['desc'] : "";  
         } else {
            throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here")));  Column doesn\'t exist.');
         }

      // If more then one
      } else {

   		foreach ($describes as $d) {
   			if (!isset($d['column']) || !isset($d['desc'])) 
   				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Both argument are required');
   			if (array_key_exists($d['column'], $this->columns)) { 
   				$this->columns["{$d['column']}"]["description"] = (is_string($d['desc']))? $d['desc'] : ""; 	
   			} else {
   				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here")));  Column doesn\'t exist.');
   			}
   		}
      }
		
		return $this;
	}

	public function displayOrder($columns)
	{
      $base = 'displayOrder(array("column"));';

      if (!is_array($columns)) 
         throw new ScaffoldConfigurationException($base.' First argument must an array. column index must be set. ');
      
      foreach ($columns as $column) {
         if (array_key_exists($column, $this->columns)) { 
            $this->columns["{$column}"]["display_order"] = true;
         } else {
            throw new ScaffoldConfigurationException($base.'  Column doesn\'t exist.');
         }
      }
      
      return $this;
	}

	public function listColumns($list)
	{
		if (!is_array($list)) 
			throw new ScaffoldConfigurationException('list(array("name", "count", "active")); First argument should be an array of the names of the columns wanted listed on landing page.');
		array_push($list, "updated_at");
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
	
	public function associateToSameParentOfUserRelatedBy($foreignKey)
	{
		$this->associateToSameParentOfUserRelatedBy = $foreignKey;
		return $this;
	}

	public function onlyLoadSiblingsOfUserRelatedBy($foreignKey) 
	{
		$this->onlyLoadSiblingsOfUserRelatedBy = $foreignKey;
		return $this;
	}

	public function siblingOfUserParentOnly($info) 
	{	
		$base = 'siblingOfUserParentOnly(array("parent_model" => "SomeModel", "parent_foriegn_id" => "some_id", "foreign_id" => "some_id"));';

		if (!is_array($info)) 
			throw new ScaffoldConfigurationException($base.' First argument should be an array. ');
		if (!array_key_exists("parent_model", $info)) 
			throw new ScaffoldConfigurationException($base.' parent_model index should be set. ');
		if (!array_key_exists("parent_foriegn_id", $info))
			throw new ScaffoldConfigurationException($base.' parent_foriegn_id index should be set. ');
		if (!array_key_exists("foreign_id", $info))
			throw new ScaffoldConfigurationException($base.' foreign_id index should be set. ');
		
		$this->siblingOfUserParentOnly = $info;
		return $this;
	}

	
	public function callFunction($info) 
	{	
		$base = 'callFunction(array("function" => "duplicate", "display_name" => "Duplicate")); Optional: target (anchor tag target), long_process (UI Block Screen and message shown).';
		
		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['function']) && isset($info['display_name']) ) {

			array_push($this->callFunctions, $info);

		// If more then one
		} else {
			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['function']) || !isset($i['display_name']))) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				array_push($this->callFunctions, $i);
			}
		}
		
		return $this;
	}

	public function addOtherAction($info) 
	{	
		$base = 'addOtherAction(array("function" => "generateReport", "display_name" => "Generate Report"));';
		
		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['function']) && isset($info['display_name']) ) {

			array_push($this->addOtherActions, $info);

		// If more then one
		} else {
			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['function']) || !isset($i['display_name']))) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				array_push($this->addOtherActions, $i);
			}
		}
		
		return $this;
	}

   public function beforeEdit($info) 
   {  
      $base = 'beforeEdit(function(){});';
      
      if (!is_callable($info)) 
         throw new ScaffoldConfigurationException($base.' Must be a function.');

      $this->beforeEdit = $info;
      
      return $this;
   }
   

	public function belongsToUserManyToMany($info)
	{
		$base = 'belongsToUserManyToMany(array("Model", "pivot_table", "user_id", "model_foreign_key_id"));';
		
		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		
		if (count($info) != 4 ) 
			throw new ScaffoldConfigurationException($base.' Should have the laravel belongsToMany four parameters.'. count($info));

		$this->belongsToUserManyToMany = $info;
		
		return $this;
	}

	public function addPermanentFilters($info) 
	{	
		$base = 'addPermanentFilters(array("column" => "columnName", "operator" => "=", "value" => 34));';
		
		if (!is_array($info) ) 
			throw new ScaffoldConfigurationException($base.' Must be an array.');

		// If only one
		if ( isset($info['column']) && isset($info['operator']) && isset($info['value'])) {

			array_push($this->permanentFilters, $info);

		// If more then one
		} else {
			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['column']) || !isset($i['operator']) || !isset($i['value']))) 
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				array_push($this->permanentFilters, $i);
			}
		}
		
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
				case 'associateToSameParentOfUserRelatedBy':
					$this->associateToSameParentOfUserRelatedBy($f);
					break;
				case 'onlyLoadSiblingsOfUserRelatedBy':
					$this->onlyLoadSiblingsOfUserRelatedBy($f);
					break;
				case 'addCkeditor':
					$this->addCkeditor($f);
					break;
				case 'addSelect':
					$this->addSelect($f);
					break;
				case 'addMultiSelect':
					$this->addMultiSelect($f);
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
				case 'importFromModel':
					$this->importFromModel($f);
					break;
				case 'siblingOfUserParentOnly':
					$this->siblingOfUserParentOnly($f);
					break;
				case 'callFunction':
					$this->callFunction($f);
					break;
				case 'belongsToUserManyToMany':
					$this->belongsToUserManyToMany($f);
					break;
				case 'addOtherAction':
					$this->addOtherAction($f);
					break;
				case 'addPermanentFilters':
					$this->addPermanentFilters($f);
					break;
            case 'beforeEdit':
               $this->beforeEdit($f);
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


	public function route()
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
				break;
			case 'icsv':
				$this->importCSV();
				break;
			case 'icsvc':
				$this->importCSVCustom();
				break;
			case 'cf':
				$this->callActionFunction();
				break;
			case 'oa':
				$this->callOtherAction();
				break;

			default:
				$this->listAll();
				break;
		} 
	}


	public function checkPermissions($redirectBack)
  {	
  	// Check if role/permission authentication is on otherwise return false and check no permissions.
  	if (!$this->rolePermission)
  		return false;

  	switch ($this->action) {
			case 'l':
			case 's':
			case 'f':
				$action = "view";
				$redirect = "/admin/dashboard";
				break;
			case 'e':
			case 'u':
			case 'cf':
			case 'oa':
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

			if ($this->user->is('Super Admin'))
				return false;

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

  private function filterQueryForListings($count = false, &$pagination)
  {
  	$model = $this->model;
  	$modelInstance = null;

  	$offset = 0;
		$page = Util::get("pg");
		if ($page && $page != 1 && $page != 'a') {
			$offset =  ($page-1) * $pagination['limit'];
			$pagination['active_page'] = $page;
		}

  	if ($this->siblingOfUserParentOnly) { 
			$foreign_id = $this->user 
											->belongsTo($this->siblingOfUserParentOnly['parent_model'], Util::createForeignKeyString($this->siblingOfUserParentOnly['parent_model']))
											->firstOrFail()
											->{$this->siblingOfUserParentOnly['parent_foriegn_id']};
		}

		if ($this->belongsToUserManyToMany && !$this->user->is('Super Admin')) {
			$modelInstance = $this->user->belongsToMany($this->belongsToUserManyToMany[0], $this->belongsToUserManyToMany[1], $this->belongsToUserManyToMany[2], $this->belongsToUserManyToMany[3]);
		} else {
			$modelInstance = new $model;
		}
		
		if (!$count) { 
			if ($page == "a") { 
				$modelInstance = $modelInstance->orderBy($this->order["column"], $this->order["order"]);
			} else { 
				$modelInstance = $modelInstance->orderBy($this->order["column"], $this->order["order"])->take($pagination['limit'])->skip($offset);
			}
		}
		
  	if ($this->infuseLogin && !$this->user->is('Super Admin'))
			$modelInstance = $modelInstance->where("id", "!=", 1)->where("username", "!=", 'super');

		if ($this->belongsToUser && !$this->user->is('Super Admin'))
			$modelInstance = $modelInstance->where("infuse_user_id", "=", $this->user->id);

		if ($this->onlyLoadSiblingsOfUserRelatedBy)
			$modelInstance = $modelInstance->where($this->onlyLoadSiblingsOfUserRelatedBy, "=", $this->user->{$this->onlyLoadSiblingsOfUserRelatedBy});

		if ($this->siblingOfUserParentOnly)
			$modelInstance = $modelInstance->where($this->siblingOfUserParentOnly['foreign_id'], "=", $foreign_id);

		if ($this->rolePermission && $model instanceof \InfuseUser && !$this->user->is('Super Admin')) { 
			$user = $this->user;

			if ($user->can("infuse_user_load_level_comparison_or_equal_zero")) { 
   			$db = self::$db;
	   		$originalIds = $db::table('users')->lists("id");
	   		$newIds = $db::table('role_user')->distinct("user_id")->lists("user_id");
				$usersWithNoRole = array_diff($originalIds, $newIds);
				if (!empty($usersWithNoRole))
					$modelInstance = $modelInstance->orWhereIn('id', $usersWithNoRole);
   		}

			$role = $this->user->roles()->orderBy("level", "asc")->limit(1)->first();
			$level = (count($role) == 1)? $role->level : 0;
			
			$modelInstance = $modelInstance->with('roles')->whereHas('roles', function($q) use ($level, $user) {
     		if ($user->can("infuse_user_load_level_comparison_greater_or_equal")) {
     			$q->where('level', '>=', $level);
     		} else {
     			$q->where('level', '>', $level);
     		}	
			}); 

		}

		foreach($this->permanentFilters as $where) {
			$modelInstance = $modelInstance->where($where['column'], $where['operator'], $where['value']);
		}

  	return $modelInstance;
  }


	private function listAll()
	{	
		$pagination = array(
			"limit" => $this->limit,
			"active_page" => 1,
			"count" => 0
		);

		
		$pagination['count'] = $this->filterQueryForListings(true, $pagination)->count();

		$prepareModel = $this->filterQueryForListings(false, $pagination);

		if (Util::get("toCSV"))
			$this->toCSV($prepareModel);

		$this->entries = $prepareModel->get();
		
		$this->header = array( 
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"onlyOne" => $this->onlyOne,
				"addOtherActions" => $this->addOtherActions,
				"columnNames" => $this->columnNames
		);
	}

	private function listAllFilter()
	{
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

		$prepareModel = $this->filterQueryForListings(false, $pagination);

		$columnNames = array_keys($this->columns);
		$comparisons = array("equals" => "=", "less than" => "<", "greater than" => ">", "not equal to" => "!=", "contains" => "like");

		foreach($filters as $filter) {
         if (isset($comparisons[$filter[1]]) && $comparisons[$filter[1]] == "like" && in_array($filter[0], $columnNames)) {
            $prepareModel = $prepareModel->whereRaw("{$filter[0]} LIKE ?", array("%".$filter[2]."%"));
         } else if (isset($comparisons[$filter[1]])) {
            $prepareModel = $prepareModel->where($filter[0], $comparisons[$filter[1]], $filter[2]);
         }
		}


		if (Util::get("toCSV"))	{
			$this->toCSV($prepareModel);
		}

		$this->entries = $prepareModel->get();

		$prepareModelForCount = $this->filterQueryForListings(true, $pagination);

		foreach($filters as $filter) {
         if (isset($comparisons[$filter[1]]) && $comparisons[$filter[1]] == "like" && in_array($filter[0], $columnNames)) {
            $prepareModelForCount = $prepareModelForCount->whereRaw("{$filter[0]} LIKE ?", array("%".$filter[2]."%"));
         } else if (isset($comparisons[$filter[1]])) {
            $prepareModelForCount = $prepareModelForCount->where($filter[0], $comparisons[$filter[1]], $filter[2]);
         }
		}

		$pagination['count'] = $prepareModelForCount->count();
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"filters" => $filters,
				"onlyOne" => $this->onlyOne,
				"addOtherActions" => $this->addOtherActions,
				"columnNames" => $this->columnNames
			);
	}

	

	public function search($search = null, $columns)
	{
		$pagination = array(
			"limit" => $this->limit,
			"active_page" => 1,
			"count" => 0
		);

		
		$prepareModel = $this->filterQueryForListings(false, $pagination);
		
		foreach ($columns as $column) {
			if ($columns[0] == $column) 
				$prepareModel =  $prepareModel->where($column, "LIKE", "%".$search."%");
			else
				$prepareModel = $prepareModel->orWhere($column, "LIKE", "%".$search."%");
		}


		if (Util::get("toCSV"))	{
			$this->toCSV($prepareModel);
		}

		$this->entries = $prepareModel->get();

		$prepareModelForCount = $this->filterQueryForListings(true, $pagination);

		foreach ($columns as $column) {
			if ($columns[0] == $column) 
				$prepareModelForCount = $prepareModelForCount->where($column, "LIKE", "%".$search."%");
			else
				$prepareModelForCount = $prepareModelForCount->orWhere($column, "LIKE", "%".$search."%");
		}

		$pagination['count'] = $prepareModelForCount->count();
		
		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"onlyOne" => $this->onlyOne,
				"addOtherActions" => $this->addOtherActions,
				"columnNames" => $this->columnNames
			);
	}


	public function closestLocationsWithinRadius($search, $columns, $latitude, $longitude, $distance)
	{
		$pagination = array(
			"limit" => $this->limit,
			"active_page" => 1,
			"count" => 0
		);

		$page = Util::get("pg");
		if ($page && $page != 1 && $page != 'a') {
			$pagination['active_page'] = $page;
		}
		

		$prepareModel = $this->filterQueryForListings(false, $pagination);

		if (!empty($search)) {
			foreach ($columns as $column) {
				if ($columns[0] == $column) 
					$prepareModel = $prepareModel->where($column, "LIKE", "%".$search."%");
				else
					$prepareModel = $prepareModel->orWhere($column, "LIKE", "%".$search."%");
			}
		}
		
		
		/*
			Implemented Haversine formula
			------------------------------
			Will find the closest locations that are within a radius of X miles to the latitude, longitude coordinate.
			reference: https://developers.google.com/maps/articles/phpsqlsearch_v3?csw=1
		*/

		$db = self::$db;
		$prepareModel = $prepareModel
			->select($db::raw("*, ( 3959 * acos( cos( radians({$latitude}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$longitude}) ) + sin( radians({$latitude}) ) * sin( radians( latitude ) ) ) ) AS distance "))
			->having('distance', '<', $distance);


		if (Util::get("toCSV"))	{
			$this->toCSV($prepareModel);
		}

		

		$this->entries = $prepareModel->get();

		$prepareModelForCount = $this->filterQueryForListings(true, $pagination);
		
		if (!empty($search)) { 
			foreach ($columns as $column) {
				if ($columns[0] == $column) 
					$prepareModelForCount = $prepareModelForCount->where($column, "LIKE", "%".$search."%");
				else
					$prepareModelForCount = $prepareModelForCount->orWhere($column, "LIKE", "%".$search."%");
			}
		}

		$prepareModelForCount = $prepareModelForCount
			->select($db::raw("( 3959 * acos( cos( radians({$latitude}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$longitude}) ) + sin( radians({$latitude}) ) * sin( radians( latitude ) ) ) ) AS distance "))
			->having('distance', '<', $distance);


		$pagination['count'] = count($prepareModelForCount->get()->toArray());

		$this->header = array(
				"pagination" => $pagination,
				"name" => $this->name,
				"list" => $this->list,
				"onlyOne" => $this->onlyOne,
				"addOtherActions" => $this->addOtherActions,
				"columnNames" => $this->columnNames
			);
	}


	private function show()
	{	
		$model = $this->model;
		$this->header = array(
				"pagination" => array(),
				"name" => $this->name,
				"columnNames" => $this->columnNames,
				"onlyOne" => $this->onlyOne
			);

		if ($this->belongsToUser) {
			try {

				$this->entries = $model->where("infuse_user_id", "=", $this->user->id)
				->where("id", "=", Util::get("id"))
				->firstOrFail();

			} catch (ModelNotFoundException $e) {

				if (Util::get("stack")) {
					$redirect_path = Util::childBackLink();
				} else {
					$redirect_path = Util::redirectUrl();
				}

				Util::flash(array(
					"message" => "Can not see this entry.", 
					"type" => "error"
					)
				);
				
				if (!$this->testing) {
					header("Location: {$redirect_path}");
					exit();
				}
			}
			
			
		} else if ($this->belongsToUserManyToMany) {
				try {
					$this->entries = $this->user
						->belongsToMany($this->belongsToUserManyToMany[0], $this->belongsToUserManyToMany[1], $this->belongsToUserManyToMany[2], $this->belongsToUserManyToMany[3])
						->findOrFail(Util::get("id"));
				} catch (ModelNotFoundException $e) {

					if (Util::get("stack")) {
						$redirect_path = Util::childBackLink();
					} else {
						$redirect_path = Util::redirectUrl();
					}

					Util::flash(array(
						"message" => "Can not see this entry.", 
						"type" => "error"
						)
					);
					
					if (!$this->testing) {
						header("Location: {$redirect_path}");
						exit();
					}
				}
				
		} else {
			$this->entries = $model::find(Util::get("id"));
		}
		
	}

	private function edit()
	{	
		if ($this->infuseLogin && Util::get("id") == 1 && !$this->user->is('Super Admin')) {
			if (Util::get("stack")) {
				$redirect_path = Util::childBackLink();
			} else {
				$redirect_path = Util::redirectUrl();
			}

			Util::flash(array(
				"message" => "Can not edit this entry.", 
				"type" => "error"
				)
			);

			if (!$this->testing) {
				header("Location: {$redirect_path}");
				exit();
			}
		}


		$model = $this->model;
		$this->header = array(
				"edit" => true, 
				"name" => $this->name,
				"associations" => $this->hasMany,
				"manyToManyAssociations" => $this->manyToMany,
				"hasOneAssociation" => $this->hasOne,
				"onlyOne" => $this->onlyOne,
				"columnNames" => $this->columnNames,
				"importFromModel" => $this->importFromModel
			);
      $beforeEdit = $this->beforeEdit;

		$post = Util::flashArray("post");
		if (!$post) {
			
			if ($this->belongsToUser) {
				try {
					$this->entries = $model->where("infuse_user_id", "=", $this->user->id)
						->where("id", "=", Util::get("id"))
						->firstOrFail();
				} catch (ModelNotFoundException $e) {

					if (Util::get("stack")) {
						$redirect_path = Util::childBackLink();
					} else {
						$redirect_path = Util::redirectUrl();
					}

					Util::flash(array(
						"message" => "Can not edit this entry.", 
						"type" => "error"
						)
					);
					
					if (!$this->testing) {
						header("Location: {$redirect_path}");
						exit();
					}
				}
				
				
			} else if ($this->belongsToUserManyToMany) {
				try {
					$this->user
						->belongsToMany($this->belongsToUserManyToMany[0], $this->belongsToUserManyToMany[1], $this->belongsToUserManyToMany[2], $this->belongsToUserManyToMany[3])
						->findOrFail(Util::get("id"));
               // laravel many to many bug. Call by it self to get updated_at and created_at times stamps returned
               $this->entries = $model::find(Util::get("id"));

				} catch (ModelNotFoundException $e) {

					if (Util::get("stack")) {
						$redirect_path = Util::childBackLink();
					} else {
						$redirect_path = Util::redirectUrl();
					}

					Util::flash(array(
						"message" => "Can not edit this entry.", 
						"type" => "error"
						)
					);
					
					if (!$this->testing) {
						header("Location: {$redirect_path}");
						exit();
					}
				}
				
			} else {
				$this->entries = $model::find(Util::get("id"));
			}
			
		} else {
			$this->entries = $model::find($post["id"]);
			foreach ($this->columns as $column) {
				if (isset($post[$column['field']]))
					$this->entries->{$column['field']} = $post[$column['field']];
			}
		}

      // Check if beforeEdit function returns false then exit and redirect
      $exitCheck = $beforeEdit($this->entries);
      if (!$exitCheck) {
         if (Util::get("stack")) {
            $redirect_path = Util::childBackLink();
         } else {
            $redirect_path = Util::redirectUrl();
         }

         Util::flash(array(
            "message" => "Can not edit this entry.", 
            "type" => "error"
            )
         );
         
         if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
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
				"columnNames" => $this->columnNames,
				"importFromModel" => $this->importFromModel
			);
		$post = Util::flashArray("post");
		if (!$post) {
			$this->entries = $model;
		} else {
			$this->entries = new $model;
			foreach ($this->columns as $column) {
				if (isset($post[$column['field']]))
					$this->entries->{$column['field']} = $post[$column['field']];
			}
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

		if ($this->belongsToUser) {
			try {

				$entry = $model->where("infuse_user_id", "=", $this->user->id)
				->where("id", "=", Util::get("id"))
				->firstOrFail();

			} catch (ModelNotFoundException $e) {

				if (Util::get("stack")) {
					$redirect_path = Util::childBackLink();
				} else {
					$redirect_path = Util::redirectUrl();
				}

				Util::flash(array(
					"message" => "Can not delete this entry.", 
					"type" => "error"
					)
				);
				
				if (!$this->testing) {
					header("Location: {$redirect_path}");
					exit();
				}
			}
			
			
		} else {
			$entry = $model::find(Util::get("id"));
		}

		

		if ($this->infuseLogin && $entry->id == 1 && $entry->username == 'super' ) {
			Util::flash(array(
				"message" => "Can't delete super.", 
				"type" => "error"
				)
			);

		} else {

			$entryBackUp = clone $entry;

			if ($entry->delete()) {
				foreach ($this->columns as $column) {
					if (array_key_exists("upload", $column) && !empty($entryBackUp->{$column['field']}) && file_exists($_SERVER['DOCUMENT_ROOT']."/".$entryBackUp->url($column['field']))) {
            $currentFile = $_SERVER['DOCUMENT_ROOT']."/".$entryBackUp->url($column['field']);
            unlink($currentFile);
            $name = pathinfo($currentFile, PATHINFO_FILENAME);
            $ext  = pathinfo($currentFile, PATHINFO_EXTENSION);
            $retinaImage = $entryBackUp->uploadPath($column['field']).$name."@2x.".$ext;
            if (file_exists($retinaImage)) {
              unlink($retinaImage);
            }
						$entryBackUp->{$column['field']} = ""; // Set to blank so nested unlinks can work in model
					}
				}

				Util::flash(array(
					"message" => "Deleted {$this->name}.", 
					"type" => "success"
					)
				);

			} else {
				Util::flash(array(
					"message" => "Failed to delete {$this->name}.", 
					"type" => "error"
					)
				);
			}

			
			
			
		}
		 

		if (Util::get("stack")) {
			$redirect_path = Util::childBackLink();
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
					
					unlink($destinationPath.$filename);
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

	private function importCSVCustom()
	{
		$model = $this->model;
		$entry = $model::find(Util::get("parent_id"));
		$redirect_path = Util::get("back");
		$function = Util::get("function");

		$entry->{$function}();
		
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

			$message = array("message" => "Updated {$this->name}.", "type" => "success");

			if ($this->infuseLogin && $entry->id == 1 && !$this->user->is('Super Admin')) {
				if (Util::get("stack")) {
					$redirect_path = Util::childBackLink();
				} else {
					$redirect_path = Util::redirectUrl();
				}

				Util::flash(array(
					"message" => "Can not update this entry.", 
					"type" => "error"
					)
				);

				if (!$this->testing) {
					header("Location: {$redirect_path}");
					exit();
				}
			}

		} elseif (Util::get("stack")) {
			$entry = $model;
			$message = array("message" => "Added {$this->name}.", "type" => "success");
		} else {
			$entry = $model;
			$message = array("message" => "Created {$this->name}.", "type" => "success");
		}

		$fileErrors = array(); 
		
		foreach ($this->columns as $column) {

			if (array_key_exists("upload", $column)) {
		

				// If column in files array or if uploaded already by cropping tool
				$checkIfInFiles = (array_key_exists($column['field'], $_FILES) && !empty($_FILES["{$column['field']}"]['name']));
				$checkIfAlreadyUploaded = (Util::get($column['field']) && !filter_var(Util::get($column['field']), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));
				$checkIfExternalFile = (Util::get($column['field']) && filter_var(Util::get($column['field']), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));

				// Only process if file present
				if ($checkIfInFiles || $checkIfAlreadyUploaded || $checkIfExternalFile) {
					
					if ($checkIfInFiles) {
						$passFileTotransit = $_FILES["{$column['field']}"];
					} else if ($checkIfAlreadyUploaded) {
						$passFileTotransit = $_SERVER['DOCUMENT_ROOT'].Util::get($column['field']);
					} else if ($checkIfExternalFile) {
						$passFileTotransit  = Util::get($column['field']);
					}


					$transit = new Transit($passFileTotransit);

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

						if ($checkIfInFiles) {
							$success = $transit->upload();
						} else if ($checkIfAlreadyUploaded) {
							$overwrite = false;
							$delete = true;
							$success = $transit->importFromLocal($overwrite, $delete);
						} else if ($checkIfExternalFile) {
							$overwrite = false;
							$options = array(
		            CURLOPT_RETURNTRANSFER => true,
		            CURLOPT_FOLLOWLOCATION => false,
		            CURLOPT_FAILONERROR => true,
		            CURLOPT_SSL_VERIFYPEER => false
			        );
							$success = $transit->importFromRemote($overwrite, $options);
						}
						
						if ($success) {
							$fileName = explode(DIRECTORY_SEPARATOR, $transit->getOriginalFile());
              $fileName = end($fileName);
							
							if (!empty($entry->{$column['field']}) && file_exists($_SERVER['DOCUMENT_ROOT']."/".$entry->url($column['field']))) {
                $currentFile = $_SERVER['DOCUMENT_ROOT']."/".$entry->url($column['field']);
                unlink($currentFile);
                $name = pathinfo($currentFile, PATHINFO_FILENAME);
                $ext  = pathinfo($currentFile, PATHINFO_EXTENSION);
                $retinaImage = $entry->uploadPath($column['field']).$name."@2x.".$ext;
                if (file_exists($retinaImage)) {
                  unlink($retinaImage);
                }
              }
								
							
              if(strpos($fileName, "@2x.") !== FALSE) {
                $uploadPath = $model->uploadPath($column['field']);
                $halfRetinaSize = floor($transit->getOriginalFile()->width()/2);
                $retinaFileName = $fileName;

                $fileName = explode("@2x.", $fileName);
                $fileName = $fileName[0].".".$fileName[1];
                if (copy($uploadPath.$retinaFileName, $uploadPath.$fileName)) {

                  $transitRetina = new ResizeTransformer(array('width' => $halfRetinaSize));

                  if (!$transitRetina->transform(new File($uploadPath.$fileName), true)) {
                    throw new Exception("Failed to resize retina for non retina version.");
                  }
                } else {
                  throw new Exception("Failed to copy retina image for processing.");
                }
              }

              $entry->{$column['field']} = $fileName;

						} 
					} catch (Exception $e) {
						$fileErrors["{$column['field']}"] = $e->getMessage();
					}
				}
				
				if (Util::get($column['field']."_delete")) {
          $currentFile = $_SERVER['DOCUMENT_ROOT']."/".$entry->url($column['field']);
          if (file_exists($currentFile)) {
            unlink($currentFile);
          }
          $name = pathinfo($currentFile, PATHINFO_FILENAME);
          $ext  = pathinfo($currentFile, PATHINFO_EXTENSION);
          $retinaImage = $entry->uploadPath($column['field']).$name."@2x.".$ext;
          if (file_exists($retinaImage)) {
            unlink($retinaImage);
          }

					$entry->{$column['field']} = "";
				}
					

			} else {
				if ($column['field'] != "created_at" && $column['field'] != "updated_at" && Util::checkInfuseLoginFields($this->infuseLogin, $column) ) {

					$inputsTemp = Util::get($column['field']);

					if ($this->belongsToUser && $column['field'] == "infuse_user_id" && !$this->user->is('Super Admin'))
						$inputsTemp = $this->user->id;


               /*   
					if (isset($column['display_order']) && Util::get("stack") && empty($inputsTemp)) {
                  $parent = Util::stackParentName();
                  $parent = Util::foreignKeyString($parent);
                  $parentId = Util::stackParentId();

						$count = $model::where($parent, "=", $parentId)->count();
						$count = 1+(int)$count;
						$inputsTemp = $count;
					} else 
               */

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
			}

			if(!Util::get("id")) { 
				if ($this->associateToSameParentOfUserRelatedBy){
					$entry->{$this->associateToSameParentOfUserRelatedBy} = $this->user->{$this->associateToSameParentOfUserRelatedBy};
				}
					
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
						$secondForeignId = $association[3];
					} else if ($model == $secondModel) {
						$belongsToModel = $firstModel;
						$firstForeignId =	$association[3];
						$secondForeignId = $association[1];
					}

					$event = $this->event;
					$newIds = Util::get($manyToManyTable); 
					if ($newIds) { 
						$originalIds = $entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->lists('id');

						$added = array_diff($newIds, $originalIds);
						$removed = array_diff($originalIds, $newIds);

						foreach($added as $id) { 
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->attach($id);
							$event::fire('infuse.attach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, $id));
						}
						
						foreach($removed as $id) {
							$event::fire('infuse.detach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, $id));
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->detach($id);
						}

						
						if ($this->infuseLogin && $entry->id == 1)
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->attach(1);

					} else {
						if ($this->infuseLogin && $entry->id == 1 && $this->user->is('Super Admin')) {
						} else {
							$event::fire('infuse.detach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, 0));
							$entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->detach();
						}
							
					}
				}
			}

			
			
			$entry->save();
			Util::flash($message);

         if (isset($column['display_order']) && empty($entry->{$column['field']})) {
           $entry->{$column['field']} =  $entry->id;
           $entry->save();
         } 

			if (!Util::get("id") && $this->belongsToUserManyToMany) {
				$this->user
					->belongsToMany($this->belongsToUserManyToMany[0], $this->belongsToUserManyToMany[1], $this->belongsToUserManyToMany[2], $this->belongsToUserManyToMany[3])
					->attach($entry->id);
			}


			
			if (Util::get("oneToOne")) { 
				$entry->belongsTo(ucfirst(Util::get("oneToOne")))->get()->{Util::getForeignKeyString($entry)} = $entry->id;
			}

         $afterSavePage = Util::get("typeSubmit");
         

			if (Util::get("stack")) {
            switch (Util::get("typeSubmit")) {
               case 'save':
                  $redirect_path = Util::childBackLink();
                  break;
               case 'save_and_return':
                  $redirect_path = Util::childActionLink(Util::get("stack"),"e", $entry->id);
                  break;
               case 'save_and_create_another':
                  $redirect_path = Util::childActionLink(Util::get("stack"), "c");
                  break;
               
               default:
                  $redirect_path = Util::childBackLink();
                  break;
            }
			} else {
            switch (Util::get("typeSubmit")) {
               case 'save':
                  $redirect_path = Util::redirectUrl();
                  break;
               case 'save_and_return':
                  $redirect_path = Util::redirectUrl("e", $entry->id);
                  break;
               case 'save_and_create_another':
                  $redirect_path = Util::redirectUrl("c");
                  break;
               
               default:
                  $redirect_path = Util::redirectUrl();
                  break;
            }
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


	
	private function callActionFunction() 
	{
		$model = $this->model;
		$entry = $model::find(Util::get("id"));
		$callFunction = Util::get("cf");

		$success = $entry->{$callFunction}();

		if ($success && is_array($success) && isset($success['message']) && isset($success['type'])) {
			Util::flash(array(
				"message" => $success['message'], 
				"type" => $success['type']
				)
			);
		} else if ($success) {
			Util::flash(array(
				"message" => "Succesfully called {$callFunction} action.", 
				"type" => "success"
				)
			);
		} else {
			Util::flash(array(
				"message" => "Failed to call {$callFunction} action.", 
				"type" => "error"
				)
			);
		}
		 
		if (Util::get("stack")) {
			$redirect_path = Util::childBackLink();
		} else {
			$redirect_path = Util::redirectUrl();
		}
		
		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
	}

	private function callOtherAction() 
	{
		$model = $this->model;
		$entry = $model::find(Util::get("id"));
		$callFunction = Util::get("cf");

		$success = $model::$callFunction($this->user);

		if ($success) {
			Util::flash(array(
				"message" => "Succesfully called {$callFunction} action.", 
				"type" => "success"
				)
			);
		} else {
			Util::flash(array(
				"message" => "Failed to call {$callFunction} action.", 
				"type" => "error"
				)
			);
		}
		 

		if (Util::get("stack")) {
			$redirect_path = Util::childBackLink();
		} else {
			$redirect_path = Util::redirectUrl();
		}
		
		if (!$this->testing) {
			header("Location: {$redirect_path}");
			exit();
		}
	}
	

	


	/******************************
		Final build scaffold
	*******************************/
	public function process()
	{	
		$this->route();
		$this->header['description'] = $this->description;
		$this->header['deleteAction'] = $this->deleteAction;
		$this->header['callFunctions'] = $this->callFunctions;
      $this->header['formatLaravelTimestamp'] = $this->formatLaravelTimestamp;

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

	// Used to test scaffold need to call route before
	public function processDataOnly()
	{	
		$this->header['description'] = $this->description;
		$this->header['deleteAction'] = $this->deleteAction;
		$this->header['callFunctions'] = $this->callFunctions;
      $this->header['formatLaravelTimestamp'] = $this->formatLaravelTimestamp;

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