<?php namespace Bpez\Infuse;

use Exception;

/*
|--------------------------------------------------------------------------
| WebService 
|--------------------------------------------------------------------------
| All infuse ajax functions hit this web service class to process
|
*/

class WebService {

	private $action = null;
	private $db;

	public function __construct(\Illuminate\Support\Facades\DB $db)
	{	
	  $this->action = Util::get('action');
	  $this->db = $db;
	}

	public function process()
	{
		switch ($this->action) {
			case 'swap_order':
				$response = $this->swapOrder();
				break;
			default:
				$response = $this->noAction();
				break;
		}
		return $response;
	}

	protected function swapOrder()
	{
		$column = Util::get('column');

		$model = Util::get('model');
		$model1 = new $model();
		$model2 = new $model();

		$entry1 = $model1::find(Util::get('id'));
		$idswap1 = $entry1->{$column};

		$entry2 = $model2::find(Util::get('prevId'));
		$idswap2 = $entry2->{$column};

		$entry1->{$column} = $idswap2;
		$entry2->{$column} = $idswap1;

		$entry1->save();
		$entry2->save();
		
		$swaps = array(
			"{$entry1->id}" => $entry1->{$column},
			"{$entry2->id}" => $entry2->{$column}
			);

		return array( "success" => true, "swaps" => $swaps);
	}

	protected function noAction()
	{
		return array("response" => "Action does not exist.");
	}

}

?>