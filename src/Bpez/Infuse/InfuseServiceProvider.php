<?php namespace Bpez\Infuse;

use Illuminate\Support\ServiceProvider;



class InfuseServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('bpez/infuse');

		// Register namespace for config resources
		\Config::addNamespace('infuse', app_path().'/config/packages/bpez/infuse');
		\Config::addNamespace('infuse_deploy', app_path().'/config/packages/bpez/infuse/deploy');

		

		include __DIR__.'/../../routes.php';
		
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->register('Toddish\Verify\VerifyServiceProvider');
		$this->app->register('Intervention\Image\ImageServiceProvider');
		$this->app->register('Codesleeve\AssetPipeline\AssetPipelineServiceProvider');
		$this->app->register('Barryvdh\Debugbar\ServiceProvider');



		$this->app->bind('InfuseController', function($app)
    {
        return new \InfuseController(
        	$app->make("auth")->user()
        );
    });

		$this->app['scaffold'] = $this->app->share(function($app)
    {
        return new  Scaffold(
        	$app['view'],
        	$app->make("auth")->user(),
        	$app->make("DB"),
        	$app['request']
        );
    });

    $this->app['util'] = $this->app->share(function($app)
    {
        return new Util($app['request']);
    });

    $this->app['web.service'] = $this->app->share(function($app)
    {
        return new WebService($app->make("DB"));
    });

		$this->app['command.infuse.dump'] = $this->app->share(function($app)
    {
        return new Commands\InfuseDump();
    });

		$this->app['command.infuse.deploy'] = $this->app->share(function($app)
    {
        return new Commands\InfuseDeploy();
    });

    $this->commands(array('command.infuse.deploy', 'command.infuse.dump'));


    // Shortcut so developers don't need to add an Alias in app/config/app.php
    $this->app->booting(function()
    {
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Util', 'Bpez\Infuse\Facades\Util');
        $loader->alias('Scaffold', 'Bpez\Infuse\Facades\Scaffold');
        $loader->alias('WebService', 'Bpez\Infuse\Facades\WebService');
        $loader->alias('Image', 'Intervention\Image\Facades\Image');
        $loader->alias('Debugbar', 'Barryvdh\Debugbar\Facade');
        $loader->alias('Carbon', 'Carbon\Carbon');
    });
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('scaffold', 'util', 'web.service');
	}

}