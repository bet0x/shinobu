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
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU')) ? exit : NULL;

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime())
	set_magic_quotes_runtime(0);

// Disable Magicquotes
// Strip slashes from GET/POST/COOKIE (if magic_quotes_gpc is enabled)
if (get_magic_quotes_gpc())
{
	function stripslashes_array($array)
	{
		return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
	}

	$_GET = stripslashes_array($_GET);
	$_POST = stripslashes_array($_POST);
	$_COOKIE = stripslashes_array($_COOKIE);
}

// Block prefetch requests
// http://www.addedbytes.com/blog/block-prefetching/
if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch')
{
	header('HTTP/1.1 403 Prefetching Forbidden');

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compability

	exit;
}

require 'config.php';

// Load functions
require SYS_CORE_DIR.'functions.php';

// Load UTF-8 functions
require SYS_LIBRARY_DIR.'utf8/utf8.php';
require SYS_LIBRARY_DIR.'utf8/trim.php';

// Load database layer
require SYS_CORE_DIR.'db_layer/'.$db_type.'.php';

// Reverse the effect of register_globals
unregister_globals();

// Make a database connection
$sys_db = new database($db_host, $db_user, $db_password, $db_name, $db_persistent);

// Get the config and cache it if it's not
if (file_exists(SYS_CACHE_DIR.'.cache_config'))
	require SYS_CACHE_DIR.'.cache_config';
else
{
	cache_config();
	require SYS_CACHE_DIR.'.cache_config';
}

// Generate user
$sys_user = check_user_cookie();

// Load language file
if ($sys_user['id'] == GUEST_UID)
	require SYS_LANG_DIR.$sys_config['language'].'/lang.php';
else
	require SYS_LANG_DIR.$sys_user['language'].'/lang.php';

// For some very odd reason, "Norton Internet Security" unsets this (From FluxBB)
$_SERVER['HTTP_ACCEPT_ENCODING'] = isset($_SERVER['HTTP_ACCEPT_ENCODING']) ? $_SERVER['HTTP_ACCEPT_ENCODING'] : '';

// Should we use gzip output compression?
if (extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
	ob_start('ob_gzhandler');
else
	ob_start();

?>
