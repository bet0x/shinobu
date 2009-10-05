<?php

# =============================================================================
# include/init.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

defined('SYS') or exit;

// Load configuration and functions
require SYS.'/include/config.php';
require SYS_INCLUDE.'/functions.php';
require SYS_INCLUDE.'/classes.php';

// Disable evil stuff
disable_magic_quotes();
unregister_globals();

// Force POSIX locale (to prevent functions such as strtolower() from messing up UTF-8 strings)
setlocale(LC_CTYPE, 'C');

// Make database connection with PDO MySQL
db::initialize($db_host, $db_name, $db_user, $db_password);
unset($db_user, $db_password);

// Check user cookie
user::initialize();
