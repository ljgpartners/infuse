<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;

class InfusePage extends InfuseEloquent {

  use InfuseEloquentLibrary;

  protected $table = 'pages';

  protected $uploadFolder = "/uploads";

  protected $pageValues;

  public static function boot()
  {
      parent::boot();
  }

  public static function processType($pageValue)
  {
    switch ($pageValue['type']) {
      case 'string':
      case 'text':
        $return = $pageValue['value'];
        break;
      case 'upload':
        $infusePage = new InfusePage;
        $return = $infusePage->uploadPath("page_data").$pageValue['value'];
        break;
      case 'group':
        $temp = array();
        $count = 0;
        $return = $pageValue['value'];
        if (count($return) > 0) {
          foreach ($return as $item) {
            if ($item['type'] == "divider") {
              $count++;
            } else {
              if (isset($temp[$count]) && is_array($temp[$count])) {
                array_push($temp[$count], self::processType($item));
              } else {
                $temp[$count] = array(self::processType($item));
              }
              
            }
          }

          if ($count == 0) {
            $temp = $temp[$count];
          }
        }
        
        $return = $temp;
        break;
      
      default:
        $return = "";
        break;
    }
    return $return;
  }


  // $pip = page instance path
  // $jsonQuery .= "->'page'";
  public static function extract($id, $pip, $target, $label)
  { 
    if (!Session::has('infuse_page_values')) { 
      $jsonQuery = "page_data";
      $parent = explode(";", $pip);
      foreach ($parent as $p) {
        $jsonQuery .= "->'{$p}'->'pages'";
      }
      $jsonQuery = preg_replace('/->\'pages\'$/', '', $jsonQuery);
      
      try {
        $infusePage =  self::select(DB::raw("{$jsonQuery}->'pageValues' as page_values"))
          ->where("id", "=", $id)
          ->firstOrFail();
      } catch (ModelNotFoundException $e) {
        return "";
      }
      
      $pageValues = json_decode($infusePage->page_values, true);
      $temp = array();
      foreach ($pageValues as $value) {
        $temp["{$value['id']}"] = $value;
      }
      $pageValues = $temp;
      unset($infusePage);
      unset($temp);
      Session::put('infuse_page_values', $pageValues);
      $currentPage = $id.$pip;
      Session::put('infuse_page_extract_current', $currentPage);
    } else {
      $pageValues = Session::get('infuse_page_values');
      $currentPage = Session::get('infuse_page_extract_current');
    }

    if ($currentPage != $id.$pip) {
      return "Content not from same InfusePage. Only content from the same InfusePage can be displayed.";
    }

    if (isset($pageValues[$target])) {
      $return = self::processType($pageValues[$target]);
    } else {
      $return = "";
    }


    return $return;

  }

}




