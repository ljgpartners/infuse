<?php

/*
 * CKFinder Configuration File
 *
 * For the official documentation visit http://docs.cksource.com/ckfinder3-php/
 */

/*============================ PHP Error Reporting ====================================*/
// http://docs.cksource.com/ckfinder3-php/debugging.html

// Production
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);

// Development
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

/*============================ General Settings =======================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html

$config = array();

/*============================ Enable PHP Connector HERE ==============================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_authentication

function auth() {

  require $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/autoload.php';
  $app = require_once $_SERVER['DOCUMENT_ROOT'] . '/../bootstrap/app.php';

  $kernel = $app->make('Illuminate\Contracts\Http\Kernel');

  $response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
  );

  return $app['auth']->check();
}



$config['authentication'] = function () {
    return auth();
};

function public_path () {
    return $_SERVER['DOCUMENT_ROOT'];
}

$infuseConfig = require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/infuse/config.php';
$fileSystemConfig = require_once $_SERVER['DOCUMENT_ROOT'] . '/../config/filesystems.php';

/*============================ License Key ============================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_licenseKey

if (array_key_exists('licenseName', $infuseConfig) && array_key_exists('licenseKey', $infuseConfig)) {
    $config['licenseName'] = $infuseConfig['licenseName'];
    $config['licenseKey']  = $infuseConfig['licenseKey'];
}
else {
    $config['licenseName'] = '';
    $config['licenseKey']  = '';
}

/*============================ CKFinder Internal Directory ============================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_privateDir

$config['privateDir'] = array(
    'backend' => 'default',
    'tags'   => '.ckfinder/tags',
    'logs'   => '.ckfinder/logs',
    'cache'  => '.ckfinder/cache',
    'thumbs' => '.ckfinder/cache/thumbs',
);

/*============================ Images and Thumbnails ==================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_images

$config['images'] = array(
    'maxWidth'  => 1600,
    'maxHeight' => 1200,
    'quality'   => 80,
    'sizes' => array(
        'small'  => array('width' => 480, 'height' => 320, 'quality' => 80),
        'medium' => array('width' => 600, 'height' => 480, 'quality' => 80),
        'large'  => array('width' => 800, 'height' => 600, 'quality' => 80)
    )
);

/*=================================== Backends ========================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_backends
// *note* CKFINDER does not currently support rackspace so adapter cannot be defined by fileSystemConfig
if ($fileSystemConfig['default'] == 's3'  && $config['licenseName'] && $config['licenseKey']) {
    $config['backends'][] = array(
        'name'         => 'default', // default
        'adapter'      => 's3',
        'bucket'       => $fileSystemConfig['disks']['s3']['bucket'],
        'key'          => $fileSystemConfig['disks']['s3']['key'],
        'secret'       => $fileSystemConfig['disks']['s3']['secret'],
        'visibility'   => 'public',
        'baseUrl'      => 'https://s3.amazonaws.com/' . $fileSystemConfig['disks']['s3']['bucket'] . '/ckfinder/',
        'root'         => 'ckfinder'
    );
}
else {
    $config['backends'][] = array(
        'name'         => 'default', // default
        'adapter'      => 'local',
        'visibility'   => 'public',
        'baseUrl'      => '/ckfinder/'
    );
}

/*================================ Resource Types =====================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_resourceTypes

$config['defaultResourceTypes'] = 'Images';

$config['resourceTypes'][] = array(
    'name'              => 'Files', // Single quotes not allowed.
    'directory'         => 'files',
    'maxSize'           => 0,
    //'allowedExtensions' => '7z,aiff,asf,avi,bmp,csv,doc,docx,fla,flv,gif,gz,gzip,jpeg,jpg,mid,mov,mp3,mp4,mpc,mpeg,mpg,ods,odt,pdf,png,ppt,pptx,pxd,qt,ram,rar,rm,rmi,rmvb,rtf,sdc,sitd,swf,sxc,sxw,tar,tgz,tif,tiff,txt,vsd,wav,wma,wmv,xls,xlsx,zip',
    'allowedExtensions' => 'jpeg,jpg,png,tiff',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

$config['resourceTypes'][] = array(
    'name'              => 'Images',
    'directory'         => 'images',
    'maxSize'           => 0,
    //'allowedExtensions' => 'bmp,gif,jpeg,jpg,png',
    'allowedExtensions' => 'jpeg,jpg,png',
    'deniedExtensions'  => '',
    'backend'           => 'default'
);

/*================================ Access Control =====================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_roleSessionVar

$config['roleSessionVar'] = 'CKFinder_UserRole';

// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_accessControl
$config['accessControl'][] = array(
    'role'                => '*',
    'resourceType'        => '*',
    'folder'              => '/',

    'FOLDER_VIEW'         => true,
    'FOLDER_CREATE'       => false,
    'FOLDER_RENAME'       => false,
    'FOLDER_DELETE'       => false,

    'FILE_VIEW'           => true,
    'FILE_CREATE'         => true,
    'FILE_RENAME'         => true,
    'FILE_DELETE'         => true,

    'IMAGE_RESIZE'        => true,
    'IMAGE_RESIZE_CUSTOM' => true
);


/*================================ Other Settings =====================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html

$config['overwriteOnUpload'] = false;
$config['checkDoubleExtension'] = true;
$config['disallowUnsafeCharacters'] = false;
$config['secureImageUploads'] = true;
$config['checkSizeAfterScaling'] = true;
$config['htmlExtensions'] = array('html', 'htm', 'xml', 'js');
$config['hideFolders'] = array('.*', 'CVS', '__thumbs');
$config['hideFiles'] = array('.*');
$config['forceAscii'] = false;
$config['xSendfile'] = false;

// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_debug
$config['debug'] = false;

/*==================================== Plugins ========================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_plugins

$config['pluginsDirectory'] = __DIR__ . '/plugins';
$config['plugins'] = array();

/*================================ Cache settings =====================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_cache

$config['cache'] = array(
    'imagePreview' => 24 * 3600,
    'thumbnails'   => 24 * 3600 * 365,
    'proxyCommand' => 0
);

/*============================ Temp Directory settings ================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_tempDirectory

$config['tempDirectory'] = sys_get_temp_dir();

/*============================ Session Cause Performance Issues =======================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_sessionWriteClose

$config['sessionWriteClose'] = true;

/*================================= CSRF protection ===================================*/
// http://docs.cksource.com/ckfinder3-php/configuration.html#configuration_options_csrfProtection

$config['csrfProtection'] = true;

/*============================== End of Configuration =================================*/

// Config must be returned - do not change it.
return $config;
