<?php

# =============================================================================
# index.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

$start_timer = microtime();

error_reporting(E_ALL);
define('SYS', dirname(__FILE__));

// Force POSIX locale (to prevent functions such as
// strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Load configuration, functions and libraries
require SYS.'/include/config.php';
require SYS_INCLUDE.'/functions.php';

// Disable evil stuff
disable_magic_quotes();
unregister_globals();

// Load UTF-8 library
if (extension_loaded('mbstring'))
{
	if (ini_get('mbstring.func_overload') & MB_OVERLOAD_STRING)
		trigger_error('String functions are overloaded by mbstring', E_USER_ERROR);

	mb_language('uni');
	mb_internal_encoding('UTF-8');

	require SYS_UTF8.'/mbstring/core.php';
}
else
{
	require SYS_UTF8.'/utils/unicode.php';
	require SYS_UTF8.'/native/core.php';
}

// Load classes and controllers
require SYS_INCLUDE.'/classes.php';
require SYS_INCLUDE.'/controllers.php';

// Return content to the visitor
echo request::answer();
echo "\n\n", round(get_microtime(microtime()) - get_microtime($start_timer), 5),
     's - ', file_size(memory_get_usage()), ' - ',
     file_size(memory_get_peak_usage());
