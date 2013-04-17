<?php 
namespace Infuse;

	class Util {


		public static function get($name)
		{
			return (isset($_POST["{$name}"]))? $_POST["{$name}"] : 
				((isset($_GET["{$name}"]))? $_GET["{$name}"] : false);
		}


		public static function truncateText($text, $nbrChar, $append='...') 
		{
			if (strlen($text) > $nbrChar) {
		  	$text = substr($text, 0, $nbrChar);
		    $text .= $append;
		 	}
		  return $text;
		}

		public static function cleanName($name) 
		{
			return ucfirst(str_replace('_', ' ', $name));
		}


		public static function debug($var)
		{
			ob_start();
			echo "<pre>";
			print_r($var);
			echo "</pre>";
      return ob_get_clean();
			
		}

	}

?>