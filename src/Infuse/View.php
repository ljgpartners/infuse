<?php 
namespace Infuse;

use Infuse\Util;

class View {

	public static function fuse($data)
	{	
		$entries = $data['enrties'];
		$columns = $data['columns'];
		$header = $data['header'];

		ob_start();
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




}

?>