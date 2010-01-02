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

// Load configuration, functions and libraries
require SYS.'/include/config.php';
require SYS_INCLUDE.'/functions.php';
require SYS_INCLUDE.'/classes.php';
require SYS_INCLUDE.'/auth.php';

// Disable evil stuff
disable_magic_quotes();
unregister_globals();

// Force POSIX locale (to prevent functions such as
// strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Make the primary connection with the database
db::connect($db_type, $db_host, $db_name, $db_user, $db_password);
unset($db_user, $db_password);

// Check user cookie
user::authenticate();

// Return content to the visitor
echo request::answer();
echo "\n\n", round(get_microtime(microtime()) - get_microtime($start_timer), 5),
     'ms - ', file_size(memory_get_usage()), ' - ',
     file_size(memory_get_peak_usage());
