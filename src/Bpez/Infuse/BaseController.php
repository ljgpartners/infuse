<?php namespace Bpez\Infuse;

use Exception;
use Illuminate\Routing\Controller;
use View;

class BaseController extends Controller {

    protected $layout = 'infuse::layouts.application';

    /**
     * Show the user profile.
     */
    public function setContent($view, $data = [])
    {

        if ( ! is_null($this->layout))
        {
            return $this->layout->nest('child', $view, $data);
        }

        return view($view, $data);

    }

    /**
     * Set the layout used by the controller.
     *
     * @param $name
     * @return void
     */
    protected function setLayout($name)
    {
        $this->layout = $name;
    }

    /**
     * Setup the layout used by the controller.
     *
     * @return void
     */
    protected function setupLayout()
    {
        if ( ! is_null($this->layout))
        {
            $this->layout = view($this->layout);
        }
    }


    public function callAction($method, $parameters)
    {
        $this->setupLayout();

        $response = call_user_func_array(array($this, $method), $parameters);


        if (is_null($response) && ! is_null($this->layout))
        {
            $response = $this->layout;
        }

        return $response;
    }


    public function loadResource($resource)
    {
        $app = app();
        $packageConfigFile = app_path()."/Infuse/{$resource}.php";
        $config = $app['files']->getRequire($packageConfigFile);
        $app['config']->set("infuse::{$resource}", $config);
    }


}