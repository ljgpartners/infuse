Infuse
====================


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

### Basset asset management

> Add to composer.json "jasonlewis/basset": "dev-master", "natxet/CssMin": "dev-master", "mrclay/minify": "dev-master"
>
> Add 'Basset\BassetServiceProvider' to your laravel providers in /app/config/app.php
>
> $ php artisan config:publish jasonlewis/basset

In app/bootstrap/start.php Add local & production environments 

> $env = $app->detectEnvironment(array(
>
>	'local' => array('localhost'),
>	'production' => array('somedomain.com'),
>
> ));


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


After install
---------------------

### Deploying to production server

> php artisan basset:build --production

### Using basset in your layout templates

> {{ basset_stylesheets('application', 'admin') }}
> {{ basset_javascripts('application') }}