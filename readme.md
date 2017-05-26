Infuse
====================

[![Latest Stable Version](https://poser.pugx.org/bpez/infuse/v/stable.png)](https://packagist.org/packages/bpez/infuse) [![Total Downloads](https://poser.pugx.org/bpez/infuse/downloads.png)](https://packagist.org/packages/bpez/infuse) [![Latest Unstable Version](https://poser.pugx.org/bpez/infuse/v/unstable.png)](https://packagist.org/packages/bpez/infuse) [![License](https://poser.pugx.org/bpez/infuse/license.png)](https://packagist.org/packages/bpez/infuse)

## Setup

Step 1: Install via composer
```php
> composer require bpez/infuse:dev-laravel5_1
```

Step 2: Add the ServiceProvider
```php
// config/app.php
'providers' => [
    ...
    'Bpez\Infuse\InfuseServiceProvider' ,
] ,
```

Step 3: Add to middleware
```php
// app\http\Kernel.php
protected $middleware = [
  ...
  'Bpez\Infuse\Middleware\InfuseBeforeMiddleware',
  'Bpez\Infuse\Middleware\InfuseAfterMiddleware'
];
```


Step 4: Configure auth file
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

Step 5: Publish Configurations & public assets & run migrations
```cmd
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_public --force
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_config
> php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_structure
> php artisan migrate --path="vendor/bpez/infuse/migrations"
```

Step 6: Add to composer.json
```json
"post-update-cmd": [
    ...
    "php artisan vendor:publish --provider="\Bpez\Infuse\InfuseServiceProvider" --tag=infuse_public --force"
],
```


Step 7: Add to .htaccess
```htacess

RewriteEngine On
...
    RewriteCond %{HTTP:Cookie} devicePixelRatio [NC]
    RewriteRule \.(?:jpe?g|gif|png|bmp)$ bpez/infuse/other/retinaimages.php [NC,L]
```

Step 8: Run infuse migrations
```cmd
> php artisan migrate

```

Step 9: Add support to server retina images
```htaccess
RewriteCond %{HTTP:Cookie} devicePixelRatio [NC]
RewriteRule \.(?:jpe?g|gif|png|bmp)$ /packages/bpez/infuse/retinaimages.php [NC,L]
```

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

### High level data flow
```php
model(mapConifig -> getbModelInstance(s) -> boot -> apiCalls) -> process(route -> renderView)
```


# Infuse Configuration API

## addSelect  
Turn database field to a html form select. Take in single instanct or multiple.
```php
addSelect(array(
    array(
        "column" => $columnName,
        "array" => array(),
        "insertBlank" => false,
        "topSelect" => false,
        "nested" => $nested,
        "nestedLastArray" => $nestedLastArray )
    ),
    ...
);
```
#### Parameters
- column - (Required) [string] Database table field name
- array - (Required) [array] Array of values. Formatted:

```php
array(
    array('id' => 1, ", 'name' => 'display name'),
    array('id' => 2, ", 'name' => 'display name'),
    ...
)
```
-  insertBlank - (Optional) [boolean] Inserts a blank option to select field. Default value is false.
-  nested - (Optional) [array] Model only needed for first one. Every level after that array format required

```php
$nested = array(
    "Floorplan",
    array("model" => "ImageAsset", "column" => "title")
);
```
-  nestedLastArray - (Optional) [array] Array of level values

```php
$nestedLastArray = array(
    array('id' => 1, ", 'name' => 'display name'),
    array('id' => 2, ", 'name' => 'display name'),
    ...
);
```

## describeColumn  
Adds a description to a database field.
```php
describeColumn(array(
    array(
        "column" => $columnName,
        "desc" => "description here",
        "popover" => true
    )
    ...
);
```
#### Parameters
- column - (Required) [string] Database table field name
- desc - (Required) [string] Description
- popover (Optional) [boolean] If option present then descript displayed as html pop.

## onlyOne  
Only allow one database entry to be display and created.
```php
onlyOne();
```


## addPermanentFilters
Add a SQL where clause to configuration.
```php
addPermanentFilters(array(
    array(
        "column" => $columnName,
        "operator" => "=",
        "value" => 87
    )
    ...
);
```
#### Parameters
- column - (Required) [string] Database table field name
- operator - (Required) [string] SQL comparison opertator. If 'IN' is used for the operator then it use elequents whereIn($column, $value)
- value - (Required) [string | number] Value to be compared to

## defaultColumnValues
Add a default value for when an entry is saved.
```php
defaultColumnValues(array(
    "column_name_1" => $value1,
    "column_name_2" => $value2,
    ...
);
```
#### Parameters
- array - (Required) [array] Array of default values where the index is the column name and the value is the value for the index

## addMultiSelect
Transform field to multiple select. Where IDs are kept track separated by a coma.
```php
addMultiSelect(array(
    array(
        "column" => $columnName,
        "array" => array(),
    ),
    ...
);
```
#### Parameters
- column - (Required) [string] Database table field name
- array - (Required) [array] Array of values. Formatted

```php
array(
    array('id' => 1, ", 'name' => 'display name'),
    array('id' => 2, ", 'name' => 'display name'),
    ...
)
```
## hasMany
One To Many relationships are defined here. Define children relations like format below.
```php
hasMany(array(
    array("SomeModelName", "Display Name", array("column_name_1", "column_name2"...) $optionsArray),
    ...
);
```
#### Parameters
1. (Required) [string] Child model name
2. (Required) [string] Display name for entries listed on parent edit page
3. (Required) [array] Child columns to display on entries listed on parent edit page
4. (Optional) [array] Contains extra functionality for dislaying child entries. Some options are as follows:

Ex. Child Columns array may have an array nested to match an id with a value like below:
```php
$childColumnArray = array(
  "column_1",
  "column_2",
  array("column_3" => array(
    array("id" => 1, "name" => "some name"),
    array("id" => 2, "name" => "some name 2")
  ),
  "column_4",
  ...
)
```
array("category" => $categories)

```php
$optionsArray = array(
  "order_column" => "display_order",
  "order_direction" => "ASC",
  "order_integer" => true
)
```
Ex. For adding custom export and import functions.
```php
$optionsArray = array(
    "import_csv_function" => "importUserModelInstancMethod",
    "import_csv_function_url" => "/path/to/example/template.csv",
    "import_csv_function_text" => "Import CSV for users. Use template provided.",
    "export_csv_function" => "exportUserModelInstanceMethod",
    "export_csv_function_text" => "Export CSV template to import.",
)
```
Ex. Split table to act like 2 child tables separated by where clauses.
```php
$optionsArray = array(
    "split" => array(
		"Gallery Assets" =>	"main_gallery = 1",
		"Gallery 2 Assets" =>	"main_gallery = 0"
	)
)
```

## displayOrder
Changes column to an column to be used for ordering entries. Integer is used for the type. Makes field not editable by user and value automatically set.
```php
displayOrder(array("display_order_column"));
```
#### Parameters
1. Database table field names

## fileUpload
Configure columns for file upload functionality.
```php
fileUpload(
  array(
    array("column" => "column_name"),
  )
)
```
#### Parameters
- column - (Required) [string] Database table field name

## addCkeditor
Turns a text field to a addCkeditor.
```php
addCkeditor(
    array("column_name_1", "column_name_2", ...)
)
```
#### Parameters
- Database table field names

## callFunction
Add extra action to entry's actions (show | edit | delete | YourActionHere ). Returning an array("type" => "success", "message" => $message) can tap into notification system. Type can be ("success" || "error" || "info" || "warning"). Or returning false will trigger default error message.
```php
callFunction(array(
    array(
        "function" => "importSomeThing",
        "display_name" => "Import some thing",
        "long_process" => "Processing please wait...",
        "target" => "_BLANK"
    ),
    ...
);
```
#### Parameters
1. (Required) [string] function - Name of the function. Its a laravel elequent query scope. So if name is importSomeThing then in the model it's declared scopeImportSomeThing
2. (Required) [string] Display name for the function
3. (Optional) [string] If its a lengthy process you can set a message that will populate a waiting modal while the process is running.
4. (Optional) [string] Provide a target for the html anchor tag.

Ex. importSomeThing implementation. Returns an array to the admin marked with type ("success" || "error") and message along with it.
```php
public function importSomeThings()
{
	// ... some work here
	$message = "Thanks";
	return array("type" => "success", "message" => $message);
}
```



## addOtherAction
Add top level action under the "Other Actions" dropdown on the scaffold listing page. Function should be static function from the top level model. User instance is passed into static function. Returning an array("type" => "success", "message" => $message) can tap into notification system. Type can be ("success" || "error" || "info" || "warning"). Or returning false will trigger default error message.
```php
addOtherAction(array(
    array(
        "function" => "seedUrls",
        "display_name" => "Import some thing",
    ),
    ...
);
```
#### Parameters
1. (Required) [string] function - Name of the function. Its a laravel elequent query scope. So if name is importSomeThing then in the model it's declared scopeImportSomeThing
2. (Required) [string] Display name for the function

Ex. seedUrls implementation
```php
public function seedUrls($currentUser)
{
	// ... some work here
	$message = "Thanks";
	return array("type" => "success", "message" => $message);
}
```

## editListingOrder
Lets you select the column ordering for the edit/create page. Pass in an array with  the column names in the desired order.
```php
editListingOrder(array(
    "created_at",
    "updated_at",
    "destination_site",
    "display_state",
    "property",
    "mobile_video_img",
    "sub_title",
    "video",
    "title",
));
```
#### Parameters
1. (Required) [string] table field names


# Eloquent ORM
Setup models to work with infuse by extending the InfuseModel and using InfuseModelLibrary trait.
```php
class BlogPost extends InfuseModel {

	use InfuseModelLibrary;

}
```

### InfuseModel features

#### url($columnName, $hstoreColumn = false)
Fetch the full url path for the file name saved to the specific column. Call the url method and pass in the column name as a parameter.
```php
$post = new BlogPost;
$post->url($columnName); // Will return a url similar to http://somedomain.com/upload/some/path/name.jpg
```


#### thumbor($columnName, $hstoreColumn = false)
Building on top of url functionality return an instance of a thumbor client. Only works on columns the have a file saved to them. Thumbor is a smart imaging service. It enables on-demand crop, resizing and flipping of images. Thumbor documentation and thumber client documentation can found here:
1. https://github.com/thumbor/thumbor/wiki
2. https://github.com/99designs/phumbor/blob/master/lib/Thumbor/Url/CommandSet.php

Services configuration required please see service configuration below for setup.
```php
$post = new BlogPost;
$post->thumbor($columnName)->smartCrop(true)->resize(699, 525); // Will return a url to the cropped or filtered version
```
* When using with img tags add this data attribute: data-no-retina
```html
<?php $post = new BlogPost; ?>
<img src="{{$post->thumbor($columnName)->smartCrop(true)->resize(699, 525)}}" data-no-retina />
```
# Bpez\Infuse\Util

#### Util::thumbor($url)
Same functionality as InfuseModel url method. For other image resources.
```php
Util::thumbor($url);
```
* When using with img tags add this data attribute: data-no-retina
```html
<?php $post = new BlogPost; ?>
<img src="{{Util::thumbor($url)->smartCrop(true)->resize(699, 525)}}" data-no-retina />
```
#### Util::cdn($url)
For delivering assets originating from same origin through CloudFront CDN. Services configuration required please see service configuration below for setup.
```php
Util::cdn($url);
```
# Services
/config/services.php
```php
'thumbor' => [
    // Thumbor security key here
	'security-key' => '843208u4325032u502532582358235',
	// Use cloudfront in front of thumbor for scalability.
	'host' => 'http://d443523423.cloudfront.net',
],

'cloudfront' => [
    // Distribution domain for file uploaded through infuse.
	's3-uploads' => 'http://d4253425.cloudfront.net',
	// Distribution domain for assets originating from same origin.
	'self-hosted' => 'http://d54343425.cloudfront.net',
],
```

# Retina image support
Include the Util::infuse() on your page (put it at the bottom of your template, before your closing </body> tag)

```html
	{!! Util::infuse() !!}
    </body>
</html>
```
S3 CORS needs to be enabled and  config needed for retina support:
```xml
<?xml version="1.0" encoding="UTF-8"?>
<CORSConfiguration xmlns="http://s3.amazonaws.com/doc/2006-03-01/">
    <CORSRule>
        <AllowedOrigin>*</AllowedOrigin>
        <AllowedMethod>GET</AllowedMethod>
         <AllowedMethod>HEAD</AllowedMethod>
        <MaxAgeSeconds>3000</MaxAgeSeconds>
        <AllowedHeader>Authorization</AllowedHeader>
    </CORSRule>
</CORSConfiguration>'self-hosted' => 'http://d54343425.cloudfront.net',
],
```

Created with [http://dillinger.io](http://dillinger.io/)




Do do list

- add delete to infuse delete and then add method that can be used for deleting children
- redo generateThumbnail in model library then remove fron composer.json
- replace pages uploading functionality
