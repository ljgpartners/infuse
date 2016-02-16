<?php namespace Bpez\Infuse;

use Exception;
use Illuminate\Support\Facades\Log;
use Bpez\Infuse\Exceptions\ScaffoldConfigurationException;
use Bpez\Infuse\Exceptions\ScaffoldModelNotRecognizedException;
use Bpez\Infuse\Exceptions\ScaffoldUnknownConfigurationIndexException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Scaffold class contains all the logic for configuring and generating a scaffold on a model.
 *
 * This class does  processes and ORM model by giving you the ability to manage a model. Uses api config methods for
 * customizing the functionality of a scaffold. Also maps config files for api methods.
 *
 */
class Scaffold
{

    use ScaffoldAPI;

    /**
    * Toggles role/permission authentication.
    */
    private $rolePermission = false;

    /**
    * Allows unit testing to work.
    */
    private $testing = false;

    /**
    * Contains the ORM model.
    */
    private $model;

    /**
    * Contains the ORM model primary key.
    */
    private $primaryKey = null;

    /**
    * Contains database table columns from the Model.
    */
    private $columns = null;

    /**
    * Contains flag to determine how to proccess data.
    */
    private $action;

    /**
    * Contains current instance of model that scaffold is working with or
    * a collection of models represented by the laravel Collection Class or
    * can be model(s) representing in pure php array form.
    */
    private $entries = array();

    /**
    * An array containing various configuration information for the templates.
    * (pagination, name, list, edit, onlyOne, columnNames, associations, manyToManyAssociations,
    * hasOneAssociation, onlyOne, filters, description, deleteAction)
    */
    private $header = array();

    /**
    * Contains name of the model in string form.
    */
    private $name;

    /**
    * Array contains new column names to overide ones gathered from the model table.
    */
    private $columnNames = array();

    /**
    * Declares how instances of the model can be displayed on the listing page.
    */
    private $limit = 10;

    /**
    * Contains the display ordering on the listing page.
    */
    private $order = array(
        "order" => "desc",
        "column" => "id"
    );

    /**
    * Contains the has many relationship configuration(s) for the model.
    */
    private $hasMany = array();

    /**
    * Contains the has one relationship configuration(s) for the model.
    */
    private $hasOne = false;

    /**
    * Contains the many to many relationship configuration(s) for the model.
    */
    private $manyToMany = array();

    /**
    * Contains the description for the model.
    */
    private $description = "";

    /**
    * Contains the list of columns to only display on listing page. If empty all shown.
    */
    private $list = array();

    /**
    * Contains the list of columns to be allowed to filter through
    */
    private $filterList = array();

    /**
    * Contains the list query scopes. Index as the display name and value as the function name.
    */
    private $queryScopes = array();

    /**
    * Boolean for setting special rules for processing InfuseUser model.
    */
    private $infuseLogin = false;

    /**
    * Flag for letting scaffold only allowing one instance of the model to be created.
    */
    private $onlyOne = false;

    /**
    * Set a column default value when saved and load only load item that match that value.
    * array("column" => "value", ...)
    */
    private $defaultColumnValues = array();


    /**
    * Associates model to current user when a new instance is created and only load ones that belong to user.
    */
    private $belongsToUser = false;

    /**
    * Associates model to current user when a new instance is created and only load ones that belong to user with the many to many relationship.
    */
    private $belongsToUserManyToMany = false;

    /**
    * On the listing page only loads model instances that are siblings of the user
    * of the foriegn key provided.
    */
    private $onlyLoadSiblingsOfUserRelatedBy = false;

    /**
    * Associates model to the same parent of the user by the foreign key given.
    */
    private $associateToSameParentOfUserRelatedBy = false;

    /**
    * Only load model if sibling of user's parent. Specify parent
    * foreign id and current model  foreign id.
    */
    private $siblingOfUserParentOnly = false;

    /**
    * Disables the action delete action on an instance of the model.
    */
    private $deleteAction = true;

    /**
    * Adds elequent function calls list as an action on the listing page under Edit, Show, Delete
    */
    private $callFunctions = array();

    /**
    * Adds other function calls to the model in the Other Action dropdown on the listing page. static function call to the model.
    */
    private $addOtherActions = array();

    /**
    * Call function before edit page.
    */
    private $beforeEdit;

    /**
    * Attach elequent where method calls to the main filter
    */
    private $permanentFilters = array();

    /**
    * Contains the model configuration to what models to import from.
    */
    private $importFromModel = false;


    /**
    * \Illuminate\View\Environment instance for proccessing blade templates
    */
    protected $view;

    /**
    * \InfuseUser current instance of user logged into infuse
    */
    protected $user = false;

    /**
    * \Illuminate\Support\Facades\DB instance of current request
    */
    protected static $db;

    /**
    * \Illuminate\Http\Request instance of current request
    */
    protected $request;

    /**
    * \Illuminate\Events\Dispatchet instance of current event
    */
    protected $event;

    /**
    *  \Illuminate\Session\Store instance of current session
    */
    protected $session;

    /**
    * Date format for laravel timestamps (updated_at, created_at) displayed infuse
    */
    private $formatLaravelTimestamp;

    /**
    * Type of database (pgsql or mysql)
    */
    private $databaseConnection;

    /**
    * Allow hstore keys to be removed via hstore model schema
    */
    private $hstoreAllowKeyRemoval = false;

    public function __construct(
        \Illuminate\View\Factory $view,
        \InfuseUser $user,
        \Illuminate\Support\Facades\DB $db,
        \Illuminate\Http\Request $request,
        \Event $event, \Illuminate\Session\SessionManager $session
    ) {
        $this->view = $view;
        $this->user = $user;
        $this->request = $request;
        $this->event = $event;
        $this->session = $session;
        self::$db = $db;
        $this->rolePermission = (\Config::get("infuse::config.role_permission"))? true : false;
        $this->formatLaravelTimestamp = \Config::get("infuse::config.format_laravel_timestamp");
        $this->beforeEdit = function() { return true; };
        $this->databaseConnection = \Config::get('database.default');
        $this->hstoreAllowKeyRemoval = \Config::get('infuse::config.hstore_allow_key_removal');
    }

