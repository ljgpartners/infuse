Infuse
====================

> uses Verify (L4) A simple role/permission authentication package for Laravel 4. http://docs.toddish.co.uk/verify-l4/


Steps for installing 
---------------------

### From a composer package

> $ php artisan migrate --package="bpez/infuse"
>
> $ php artisan asset:publish bpez/infuse
>
> $ php artisan config:publish bpez/infuse
>
> Add 'Bpez\Infuse\InfuseServiceProvider' to your laravel providers in /app/config/app.php



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


### New additional infuse steps

> Add the Verify Service Provider to your config in app/config/app.php:
>
> 'Toddish\Verify\VerifyServiceProvider'
> 
> 'driver' => 'verify',
>
> You may also change the 'model' value to 'Toddish\Verify\Models\User' if you want to be able to load Verify's User model when using Auth::user().
>
> $ php artisan config:publish toddish/verify
> $ php artisan config:publish --path="workbench/bpez/infuse/vendor/toddish/verify/src/config" toddish/verify
>
> $ php artisan migrate --package="toddish/verify"
> $ php artisan migrate --bench="toddish/verify"