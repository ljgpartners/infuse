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


  public function generateThumbnail($thumbnailToColumn, $thumbnailFromColumn, $width, $height)
  {   
      // Only process if image changed
      $original = $this->getOriginal();
      if (isset($original[$thumbnailFromColumn]) &&  $this->{$thumbnailFromColumn} == $original[$thumbnailFromColumn]) {
        return false;
      } else {
        // Remove old thumbnails if exist
        if (!empty($this->{$thumbnailToColumn}) && file_exists($_SERVER['DOCUMENT_ROOT']."/".$this->url($thumbnailToColumn))) {
          $currentFile = $_SERVER['DOCUMENT_ROOT']."/".$this->url($thumbnailToColumn);
          unlink($currentFile);
          $name = pathinfo($currentFile, PATHINFO_FILENAME);
          $ext  = pathinfo($currentFile, PATHINFO_EXTENSION);
          $retinaImage = $this->uploadPath($thumbnailToColumn).$name."@2x.".$ext;
          if (file_exists($retinaImage)) {
            unlink($retinaImage);
          }
          unset($currentFile);
          unset($retinaImage);
        }
      }

      // Get image from current column.
      $thumbnailImageFrom = $this->uploadPath($thumbnailFromColumn).$this->{$thumbnailFromColumn};

      $fileName = $this->{$thumbnailFromColumn};
      $name = pathinfo($fileName, PATHINFO_FILENAME);
      $ext  = pathinfo($fileName, PATHINFO_EXTENSION);
      $retinaImage = $this->uploadPath($thumbnailFromColumn).$name."@2x.".$ext;
      
      // Process retina 
      if (file_exists($retinaImage)) { 
        $retinaImageThumbnail = $this->uploadPath($thumbnailToColumn).$name.".thumbnail@2x.".$ext;
        $imageThumbnail = $this->uploadPath($thumbnailToColumn).$name.".thumbnail.".$ext;
        $widthRetina = 1.5*$width;
        $heightRetina = 1.5*$height;
        try {

          if (!file_exists($this->uploadPath($thumbnailToColumn))) {
            mkdir(dirname($retinaImageThumbnail), 0775, true);
          }

          
          if (copy($retinaImage, $retinaImageThumbnail)) {
              $transitRetina = new ResizeTransformer(array('width' => $widthRetina));
              if (!$transitRetina->transform(new File($retinaImageThumbnail), true)) {
                throw new Exception("Failed to resize retina for non retina version.");
              }

              if (copy($retinaImageThumbnail, $imageThumbnail)) {
                $transitRetina = new ResizeTransformer(array('width' => $width));
                if (!$transitRetina->transform(new File($imageThumbnail), true)) {
                  throw new Exception("Failed to resize retina for non retina version.");
                }
              } else {
                throw new Exception("Failed to copy retina image for processing.");
              }


              $this->{$thumbnailToColumn} = $name.".thumbnail.".$ext;
              //return $this->save();


          } else {
            throw new Exception("Failed to copy retina image for processing.");
          }

        } catch (Exception $e) {
          return die($e->getMessage());
        }
      
      // Process standard 
      } else if (!empty($this->{$thumbnailFromColumn}) && file_exists($thumbnailImageFrom)) { 
        $imageThumbnail = $this->uploadPath($thumbnailToColumn).$name.".thumbnail.".$ext;
        try {

          if (!file_exists($this->uploadPath($thumbnailToColumn))) {
            mkdir(dirname($imageThumbnail), 0775, true);
          }
          
          if (copy($thumbnailImageFrom, $imageThumbnail)) {
            $transitRetina = new ResizeTransformer(array('width' => $width));
            if (!$transitRetina->transform(new File($imageThumbnail), true)) {
              throw new Exception("Failed to resize retina for non retina version.");
            }
          } else {
            throw new Exception("Failed to copy retina image for processing.");
          }


          $this->{$thumbnailToColumn} = $name.".thumbnail.".$ext;

        } catch (Exception $e) {
          return die($e->getMessage());
        }
      }
  }

  

}
