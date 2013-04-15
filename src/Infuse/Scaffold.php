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
				if (strlen(strstr($column->type, "varchar")) > 0) {
					$type = "varchar";
				} else if (strlen(strstr($column->type, "tinyint")) > 0) {
					$type = "tinyint";
				} else if (strlen(strstr($column->type, "int")) > 0) {
					$type = "int";
				} else {
					$type = $column->type;
				}
				$this->columns[] = array(
						"field" => $column->field,
						"type"  => $type
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
			case 'u':
				$this->update();
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
		$this->entries = $model::find(Util::get("id"));
	}

	private function create()
	{
		$model = $this->model;
		echo "create";
	}

	private function delete()
	{
		$model = $this->model;
		$model::find(Util::get("id"))->delete();
		$redirect_path = str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		header("Location: {$redirect_path}");
		exit();
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