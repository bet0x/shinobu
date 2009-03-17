<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU')) ? exit : NULL;

// Database settings
$db_type		= 'mysqli'; // Set this too 'mysql' if you would like to use MySQL
$db_host		= 'localhost';
$db_name		= 'geisha';
$db_user		= 'root';
$db_password	= 'b4@Mff$X';
$db_persistent	= false; // This setting works only for MySQL, not MySQLi

define('DB_PREFIX',	'');

// System configuration
define('APP_ROOT', '/var/www/geisha/'); // Change this to your document root (like on windows: C:/wamp/www/shinobu/)
define('MOD_REWRITE', false); // Set this to true if you would like to enable url rewriting
define('WEBSITE_URL', 'http://isamu/geisha/'); // The full url to your shinobu installation
define('SYSTEM_DEBUG', false); // Set this to true to enable debug mode

define('SHINOBU_VERSION', '0.2.3-dev');
define('SALT_LENGTH', 9); // Do NOT change this after you installed Shinobu!

// Cookie config
$cookie_name = 'shinobu_cookie';
$cookie_domain = '';
$cookie_path = '/';
$cookie_secure = 0;
$cookie_seed = ''; // Some random character should be entered here. Example: &^7hyY&*88uhY&

// Uri pre/suf-fix
define('URI_PREFIX', MOD_REWRITE === true ? '': '?q=');
define('URI_SUFFIX', '.xhtml');

// User(group) id's
define('GUEST_GID', 1);
define('GUEST_UID', 1);
define('ADMIN_GID', 2);

// Define system paths
define('SYS_CORE_DIR', APP_ROOT.'system/core/');
define('SYS_INCLUDE_DIR', APP_ROOT.'system/include/');
define('SYS_LIBRARY_DIR', APP_ROOT.'system/lib/');
define('SYS_CACHE_DIR', APP_ROOT.'system/cache/');
define('SYS_LANG_DIR', APP_ROOT.'system/lang/');
define('SYS_THEME_DIR', APP_ROOT.'themes/');
define('SYS_THEME_PATH', WEBSITE_URL.'themes/'); // Url location to theme folder

?>
