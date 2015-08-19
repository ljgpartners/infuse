<?php namespace Bpez\Infuse;

use Bpez\Infuse\Exceptions\ScaffoldConfigurationException;
use Bpez\Infuse\Exceptions\ScaffoldUnknownConfigurationIndexException;

trait ScaffoldAPI {

	public function mapConfig($config)
	{
		if (!isset($config['model'])) {
			throw new ScaffoldConfigurationException("Model not declared");
		}

		$this->model($config['model']);

		foreach ($config as $key => $f) {

			switch ($key) {
				case 'model':
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
				case 'filterListColumns':
					$this->filterListColumns($f);
					break;
				case 'defaultColumnValues':
					$this->defaultColumnValues($f);
					break;
				case 'queryScopes':
					$this->queryScopes($f);
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



	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	public function columnName($info)
	{
		$base = 'columnName(array(array("column" => someColumn, "newName" => "someName")));';

		if (!is_array($info) ) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}


		/** If only one. */
		if ( isset($info['column']) && isset($info['newName']) ) {

			if (!is_string($info['column']) || !is_string($info['newName'])) {
				throw new ScaffoldConfigurationException($base.' First aand second argument should be strings');
			}

			if (array_key_exists($info['column'], $this->columns)) {
				$this->columnNames["{$info['column']}"] = $info['newName'];
			} else {
				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
			}


		/** If more then one. */
		} else {

			foreach ($info as $i) {
				if (!isset($i['column']) && !isset($i['column'])) {
					throw new ScaffoldConfigurationException($base.' First argument should name of column. Second argument should be replacement name.');
				}

				if (!is_string($i['column']) || !is_string($i['newName'])) {
					throw new ScaffoldConfigurationException($base.' First aand second argument should be strings');
				}

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
		$base = 'addSelect(array(array("column" => $columnName, "array" => array(), "insertBlank" => false, "topSelect" => false, "nested" => array("Model1", "Model2"), "nestedLastArray" => array() )));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		// If only one
		if ( isset($info['column']) && isset($info['array']) ) {

			if ( array_key_exists($info['column'], $this->columns) ) {

				$this->columns["{$info['column']}"]["select"] = $info['array'];

				if (isset($info['insertBlank']) && $info['insertBlank'] == true) {
					$this->columns["{$info['column']}"]["select_blank"] = true;
				}

				if (isset($info['topSelect']) && $info['topSelect'] == true) {
					$this->columns["{$info['column']}"]["top_select"] = true;
				}

				if (isset($info['nested']) && $info['nested'] == true) {
					$this->columns["{$info['column']}"]["nested"] = $info['nested'];
				}

				if (isset($info['nestedLastArray']) && $info['nestedLastArray'] == true) {
					$this->columns["{$info['column']}"]["nested_last_array"] = $info['nestedLastArray'];
				}


			} else {

				throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');

			}

		// If more then one roles
		} else {

			foreach ($info as $i) {

				if (is_array($i) && (!isset($i['column']) || !isset($i['array']))) {
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				}

				if (!is_string($i['column']) || !is_array($i['array'])) {
					throw new ScaffoldConfigurationException($base.' Column must be a string. Array index must be an array.');
				}


				if (array_key_exists($i['column'], $this->columns)) {

					$this->columns["{$i['column']}"]["select"] = $i['array'];
					if (isset($i['insertBlank']) && $i['insertBlank'] == true) {
						$this->columns["{$i['column']}"]["select_blank"] = true;
					}

					if (isset($i['topSelect']) && $i['topSelect'] == true) {
						$this->columns["{$i['column']}"]["top_select"] = true;
					}

					if (isset($i['nested']) && $i['nested'] == true) {
						$this->columns["{$i['column']}"]["nested"] = $i['nested'];
					}

					if (isset($i['nestedLastArray']) && $i['nestedLastArray'] == true) {
						$this->columns["{$i['column']}"]["nested_last_array"] = $i['nestedLastArray'];
					}


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

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		if (!is_string($info[0]) || !is_string($info[1])) {
			throw new ScaffoldConfigurationException($base.' First and second argument should name of type string in each import array. ');
		}

		if (!is_array($info[2])) {
			throw new ScaffoldConfigurationException($base.' Third argument can only be an array in each import array. ');
		}

		$this->importFromModel = $info;

		return $this;
	}

	public function addMultiSelect($info)
	{
		$base = 'addMultiSelect(array("column" => $columnName, "array" => array()));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		// If only one
		if (isset($info['column']) && isset($info['array'])) {

			if (is_string($info['column']) && is_array($info['array'])) {
				if (array_key_exists($info['column'], $this->columns)) {
					$this->columns["{$info['column']}"]["multi_select"] = $info['array'];
				} else {
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
				}
			} else {
				throw new ScaffoldConfigurationException($base.' column index must be a string and array index must be an array.');
			}

		// If more then one
		} else {

			foreach ($info as $i) {

				if (!is_array($i) && isset($i['column']) && isset($i['array'])) {
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				}

				if (!is_string($i['column']) || !is_array($i['array'])) {
					throw new ScaffoldConfigurationException($base.' column index must be a string and array index must be an array.');
				}

				if (array_key_exists($i['column'], $this->columns)) {
					$this->columns["{$i['column']}"]["multi_select"] = $i['array'];
				} else {
					throw new ScaffoldConfigurationException($base.' Column doesn\'t exist.');
				}

			}

		}
		return $this;
	}

	public function addCkeditor($info)
	{
		$base = 'addCkeditor(array("column1", "column2"));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		foreach ($info as $i) {

			if (!is_string($i)) {
				throw new ScaffoldConfigurationException($base.' Column must be a string. Array index must be an array.');
			}

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

		if (!is_array($uploads)) {
			throw new ScaffoldConfigurationException($base.' First argument must an array. column index must be set. ');
		}

		// If only one
		if (is_array($uploads) && isset($uploads['column']) && is_string($uploads['column'])) {

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

				if (!is_array($info) && !isset($info['column'])) {
					throw new ScaffoldConfigurationException($base.'  First argument must an array. column index must be set. ');
				}

				if (!is_string($info['column'])) {
					throw new ScaffoldConfigurationException($base.' First argument should name of column. ');
				}

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
		if (!is_array($models)) {
			throw new ScaffoldConfigurationException('hasMany( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array with all the info of the model.
		First index in the array should be the model name, second should be the wanted model title and third should be the column names to list.');
		}

		$this->hasMany = $models;
		return $this;
	}

	public function hasOne($model)
	{
		if (!is_array($model)) {
			throw new ScaffoldConfigurationException('hasOne( array(array("SomeModelName", "model_title", array("column_1", "column_2"))) ); First argument should be an array of the model.
		With name as the index and another array with the title as the first and the second array with columns to list.');
		}

		$this->hasOne = $model;
		return $this;
	}

	public function manyToMany($models)
	{
		if (!is_array($models)) {
			throw new ScaffoldConfigurationException('manyToMany(array(array("FirstModelName", "FirstForeignId", "SecondModelName", "SecondForeignId", "many_to_many_table", "FirstColumnName", "SecondColumnName"))); ');
		}

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
		if (!is_array($describes)) {
			throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Must pass array in. ');
		}


		// If only one
		if ( is_array($describes) && isset($describes['column']) && isset($describes['desc'])) {

			if (array_key_exists($describes['column'], $this->columns)) {

				$this->columns["{$describes['column']}"]["description"] = (is_string($describes['desc']))? $describes['desc'] : "";
				if (isset($describes['popover'])) {
					$this->columns["{$describes['column']}"]["description_popover"] = $describes['popover'];
				}

			} else {

				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here")));  Column doesn\'t exist.');

			}

		// If more then one
		} else {

			foreach ($describes as $d) {

			if (!isset($d['column']) || !isset($d['desc'])) {
				throw new ScaffoldConfigurationException('describeColumn(array(array("column" => "columnName", "desc" => "description here"))); Both argument are required');
			}

			if (array_key_exists($d['column'], $this->columns)) {

				$this->columns["{$d['column']}"]["description"] = (is_string($d['desc']))? $d['desc'] : "";
				if (isset($d['popover'])) {
					$this->columns["{$d['column']}"]["description_popover"] = $d['popover'];
				}

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

		if (!is_array($columns)) {
			throw new ScaffoldConfigurationException($base.' First argument must an array. column index must be set. ');
		}


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
		if (!is_array($list)) {
			throw new ScaffoldConfigurationException('list(array("name", "count", "active")); First argument should be an array of the names of the columns wanted listed on landing page.');
		}

		array_push($list, "updated_at");
		$this->list = $list;
		return $this;
	}

	public function filterListColumns($list)
	{
		if (!is_array($list)) {
			throw new ScaffoldConfigurationException('filterListColumns(array("name", "count", "active")); First argument should be an array of the names of the columns allowed to be filtered on landing page.');
		}

		$this->filterList = $list;
		return $this;
	}

	public function queryScopes($list)
	{
		if (!is_array($list)) {
			throw new ScaffoldConfigurationException('queryScopes(array("display_name" => "function_name")); First argument should be an array. Index as the display name and value as the function name.');
		}

		$this->queryScopes = $list;
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

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

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

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' First argument should be an array. ');
		}

		if (!array_key_exists("parent_model", $info)) {
			throw new ScaffoldConfigurationException($base.' parent_model index should be set. ');
		}

		if (!array_key_exists("parent_foriegn_id", $info)) {
			throw new ScaffoldConfigurationException($base.' parent_foriegn_id index should be set. ');
		}

		if (!array_key_exists("foreign_id", $info)) {
			throw new ScaffoldConfigurationException($base.' foreign_id index should be set. ');
		}

		$this->siblingOfUserParentOnly = $info;
		return $this;
	}

	public function defaultColumnValues($info)
	{
		$base = 'defaultColumnValues(array("column" => "column_value", ...));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}


		foreach ($info as $i => $value) {
			if (!array_key_exists($i, $this->columns)) {
				throw new ScaffoldConfigurationException($base.' First argument must an array. column and value must be set. ');
			}

			$this->defaultColumnValues["{$i}"] = $value;
		}

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

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}


		// If only one
		if ( isset($info['function']) && isset($info['display_name']) ) {

			array_push($this->addOtherActions, $info);

		// If more then one
		} else {

			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['function']) || !isset($i['display_name']))) {
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				}
				array_push($this->addOtherActions, $i);
			}
		}

		return $this;
	}

	public function beforeEdit($info)
	{
		$base = 'beforeEdit(function(){});';

		if (!is_callable($info)) {
			throw new ScaffoldConfigurationException($base.' Must be a function.');
		}

		$this->beforeEdit = $info;

		return $this;
	}


	public function belongsToUserManyToMany($info)
	{
		$base = 'belongsToUserManyToMany(array("Model", "pivot_table", "user_id", "model_foreign_key_id"));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		if (count($info) != 4) {
			throw new ScaffoldConfigurationException($base.' Should have the laravel belongsToMany four parameters.'. count($info));
		}

		$this->belongsToUserManyToMany = $info;

		return $this;
	}

	public function addPermanentFilters($info)
	{
		$base = 'addPermanentFilters(array("column" => "columnName", "operator" => "=", "value" => 34));';

		if (!is_array($info)) {
			throw new ScaffoldConfigurationException($base.' Must be an array.');
		}

		// If only one
		if ( isset($info['column']) && isset($info['operator']) && isset($info['value'])) {

			array_push($this->permanentFilters, $info);

		// If more then one
		} else {

			foreach ($info as $i) {
				if (is_array($i) && (!isset($i['column']) || !isset($i['operator']) || !isset($i['value']))) {
					throw new ScaffoldConfigurationException($base.' First argument must an array. column and array must be set. ');
				}

				array_push($this->permanentFilters, $i);
			}

		}

		return $this;
	}

}
