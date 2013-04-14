<?php 
namespace Bpez\Infuse;

	class Util {

		/**
		 * 
		 *
		 * @param  string  $driver
		 * @return mixed
		 */
		public static function get($name)
		{
			return (isset($_POST["{$name}"])? $_POST["{$name}"] : 
				(isset($_GET["{$name}"]))? $_GET["{$name}"] : false );
		}

		/**
		 * .
		 *
		 * @param  string  $driver
		 * @return mixed
		 */
		public static function truncateText($text, $nbrChar, $append='...') 
		{
			if (strlen($text) > $nbrChar) {
		  	$text = substr($text, 0, $nbrChar);
		    $text .= $append;
		 	}
		  return $text;
		}

		/**
		 * 
		 *
		 * @param  string  $driver
		 * @return mixed
		 */
		public static function debug($var)
		{
			return "<pre>".print_r($var)."</pre>";
		}

	}

?>