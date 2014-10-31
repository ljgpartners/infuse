<?php namespace Bpez\Infuse;

trait InfuseEloquentLibrary {

  public function validate($data)
  {
    /*
      $replace = ($this->getKey() > 0) ? $this->getKey() : '';
      foreach ($this->rules as $key => $rule) {
          $this->rules[$key] = str_replace('[id]', $replace, $rule);
      }
    */
      // make a new validator object
      $v = \Validator::make($data, $this->rules);

      // check for failure
      if ($v->fails()) { 
          // set errors and return false
          $this->errors = $v->messages();
          return false;
      }
      
      // validation pass
      return true;
  }

  protected function processRules(array $rules)
  {
      $id = $this->getKey();
      array_walk($rules, function(&$item) use ($id)
      {
          // Replace placeholders
          $item = stripos($item, ':id:') !== false ? str_ireplace(':id:', $id, $item) : $item;
      });

      return $rules;
  }


  public function errors()
  {
      return $this->errors;
  }
  

  public function uploadPath($column)
  {
      return strtolower($_SERVER['DOCUMENT_ROOT'].$this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR);
  }

  public function url($column)
  { 
    $processedColumn = $this->{$column};

    if (filter_var($this->{$column}, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
      return $processedColumn;
    } else { 
      $baseUrlUploadedAssetsLocal = \Config::get("infuse::base_url_uploaded_assets_local");

      if (\App::environment() != "production") { 
        return $baseUrlUploadedAssetsLocal.strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR).$processedColumn;
      } else {
        return strtolower($this->uploadFolder.DIRECTORY_SEPARATOR
                  .get_class($this).DIRECTORY_SEPARATOR
                  .$column.DIRECTORY_SEPARATOR).$processedColumn;
      }
    }
  }

  

}
