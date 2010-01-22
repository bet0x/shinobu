<?php

# =============================================================================
# include/modules/db.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/* For now this database module uses MySQLi, but support for other database
   systems will be added later.  Like MySQL and PostgreSQL (and maybe more).
*/

class db
{
	protected $link_id = false, $query_result, $persistent = false;

	// Connect to a database
	public function __construct()
	{
		global $db_type, $db_host, $db_name, $db_user, $db_password, $db_persistent;

		$this->open($db_host, $db_user, $db_password, $db_name, $db_persistent);
	}

	public function __destruct()
	{
		if (!$this->persistent)
			$this->close();
	}

	public function open($db_host, $db_username, $db_password, $db_name, $p_connect)
	{
		// Was a custom port supplied with $db_host?
		if (strpos($db_host, ':') !== false)
			list($db_host, $db_port) = explode(':', $db_host);

		// Persistent connection in MySQLi are only available in PHP 5.3 and later releases
		$this->persistent = $p_connect && version_compare(PHP_VERSION, '5.3.0', '>=') ? 'p:' : '';

		if (isset($db_port))
			$this->link_id = mysqli_connect($this->persistent.$db_host, $db_username, $db_password, $db_name, $db_port);
		else
			$this->link_id = mysqli_connect($this->persistent.$db_host, $db_username, $db_password, $db_name);

		if (!$this->link_id)
			error('Unable to connect to MySQL and select database. MySQL reported: '.mysqli_connect_error());

		// Setup the client-server character set (UTF-8)
		$this->set_names('utf8');

		return $this->link_id;
	}

	public function close()
	{
		if ($this->link_id)
		{
			if (!is_bool($this->query_result))
				$this->free_result($this->query_result);

			return mysqli_close($this->link_id);
		}
		else
			return false;
	}

	public function query($sql)
	{
		if (strlen($sql) > 140000)
			error('Insane query. Aborting.');

		$this->query_result = mysqli_query($this->link_id, $sql);

		return $this->query_result ? $this->query_result : false;
	}

	public function result($query_id = false, $row = 0, $col = 0)
	{
		if ($query_id)
		{
			if ($row)
				mysqli_data_seek($query_id, $row);

			$cur_row = mysqli_fetch_row($query_id);
			return $cur_row[$col];
		}
		else
			return false;
	}

	public function fetch_assoc($query_id = false)
	{
		return $query_id ? mysqli_fetch_assoc($query_id) : false;
	}

	public function fetch_row($query_id = false)
	{
		return $query_id ? mysqli_fetch_row($query_id) : false;
	}

	public function num_rows($query_id = false)
	{
		return $query_id ? mysqli_num_rows($query_id) : false;
	}

	public function affected_rows()
	{
		return $this->link_id ? mysqli_affected_rows($this->link_id) : false;
	}

	public function insert_id()
	{
		return $this->link_id ? mysqli_insert_id($this->link_id) : false;
	}

	public function free_result($query_id = false)
	{
		return $query_id ? mysqli_free_result($query_id) : false;
	}

	public function escape($str)
	{
		return is_array($str) ? '' : mysqli_real_escape_string($this->link_id, $str);
	}

	public function set_names($names)
	{
		return $this->query('SET NAMES \''.$this->escape($names).'\'');
	}

	public function error()
	{
		return mysqli_error($this->link_id);
	}

	public function get_version()
	{
		return array(
			'name'    => 'MySQL Improved',
			'version' => mysqli_get_server_info($this->link_id)
			);
	}
}
