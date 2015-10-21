<?php namespace Bpez\Infuse;

use Aws\S3\S3Client;
use League\Flysystem\Filesystem;
use League\Flysystem\AwsS3v2\AwsS3Adapter;
use League\Flysystem\Adapter\Local as Adapter;
use Config;

use Intervention\Image\ImageManagerStatic as Image;



/*
|--------------------------------------------------------------------------
| FileUpload
|--------------------------------------------------------------------------
| This class contains helper uploading methods used by infuse
|
*/

class FileUpload {

	private $filesUploaded = array();

	private $disk;

	private $request;

	private $fileErrors = array();

	private $fileSystemDiskType;

	private $s3Bucket;

	private $deleteQueue = array();

	private $savedQueue = array();

	public function __construct(\Illuminate\Http\Request $request)
	{
		$this->fileSystemDiskType = \Config::get("filesystems.default");
		$this->request = $request;

		if ($this->fileSystemDiskType == "s3") {  // disks.s3.bucket secret bucket

			$client = S3Client::factory(array(
			    'key'    => \Config::get("filesystems.disks.s3.key"),
			    'secret' => \Config::get("filesystems.disks.s3.secret")
			));

			$this->s3Bucket = \Config::get("filesystems.disks.s3.bucket");

			$adapter = new AwsS3Adapter($client,  $this->s3Bucket);

			$this->disk = new Filesystem($adapter, [
	    	'visibility' => AwsS3Adapter::VISIBILITY_PUBLIC
			]);

		} else if ($this->fileSystemDiskType == "local") {

			$adapter = new Adapter(public_path());

			$this->disk = new Filesystem($adapter, [
	    	'visibility' => Adapter::VISIBILITY_PUBLIC
			]);
		}


	}

	public function fileErrors()
	{
		return $this->fileErrors;
	}

	public function uploadPath($instance, $column)
	{
    $uploadPath = strtolower($instance->uploadFolder.DIRECTORY_SEPARATOR
                .get_class($instance).DIRECTORY_SEPARATOR
                .$column.DIRECTORY_SEPARATOR);
    return $uploadPath;
	}


