<?php

# =============================================================================
# include/modules/db.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class db extends MySQLi
{
	protected $_persistent = false;

	public function __construct()
	{
		global $db_host, $db_name, $db_user, $db_password, $db_persistent, $db_user, $db_flags;

		$this->init();

		// Was a custom port supplied with $db_host?
		if (strpos($db_host, ':') !== false)
			list($db_host, $db_port) = explode(':', $db_host);
		else
			$db_port = false;

		// Persistent connection in MySQLi are only available in PHP 5.3 and later releases
		$this->_persistent = $db_persistent && version_compare(PHP_VERSION, '5.3.0', '>=') ? 'p:' : '';

		// Setup the client-server character set (UTF-8)
		if (!$this->options(MYSQLI_INIT_COMMAND, 'SET NAMES "utf8"'))
			error('Setting MYSQLI_INIT_COMMAND failed.');

		// Make a connection
		if (!$this->real_connect($this->_persistent.$db_host, $db_user, $db_password, $db_name, $db_port, false, $db_flags))
			error('Connection error ('.mysqli_connect_errno().'): '.mysqli_connect_error());
	}

	public function __destruct()
	{
		$this->close();
	}

	public function escape($str)
	{
		return is_array($str) ? '' : $this->real_escape_string($str);
	}
}
