Infuse
====================

[![Latest Stable Version](https://poser.pugx.org/bpez/infuse/v/stable.png)](https://packagist.org/packages/bpez/infuse) [![Total Downloads](https://poser.pugx.org/bpez/infuse/downloads.png)](https://packagist.org/packages/bpez/infuse) [![Latest Unstable Version](https://poser.pugx.org/bpez/infuse/v/unstable.png)](https://packagist.org/packages/bpez/infuse) [![License](https://poser.pugx.org/bpez/infuse/license.png)](https://packagist.org/packages/bpez/infuse)

## Setup

Step 1: Add laravel5 path repo for toddish/verify package
```json
"repositories": [
 {
      "type": "vcs",
      "url": "https://github.com/industrious-mouse/Verify-L4"
  }
],
```

Step 2: Install via composer
```php
> composer require bpez/infuse:5.*
```

Step 3: Add the ServiceProvider
```php
// config/app.php
'providers' => [
    ...
    'Bpez\Infuse\InfuseServiceProvider' ,
] ,
```

Step 4: Add to middleware
```php
// app\http\Kernel.php
protected $middleware = [
  ...
  'Bpez\Infuse\Middleware\InfuseBeforeMiddleware',
  'Bpez\Infuse\Middleware\InfuseAfterMiddleware'
];
```


Step 5: Configure auth file
```php
// config/auth.php
...
'driver' => 'verify',
'model' => 'InfuseUser',
...
'password' => [
    'email' => 'infuse::emails.password',
    ...
  ],
...
```

Step 6: Publish Configurations & public assets
```cmd
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_public --force
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_config
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_structure
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_migrations
```

Step 7: Add to composer.json
```json
"post-update-cmd": [
    ...
    "php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_public --force"
],
```


Step 8: Add to .htaccess
```htacess

RewriteEngine On
...
    RewriteCond %{HTTP:Cookie} devicePixelRatio [NC]
    RewriteRule \.(?:jpe?g|gif|png|bmp)$ bpez/infuse/other/retinaimages.php [NC,L]
```

Step 9: Run infuse migrations
```cmd
> php artisan migrate

```

Step 10: Add support to server retina images
```htaccess
RewriteCond %{HTTP:Cookie} devicePixelRatio [NC]
RewriteRule \.(?:jpe?g|gif|png|bmp)$ /packages/bpez/infuse/retinaimages.php [NC,L]
```

### API Documentation
[Documentation](infuse_api_doc.md)


### Possible future features 

```
> https://github.com/websoftwares/Throttle
> http://laravel.com/docs/cache
> https://github.com/frenzyapp/turbolinks
> https://github.com/rails/turbolinks
> https://github.com/kossnocorp/jquery.turbolinks
```

###  Install artisan on sterioids
```cmd
> sudo curl https://raw.github.com/villimagg/Artisan-on-Steroids/master/artisan -o /usr/local/bin/artisan && sudo chmod +x /usr/local/bin/artisan
```

