<?php namespace Bpez\Infuse;

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

		public static function getForeignKeyString($class) 
		{
			return strtolower(get_class($class))."_id";
		}

		public static function foreignKeyString($modelString) 
		{
			return strtolower($modelString)."_id";
		}

		public static function isForeignKey($columnString)
		{
	    $matches = null;
			return (preg_match("/_id$/", $columnString, $matches, PREG_OFFSET_CAPTURE) == 0)? false : true;
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

		public static function getPath()
		{
			if (strpos($_SERVER['REQUEST_URI'], "?")) {
				$path = explode("?", $_SERVER['REQUEST_URI'])[0];
			} else {
				$path = $_SERVER['REQUEST_URI'];
			}
			return $path;
		}

		public static function classToString($instance)
		{
			return strtolower(self::camel2under(get_class($instance)));
		}

		public static function stringToCLass($name)
		{
			$class = ucfirst($name);
			return new $class;
		}

		public static function redirectUrl()
		{
			return str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		}

		public static function redirectUrlSaveFailed($id)
		{
			$action = ($id)? "?action=e&id=".$id : "?action=c";
			return str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])."{$action}";;
		}

		public static function redirectUrlChildSaveFailed($parent, $pid)
		{
			$action = "?action=c&pid={$pid}&parent={$parent}";
			return str_replace("?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])."{$action}";;
		}

		

		public static function redirectBackToParentUrl($currentModel, $parentId)
		{
			return str_replace("/".strtolower($currentModel)."?".$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI'])."?action=e&id={$parentId}";
		}


		public static function camel2under($str) 
	  { 
	     $regexp = '#(?<!=^)[A-Z]#e'; 
	     $str = preg_replace($regexp, "'_'.strtolower('\\0')", $str);
	     if (substr($str, 0, 1) == "_"){
	     	return  substr($str, 1);
	     } else {
	     	return $str; 
	     }
	  } 

    public static function under2camel($str) 
    { 
      $regexp = '#_(.)#e'; 
      return preg_replace($regexp, "strtoupper('\\1')", $str); 
    } 


		 /**
     * Inserts values after specific key.
     *
     * @param array $array
     * @param sting/integer $position
     * @param array $values
     * @throws Exception
     */
    public static function insertAfter(array &$array, $position, array $values)
    {
        // enforce existing position
        if (!isset($array[$position])) {
            throw new Exception(strtr('Array position does not exist (:1)', [':1' => $position]));
        }
 
        // offset
        $offset = 0;
 
        // loop through array
        foreach ($array as $key => $value) {
            // increase offset
            ++$offset;
 
            // break if key has been found
            if ($key == $position) {
                break;
            }
        }
 
        $array = array_slice($array, 0, $offset, TRUE) + $values + array_slice($array, $offset, NULL, TRUE);
        return $array;
    }


    /**
     * Inserts values before specific key.
     *
     * @param array $array
     * @param sting/integer $position
     * @param array $values
     * @throws Exception
     */
    public static function insertBefore(array &$array, $position, array $values)
    {
        // enforce existing position
        if (!isset($array[$position])) {
            throw new Exception(strtr('Array position does not exist (:1)', [':1' => $position]));
        }
 
        // offset
        $offset = -1;
 
        // loop through array
        foreach ($array as $key => $value) {
            // increase offset
            ++$offset;
 
            // break if key has been found
            if ($key == $position) {
                break;
            }
        }
 
        $array = array_slice($array, 0, $offset, TRUE) + $values + array_slice($array, $offset, NULL, TRUE);
        return $array;
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


		public static function outputCSV($data) 
		{
        $outputBuffer = fopen("php://output", 'w');
        foreach($data as $val) {
           fputcsv($outputBuffer, $val);
        }
        fclose($outputBuffer);
    }


    public static function returnCSVDataAsFile($filename, $data) 
		{
        header("Content-type: text/csv");
		    header("Content-Disposition: attachment; filename={$filename}.csv");
		    header("Pragma: no-cache");
		    header("Expires: 0");
		    self::outputCSV($data);
		    exit();
    }


    

		
 

	}

?>