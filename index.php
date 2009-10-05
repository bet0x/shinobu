<?php

# =============================================================================
# index.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

ob_start();
error_reporting(E_ALL);
define('SYS', dirname(__FILE__));

require SYS.'/include/init.php';
require SYS_INCLUDE.'/system.php';

//var_export(user::login('Frank', 'password'));

system::run();
db::close();
