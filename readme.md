Infuse
====================

[![Latest Stable Version](https://poser.pugx.org/bpez/infuse/v/stable.png)](https://packagist.org/packages/bpez/infuse) [![Total Downloads](https://poser.pugx.org/bpez/infuse/downloads.png)](https://packagist.org/packages/bpez/infuse) [![Latest Unstable Version](https://poser.pugx.org/bpez/infuse/v/unstable.png)](https://packagist.org/packages/bpez/infuse) [![License](https://poser.pugx.org/bpez/infuse/license.png)](https://packagist.org/packages/bpez/infuse)

> uses Verify (L4) A simple role/permission authentication package for Laravel 4. http://docs.toddish.co.uk/verify-l4/


Steps for installing 
---------------------

### From a composer package

> Install through composer package
>
> $ composer require bpez/infuse:3.*
>
> Add the Infuse Service Provider to your config in app/config/app.php: 
>
> 'Bpez\Infuse\InfuseServiceProvider'
>
> $ php artisan asset:publish bpez/infuse
>
> $ php artisan config:publish bpez/infuse
> 
> $ php artisan migrate --package="bpez/infuse"
>
> In app/config/auth.php set:
> 'auth.driver' => 'verify'
> 'auth.model' => 'InfuseUser'
>
> php artisan debugbar:publish
>
> add the following to post-update-cmd in composer.json
> "php artisan debugbar:publish",
> "php artisan asset:publish bpez/infuse"

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


### Possible future features 

> add Throttling
>
> https://github.com/websoftwares/Throttle
> http://laravel.com/docs/cache
> https://github.com/frenzyapp/turbolinks
> https://github.com/rails/turbolinks
> https://github.com/kossnocorp/jquery.turbolinks


### artisan install 
> sudo curl https://raw.github.com/villimagg/Artisan-on-Steroids/master/artisan -o /usr/local/bin/artisan && sudo chmod +x /usr/local/bin/artisan

phpdoc.php -f src/Bpez/Infuse/Scaffold.php -t ./docs --template="responsive-twig"

implement request dependency

