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
   * Perform post-registration booting of services.
   *
   * @return void
   */
  public function boot()
  {


    $this->loadViewsFrom(__DIR__.'/../views', "infuse");


    // artisan vendor:publish --tag=infuse_public --force

    $this->publishes([
      __DIR__.'/../public/css' => public_path('bpez/infuse')."/css",
      __DIR__.'/../public/js' => public_path('bpez/infuse')."/js",
      __DIR__.'/../public/fonts' => public_path('bpez/infuse')."/fonts",
      __DIR__.'/../public/images' => public_path('bpez/infuse')."/images",
      __DIR__.'/../public/ckeditor' => public_path('bpez/infuse')."/ckeditor",
      __DIR__.'/../public/other' => public_path('bpez/infuse')."/other",
    ], 'infuse_public');

    // artisan vendor:publish --tag=infuse_config

    $this->publishes([
      __DIR__.'/../config/config.php' => config_path('infuse/config.php'),
    ], 'infuse_config');

    // artisan vendor:publish --tag=infuse_structure

    $this->publishes([
      __DIR__.'/../structure/' =>  app_path().'/Infuse'
    ], 'infuse_structure');

     // artisan vendor:publish --tag=infuse_migrations

    // Publish your migrations
    /*
    $this->publishes([
        __DIR__.'/../migrations/' => base_path('/database/migrations')
    ], 'infuse_migrations');
    */

    require __DIR__ . '/../routes.php';
  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register()
  {

    $this->registerResources();


    $this->app->register('Toddish\Verify\VerifyServiceProvider');
    $this->app->register('Barryvdh\Debugbar\ServiceProvider');




    $this->app->bind('InfuseController', function($app)
    {
        $user = $app->make("auth")->user();
        $user = ($user == null)? new \InfuseUser : $user;
        return new \InfuseController(
          $user
        );
    });

    $this->app->bind('InfusePageController', function($app)
    {
        $user = $app->make("auth")->user();
        $user = ($user == null)? new \InfuseUser : $user;
        return new \InfusePageController(
          $user,
          $app['request'],
          $this->app->session
        );
    });


    $this->app['scaffold'] = $this->app->share(function($app)
    {
        $user = $app->make("auth")->user();
        $user = ($user == null)? new \InfuseUser : $user;
        return new  Scaffold(
          $app['view'],
          $user,
          $app->make("DB"),
          $app['request'],
          new \Event,
          $this->app->session
        );
    });

    $this->app['file.upload'] = $this->app->share(function($app)
    {
        return new FileUpload($app['request']);
    });

    $this->app['util'] = $this->app->share(function($app)
    {
        return new Util(
            $app['request']);
    });

    $this->app['web.service'] = $this->app->share(function($app)
    {
        return new WebService($app->make("DB"));
    });

    $this->app['command.infuse.deploy'] = $this->app->share(function($app)
    {
        return new Commands\InfuseDeploy();
    });





    $this->commands(array('command.infuse.deploy'));

    $loader = \Illuminate\Foundation\AliasLoader::getInstance();
    $loader->alias('Util', 'Bpez\Infuse\Facades\Util');
    $loader->alias('Scaffold', 'Bpez\Infuse\Facades\Scaffold');
    $loader->alias('WebService', 'Bpez\Infuse\Facades\WebService');
    $loader->alias('FileUpload', 'Bpez\Infuse\Facades\FileUpload');
    $loader->alias('Debugbar', 'Barryvdh\Debugbar\Facade');
    $loader->alias('Carbon', 'Carbon\Carbon');
    $loader->alias('InfuseModel', 'Bpez\Infuse\InfuseModel');
    $loader->alias('InfuseModelLibrary', 'Bpez\Infuse\InfuseModelLibrary');
    $loader->alias('InfuseUserLibrary', 'Bpez\Infuse\InfuseUserLibrary');


  }

  /**
 * Register the package resources.
 *
 * @return void
 */
  protected function registerResources()
  {
    $userConfigFile    = config_path('infuse').'/config.php';
    $packageConfigFile = __DIR__.'/../config/config.php';
    $config            = $this->app['files']->getRequire($packageConfigFile);

    if (file_exists($userConfigFile)) {
      $userConfig = $this->app['files']->getRequire($userConfigFile);
      // Uncomment to merge down configs
      // $config     = array_replace_recursive($config, $userConfig);
      $config = $userConfig;
    }

    $this->app['config']->set('infuse::config', $config);

  }


  /**
   * Get the services provided by the provider.
   *
   * @return array
   */
  public function provides()
  {
    return array('file.upload', 'scaffold', 'util', 'web.service', 'infuse.model', 'infuse.model.library', 'infuse.user.library');
  }

}
