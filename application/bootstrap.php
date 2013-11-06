<?php defined('SYSPATH') or die('No direct script access.');

// -- Environment setup --------------------------------------------------------

// Load the core Kohana class
require SYSPATH.'classes/Kohana/Core'.EXT;

if (is_file(APPPATH.'classes/Kohana'.EXT))
{
	// Application extends the core
	require APPPATH.'classes/Kohana'.EXT;
}
else
{
	// Load empty core extension
	require SYSPATH.'classes/Kohana'.EXT;
}

/**
 * Set the default time zone.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/timezones
 */
date_default_timezone_set('America/Chicago');

/**
 * Set the default locale.
 *
 * @link http://kohanaframework.org/guide/using.configuration
 * @link http://www.php.net/manual/function.setlocale
 */
setlocale(LC_ALL, 'en_US.utf-8');

/**
 * Enable the Kohana auto-loader.
 *
 * @link http://kohanaframework.org/guide/using.autoloading
 * @link http://www.php.net/manual/function.spl-autoload-register
 */
spl_autoload_register(array('Kohana', 'auto_load'));

/**
 * Optionally, you can enable a compatibility auto-loader for use with
 * older modules that have not been updated for PSR-0.
 *
 * It is recommended to not enable this unless absolutely necessary.
 */
//spl_autoload_register(array('Kohana', 'auto_load_lowercase'));

/**
 * Enable the Kohana auto-loader for unserialization.
 *
 * @link http://www.php.net/manual/function.spl-autoload-call
 * @link http://www.php.net/manual/var.configuration#unserialize-callback-func
 */
ini_set('unserialize_callback_func', 'spl_autoload_call');

// -- Configuration and initialization -----------------------------------------

/**
 * Set the default language
 */
I18n::lang('en-us');

/**
 * Set Kohana::$environment if a 'KOHANA_ENV' environment variable has been supplied.
 *
 * Note: If you supply an invalid environment name, a PHP warning will be thrown
 * saying "Couldn't find constant Kohana::<INVALID_ENV_NAME>"
 */
if (isset($_SERVER['KOHANA_ENV']))
{
	Kohana::$environment = constant('Kohana::'.strtoupper($_SERVER['KOHANA_ENV']));
}

Cookie::$salt = 'FPRO COOKIE SALT';

/**
 * Initialize Kohana, setting the default options.
 *
 * The following options are available:
 *
 * - string   base_url    path, and optionally domain, of your application   NULL
 * - string   index_file  name of your index file, usually "index.php"       index.php
 * - string   charset     internal character set used for input and output   utf-8
 * - string   cache_dir   set the internal cache directory                   APPPATH/cache
 * - integer  cache_life  lifetime, in seconds, of items cached              60
 * - boolean  errors      enable or disable error handling                   TRUE
 * - boolean  profile     enable or disable internal profiling               TRUE
 * - boolean  caching     enable or disable internal caching                 FALSE
 * - boolean  expose      set the X-Powered-By header                        FALSE
 */
/*
Kohana::init(array(
	'base_url'   => '/kohana/',
));*/
$my_base_url = str_replace('index.php','', $_SERVER['SCRIPT_NAME']);

// Detect user timezone
require_once APPPATH . 'user_timezone_detect.php';

//Cookie::delete('last_tz_autodetect');
//Cookie::delete('user_timezone');

Kohana::init(array(
	'base_url'   => $my_base_url,
	'index_file' => false,
    'profile'    => (Kohana::$environment == Kohana::DEVELOPMENT),
    'caching'    => (Kohana::$environment == Kohana::PRODUCTION)
));

if( PHP_SAPI == 'cli' ) { Session::$default = 'cookie'; }

// Update .htaccess if needed
$file = $_SERVER['DOCUMENT_ROOT']. $my_base_url . '.htaccess';
if (file_exists($file)) {
	$file_data = file_get_contents($file);
	// only update if our kohana installation directory has changed
	if (strpos($file_data, $my_base_url ) === false) {
		$htaccess_file = @file($file);
		$handle = @fopen($file,'w');
		if( is_array( $htaccess_file ) ) {
			foreach($htaccess_file as $line_number => $line ) {
				if( $my_base_url != "/" ) {
					if( strpos($line,"RewriteBase /") !== false ) {
						// check if default htaccess file or not
						if (strlen($line) == strlen("'RewriteBase /'")) {
							fwrite($handle, str_replace("/",$my_base_url,$line));
						} elseif (strlen($line) > strlen("'RewriteBase /'")) {
							fwrite($handle, preg_replace("/\/(.*?)\//i", $my_base_url, $line));
						}
					} else {
						fwrite($handle,$line);
					}

				} else {
					fwrite($handle,$line);
				}
			}
		}
	}
}

/**
 * Attach the file write to logging. Multiple writers are supported.
 */
Kohana::$log->attach(new Log_File(APPPATH.'logs'));

/**
 * Attach a file reader to config. Multiple readers are supported.
 */
Kohana::$config->attach(new Config_File);

// Make current theme config readable by config reader
$theme = Kohana::$config->load('settings.template');
Kohana::$config->attach(new Config_File('../templates/' . $theme));

/**
 * Enable modules. Modules are referenced by a relative or absolute path.
 */
Kohana::modules(array(
	'auth'       		=> MODPATH.'auth',     	 		// Basic authentication
	'template'			=> DOCROOT.'templates/'.$theme,	// Active theme
	'cache'      		=> MODPATH.'cache',      		// Caching with multiple backends
	// 'codebench'  	=> MODPATH.'codebench',  		// Benchmarking tool
	'database'   		=> MODPATH.'database',   		// Database access
	//'barcode'  			=> MODPATH.'barcode',  	 		// BarCode API
	'image'      		=> MODPATH.'image',      		// Image manipulation
	//'minion'	     	=> MODPATH.'minion',     		// CLI Tasks
	'orm'        		=> MODPATH.'orm',        		// Object Relationship Mapping
	'db-acl'			=> MODPATH.'db-acl',			// Kohana dB ACL
	//'phpexcel'   		=> MODPATH.'phpexcel',		//Kohana PHP to Excel.
	//'paginate'		=> MODPATH.'paginate',        	// Kohana Paginate
	//'datatables'		=> MODPATH.'datatables',        // Kohana DataTables
	//'datatable'		=> MODPATH.'datatable',			// Kohana DataTable
	//'dtt'				=> MODPATH.'dtt',
	//'oauth2'  	 	=> MODPATH.'oauth2',	 		// My custom oauth2 module
	//'uuid'        	=> MODPATH.'uuid',       		// UUID Generation
	'pagination'		=> MODPATH.'pagination',        // Pagination
	//'email'           => MODPATH.'shadowhand-email',// Shadowhand email module
	//'jasper-report'		=> MODPATH.'jasper-report',     // JasperReport module for Kohana
	'menu'				=> MODPATH.'menu',				// Menu builder
	'beautiful-asset'	=> MODPATH.'beautiful-asset',   // Beatiful asset manager
	'beautiful-view'	=> MODPATH.'beautiful-view',    // Beautiful View class
	//'unittest'   		=> MODPATH.'unittest',   		// Unit testing
	'userguide'  		=> MODPATH.'userguide',  	 	// User guide and API documentation
	));

/**
 * Set the routes. Each route must have a minimum of a name, a URI and a set of
 * defaults for the URI.
 */

Route::set('default', '(<action>(/<id>))')
	->defaults(array(
		'controller' => 'default',
		'action'     => 'index'
	));

Route::set('default', '(<controller>(/<action>(/<id>)))')
	->defaults(array(
		'controller' => 'default',
		'action'     => 'index'
	));
