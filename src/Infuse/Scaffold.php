<?php 
namespace Infuse;

use Infuse\Util;

class Scaffold {

	private $model;
	private $columns;
	private $action;
	private $entries = array();
	private $header = array();


	public function __construct($model, $db)
	{	
		$this->action = Util::get("action");
		$this->model = $model;
		$columns = $db::query("SHOW COLUMNS FROM ".$model->table());
		foreach ($columns as $column) {
			if ($column->field != 'id' ) {
				$this->columns[] = array(
						"field" => $column->field,
						"type"  => $column->type
					);
			}
		}

		$this->route();

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
			default:
				$this->listAll();
				break;
		}
	}

	private function listAll()
	{	
		$model = $this->model;
		$this->header = array(
				"pagination" => array()
			);
		$this->entries = $model::all();
	}

	private function show()
	{	
		$model = $this->model;
		$this->header = array(
				"pagination" => array()
			);
		$this->entries = $model::find(Util::get("id"));
	}

	private function edit()
	{
		$model = $this->model;
		echo "edit";
	}

	private function create()
	{
		$model = $this->model;
		echo "create";
	}

	private function delete()
	{
		$model = $this->model;
		echo "delete";
	}

	private function update()
	{	
		$model = $this->model;
		echo "update";
	}

	public function config()
	{
		$model = $this->model;
		echo "config";
	}

	public function build()
	{	
		$model = $this->model;

		//echo Util::debug($this->columns);
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