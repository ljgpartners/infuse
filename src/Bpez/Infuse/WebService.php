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
			case 'upload_temp_image':
				$response = $this->uploadTempImage();
				break;
			case 'crop_image_send_back_url':
				$response = $this->cropImageSendBackUrl();
				break;
			case 'clean_temp_folder':
				$response = $this->cleanTempFolder();
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
			foreach($tempFiles as $file) {
				$fileTime->setTimestamp(\File::lastModified($file));
				$interval = $now->diff($fileTime);
				$elapsed = (integer) $interval->format('%i');
				// If file older then 15 minutes delete
				if ($elapsed > 15)
					unlink($file);
			}
			return array("status" => 'success', "message" => 'Files cleaned up.');
		} else {
			return array("status" => 'success', "message" => 'No files cleaned up.');
		}
		
	}

	

	protected function noAction()
	{
		return array("response" => "Action does not exist.");
	}

}

?>