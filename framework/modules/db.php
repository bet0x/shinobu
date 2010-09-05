<?php

# =============================================================================
# framework/modules/db.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class db extends MySQLi
{
	public function __construct()
	{
		$this->init();

		// Was a custom port supplied with $db_host?
		if (strpos(conf::$db_host, ':') !== false)
			list(conf::$db_host, $port) = explode(':', conf::$db_host);
		else
			$port = false;

		// Persistent connection in MySQLi are only available in PHP 5.3 and later releases
		$persistent = conf::$db_persistent && version_compare(PHP_VERSION, '5.3.0', '>=') ? 'p:' : '';

		// Setup the client-server character set (UTF-8)
		if (!$this->options(MYSQLI_INIT_COMMAND, 'SET NAMES "utf8"'))
			throw new Exception('Setting MYSQLI_INIT_COMMAND failed.');

		// Make a connection
		if (!$this->real_connect($persistent.conf::$db_host, conf::$db_user,
		    conf::$db_password, conf::$db_name, $port, false, conf::$db_flags))
			throw new Exception('Connection error ('.mysqli_connect_errno().'): '.mysqli_connect_error());
	}

	public function __destruct()
	{
		$this->close();
	}

	public function escape($str)
	{
		return is_array($str) ? '' : $this->real_escape_string($str);
	}

	public function query($query, $resultmode = MYSQLI_STORE_RESULT)
	{
		//if (!$result = parent::query($query, $resultmode))
			//throw new Exception($this->error);

		if (!$this->real_query($query))
			throw new Exception($this->error);

		$result = new db_result($this, $resultmode);
		return $result;
	}

	public function prepare($query)
	{
		//if (!$stmt = parent::prepare($query))
			//throw new Exception($this->error);

		if (!$stmt = new db_stmt($this, $query))
			throw new Exception($this->error);

		return $stmt;
	}
}

class db_result extends MySQLi_Result
{
	public function fetch_offset($row_offset = 0, $col_offset = 0)
	{
		if ($row_offset !== 0 && $this->data_seek($row_offset) === false)
			return false;

		$cur_row = $this->fetch_row();
		if ($cur_row === false)
			return false;

		return $cur_row[$col_offset];
	}
}

class db_stmt extends MySQLi_Stmt
{

}