    /**
    * Loads in external model to be processed by the scaffold class.
    */
    public function model($model)
    {
        $this->model = $model;
        if (is_subclass_of($this->model, 'Bpez\Infuse\InfuseModel')
            || is_subclass_of($this->model, 'Toddish\Verify\Models\User')
            || is_subclass_of($this->model, 'Toddish\Verify\Models\Role')
            || is_subclass_of($this->model, 'Toddish\Verify\Models\Permission')
            || (in_array("Bpez\Infuse\ImportFromServiceModelInterface", class_implements($this->model)) && is_subclass_of($this->model, 'Bpez\Infuse\ImportFromServiceModel'))
        ) {
            $this->name = get_class($this->model);
            $this->boot();
            return $this;
        }

        throw new ScaffoldModelNotRecognizedException(
            get_parent_class($this->model)." is the wrong model to inherit from. Extend from InfuseEloquent or be an interface of ImportFromServiceModelInterface & extend from ImportFromServiceModel."
        );
    }


    /**
    * Boot sets up base configuration for model right after model is loaded into the instance.
    */
    protected function boot()
    {
        $db = self::$db;

        $this->checkIfOverUploadLimit();
        $this->action = Util::get("action");

        $model = $this->model;
        if ($model::INTERFACE_MODEL === false) {

            $table = $this->model->getTable();

            $columns = $db::select("select column_name as field, data_type as type, character_maximum_length from INFORMATION_SCHEMA.COLUMNS where table_name = '".$table."'");

            $atLeastOneExist = ($db::table($table)->count() > 0)? true : false;

            $this->order['column'] = $this->model->getKeyName();

            $this->primaryKey = $this->model->getKeyName();

        } else {

            $columns = $this->model->getColumns();

        }


        if ($this->databaseConnection == "pgsql" && count($this->model->hstore) > 0) {
            $hstoreColumns = $this->model->hstore;
            $hstoreEmulationColumnTypes = array("string", "text");
        }

        foreach ($columns as $column) {

            if (isset($hstoreColumns) && isset($hstoreColumns[$column->field])) {

                $hstoreColumn = $hstoreColumns[$column->field];
                $this->hstoreColumnStructureCheck(
                    $column->field,
                    $hstoreColumns[$column->field],
                    $atLeastOneExist,
                    $table
                );

                foreach ($hstoreColumn['types'] as $hstoreKey => $columnType) {
                    if (in_array($columnType, $hstoreEmulationColumnTypes)) {
                        $hstore = $column->field;
                        $this->processColumn($hstoreKey, $columnType, $hstore);
                    }
                }

            } else {

                $this->processColumn($column->field, $column->type);

            }
        }
    }

    private function hstoreColumnStructureCheck($column, $hstoreColumn, $atLeastOneExist, $table)
    {
        if (!$atLeastOneExist) {
            return;
        }

        $db = self::$db;
        $model = $this->model;

        // Check if any keys need to be added
        $actualKeys = $model::select(\DB::raw("akeys({$column})"))->first();

        $keys = $actualKeys->akeys;

        if ($keys[0] === '{') {
            $keys = str_replace('{', '', $keys);
        }

        if (substr($keys, -1) === '}') {
            $keys = str_replace('}', '', $keys);
        }

        $currentKeySet = explode(",", $keys);
        $masterKeySet = array_keys($hstoreColumn['columns']);

        // Check what keys current are missing
        $addKeys = array_diff($masterKeySet, $currentKeySet);

        foreach ($addKeys as $key) {
            $db::update("update {$table} set {$column} = {$column} || hstore('{$key}', '{$hstoreColumn['columns'][$key]}')");
        }

        if ($this->hstoreAllowKeyRemoval) {
            // check if any keys need to be removed
            $removeKeys = array_diff($currentKeySet, $masterKeySet);

            foreach ($removeKeys as $key) {
                $db::update("update {$table} set {$column} = delete({$column}, '{$key}')");
            }
        }


    }


    private function processColumn($columnName, $columnType, $hstore = false)
    {
        if ($columnName != $this->primaryKey) {

            if (strlen(strstr($columnType, "varchar")) > 0 || strlen(strstr($columnType, "character varying")) > 0) {
                $type = "varchar";
            } else if (strlen(strstr($columnType, "tinyint")) > 0 || strlen(strstr($columnType, "boolean")) > 0) {
                $type = "tinyint";
            } else if (strlen(strstr($columnType, "int")) > 0) {
                $type = "int";
            } else if (strlen(strstr($columnType, "timestamp")) > 0) {
                $type = "timestamp";
            } else if (strlen(strstr($columnType, "json")) > 0) {
                $type = "text";
            } else if (strlen(strstr($columnType, "float")) > 0 || strlen(strstr($columnType, "double precision")) > 0) {
                $type = "float";
            } else {
                $type = $columnType;
            }

            array_push($this->list, $columnName);

            $this->columns["{$columnName}"] = array(
                "field" => $columnName,
                "type"  => $type,
                "type_original" => $columnType,
                'hstore_column' => $hstore
            );

        }
    }

    /**
    * Checks if post request overflowed limit and so exits class and redirects. (post_max_size and/or upload_max_filesize)
    */
    protected function checkIfOverUploadLimit()
    {
        if (empty($_FILES) && empty($_POST) &&
            isset($_SERVER['REQUEST_METHOD']) &&
            strtolower($_SERVER['REQUEST_METHOD']) == 'post'
        ) {
            $postMax = ini_get('post_max_size');

            Util::flash(array(
                "message" => "Maximum upload size exceeded on server. Please note files or total combined size of files larger than {$postMax} will result in this error!",
                "type" => "error"
            ));

            header("Location: http://".$_SERVER['HTTP_HOST']."/admin/dashboard");
            exit();
        }
    }

    /**
    * Set testing to true so that redirects stop within class so that unit testing can take place.
    */
    public function testing()
    {
        $this->testing = true;
        return $this;
    }


