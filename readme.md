Infuse
====================

[![Latest Stable Version](https://poser.pugx.org/bpez/infuse/v/stable.png)](https://packagist.org/packages/bpez/infuse) [![Total Downloads](https://poser.pugx.org/bpez/infuse/downloads.png)](https://packagist.org/packages/bpez/infuse) [![Latest Unstable Version](https://poser.pugx.org/bpez/infuse/v/unstable.png)](https://packagist.org/packages/bpez/infuse) [![License](https://poser.pugx.org/bpez/infuse/license.png)](https://packagist.org/packages/bpez/infuse)


Steps for installing 
---------------------

### From a composer package

> Install through composer package
>
> $ composer require bpez/infuse:4.*
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
>
> add the following to post-update-cmd in composer.json
>
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
> add the following .htaccess rule for adding validate http cache to static assets

### Munee Validate Cache .htaccess Code Start #
RewriteRule ^(.*\.(?:css|less|scss|js|coffee|jpg|png|gif|jpeg))$ packages/bpez/infuse/munee.php?files=/$1 [L,QSA,NC]


#### Munee Validate Cache .htaccess Code Start ####
RewriteCond %{REQUEST_URI} !^/packages/bpez/infuse/ckeditor/(.*)
RewriteCond %{REQUEST_URI} !^/packages/maximebf/(.*)
RewriteCond %{REQUEST_URI} !^/packages/barryvdh/(.*)
# RewriteRule ^(.*.(?:css|less|scss|js|coffee))$ packages/bpez/infuse/munee.php?files=/$1 [L,QSA,NC]
RewriteRule ^(.*.(?:css|less|scss|js|coffee|jpg|png|gif|jpeg))$ packages/bpez/infuse/munee.php?files=/$1 [L,QSA,NC]
### Munee Validate Cache .htaccess Code End #

####  Serve Retina Images .htaccess Code Start ####
RewriteCond %{HTTP:Cookie} devicePixelRatio [NC]
RewriteRule \.(?:jpe?g|gif|png|bmp)$ /packages/bpez/infuse/retinaimages.php [NC,L]
### Serve Retina Images .htaccess Code End #

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

