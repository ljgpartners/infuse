<?php namespace Bpez\Infuse;

use Illuminate\Support\ServiceProvider;
use Bpez\Infuse\Commands\MysqlDumpCommand;

/*
|--------------------------------------------------------------------------
| Steps for installing from a package
|--------------------------------------------------------------------------
|
| php artisan migrate --package="bpez/infuse"
| php artisan asset:publish bpez/infuse
|	php artisan config:publish bpez/infuse
| Add 'Bpez\Infuse\InfuseServiceProvider' to your laravel providers in /app/config/app.php
|
*/


/*
|--------------------------------------------------------------------------
| Steps for installing from a package (When working in workbench )
|--------------------------------------------------------------------------
|
| php artisan migrate --bench="bpez/infuse"
| php artisan asset:publish --bench="bpez/infuse"
| php artisan config:publish --path="workbench/bpez/infuse/src/config" bpez/infuse
| Add 'Bpez\Infuse\InfuseServiceProvider' to your laravel providers in /app/config/app.php
|
*/

/*
|--------------------------------------------------------------------------
| Add another class run this in infuse root
|--------------------------------------------------------------------------
|
| composer dump-autoload 
|
*/


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