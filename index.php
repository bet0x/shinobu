<?php

# =============================================================================
# index.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

$start_timer = microtime();

error_reporting(E_ALL | E_STRICT);
define('SYS', dirname(__FILE__));

// Force POSIX locale (to prevent functions such as
// strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Load configuration, functions and libraries
require SYS.'/application/config.php';
require SYS_INCLUDE.'/functions.php';

// Turn off magic_quotes_runtime
if (get_magic_quotes_runtime())
    set_magic_quotes_runtime(0);

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
	$_REQUEST = stripslashes_array($_REQUEST);
}

// Unset any variables instantiated as a result of register_globals being enabled
unregister_globals();

if (defined('UTF8'))
{
	// Check whether PCRE has been compiled with UTF-8 support
	$UTF8_ar = array();
	if (preg_match('/^.{1}$/u', "ñ", $UTF8_ar) != 1)
		trigger_error('PCRE is not compiled with UTF-8 support', E_USER_ERROR);

	unset($UTF8_ar);
}

// Load UTF-8 library
if (defined('UTF8_USE_MBSTRING'))
{
	/* If string overloading is active, it will break many of the
	native implementations.  mbstring.func_overload must be set
	to 0, 1 or 4 in php.ini (string overloading disabled).
	Also need to check we have the correct internal mbstring
	encoding. */
	if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING)
		trigger_error('String functions are overloaded by mbstring', E_USER_ERROR);

	mb_language('uni');
	mb_internal_encoding('UTF-8');

	if (!defined('UTF8_CORE'))
		require UTF8.'/mbstring/core.php';
}
elseif (defined('UTF8_USE_NATIVE'))
{
	if (!defined('UTF8_CORE'))
	{
		require UTF8.'/utils/unicode.php';
		require UTF8.'/native/core.php';
	}
}

// Load classes
require SYS_INCLUDE.'/classes.php';

// Load base controllers
require SYS.'/application/functions.php';
require SYS.'/application/base.php';

// Return content to the visitor
$application = new Application();
echo $application->output;

// This is just for testing
$stop_timer = microtime();
echo "\n\n", round(get_microtime($stop_timer) - get_microtime($start_timer), 5),
     's - ', file_size(memory_get_usage()), ' - ',
     file_size(memory_get_peak_usage());
