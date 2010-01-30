<?php

defined('SYS') or exit;

// Get protocol (http/https) and port (80/443) for SYSTEM_BASE_URL
$protocol = !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off' ? 'http://' : 'https://';
$port = isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') ||
        ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) ? ':'.$_SERVER['SERVER_PORT'] : '';

// System settings
define('SHINOBU', '0.4-dev');
define('SYSTEM_BASE_URL', $protocol.$_SERVER['SERVER_NAME'].$port.rtrim(dirname($_SERVER['SCRIPT_NAME']),'/\\'));
define('REWRITE_URL', false);
define('SYSTEM_DEVEL', true); // Development mode

$SYSTEM_DEFAULT_CONTROLLER = 'BaseController';

// Database settings
$db_type       = ''; // Not used at the moment.  MySQLi will be used by default for now.
$db_host       = '';
$db_name       = '';
$db_user       = '';
$db_password   = '';
$db_flags      = 0;

define('DB_PREFIX', '');

// Cookie settings
$sys_cookie_name     = 'shinobu04_cookie';
$sys_cookie_domain   = '';
$sys_cookie_path     = '/';
$sys_cookie_secure   = 0;
$sys_cookie_seed     = '&^7hyY&*88uhY&'; // Some random characters should be entered here. Example: &^7hyY&*88uhY&
$sys_cookie_lifetime = 34560000; // 400 days

// Paths
define('SYS_INCLUDE', SYS.'/include');
define('SYS_CONTROL', SYS.'/controllers');
define('SYS_TEMPLATE', SYS.'/templates');
define('SYS_STATIC', SYS.'/static');
define('SYS_UTF8', SYS_INCLUDE.'/utf8');

// ACL constants
define('ACL_CREATE', 1);
define('ACL_READ', 2);
define('ACL_UPDATE', 4);
define('ACL_DELETE', 8);

define('ACL_PERM_5', 16);
define('ACL_PERM_6', 32);
define('ACL_PERM_7', 64);
define('ACL_PERM_8', 128);