    public function route()
    {
        switch ($this->action) {
            case 'l':
                $this->listAll();
                break;
            case 'e':
                $this->edit();
                break;
            case 's':
                $this->show();
                break;
            case 'd':
                $this->delete();
                break;
            case 'c':
                $this->create();
                break;
            case 'cd':
                $this->createDuplicate();
                break;
            case 'u':
            case 'cu':
                $this->update();
                break;
            case 'f':
                $this->listAllFilter();
                break;
            case 'rrpp':
                $this->sendRequestResetPasswordPage();
                break;
            case 'icsv':
                $this->importCSV();
                break;
            case 'icsvc':
                $this->importCSVCustom();
                break;
            case 'ecsvc':
                $this->exportCSVCustom();
                break;
            case 'cf':
                $this->callActionFunction();
                break;
            case 'oa':
                $this->callOtherAction();
                break;

            default:
                $this->listAll();
                break;
        }
    }


    public function checkPermissions($redirectBack)
    {
        // Check if role/permission authentication is on otherwise return false and check no permissions.
        if (!$this->rolePermission) {
            return false;
        }


        switch ($this->action) {
            case 'l':
            case 's':
            case 'f':
            case 'ecsvc':
                $action = "view";
                $redirect = "/admin/dashboard";
                break;
            case 'e':
            case 'u':
            case 'cf':
            case 'oa':
                $action = "update";
                $redirect = $redirectBack;
            break;
            case 'd':
                $action = "delete";
                $redirect = $redirectBack;
            break;
            case 'c':
            case 'cd':
            case 'cu':
            case 'icsv':
            case 'icsvc':
                $action = "create";
                $redirect = $redirectBack;
            break;

            default:
                $action = "view";
                $redirect = "/admin/dashboard";
            break;
        }

        $resource = Util::camel2under(get_class($this->model));

        if ($this->user->can($resource."_".$action)) {

            // User has permission let user continue action
            return false;

        } else {

            if ($this->user->is('Super Admin')) {
                return false;
            }

            $name = Util::cleanName($this->name);
            Util::flash(array(
                "message" => "{$this->user->username} is not authorized to {$action} {$name}.",
                "type" => "warning"
            ));
            // User does not have permission don't let user continue action
            return $redirect;

        }
    }

    private function filterQueryForListings($count = false, &$pagination)
    {
        $model = $this->model;
        $modelInstance = null;

        $order = Util::get("order");

        $offset = 0;
        $page = Util::get("pg");

        if ($page && $page != 1 && $page != 'a') {
            $offset =  ($page-1) * $pagination['limit'];
            $pagination['active_page'] = $page;
        }

        if ($this->siblingOfUserParentOnly) {
            $foreign_id = $this->user
                ->belongsTo($this->siblingOfUserParentOnly['parent_model'], Util::createForeignKeyString($this->siblingOfUserParentOnly['parent_model']))
                ->firstOrFail()
                ->{$this->siblingOfUserParentOnly['parent_foriegn_id']};
        }

        if ($this->belongsToUserManyToMany && !$this->user->is('Super Admin')) {

            $modelInstance = $this->user->belongsToMany(
                $this->belongsToUserManyToMany[0],
                $this->belongsToUserManyToMany[1],
                $this->belongsToUserManyToMany[2],
                $this->belongsToUserManyToMany[3]
            );

        } else {

            $modelInstance = new $model;

        }

        if (!$count) {
            $modelNameString = get_class($model);

            if ($order) {
                if ($this->session->has("infuse_order_column") &&
                    $this->session->has("infuse_order") &&
                    $this->session->has("infuse_order_model") &&
                    $this->session->get("infuse_order_column") == $order &&
                    $this->session->get("infuse_order_model") == $modelNameString
                ) {

                    $direction = ($this->session->get("infuse_order") == "asc")? "desc" : "asc";
                    $this->session->put("infuse_order", $direction);

                } else {

                    $this->session->put("infuse_order_model", $modelNameString);
                    $this->session->put("infuse_order", "asc");
                    $this->session->put("infuse_order_column", $order);

                }
            }

            $orderSessionCheck = ($this->session->has("infuse_order_column") &&
                $this->session->has("infuse_order") &&
                $this->session->has("infuse_order_model") &&
                $this->session->get("infuse_order_model") == $modelNameString
            );
            $orderByColumn = ($orderSessionCheck)? $this->session->get("infuse_order_column") : $this->order["column"];
            $orderByDirection = ($orderSessionCheck)? $this->session->get("infuse_order") : $this->order["order"];

            if ($page == "a") {
                $modelInstance = $modelInstance->orderBy($orderByColumn, $orderByDirection);
            } else {
                $modelInstance = $modelInstance->orderBy($orderByColumn, $orderByDirection)
                    ->take($pagination['limit'])
                    ->skip($offset);
            }
        }

        if ($this->infuseLogin && !$this->user->is('Super Admin')) {
            $modelInstance = $modelInstance->where("id", "!=", 1)->where("username", "!=", 'super');
        }

        if ($this->belongsToUser && !$this->user->is('Super Admin')) {
            $modelInstance = $modelInstance->where("infuse_user_id", "=", $this->user->id);
        }

        if ($this->onlyLoadSiblingsOfUserRelatedBy) {
             $modelInstance = $modelInstance->where($this->onlyLoadSiblingsOfUserRelatedBy, "=", $this->user->{$this->onlyLoadSiblingsOfUserRelatedBy});
        }


        if ($this->siblingOfUserParentOnly) {
            $modelInstance = $modelInstance->where($this->siblingOfUserParentOnly['foreign_id'], "=", $foreign_id);
        }


        if ($this->rolePermission && $model instanceof \InfuseUser && !$this->user->is('Super Admin')) {
            $user = $this->user;

            if ($user->can("infuse_user_load_level_comparison_or_equal_zero")) {
                $db = self::$db;
                $originalIds = $db::table('users')->lists("id");
                $newIds = $db::table('role_user')->distinct("user_id")->lists("user_id");
                $usersWithNoRole = array_diff($originalIds, $newIds);

                if (!empty($usersWithNoRole)) {
                    $modelInstance = $modelInstance->orWhereIn('id', $usersWithNoRole);
                }
            }

            $role = $this->user
                ->roles()
                ->orderBy("level", "asc")
                ->limit(1)
                ->first();

            $level = (count($role) == 1)? $role->level : 0;

            $modelInstance = $modelInstance->with('roles')->whereHas('roles', function($q) use ($level, $user) {
                if ($user->can("infuse_user_load_level_comparison_greater_or_equal")) {
                    $q->where('level', '>=', $level);
                } else {
                    $q->where('level', '>', $level);
                }
            });
        }

        foreach ($this->permanentFilters as $where) {

            if ($where['operator'] == "IN") {
                $modelInstance = $modelInstance->whereIn($where['column'], $where['value']);
            } else {
                $modelInstance = $modelInstance->where($where['column'], $where['operator'], $where['value']);
            }

        }

        foreach ($this->defaultColumnValues as $index => $value) {
            $modelInstance = $modelInstance->where($index, "=", $value);
        }

        $scope = Util::get("scope");

        foreach ($this->queryScopes as $key => $functionName) {
            if ($scope == $functionName) {
                $modelInstance = $modelInstance->{$functionName}();
            }
        }

        return $modelInstance;
    }


