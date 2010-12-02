<?php

defined('SYS') or exit;

// Get protocol (http/https) and port (80/443) for SYSTEM_BASE_URL
$protocol = !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off' ? 'http://' : 'https://';
$port = isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') ||
        ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) ? ':'.$_SERVER['SERVER_PORT'] : '';

// System settings
define('SHINOBU', '0.4-alpha1');
define('SYSTEM_BASE_URL', $protocol.$_SERVER['SERVER_NAME'].$port.rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\'));

// Paths
define('SYS_INCLUDE', SYS.'/framework');
define('SYS_LIB', SYS_INCLUDE.'/lib');
define('SYS_CONTROL', SYS.'/application/controllers');
define('SYS_TEMPLATE', SYS.'/application/templates');
define('SYS_CACHE', SYS.'/application/cache');
define('SYS_STATIC', SYS.'/static');

// UTF-8 Configuration
define('UTF8', SYS_LIB.'/php-utf8');
define('PHP_UTF8_MODE', 'mbstring');
#define('PHP_UTF8_MODE', 'native');

class conf
{
	// System settings
	static public $default_controller = 'BaseController';

	const REWRITE_URL = false;
	const DEBUG = true; // Development mode

	// Database settings
	static public $db_host = 'localhost',
	              $db_name = 'shinobu',
	              $db_user = 'root',
	              $db_password = 'password',
	              $db_flags = 0,
				  $db_persistent = false;

	// Cookie settings
	static public $cookie_name = 'shinobu_xampp_cookie',
	              $cookie_domain = '',
	              $cookie_path = '/',
	              $cookie_secure = 0,
	              $cookie_seed = '&^7hyY&*8dd8uhY&',  // Some random characters should be entered here. Example: &^7hyY&*88uhY&
	              $cookie_ttl = 34560000; // 400 days
}

define('DB_PREFIX', ''); // Database table prefix

// A simpel container for permission sets
class _permission_struct
{
	// Define permission sets
	static public $sets = array(
		'admin' => array(
			'info'    => 1,
			'pages'   => 2,
			'users'   => 4,
			'menu'    => 8,
			'options' => 16,
			'groups'  => 32,
			//'' => 64,
			//'' => 128,
		),

		'test' => array(
			'one'   => 1,
			'two'   => 2,
			'three' => 4,
			'four'  => 8,
			'five'  => 16,
			'six'   => 32,
			'seven' => 64,
			'eight' => 128,
		)
	);
}
