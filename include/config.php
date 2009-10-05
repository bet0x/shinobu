<?php

defined('SYS') or exit;

// Get protocol (http/https) and port for SYSTEM_BASE_URL
$protocol = !isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off' ? 'http://' : 'https://';
$port = isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') ||
        ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) ? ':'.$_SERVER['SERVER_PORT'] : '';

// System settings
define('SYSTEM_BASE_URL', $protocol.$_SERVER['SERVER_NAME'].$port.rtrim(dirname($_SERVER['SCRIPT_NAME']),'/'));

// Database settings
$db_host       = '';
$db_name       = '';
$db_user       = '';
$db_password   = '';

define('DB_PREFIX', '');

// Cookie settings
$sys_cookie_name   = 'system_cookie';
$sys_cookie_domain = '';
$sys_cookie_path   = '/';
$sys_cookie_secure = 0;
$sys_cookie_seed   = '&^7hyY&*88uhY&'; // Some random characters should be entered here. Example: &^7hyY&*88uhY&

// Paths
define('SYS_INCLUDE', SYS.'/include');
define('SYS_CONTROL', SYS.'/controllers');
define('SYS_TEMPLATE', SYS.'/templates');

// Hardcode usergroup IDs
define('ADMIN_ID', 1);
define('MEMBER_ID', 2);
