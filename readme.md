Infuse
====================

> uses Verify (L4) A simple role/permission authentication package for Laravel 4. http://docs.toddish.co.uk/verify-l4/


Steps for installing 
---------------------

### From a composer package

> Install through composer package
>
> require: "bpez/infuse": "dev-master"
>
> Add the Infuse Service Provider to your config in app/config/app.php: 
>
> 'Bpez\Infuse\InfuseServiceProvider'
>
> $ php artisan asset:publish bpez/infuse
>
> $ php artisan config:publish bpez/infuse
>
> Add the Verify Service Provider to your config in app/config/app.php:
>
> 'Toddish\Verify\VerifyServiceProvider'
> 
> $ php artisan migrate --package="bpez/infuse"
>
> In app/config/auth.php set:
> 'auth.driver' => 'verify'
> 'auth.model' => 'InfuseUser'

### When working in workbench

> $ php artisan migrate --bench="bpez/infuse"
>
> $ php artisan asset:publish --bench="bpez/infuse"
>
> $ php artisan config:publish --path="workbench/bpez/infuse/src/config" bpez/infuse
> 
> Add 'Bpez\Infuse\InfuseServiceProvider' to your laravel providers in /app/config/app.php
>
> Adding another class run this in infuse root (composer dump-autoload )
>


### After installing


> Add local and production environments
>
> $env = $app->detectEnvironment(array(
>		'local'   => array('imac.local'),
> 	'production' => array('dev.domain.com')
>	));
>

### After any additonal migrations added to the project clear the cache

> php artisan cache:clear

### Future features 

> add Throttling
>
> https://github.com/websoftwares/Throttle
> http://laravel.com/docs/cache
> https://github.com/frenzyapp/turbolinks
> https://github.com/rails/turbolinks
> https://github.com/kossnocorp/jquery.turbolinks


### artisan install 
> sudo curl https://raw.github.com/villimagg/Artisan-on-Steroids/master/artisan -o /usr/local/bin/artisan && sudo chmod +x /usr/local/bin/artisan