	public function url($instance, $column, $hstoreColumn = false)
	{
		$columnConfig = array(
	      "field" => $column,
	      'hstore_column' => $hstoreColumn
	    );

		$value = Util::getColumnValue($instance, $columnConfig);

	    if (filter_var($value, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
	      return $value;
	    } else {

			$url = strtolower($instance->uploadFolder.DIRECTORY_SEPARATOR
			          .get_class($instance).DIRECTORY_SEPARATOR
			          .$column.DIRECTORY_SEPARATOR).$value;

			if ($this->fileSystemDiskType == "s3") {

				if (!isset($instance->infuseCdnOff) && Config::has("services.cloudfront.s3-uploads")) {
					return Config::get("services.cloudfront.s3-uploads") . $url;
				} else {
					return $this->disk->getAdapter()->getClient()->getObjectUrl($this->s3Bucket, $url);
				}

			} else if ($this->fileSystemDiskType == "local") {
				$baseUrlUploadedAssetsLocal = \Config::get("infuse::config.base_url_uploaded_assets_local");
				$baseUrlAlwaysServe = \Config::get("infuse::config.base_url_for_uploaded_assets_always", false);

				if ($baseUrlAlwaysServe ||
					(\App::environment() != "production" && !empty($baseUrlUploadedAssetsLocal))
				) {
					return $baseUrlUploadedAssetsLocal.$url;
				} else {
					$httpHOST = (strpos($this->request->server("HTTP_HOST"), 'http://') !== false)? $this->request->server("HTTP_HOST") : "http://".$this->request->server("HTTP_HOST");
					return $httpHOST.$url;
				}
			}
		}
	}


	private function checkIfFileExistReturnNewName($uploadPath, $filename)
	{
		$retinaImage = false;

		if (strpos($filename, "@2x.") !== FALSE) {
			$filename = explode("@2x.", $filename);
			$filename = $filename[0].".".$filename[1];
			$retinaImage = true;
		}


		$newname = $filename;
    	$newname = Util::camel2under($newname);
		$newname = preg_replace('/\s+/', '', $newname);

		$newpath = $uploadPath.$newname;

		$name = pathinfo($newname, PATHINFO_FILENAME);
    	$ext  = pathinfo($newname, PATHINFO_EXTENSION);

		$count = 1;

		while ($this->disk->has($newpath)) {
			$newname = $name .'_'. $count . "." . $ext;
			$newpath = $uploadPath.$newname;
			$count++;
		}

		if ($retinaImage) {
			$name = pathinfo($newname, PATHINFO_FILENAME);
	    	$ext  = pathinfo($newname, PATHINFO_EXTENSION);
			$newname = $name . "@2x." . $ext;
		}

    	return $newname;
	}

	public function delete($column, $entry, $columnConfig)
	{
		$value = Util::getColumnValue($entry, $columnConfig);


		$uploadPath = $entry->uploadPath($column);
		$originalFile = $uploadPath.$value;

		$exists = $this->disk->has($originalFile);

		if (!empty($value) && $exists) {
		    $this->disk->delete($originalFile);

		    $name = pathinfo($value, PATHINFO_FILENAME);
		    $ext  = pathinfo($value, PATHINFO_EXTENSION);

		    $retinaImage = $uploadPath.$name."@2x.".$ext;


		    $exists = $this->disk->has($retinaImage);

		    if ($exists) {
		       $this->disk->delete($retinaImage);
		    }
		}
	}

	public function deleteBasicUpload($uploadPath, $value)
	{
		$originalFile = $uploadPath.$value;

		$exists = $this->disk->has($originalFile);

		if (!empty($value) && $exists) {
		    $this->disk->delete($originalFile);

		    $name = pathinfo($value, PATHINFO_FILENAME);
		    $ext  = pathinfo($value, PATHINFO_EXTENSION);

		    $retinaImage = $uploadPath.$name."@2x.".$ext;

		    $exists = $this->disk->has($retinaImage);

		    if ($exists) {
		       $this->disk->delete($retinaImage);
		    }
	  }
	}


	public function deleteByPath($path)
	{
		$exists = $this->disk->has($path);
		if ($exists) {
			$this->disk->delete($path);
		}
	}

	public function addToDeleteQueue($column, $entry, $columnConfig)
	{
		$this->deleteQueue[$column] = array($entry, $columnConfig);
	}

	public function  addToSavedQueue($fileSaved)
	{
		array_push($this->savedQueue, $fileSaved);
	}

	private function processRetina($uploadPath, $filename, $url)
	{

		if(strpos($filename, "@2x.") !== FALSE) {

			ini_set('memory_limit', '64M');

		  	$img = Image::make($url);

		  	// 1.5 instead of 2. Almost same quality but saves more space and bandwidth.
		  	$halfRetinaSize = floor($img->width()/1.5);
		  	$retinaFileName = $filename;

		  	$filename = explode("@2x.", $filename);
		  	$filename = $filename[0].".".$filename[1];


		  	$img->resize($halfRetinaSize, null, function ($constraint) {
				$constraint->aspectRatio();
			});


			$img = (string) $img->encode('data-url');

			if ($this->fileSystemDiskType == "s3") {
				$stream = fopen($img, 'r+');
				$this->disk->writeStream($uploadPath.$filename, $stream);

			} else if ($this->fileSystemDiskType == "local") {
				$stream = fopen($img, 'r+');
				$this->disk->writeStream($uploadPath.$filename, $stream);
				fclose($stream);
			}


			$this->addToSavedQueue($uploadPath.$filename);

			return $filename;

		} else {
			return false;
		}

	}


	public function add($column, &$entry)
	{
		$columnConfig = $column;
		$column = $column['field'];

		// If column in files array or if uploaded already by cropping tool
		$checkIfInFiles = (array_key_exists($column, $_FILES) && !empty($_FILES["{$column}"]['name']));
		$checkIfAlreadyUploaded = (Util::get($column) && !filter_var(Util::get($column), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));
		$checkIfExternalFile = (Util::get($column) && filter_var(Util::get($column), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));

		// Only process if file present
		if (!$checkIfInFiles && !$checkIfAlreadyUploaded && !$checkIfExternalFile) {
			return false;
		}


		$newFilename = "";

		try {

			if ($checkIfInFiles) {


				$file = $this->request->file($column);
				$originalFilename =  $file->getClientOriginalName();
				$uploadPath = $this->uploadPath($entry, $column);


				/*if ($this->fileSystemDiskType != "s3") {
					$this->disk->makeDirectory($uploadPath, 775, true, true);
				}*/

				$newFilename = $this->checkIfFileExistReturnNewName($uploadPath, $originalFilename);


				if ($file->isValid()) {


					if ($this->fileSystemDiskType == "s3") {
						$stream = fopen($file->getRealPath(), 'r+');
						$this->disk->writeStream($uploadPath.$newFilename, $stream);
						//fclose($stream);
						$this->addToSavedQueue($uploadPath.$newFilename);

					} else if ($this->fileSystemDiskType == "local") {
						$stream = fopen($file->getRealPath(), 'r+');
						$this->disk->writeStream($uploadPath.$newFilename, $stream);
						fclose($stream);
						$this->addToSavedQueue($uploadPath.$newFilename);
					}

				}


			} else if ($checkIfAlreadyUploaded) {
				//$tempUploadedFilePath = $_SERVER['DOCUMENT_ROOT'].Util::get($column);
				//$filename = Util::get($column);

			} else if ($checkIfExternalFile) {
				//$tempUploadedFilePath  = Util::get($column);
			}

		} catch (Exception $e) {
			$this->fileErrors[$column] = "File failed uploading.";
		}


		$columnValue = Util::getColumnValue($entry, $columnConfig);

		// If old present queue removal
		if (!empty($columnValue)) {
			$originalEntry = clone $entry;
			$this->addToDeleteQueue($column, $originalEntry, $columnConfig);
			unset($originalEntry);
		}

	  	Util::setColumnValue($entry, $columnConfig, $newFilename);

	  	// process retina
  		$url = $this->url($entry, $column, $columnConfig['hstore_column']);
		$processRetinaImage = $this->processRetina($uploadPath, $newFilename, $url);
		if ($processRetinaImage) {
			Util::setColumnValue($entry, $columnConfig, $processRetinaImage);
		}

	} // END OF ADD


	public function addBasicUpload(&$entry, $entryPropertyName, $uploadFieldName, $uploadPath)
	{

		// If column in files array or if uploaded already by cropping tool
		$checkIfInFiles = (array_key_exists($uploadFieldName, $_FILES) && !empty($_FILES["{$uploadFieldName}"]['name']));
		$checkIfAlreadyUploaded = (Util::get($uploadFieldName) && !filter_var(Util::get($uploadFieldName), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));
		$checkIfExternalFile = (Util::get($uploadFieldName) && filter_var(Util::get($uploadFieldName), FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED));

		// Only process if file present
		if (!$checkIfInFiles && !$checkIfAlreadyUploaded && !$checkIfExternalFile) {
			return true;
		}


		$newFilename = "";

		try {

			if ($checkIfInFiles) {

				$file = $this->request->file($uploadFieldName);
				$originalFilename =  $file->getClientOriginalName();

				$newFilename = $this->checkIfFileExistReturnNewName($uploadPath, $originalFilename);

				if ($file->isValid()) {


					if ($this->fileSystemDiskType == "s3") {
						$stream = fopen($file->getRealPath(), 'r+');
						$this->disk->writeStream($uploadPath.$newFilename, $stream);
						//fclose($stream);
						$this->addToSavedQueue($uploadPath.$newFilename);

					} else if ($this->fileSystemDiskType == "local") {
						$stream = fopen($file->getRealPath(), 'r+');
						$this->disk->writeStream($uploadPath.$newFilename, $stream);
						fclose($stream);
						$this->addToSavedQueue($uploadPath.$newFilename);
					}

				}


			} else if ($checkIfAlreadyUploaded) {
				//$tempUploadedFilePath = $_SERVER['DOCUMENT_ROOT'].Util::get($column);
				//$filename = Util::get($column);

			} else if ($checkIfExternalFile) {
				//$tempUploadedFilePath  = Util::get($column);
			}

		} catch (Exception $e) {
			$this->fileErrors[$uploadFieldName] = "File failed uploading.";
			return false;
		}


		$entry->{$entryPropertyName} = $newFilename;

		/************************************************************
		* Url function without instance references refactor later
		************************************************************/
		$url = $uploadPath.$newFilename;

		if ($this->fileSystemDiskType == "s3") {

			$url = $this->disk->getAdapter()->getClient()->getObjectUrl($this->s3Bucket, $url);

		} else if ($this->fileSystemDiskType == "local") {
			$baseUrlUploadedAssetsLocal = \Config::get("infuse::config.base_url_uploaded_assets_local");
			$baseUrlAlwaysServe = \Config::get("infuse::config.base_url_for_uploaded_assets_always", false);

			if ($baseUrlAlwaysServe ||
				(\App::environment() != "production" && !empty($baseUrlUploadedAssetsLocal))
			) {
				$url = $baseUrlUploadedAssetsLocal.$url;
			} else {
				$httpHOST = (strpos($this->request->server("HTTP_HOST"), 'http://') !== false)? $this->request->server("HTTP_HOST") : "http://".$this->request->server("HTTP_HOST");
				$url = $httpHOST.$url;
			}
		}
		/************************************************************
		* end of url refactor section
		************************************************************/


		$processRetinaImage = $this->processRetina($uploadPath, $newFilename, $url);
		if ($processRetinaImage) {
			$entry->{$entryPropertyName} = $processRetinaImage;
		}

		return true;

	} // END OF ADD

	public function allowDeletion()
	{
		foreach ($this->deleteQueue as $column => $both) {
			$this->delete($column, $both[0], $both[1]);
		}
	}

	public function processUploads()
	{
		if (count($this->fileErrors) == 0) {
			// allow deletion
			$this->allowDeletion();

		}
	}

	public function resetUploads()
	{
		foreach ($this->savedQueue as $path) {
			$this->deleteByPath($path);
		}
	}


}
