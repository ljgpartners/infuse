<?php namespace Bpez\Infuse;

use Bpez\Infuse\Util;

class View {

	public static function fuse($data)
	{	
		$entries = $data['enrties'];
		$columns = $data['columns'];
		$header  = $data['header'];
		$infuseLogin = $data['infuseLogin'];
		$message = Util::flash();

		ob_start();
		switch ($data['action']) {
			case 'l':
				require dirname(__FILE__)."/templates/listAll.php";
				break;
			case 'e':
			case 'c':
			case 'cd':
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
