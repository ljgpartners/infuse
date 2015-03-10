<?php namespace Bpez\Infuse\Facades;

use Illuminate\Support\Facades\Facade;

class FileUpload extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'file.upload'; }

}