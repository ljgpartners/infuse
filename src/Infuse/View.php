<?php 
namespace Infuse;

use Infuse\Util;

class View {

	private static $js = array(
				"jquery-1.9.1.min.js",
				"bootstrap.min.js",
				"jquery-ui.min.js",
				"jquery-ui-timepicker-addon.js",
			);

	private static $css = array(
				"bootstrap-combined.min.css",
				"jquery-ui.min.css",
				"jquery-ui-timepicker-addon.css"
			);

	public static function fuse($data)
	{	
		$entries = $data['enrties'];
		$columns = $data['columns'];
		$header  = $data['header'];
		$message = isset($_SESSION['infuse_message'])? $_SESSION['infuse_message'] : false;
		unset($_SESSION['infuse_message']);

		ob_start();
		View::fuseAssets();
		switch ($data['action']) {
			case 'l':
				require dirname(__FILE__)."/templates/listAll.php";
				break;
			case 'e':
			case 'c':
				require dirname(__FILE__)."/templates/create_edit.php";
				break;
			case 's':
				require dirname(__FILE__)."/templates/show.php";
				break;
			default:
				require dirname(__FILE__)."/templates/listAll.php";
				break;
		}
		return ob_get_clean();
	}

	private static function fuseAssets()
	{	
		
		foreach (View::$css  as $file) {
			echo '<link href="/css/'.$file.'" rel="stylesheet">';
		}

		foreach (View::$js  as $file) {
			echo '<script type="text/javascript" src="/js/'.$file.'"></script>';
		}

		require dirname(__FILE__)."/assets/infuse.php";
	}


	public static function fuseAlerts($message)
	{	
		if ($message) {
			switch ($message['type']) {
				case 'warning':
					$message['type'] = "alert-block";
					break;
				case 'error':
					$message['type'] = "alert-error";
					break;
				case 'success':
					$message['type'] = "alert-success";
					break;
				case 'info':
					$message['type'] = "alert-info";
					break;
				default:
					$message['type'] = "alert-info";
					break;
			}
			echo '<div class="alert '.$message['type'].'">
					  <button type="button" class="close" data-dismiss="alert">&times;</button>
					  <h4>'.$message['message'].'</h4>
						</div>';
		}
		
	}


}

?>