    private function listAll()
    {
        $pagination = array(
            "limit" => $this->limit,
            "active_page" => 1,
            "count" => 0
        );


        $pagination['count'] = $this->filterQueryForListings(true, $pagination)->count();

        $prepareModel = $this->filterQueryForListings(false, $pagination);

        if (Util::get("toCSV")) {
            $this->toCSV($prepareModel);
        }

        $this->entries = $prepareModel->get();

        $this->header = array(
            "pagination" => $pagination,
            "name" => $this->name,
            "list" => $this->list,
            "filterList" => $this->filterList,
            "queryScopes" => $this->queryScopes,
            "onlyOne" => $this->onlyOne,
            "addOtherActions" => $this->addOtherActions,
            "columnNames" => $this->columnNames
        );
    }

    private function listAllFilter()
    {
        $pagination = array(
            "limit" => $this->limit,
            "active_page" => 1,
            "count" => 0
        );


        $filterCount = Util::get("filter_count");
        $filters = array();

        for ($x=1; $x <= $filterCount; $x++) {
            $filter = (is_array(Util::get("filter_".$x))) ? Util::get("filter_".$x) : json_decode(Util::get("filter_".$x));

            if (count($filter) == 3) {

                if (!isset($filter[0]) && !in_array($filter[0], $columnNames)) {
                    continue;
                }

                if (!isset($filter[1]) && !array_key_exists($filter[1], $comparisons)) {
                    continue;
                }

                if (!isset($filter[2])) {
                    continue;
                }

                array_push($filters, $filter);
            }
        }

        $prepareModel = $this->filterQueryForListings(false, $pagination);

        $columnNames = array_keys($this->columns);

        $comparisons = array(
            "equals" => "=",
            "less than" => "<",
            "greater than" => ">",
            "not equal to" => "!=",
            "contains" => "like"
        );

        if ($this->databaseConnection == "pgsql") {
            $comparisons["contains"] = "ILIKE";
        }

        foreach ($filters as $filter) {

            if (isset($comparisons[$filter[1]]) &&
                $comparisons[$filter[1]] == "like" &&
                in_array($filter[0], $columnNames)
            ) {
                $prepareModel = $prepareModel->whereRaw("{$filter[0]} LIKE ?", array("%".$filter[2]."%"));
            } else if (isset($comparisons[$filter[1]])) {
                $prepareModel = $prepareModel->where($filter[0], $comparisons[$filter[1]], $filter[2]);
            }

        }


        if (Util::get("toCSV")) {
            $this->toCSV($prepareModel);
        }

        $this->entries = $prepareModel->get();

        $prepareModelForCount = $this->filterQueryForListings(true, $pagination);

        foreach($filters as $filter) {

            if (isset($comparisons[$filter[1]]) &&
                $comparisons[$filter[1]] == "like" &&
                in_array($filter[0], $columnNames)
            ) {
                $prepareModelForCount = $prepareModelForCount->whereRaw("{$filter[0]} LIKE ?", array("%".$filter[2]."%"));
            } else if (isset($comparisons[$filter[1]])) {
                $prepareModelForCount = $prepareModelForCount->where($filter[0], $comparisons[$filter[1]], $filter[2]);
            }

        }

        $pagination['count'] = $prepareModelForCount->count();

        $this->header = array(
            "pagination" => $pagination,
            "name" => $this->name,
            "list" => $this->list,
            "filterList" => $this->filterList,
            "queryScopes" => $this->queryScopes,
            "filters" => $filters,
            "onlyOne" => $this->onlyOne,
            "addOtherActions" => $this->addOtherActions,
            "columnNames" => $this->columnNames
        );
    }



    public function search($search = null, $columns)
    {
        $pagination = array(
            "limit" => $this->limit,
            "active_page" => 1,
            "count" => 0
        );


        $prepareModel = $this->filterQueryForListings(false, $pagination);

        foreach ($columns as $column) {
            if ($columns[0] == $column) {
                $prepareModel =  $prepareModel->where($column, "LIKE", "%".$search."%");
            }  else {
                $prepareModel = $prepareModel->orWhere($column, "LIKE", "%".$search."%");
            }
        }


        if (Util::get("toCSV")) {
            $this->toCSV($prepareModel);
        }

        $this->entries = $prepareModel->get();

        $prepareModelForCount = $this->filterQueryForListings(true, $pagination);

        foreach ($columns as $column) {
            if ($columns[0] == $column) {
                $prepareModelForCount = $prepareModelForCount->where($column, "LIKE", "%".$search."%");
            } else {
                $prepareModelForCount = $prepareModelForCount->orWhere($column, "LIKE", "%".$search."%");
            }
        }

        $pagination['count'] = $prepareModelForCount->count();

        $this->header = array(
            "pagination" => $pagination,
            "name" => $this->name,
            "list" => $this->list,
            "filterList" => $this->filterList,
            "queryScopes" => $this->queryScopes,
            "onlyOne" => $this->onlyOne,
            "addOtherActions" => $this->addOtherActions,
            "columnNames" => $this->columnNames
        );
    }


