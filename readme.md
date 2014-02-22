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


### Future features 

> add Throttling
>
> https://github.com/websoftwares/Throttle
> http://laravel.com/docs/cache






