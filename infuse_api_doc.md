# Infuse Configuration API

##### addSelect  
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
###### Parameters
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

##### describeColumn  
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
###### Parameters
- column - (Required) [string] Database table field name
- desc - (Required) [string] Description
- popover (Optional) [boolean] If option present then descript displayed as html pop. 

##### onlyOne  
Only allow one database entry to be display and created.
```php
onlyOne();
```


##### addPermanentFilters
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
###### Parameters
- column - (Required) [string] Database table field name
- operator - (Required) [string] SQL comparison opertator 
- value - (Required) [string | number] Value to be compared to 

##### defaultColumnValues
Add a default value for when an entry is saved.
```php
defaultColumnValues(array(
    "column_name_1" => $value1, 
    "column_name_2" => $value2,
    ...
);
```
###### Parameters
- array - (Required) [array] Array of default values where the index is the column name and the value is the value for the index

##### addMultiSelect
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
###### Parameters
- column - (Required) [string] Database table field name
- array - (Required) [array] Array of values. Formatted:
```php
array(
    array('id' => 1, ", 'name' => 'display name'),
    array('id' => 2, ", 'name' => 'display name'),
    ...
)
```
##### hasMany 
One To Many relationships are defined here. Define children relations like format below.
```php
hasMany(array(
    array("SomeModelName", "Display Name", array("column_name_1", "column_name2"...) $optionsArray),
    ...
);
```
###### Parameters
1. (Required) [string] Child model name 
2. (Required) [string] Display name for entries listed on parent edit page
3. (Required) [array] Child columns to display on entries listed on parent edit page 
4. (Optional) [array] Contains extra functionality for dislaying child entries. Some options are as follows:
```php
$optionsArray = array(
	"order_column" => "display_order", 
	"order_direction" => "ASC", 
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

##### displayOrder
Changes column to an column to be used for ordering entries. Integer is used for the type. Makes field not editable by user and value automatically set.
```php
displayOrder(array("display_order_column"));
```
###### Parameters
1. Database table field names

##### fileUpload
Configure columns for file upload functionality.
```php
fileUpload(
	array(
		array("column" => "column_name"),
	)
)	
```
###### Parameters
- column - (Required) [string] Database table field name




Created by [http://dillinger.io](http://dillinger.io/)