<?php 
namespace Infuse;

	class Util {


		public static function get($name)
		{
			return (isset($_POST["{$name}"]))? $_POST["{$name}"] : 
				((isset($_GET["{$name}"]))? $_GET["{$name}"] : false);
		}

		public static function getAll()
		{
			return ($_SERVER['REQUEST_METHOD'] === 'POST')? $_POST : $_GET;
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
			var_dump($var);//print_r($var);
			echo "</pre>";
      return ob_get_clean();
			
		}

		public static function flash($message = null)
		{
			if (!isset($_SESSION)) session_start();
			if ($message == null && isset($_SESSION['flash_message'])) {
				$temp = $_SESSION['flash_message'];
				unset($_SESSION['flash_message']);
				return $temp;
			} else if ($message) {
				$_SESSION['flash_message'] = $message;
			} else {
				return false;
			}
			
		}

		public static function flashArray($index, $message = null)
		{
			if (!isset($_SESSION)) session_start();
			if ($message == null && isset($_SESSION["{$index}"])) {
				$temp = $_SESSION["{$index}"];
				unset($_SESSION["{$index}"]);
				return $temp;
			} else if ($message) {
				$_SESSION["{$index}"] = $message;
			} else {
				return false;
			}
			
		}

		public static function arrayToObject($array)
		{
			return (object)$array;
		}

	}

?>