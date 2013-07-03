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

		include __DIR__.'/../../routes.php';
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{/*
    $this->app['mysqldump.infuse.mysqldump'] = $this->app->share(function($app)
    {		
        return new MysqlDumpCommand();
    });
 
    $this->commands('mysqldump.infuse.mysqldump');*/
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