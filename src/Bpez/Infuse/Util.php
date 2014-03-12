<?php namespace Bpez\Infuse;

use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Util 
|--------------------------------------------------------------------------
| This class contains helper methods used by infuse 
|
*/

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


	public static function truncateText($text, $nbrChar, $append = '...') 
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

	public static function debug($var, $laravelLogger = false)
	{
		ob_start();
		echo "<pre>";
		var_dump($var);//print_r($var);
		echo "</pre>";

		if ($laravelLogger) {
			Log::error(ob_get_clean());
		} else {
			return ob_get_clean();
		}
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


	////////////////////////////////////////////
	/// Stack Functions
	///////////////////////////////////////////


	public static function stackPush($resource, $id, $uri)
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		if (!array_key_exists($resource, $stack))
			$stack[$resource] = array($resource, $id, $uri);
		self::flashArray("infuse_stack", $stack);
	}

	public static function stackPop()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		$popped = array_pop($stack);
		self::flashArray("infuse_stack", $stack);
		return $popped;
	}

	public static function stackParent()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		$parent = end($stack);
		if (count($stack) >= 2) {
			prev($stack);
			$parent = current($stack);
		}
		end($stack);
		self::flashArray("infuse_stack", $stack);
		return $parent;
	}

	public static function stackParentId()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		$parent = end($stack);
		if (count($stack) >= 2) {
			prev($stack);
			$parent = current($stack);
		}
		end($stack);
		self::flashArray("infuse_stack", $stack);
		return $parent[1];
	}

	public static function stackParentName()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		$parent = end($stack);
		if (count($stack) >= 2) {
			prev($stack);
			$parent = current($stack);
		}
		end($stack);
		self::flashArray("infuse_stack", $stack);
		return $parent[0];
	}

	public static function stackParentBaseUri()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		$parent = end($stack);
		if (count($stack) >= 2) {
			prev($stack);
			$parent = current($stack);
		}
		end($stack);
		self::flashArray("infuse_stack", $stack);
		return $parent[2];
	}

	public static function stackSize()
	{
		$stack = self::flashArray("infuse_stack");
		$stack = ($stack == null)? array() : $stack;
		self::flashArray("infuse_stack", $stack);
		return count($stack);
	}

	public static function stackReset()
	{
		self::flashArray("infuse_stack", array());
	}

	public static function childActionLink($model, $action, $id = null)
	{
		$model = self::camel2under($model);
		
		if (self::stackSize() == 1) {
			$top = self::stackPop();
			$baseUri  = $top[2]."/child";
		} else {
			$top = self::stackPop();
			$baseUri  = $top[2];
		}
		self::stackPush($top[0], $top[1], $top[2]);
		
		if ($id == null) {
			return "/{$baseUri}?stack={$model}&action=c";
		} else {
			return "/{$baseUri}?stack={$model}&action={$action}&id={$id}";
		}
		 
	}
	
	public static function childBackLink($popValues = false)
	{
		if (self::stackSize() == 2) { 
			if ($popValues) { 
				self::stackPop();
				$parent = self::stackPop(); 
			} else {
				$parent = self::stackParent();
			}
			$id = $parent[1];
			$baseUri = $parent[2];
		 	return "/{$baseUri}?action=e&id={$id}&pop=1";
		} else { 
			if ($popValues) {
				self::stackPop(); 
				$parent = self::stackPop(); 
				self::stackPush($parent[0], $parent[1], $parent[2]); 
			} else {
				$parent = self::stackParent();
			}
			$model = $parent[0];
			$id = $parent[1];
			$baseUri = $parent[2];
			return "/{$baseUri}?stack={$model}&action=e&id={$id}&pop=1";
		}
			
	}

	public static function redirectUrlChildSaveFailed($id = false)
	{
		$parent = self::stackPop(); 
		$model = $parent[0];
		$baseUri = $parent[2];
		if ($id) 
			return "/{$baseUri}?stack={$model}&action=e&id={$id}";
		else
			return "/{$baseUri}?stack={$model}&action=c";
	}

	//////////////////////////////////////////
	// 	End of stack functions
	//////////////////////////////////////////

	public static function arrayToObject($array)
	{
		return (object)$array;
	}

	public static function getPath()
	{
		if (strpos($_SERVER['REQUEST_URI'], "?")) {
			$path = explode("?", $_SERVER['REQUEST_URI']);
			$path = $path[0];
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

	/*
	Replace last instancex
	*/
	public static function str_lreplace($search, $replace, $subject)
	{
    $pos = strrpos($subject, $search);

    if($pos !== false) {
    	$subject = substr_replace($subject, $replace, $pos, strlen($search));
    }

    return $subject;
	}



	public static function splitReturnFirst($string, $delimiter)
	{
		if (strpos($string, "@") !== FALSE) {
			$string = explode($delimiter, $string);
			return $string[0];
		} else {
			return false;
		}
	}


	public static function camel2under($str) 
  { 
     /* The e modifier is deprecated as of PHP 5.5.0 . 
     		Replace preg_replace() e modifier with preg_replace_callback 
	     $regexp = '#(?<!=^)[A-Z]#e'; 
	     $str = preg_replace($regexp, "'_'.strtolower('\\0')", $str); 
     */
	   $regexp = '#(?<!=^)[A-Z]#'; 
     $str = preg_replace_callback($regexp, function($matches){ return '_'.strtolower($matches[0]); }, $str);

     if (substr($str, 0, 1) == "_"){
     	return  substr($str, 1);
     } else {
     	return $str; 
     }
  } 

  public static function under2camel($str)  
  { 
  	/* The e modifier is deprecated as of PHP 5.5.0 . 
     	 Replace preg_replace() e modifier with preg_replace_callback 
       $regexp = '#_(.)#e'; 
       return preg_replace($regexp, "strtoupper('\\1')", $str); 
    */
    $regexp = '#_(.)#';
    return preg_replace_callback($regexp, function($matches){ return '_'.strtolower($matches[1]); }, $str);
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
          throw new Exception(strtr('Array position does not exist (:1)', array(':1' => $position)));
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
          throw new Exception(strtr('Array position does not exist (:1)', array(':1' => $position)));
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

  public static function importCSV($filename, $keysOnly = false) 
	{
	  $header = false;
		$data = array();
		
		ini_set('auto_detect_line_endings', true);
		if(($handle = fopen($filename, "r")) !== FALSE)	{
			while(($row = fgetcsv($handle, 1000, ",")) !== FALSE)	{
				if(!$header) {
					$header = $row;
					if ($keysOnly)
						return $header;
				} else {
					$data[]  = array_combine($header, $row);
				}
			}
			fclose($handle);
		}

		return $data;
  }


  public static function checkInfuseLoginFields($infuseLogin, $column) 
	{
      if ($infuseLogin && $column['field'] == 'password')
      	return false;
      if ($infuseLogin && $column['field'] == 'salt')
      	return false;
      if ($infuseLogin && $column['field'] == 'verified')
      	return false;
      if ($infuseLogin && $column['field'] == 'deleted_at')
      	return false;

      return true;
  }

	


}

?>