    public function closestLocationsWithinRadius($search, $columns, $latitude, $longitude, $distance)
    {
        $pagination = array(
            "limit" => $this->limit,
            "active_page" => 1,
            "count" => 0
        );

        $page = Util::get("pg");
        if ($page && $page != 1 && $page != 'a') {
            $pagination['active_page'] = $page;
        }


        $prepareModel = $this->filterQueryForListings(false, $pagination);

        if (!empty($search)) {
            foreach ($columns as $column) {
                if ($columns[0] == $column) {
                    $prepareModel = $prepareModel->where($column, "LIKE", "%".$search."%");
                } else {
                    $prepareModel = $prepareModel->orWhere($column, "LIKE", "%".$search."%");
                }
            }
        }


        /*
            Implemented Haversine formula
            ------------------------------
            Will find the closest locations that are within a radius of X miles to the latitude, longitude coordinate.
            reference: https://developers.google.com/maps/articles/phpsqlsearch_v3?csw=1
        */

        $db = self::$db;
        $prepareModel = $prepareModel->select($db::raw("*, ( 3959 * acos( cos( radians({$latitude}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$longitude}) ) + sin( radians({$latitude}) ) * sin( radians( latitude ) ) ) ) AS distance "))
            ->having('distance', '<', $distance);


        if (Util::get("toCSV")) {
            $this->toCSV($prepareModel);
        }



        $this->entries = $prepareModel->get();

        $prepareModelForCount = $this->filterQueryForListings(true, $pagination);

        if (!empty($search)) {
            foreach ($columns as $column) {
                if ($columns[0] == $column) {
                    $prepareModelForCount = $prepareModelForCount->where($column, "LIKE", "%".$search."%");
                } else {
                     $prepareModelForCount = $prepareModelForCount->orWhere($column, "LIKE", "%".$search."%");
                }
            }
        }

        $prepareModelForCount = $prepareModelForCount->select($db::raw("( 3959 * acos( cos( radians({$latitude}) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians({$longitude}) ) + sin( radians({$latitude}) ) * sin( radians( latitude ) ) ) ) AS distance "))
            ->having('distance', '<', $distance);


        $pagination['count'] = count($prepareModelForCount->get()->toArray());

        $this->header = array(
            "pagination" => $pagination,
            "name" => $this->name,
            "list" => $this->list,
            "filterList" => $this->filterList,
            "queryScopes" => $this->queryScopes,
            "onlyOne" => $this->onlyOne,
            "addOtherActions" => $this->addOtherActions,
            "columnNames" => $this->columnNames
        );
    }


    private function show()
    {
        $model = $this->model;
        $this->header = array(
            "pagination" => array(),
            "name" => $this->name,
            "columnNames" => $this->columnNames,
            "onlyOne" => $this->onlyOne
        );

        if ($this->belongsToUser) {

            try {

                $this->entries = $model->where("infuse_user_id", "=", $this->user->id)
                    ->where("id", "=", Util::get("id"))
                    ->firstOrFail();

            } catch (ModelNotFoundException $e) {

                if (Util::get("stack")) {
                    $redirect_path = Util::childBackLink();
                } else {
                    $redirect_path = Util::redirectUrl();
                }

                Util::flash(array(
                    "message" => "Can not see this entry.",
                    "type" => "error"
                ));

                if (!$this->testing) {
                    header("Location: {$redirect_path}");
                    exit();
                }
            }


        } else if ($this->belongsToUserManyToMany) {

            try {

                $this->entries = $this->user
                    ->belongsToMany($this->belongsToUserManyToMany[0], $this->belongsToUserManyToMany[1], $this->belongsToUserManyToMany[2], $this->belongsToUserManyToMany[3])
                    ->findOrFail(Util::get("id"));

            } catch (ModelNotFoundException $e) {

                if (Util::get("stack")) {
                    $redirect_path = Util::childBackLink();
                } else {
                    $redirect_path = Util::redirectUrl();
                }

                Util::flash(array(
                    "message" => "Can not see this entry.",
                    "type" => "error"
                ));

                if (!$this->testing) {
                    header("Location: {$redirect_path}");
                    exit();
                }
            }

        } else {

            $this->entries = $model::find(Util::get("id"));

        }
    }

    private function edit()
    {
        if ($this->infuseLogin && Util::get("id") == 1 && !$this->user->is('Super Admin')) {
            if (Util::get("stack")) {
                $redirect_path = Util::childBackLink();
            } else {
                $redirect_path = Util::redirectUrl();
            }

            Util::flash(array(
                "message" => "Can not edit this entry.",
                "type" => "error"
            ));

            if (!$this->testing) {
                header("Location: {$redirect_path}");
                exit();
            }
        }


        $model = $this->model;
        $this->header = array(
            "edit" => true,
            "name" => $this->name,
            "associations" => $this->hasMany,
            "manyToManyAssociations" => $this->manyToMany,
            "hasOneAssociation" => $this->hasOne,
            "onlyOne" => $this->onlyOne,
            "columnNames" => $this->columnNames,
            "importFromModel" => $this->importFromModel
        );

        $beforeEdit = $this->beforeEdit;

        $post = Util::flashArray("post");

        if (!$post) {

            if ($this->belongsToUser) {

                try {

                $this->entries = $model->where("infuse_user_id", "=", $this->user->id)
                ->where("id", "=", Util::get("id"))
                ->firstOrFail();

                } catch (ModelNotFoundException $e) {

                    if (Util::get("stack")) {
                        $redirect_path = Util::childBackLink();
                    } else {
                        $redirect_path = Util::redirectUrl();
                    }

                    Util::flash(array(
                        "message" => "Can not edit this entry.",
                        "type" => "error"
                    ));

                    if (!$this->testing) {
                        header("Location: {$redirect_path}");
                        exit();
                    }
                }


            } else if ($this->belongsToUserManyToMany) {

                try {

                    $this->user
                    ->belongsToMany(
                        $this->belongsToUserManyToMany[0],
                        $this->belongsToUserManyToMany[1],
                        $this->belongsToUserManyToMany[2],
                        $this->belongsToUserManyToMany[3]
                    )
                    ->findOrFail(Util::get("id"));
                    // laravel many to many bug. Call by it self to get updated_at and created_at times stamps returned
                    $this->entries = $model::find(Util::get("id"));

                } catch (ModelNotFoundException $e) {

                    if (Util::get("stack")) {
                        $redirect_path = Util::childBackLink();
                    } else {
                        $redirect_path = Util::redirectUrl();
                    }

                    Util::flash(array(
                        "message" => "Can not edit this entry.",
                        "type" => "error"
                    ));

                    if (!$this->testing) {
                        header("Location: {$redirect_path}");
                        exit();
                    }
                }

            } else {

                $this->entries = $model::find(Util::get("id"));

            }

        } else {

            $this->entries = $model::find($post["id"]);
            foreach ($this->columns as $column) {
                if (isset($post[$column['field']])) {
                    $this->entries->{$column['field']} = $post[$column['field']];
                }
            }

        }

        // Check if beforeEdit function returns false then exit and redirect
        $exitCheck = $beforeEdit($this->entries);
        if (!$exitCheck) {
            if (Util::get("stack")) {
                $redirect_path = Util::childBackLink();
            } else {
                $redirect_path = Util::redirectUrl();
            }

            Util::flash(array(
                "message" => "Can not edit this entry.",
                "type" => "error"
            ));

            if (!$this->testing) {
                header("Location: {$redirect_path}");
                exit();
            }
        }
    }

