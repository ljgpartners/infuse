<?php namespace Bpez\Infuse;

use Illuminate\Support\ServiceProvider;
use Bpez\Infuse\Commands\MysqlDumpCommand;



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
		
		$this->app->booting(function()
		{
			$loader = \Illuminate\Foundation\AliasLoader::getInstance();

			$loader->alias('Scaffold', 'Bpez\Infuse\Scaffold');
			$loader->alias('WebService', 'Bpez\Infuse\WebService');
			$loader->alias('Util', 'Bpez\Infuse\Util');
			$loader->alias('InfuseDeploy', 'Bpez\Infuse\Commands\InfuseDeploy');
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
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}