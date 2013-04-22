<?php
/************************************
A task that moves assets in place

command: 

	php infuse_install.php -f laravel3

Parameters: -f laravel3

Laravel 3 only framework supported so far.

*************************************/

require dirname(__FILE__)."/src/infuse/View.php";
use Infuse\View;

date_default_timezone_set("America/Los_Angeles");


/*********************************
	Infuse Paths
**********************************/
$assetsFolderName = "infuse_assets";
$project_path = dirname(__FILE__)."/src/infuse/";

/*********************************
	Laravel Paths
**********************************/
$laravel3Public = dirname(__FILE__)."/../../../public/"; 
$laravel3Models = dirname(__FILE__)."/../../../application/models/";

$LocationParameter = array_search("-f", $argv);

if ($LocationParameter) {
	$frameworkValueIndex = $LocationParameter + 1;
	$frameworkValue = $argv["{$frameworkValueIndex}"];

	if ($frameworkValue == "laravel3") {

		$origin = $project_path.$assetsFolderName;
		$final =  $laravel3Public.$assetsFolderName;

		echo "----------------------------\n";
		echo "Moving files to {$final}\n";
		echo "from {$origin }\n";
		echo "----------------------------\n";

		$start = strlen($origin) + 1;
		$di = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origin, RecursiveDirectoryIterator::SKIP_DOTS));
		foreach ( $di as $file ) {
		    if ($file->isFile()) {
		        $new = sprintf("%s/%s", $final, substr($file, $start));
		        $dir = dirname($new);
		        is_dir($dir) or mkdir($dir, 0777, true);
		        copy($file, $new);
		        echo "{$file->getFilename()}\n ";
		    }
		}
		echo "----------------------------\n";
		echo "Finished moving assets into public directory.\n";
		echo "Adding Elegant class to models directory.\n";
		echo "Elegant adds validation functionality required for infuse.\n";
		echo "----------------------------\n";
		echo "Moving...\n";
		copy($project_path."/Elegant.php", $laravel3Models."Elegant.php");
		echo "Finished moving Elegant.php\n";
		echo "Task done.\n";
	} else {
		echo "Framework not recognized by task.\n";
	}

} else{
	echo "-f parameter required (framework)\n";
}

?>