    private function create()
    {
        $model = $this->model;
        $this->header = array(
            "name" => $this->name,
            "associations" => $this->hasMany,
            "manyToManyAssociations" => $this->manyToMany,
            "hasOneAssociation" => $this->hasOne,
            "columnNames" => $this->columnNames,
            "importFromModel" => $this->importFromModel
        );
        $post = Util::flashArray("post");

        if (!$post) {

            $this->entries = $model;

        } else {

            $this->entries = new $model;
            foreach ($this->columns as $column) {
                if (isset($post[$column['field']])) {
                    $this->entries->{$column['field']} = $post[$column['field']];
                }
            }

        }
    }

    private function createDuplicate()
    {
        $model = $this->model;
        $this->header = array(
            "name" => $this->name,
            "associations" => $this->hasMany,
            "manyToManyAssociations" => $this->manyToMany,
            "columnNames" => $this->columnNames
        );
        $post = Util::flashArray("post");

        if (!$post) {
            $this->entries = $model::find(Util::get("id"));
        } else {
            $this->entries = Util::arrayToObject($post);
        }
    }

    private function delete()
    {
        $model = $this->model;

        if ($this->belongsToUser) {

            try {

                $entry = $model->where("infuse_user_id", "=", $this->user->id)
                    ->where("id", "=", Util::get("id"))
                    ->firstOrFail();

            } catch (ModelNotFoundException $e) {

                if (Util::get("stack")) {
                    $redirect_path = Util::childBackLink();
                } else {
                    $redirect_path = Util::redirectUrl();
                }

                Util::flash(array(
                    "message" => "Can not delete this entry.",
                    "type" => "error"
                ));

                if (!$this->testing) {
                    header("Location: {$redirect_path}");
                    exit();
                }

            }


        } else {

            $entry = $model::find(Util::get("id"));

        }



        if ($this->infuseLogin && $entry->id == 1 && $entry->username == 'super' ) {

            Util::flash(array(
                "message" => "Can't delete super.",
                "type" => "error"
            ));

        } else {

            $entryBackUp = clone $entry;

            if ($entry->delete()) {

                $fileUploadManage = new FileUpload($this->request);

                foreach ($this->columns as $column) {
                    if (array_key_exists("upload", $column)) {
                        $fileUploadManage->delete($column['field'], $entryBackUp, $column);
                    }
                }

                Util::flash(array(
                    "message" => "Deleted {$this->name}.",
                    "type" => "success"
                ));

            } else {

                Util::flash(array(
                    "message" => "Failed to delete {$this->name}.",
                    "type" => "error"
                ));

            }

        }


        if (Util::get("stack")) {
            $redirect_path = Util::childBackLink();
        } else {
            $redirect_path = Util::redirectUrl();
        }

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }

    private function importCSV()
    {
        $model = $this->model;
        $entry = $model::find(Util::get("parent_id"));
        $redirect_path = Util::get("back");
        $error = false;

        if (\Input::hasFile('csv_file')) {
            $rule = array('csv_file' => 'mimes:csv');
            $validator = \Validator::make(array('csv_file' => \Input::get('csv_file')), $rule);

            if ($validator->fails()) {

                $error = true;
                $message = "File uploaded not valid.";

            } else {

                $file            = \Input::file('csv_file');
                $destinationPath = $_SERVER['DOCUMENT_ROOT'].'/uploads/tmp/';
                $filename        = time().'_'.$file->getClientOriginalName();
                $uploadSuccess   = $file->move($destinationPath, $filename);

                if (Util::get("child")) {
                    $child = Util::get("child");
                    $childInstance = new $child;
                    $db = self::$db;
                    $columns = $db::select("SHOW COLUMNS FROM ".$childInstance->getTable());
                    unset($db);

                    $columns = array_map(function($n) {
                        return $n->Field;
                    }, $columns);

                    $header = Util::importCSV($destinationPath.$filename, true);



                    foreach ($header as $h) {
                        if (!in_array($h, $columns)) {
                            $error = true;
                            $message = "Fields not correct in csv file.";
                        }
                    }

                    unset($columns);

                    if (!$error) {
                        $data = Util::importCSV($destinationPath.$filename);
                        $foreignKey = Util::getForeignKeyString($entry);
                        foreach ($data as $row) {
                            $childInstance = new $child;
                            foreach($row as $key => $value) {
                                $childInstance->{$key} = $value;
                            }
                            $childInstance->{$foreignKey} = $entry->id;
                            $entry->hasMany($child)->save($childInstance);
                        }

                        $message = "Succesfully imported csv data.";
                    }

                    unlink($destinationPath.$filename);
                }
            }

        } else {

            $error = true;
            $message = "No file uploaded.";

        }

        Util::flash(array(
            "message" => $message,
            "type" => ($error)? "error" : "success"
        ));


        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }

    private function importCSVCustom()
    {
        $model = $this->model;
        $entry = $model::find(Util::get("parent_id"));
        $redirect_path = Util::get("back");
        $function = Util::get("function");

        $entry->{$function}();

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }

