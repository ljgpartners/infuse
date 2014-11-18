<?php namespace Bpez\Infuse;

use Exception;
use Bpez\Infuse\Facades\Scaffold;
use Bpez\Infuse\Util;

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
			case 'upload_temp_image':
				$response = $this->uploadTempImage();
				break;
			case 'crop_image_send_back_url':
				$response = $this->cropImageSendBackUrl();
				break;
			case 'clean_temp_folder':
				$response = $this->cleanTempFolder();
				break;
			case 'fetch_import_batch':
				$response = $this->fetchImportBatch();
				break;
			case 'nested_select_batch':
				$response = $this->nestedSelectBatch();
				break;
			case 'log':
				$response = $this->log();
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



	protected function uploadTempImage()
	{
		if (\Input::hasFile('img')){
			$file = \Input::file('img');
			$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG");
			$extension = $file->getClientOriginalExtension();

			if (!in_array($extension, $allowedExts))
				return array("status" => 'error', "message" => 'Must be jpeg, png or gif image.');

      $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/';
      $filename        = time().'_'.$file->getClientOriginalName();
      $size = $file->getSize();
      $uploadSuccess   = $file->move($destinationPath, $filename);

      $response = array(
				"status" => 'success',
				"url" => '/uploads/tmp/'.$filename,
				"width" => $size,
				"height" => $size
		  );

		} else {
			$response = array("status" => 'error', "message" => 'Problem uploading file.');
		}

		return $response;
	}



	protected function cropImageSendBackUrl()
	{
		ini_set('memory_limit','64M');
		$file = \Input::all();
		$preserveRatio = true;  
		$upsize = true;

		$destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/';
		$fileName = explode(DIRECTORY_SEPARATOR, $file['imgUrl']);
    $filename = time().end($fileName);

		$img = \Image::make($_SERVER['DOCUMENT_ROOT'].$file['imgUrl'])
			->resize($file['imgW'], $file['imgH'], $preserveRatio, $upsize)
			->crop($file['cropW'], $file['cropH'], $file['imgX1'], $file['imgY1'])
			->save($destinationPath.$filename);


		return array("status" => 'success', "url" => '/uploads/tmp/'.$filename);
	}

	protected function cleanTempFolder()
	{
		if (!\Cache::has('infuse::clean_temp_folder')) {
			// Only allow checking files every hour
			$expiresAt = \Carbon::now()->addMinutes(59);
			\Cache::put('infuse::clean_temp_folder', true, $expiresAt);

			$now = new \DateTime;
			$tempFiles = \File::glob($_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/*');
			$fileTime = new \DateTime;
			if (count($tempFiles) > 0) {
				foreach($tempFiles as $file) {
					$fileTime->setTimestamp(\File::lastModified($file));
					$interval = $now->diff($fileTime);
					$elapsed = (integer) $interval->format('%i');
					// If file older then 15 minutes delete
					if ($elapsed > 15)
						unlink($file);
				}
			}
			
			return array("status" => 'success', "message" => 'Files cleaned up.');
		} else {
			return array("status" => 'success', "message" => 'No files cleaned up.');
		}
		
	}

	protected function fetchImportBatch()
	{
		$child = Util::camel2under(Util::get('child'));
		$resource = Util::camel2under(Util::get('resource'));
		
		$search =  Util::get('search');
		$search = (empty($search))? false : $search;

		$columns = Util::get('list');
		$columns = (empty($columns))? array() : $columns;

		$map = Util::get('map');
		$map = (empty($map))? array() : Util::get('map');

		$id = Util::get('id');
		$modelImportingTo = Util::get('modelImportingTo');
		$modelImportingToId = Util::get('modelImportingToId');

		$distance = Util::get('distance');
		$distance = (empty($distance))? 25 : $distance;

		$latitude = Util::get('latitude');
		$longitude = Util::get('longitude');

		$foriegnKey = Util::get('foriegnKey');
		$foriegnKey = (empty($foriegnKey))? false : $foriegnKey;

		$parentId = Util::get('parentId');
		$parentId = (empty($parentId))? false : $parentId;

		$config = \Config::get("infuse::{$resource}.children.{$child}");
		$scaffold = Scaffold::model($config['model'])
									->mapConfig($config);

		$redirect = $scaffold->checkPermissions(Util::childBackLink());
		if ($redirect)
			return array("status" => 'error', "flash" => Util::flash());

		 
		if ($foriegnKey){
			if ($parentId) {
				$scaffold->search($parentId, array($foriegnKey));
			} else {
				$scaffold->search("no_value", array($foriegnKey));
			}
		}else if (!empty($latitude) && !empty($longitude)) {
			$scaffold->closestLocationsWithinRadius($search, $columns, $latitude, $longitude, $distance); 
		} else if ($search){ 
			$scaffold->search($search, $columns);
		} else {
			$scaffold->route();
		}
			

		$data = $scaffold->processDataOnly();

		if ($modelImportingToId) { 
			$modelImportingTo = "\\$modelImportingTo";
			$updatedAt = new $modelImportingTo();
			$updatedAt = $updatedAt::findOrFail($modelImportingToId)->updated_at;
		} else {
			$updatedAt = false;
		}
		

		$entriesHtml = \View::make("infuse::web_service.fetch_import_batch", array(
			"entries" => $data['entries'],
			"columns" => $columns, 
			"map" => $map,
			"id" => $id,
			"updatedAt" => $updatedAt
		))->__toString();

		$response = array(
			"status" => 'success',
			"entries" => $data['entries']->toArray(),
			"pagination" => $data['header']['pagination'],
			"entries_html" => $entriesHtml
		);

		if ($search)
			$response['search'] = true;

		return $response;
	}

	

	protected function noAction()
	{
		return array("response" => "Action does not exist.");
	}
	
	
	protected function log()
	{
		Util::debug(Util::get('message'), true);
		return array("response" => "Action does not exist.");
	}

	protected function nestedSelectBatch()
	{ 
		$model = Util::get('model');
		$model = new $model();
		$foreignKey = Util::get('foreign_key');
		$value = Util::get($foreignKey);
		$column = Util::get('column');

		$foreignKey = (Util::get('overide_foreign_key'))? Util::get('overide_foreign_key') : $foreignKey;
		$notColumn = (Util::get('not_column'))? Util::get('not_column') : false;

		$return = $model::where($foreignKey, "=", $value)
			->orderBy($column, "asc");

		if ($notColumn) { 
			$notColumn = explode(",", $notColumn);
			//print_r($notColumn); die();
			$return = $return->where($notColumn[0], "!=", $notColumn[1]);
		}

		$return = $return->get(array('id', $column))
			->toArray();
			

		$returnArray = array();

		foreach ($return as $item) {
			$columnName = end($item);
			$returnArray[] = array($item["id"], $columnName);
		}
		
		return $returnArray;
		
	}

}

?>