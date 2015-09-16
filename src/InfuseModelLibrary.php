<?php namespace Bpez\Infuse;

use Config;
use Exception;

trait InfuseModelLibrary {

    public function validate($data)
    {

        $replace = ($this->getKey() > 0) ? $this->getKey() : 0;
        foreach ($this->rules as $key => $rule) {
            $this->rules[$key] = str_replace('[id]', $replace, $rule);
        }

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

    /**
    *  DO NOT USE THIS FUNCTION UNTIL RECODED USING THE FILEUPLOAD CLASS *********
    */
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

    /*
    |--------------------------------------------------------------------------
    | FileUpload Related Methods
    |--------------------------------------------------------------------------
    | This class contains helper uploading methods used by infuse
    |
    */


    public function uploadPath($column)
    {
        return \FileUpload::uploadPath($this, $column);
    }

    public function url($column, $hstoreColumn = false)
    {
        return \FileUpload::url($this, $column, $hstoreColumn);
    }

    /*
    |--------------------------------------------------------------------------
    | Thumbor Related Methods
    |--------------------------------------------------------------------------
    | On the fly croping and filter methods
    |
    */

    public function thumbor($column, $hstoreColumn = false)
    {
        if (Config::get("filesystems.default") == "s3" &&
			Config::has("services.thumbor.security-key") &&
			Config::has("services.thumbor.host")
		) {
            $server = Config::get("services.thumbor.host");
            $secret = Config::get("services.thumbor.security-key");
            $thumbnailUrlFactory = \Thumbor\Url\BuilderFactory::construct($server, $secret);
            $this->infuseCdnOff = true; // Turn off delivery from cdn and grab from direct source
            $url = \FileUpload::url($this, $column, $hstoreColumn);
            return $thumbnailUrlFactory->url($url);
        } else {
            throw new Exception("S3 Filesystem and  99designs/phumbor library required.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PGSQL Helper Methods
    |--------------------------------------------------------------------------
    | This class contains helper uploading methods used by infuse
    |
    */

    private function hstoreToArray($value)
    {
        return PGUtil::hstoreToPhp($value);
    }

    private function arrayTohstore($value)
    {
        return PGUtil::hstoreFromPhp($value);
    }

    public function infuseSaveHstoreValuesStart()
    {
        foreach ($this->hstore as $key => $value) {
            if (isset($value['modified']) && isset($value['cache'])) {
                $this->setAttribute($key, $this->arrayTohstore($value['cache']));
            }
        }
    }

    public function infuseSaveHstoreValuesEnd()
    {
        foreach ($this->hstore as $key => $value) {
            if (isset($value['modified']) && isset($value['cache'])) {
                unset($this->hstore[$key]['cache']);
                unset($this->hstore[$key]['modified']);
            }
        }
    }



    public function setHstore($hstoreColumn, Array $hstoreKeyPairs)
    {
        if (!isset($this->hstore[$hstoreColumn])) {
            throw new \Exception("{$hstoreColumn} is not an hstore column.", 1);
        }

        if (!isset($this->hstore[$hstoreColumn]['cache'])) {
            $tempHstoreColumn = $this->{$hstoreColumn};
            $this->hstore[$hstoreColumn]['cache'] = (empty($tempHstoreColumn)) ? [] : $tempHstoreColumn;
        }

        foreach ($hstoreKeyPairs as $key => $value) {
            $type = gettype($value);

            if ($type != "string" && $type != "integer" && $type != "double")  {
                throw new \Exception("Value type {$type} not accepted for {$key}.", 1);
            }

            $this->hstore[$hstoreColumn]['cache'] = array_merge($this->hstore[$hstoreColumn]['cache'], array($key => $value));
            $this->hstore[$hstoreColumn]['modified'] = true;
            return true;
        }


    }


    public function getHstore($hstoreColumn, $key)
    {
        $this->{$hstoreColumn};
        return (isset($this->hstore[$hstoreColumn]['cache'][$key]))? $this->hstore[$hstoreColumn]['cache'][$key] : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Overide Illuminate/Database/Eloquent/Model Methods
    |--------------------------------------------------------------------------
    | Extend functionality Model
    |
    */


    public function getAttributeValue($key)
    {
        if (isset($this->hstore) && array_key_exists($key, $this->hstore)) {
            if (!isset($this->hstore[$key]['cache'])) {
                $value = $this->getAttributeFromArray($key);
                $this->hstore[$key]['cache'] = PGUtil::hstoreToPhp($value);
            }
            return $this->hstore[$key]['cache'];
        }

        return parent::getAttributeValue($key);
    }

    public function save(array $options = array())
    {
        $this->infuseSaveHstoreValuesStart();
        $saved = parent::save($options);
        $this->infuseSaveHstoreValuesEnd();
        return $saved;
    }



}