    private function exportCSVCustom()
    {
        $model = $this->model;
        $entry = $model::find(Util::get("parent_id"));
        $redirect_path = Util::get("back");
        $function = Util::get("function");

        $entry->{$function}();
        exit();

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }


    private function sendRequestResetPasswordPage()
    {
        $model = $this->model;
        $user = $model::find(Util::get("id"));

        $user->sendRequestResetPasswordPage();

        Util::flash(array(
            "message" => "Sent email with link to reset password.",
            "type" => "success"
        ));

        $redirect_path = Util::redirectUrl();

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }


    private function update()
    {
        $model = $this->model;
        $createFlag = false;

        if (Util::get("id")) {

            $entry = $model::find(Util::get("id"));

            $message = array("message" => "Updated {$this->name}.", "type" => "success");

            if ($this->infuseLogin && $entry->id == 1 && !$this->user->is('Super Admin')) {
                if (Util::get("stack")) {
                    $redirect_path = Util::childBackLink();
                } else {
                    $redirect_path = Util::redirectUrl();
                }

                Util::flash(array(
                    "message" => "Can not update this entry.",
                    "type" => "error"
                ));

                if (!$this->testing) {
                    header("Location: {$redirect_path}");
                    exit();
                }
            }

        } elseif (Util::get("stack")) {

            $entry = $model;
            $message = array("message" => "Added {$this->name}.", "type" => "success");
            $createFlag = true;

        } else {

            $entry = $model;
            $message = array("message" => "Created {$this->name}.", "type" => "success");
            $createFlag = true;

        }

        $fileUploadManage = new FileUpload($this->request);

        foreach ($this->columns as $column) {

            if (array_key_exists("upload", $column)) {

                if (!$fileUploadManage->add($column, $entry) && $createFlag) {
                    Util::setColumnValue($entry, $column, "");
                }

                if (Util::get($column['field']."_delete")) {

                    $fileUploadManage->deleteBasicUpload($entry->uploadPath($column['field']), $entry->{$column['field']});
                    Util::setColumnValue($entry, $column, "");
                }

            } else {

                if ($column['field'] != "created_at" &&
                    $column['field'] != "updated_at" &&
                    Util::checkInfuseLoginFields($this->infuseLogin, $column)
                ) {

                    $inputsTemp = Util::get($column['field']);

                    if ($column['type_original'] == "json" || $column['type_original'] == "jsonb") {
                        $inputsTemp = (empty($inputsTemp))? "{}" : $inputsTemp;
                    }

                    if ($this->belongsToUser && $column['field'] == "infuse_user_id" && !$this->user->is('Super Admin')) {
                        $inputsTemp = $this->user->id;
                    }

                    if (array_key_exists($column['field'], $this->defaultColumnValues)) {
                        $inputsTemp = $this->defaultColumnValues["{$column['field']}"];
                    }

                    if (empty($inputsTemp) && $column['type'] == "int") {
                        $inputsTemp = 0;
                    }

                    Util::setColumnValue($entry, $column, $inputsTemp);

                }
            }
        }

        $data = Util::getAll();

        // Remove any FALSE values. This includes NULL values, EMPTY arrays, etc.
        $data = array_filter($data);


        if ($this->databaseConnection == "mysql") {
            //$data = array_filter($data);
        } elseif ($this->databaseConnection == "pgsql" ) {
            //$data = array_filter($data);
        }


        $fileErrors = $fileUploadManage->fileErrors();

        if ($entry->validate($data) && empty($fileErrors)) {

            $fileUploadManage->processUploads();

            // Check if brand new user
            if ($this->infuseLogin && !Util::get("id")) {
                $entry->verified = 1;
                $entry->deleted_at = null;
            }

            if (!Util::get("id")) {
                if ($this->associateToSameParentOfUserRelatedBy){
                    $entry->{$this->associateToSameParentOfUserRelatedBy} = $this->user->{$this->associateToSameParentOfUserRelatedBy};
                }
            }


            // Do many to many relationship saving
            if (Util::get("id") && count($this->manyToMany) > 0) {
                foreach ($this->manyToMany as $association) {
                    $firstModel = $association[0];
                    $secondModel = $association[2];
                    $manyToManyTable = $association[4];
                    $model = get_class($entry);

                    if ($model == $firstModel) {
                        $belongsToModel = $secondModel;
                        $firstForeignId = $association[1];
                        $secondForeignId = $association[3];
                    } else if ($model == $secondModel) {
                        $belongsToModel = $firstModel;
                        $firstForeignId = $association[3];
                        $secondForeignId = $association[1];
                    }

                    $event = $this->event;
                    $newIds = Util::get($manyToManyTable);

                    if ($newIds) {

                        $originalIds = $entry->belongsToMany(
                            $belongsToModel,
                            $manyToManyTable,
                            $firstForeignId,
                            $secondForeignId
                        )->lists('id');

                        $added = array_diff($newIds, $originalIds);
                        $removed = array_diff($originalIds, $newIds);

                        foreach($added as $id) {
                            $entry->belongsToMany(
                                $belongsToModel,
                                $manyToManyTable,
                                $firstForeignId,
                                $secondForeignId
                            )->attach($id);

                            $event::fire('infuse.attach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, $id));
                        }

                        foreach($removed as $id) {
                            $event::fire('infuse.detach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, $id));
                            $entry->belongsToMany(
                                $belongsToModel,
                                $manyToManyTable,
                                $firstForeignId,
                                $secondForeignId
                            )->detach($id);
                        }


                        if ($this->infuseLogin && $entry->id == 1) {
                            $entry->belongsToMany(
                                $belongsToModel,
                                $manyToManyTable,
                                $firstForeignId,
                                $secondForeignId
                            )->attach(1);
                        }


                    } else {

                        if ($this->infuseLogin && $entry->id == 1 && $this->user->is('Super Admin')) {
                            // do nothing
                        } else {
                            $event::fire('infuse.detach.'.Util::camel2under($model).'.'.Util::camel2under($belongsToModel), array($entry, 0));
                            $entry->belongsToMany($belongsToModel, $manyToManyTable, $firstForeignId, $secondForeignId)->detach();
                        }

                    }
                }
            }



            $entry->save();
            Util::flash($message);

            foreach ($this->columns as $column) {
                if (isset($column['display_order']) && empty($entry->{$column['field']})) {
                    $entry->{$column['field']} =  $entry->id;
                    $entry->save();
                }
            }

            if (!Util::get("id") && $this->belongsToUserManyToMany) {
                $this->user->belongsToMany(
                        $this->belongsToUserManyToMany[0],
                        $this->belongsToUserManyToMany[1],
                        $this->belongsToUserManyToMany[2],
                        $this->belongsToUserManyToMany[3]
                    )->attach($entry->id);
            }



            if (Util::get("oneToOne")) {
                $entry->belongsTo(ucfirst(Util::get("oneToOne")))->get()->{Util::getForeignKeyString($entry)} = $entry->id;
            }

            $afterSavePage = Util::get("typeSubmit");


            if (Util::get("stack")) {

                switch (Util::get("typeSubmit")) {
                    case 'save':
                        $redirect_path = Util::childBackLink();
                        break;
                    case 'save_and_return':
                        $redirect_path = Util::childActionLink(Util::get("stack"),"e", $entry->id);
                        break;
                    case 'save_and_create_another':
                        $redirect_path = Util::childActionLink(Util::get("stack"), "c");
                        break;

                    default:
                        $redirect_path = Util::childBackLink();
                        break;
                }

            } else {

                switch (Util::get("typeSubmit")) {
                    case 'save':
                        $redirect_path = Util::redirectUrl();
                        break;
                    case 'save_and_return':
                        $redirect_path = Util::redirectUrl("e", $entry->id);
                        break;
                    case 'save_and_create_another':
                        $redirect_path = Util::redirectUrl("c");
                        break;

                    default:
                        $redirect_path = Util::redirectUrl();
                        break;
                }

            }

        } else {

            Util::flash(array(
                "message" => "Failed to save {$this->name}.",
                "type" => "error"
            ));
            Util::flashArray("errors", $entry->errors());

            $fileUploadManage->resetUploads();

            Util::flashArray("file_errors", $fileUploadManage->fileErrors());
            Util::flashArray("post", Util::getAll());

            if (Util::get("stack")) {

                if (!Util::get("id")) {
                    $redirect_path = Util::redirectUrlChildSaveFailed();
                } else {
                    $redirect_path = Util::redirectUrlChildSaveFailed($entry->id);
                }

            } else {

                $redirect_path = Util::redirectUrlSaveFailed(Util::get("id"));

            }
        }

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }


    private function toCSV($prepareModel)
    {
        $model = $this->model;
        $columnNames = array_keys($this->columns);
        array_unshift($columnNames, "id");
        $data = $prepareModel->select($columnNames)->get()->toArray();

        foreach ($columnNames as $key => $column) {
            $columnNames[$key] = Util::cleanName($column);
        }

        array_unshift($data, $columnNames);
        Util::returnCSVDataAsFile(Util::classToString($model), $data);
        exit();
    }



    private function callActionFunction()
    {
        $model = $this->model;
        $entry = $model::find(Util::get("id"));
        $callFunction = Util::get("cf");

        $success = $entry->{$callFunction}();

        if ($success && is_array($success) && isset($success['message']) && isset($success['type'])) {

            Util::flash(array(
                "message" => $success['message'],
                "type" => $success['type']
            ));

        } else if ($success) {

            Util::flash(array(
                "message" => "Succesfully called {$callFunction} action.",
                "type" => "success"
            ));

        } else {

            Util::flash(array(
                "message" => "Failed to call {$callFunction} action.",
                "type" => "error"
            ));

        }

        if (Util::get("stack")) {
            $redirect_path = Util::childBackLink();
        } else {
            $redirect_path = Util::redirectUrl();
        }

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }

    private function callOtherAction()
    {
        $model = $this->model;
        $entry = $model::find(Util::get("id"));
        $callFunction = Util::get("cf");

        $success = $model::$callFunction($this->user);

        if ($success && is_array($success) && isset($success['message']) && isset($success['type'])) {

            Util::flash(array(
                "message" => $success['message'],
                "type" => $success['type']
            ));

        } else if ($success) {

            Util::flash(array(
                "message" => "Succesfully called {$callFunction} action.",
                "type" => "success"
            ));

        } else {

            Util::flash(array(
                "message" => "Failed to call {$callFunction} action.",
                "type" => "error"
            ));

        }


        if (Util::get("stack")) {
            $redirect_path = Util::childBackLink();
        } else {
            $redirect_path = Util::redirectUrl();
        }

        if (!$this->testing) {
            header("Location: {$redirect_path}");
            exit();
        }
    }





    /******************************
    Final build scaffold
    *******************************/
    public function process()
    {
        $this->route();
        $this->header['description'] = $this->description;
        $this->header['deleteAction'] = $this->deleteAction;
        $this->header['callFunctions'] = $this->callFunctions;
        $this->header['formatLaravelTimestamp'] = $this->formatLaravelTimestamp;

        $data = array(
            "action" => $this->action,
            "entries" => $this->entries,
            "header" => $this->header,
            "columns" => $this->columns,
            "infuseLogin" => $this->infuseLogin,
            "user" => $this->user,
            "db" => self::$db
        );

        return $this->view->make($this->getBladeTemplate(), $data);
    }

    // Used to test scaffold need to call route before
    public function processDataOnly()
    {
        $this->header['description'] = $this->description;
        $this->header['deleteAction'] = $this->deleteAction;
        $this->header['callFunctions'] = $this->callFunctions;
        $this->header['formatLaravelTimestamp'] = $this->formatLaravelTimestamp;

        $data = array(
            "action" => $this->action,
            "entries" => $this->entries,
            "header" => $this->header,
            "columns" => $this->columns,
            "infuseLogin" => $this->infuseLogin,
            "user" => $this->user,
            "db" => self::$db
        );

        return $data;
    }


    public function getBladeTemplate()
    {
        switch (Util::get("action")) {
            case 'l':
                return "infuse::scaffold.list";
            case 'e':
            case 'c':
            case 'cd':
                return "infuse::scaffold.create_edit";
            case 's':
                return "infuse::scaffold.show";
            default:
                return "infuse::scaffold.list";
        }
    }

}
