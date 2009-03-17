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

// MySQL class
class database
{
	private $link, $result, $saved_queries, $db_name;
	public $query_count = 0;

	// Make connection
	public function __construct($host, $user, $password, $db_name, $db_persistent)
	{
		if ($db_persistent === true)
			$this->link = mysql_pconnect($host, $user, $password);
		else
			$this->link = mysql_connect($host, $user, $password);

		$this->db_name = $db_name;

		if (!$this->link)
			error(mysql_error(), __FILE__, __LINE__);

		if (!mysql_select_db($db_name, $this->link))
			error('Can not use "'.$db_name.'".', __FILE__, __LINE__);

		$this->query('SET NAMES \'utf8\'') or error($this->error(), __FILE__, __LINE__);
	}

	// Close connection
	public function close()
	{
	 if ($this->link)
		{
			if ($this->result)
				@mysql_free_result($this->result);

			return @mysql_close($this->link);
		}
		else
			return false;
	}

	// Query
	public function query($sql)
	{
		if (SYSTEM_DEBUG === true)
		{
			$this->query_count++;
			$q_start = get_microtime();
		}

		$this->result = @mysql_query($sql, $this->link);

		if (SYSTEM_DEBUG === true)
			$this->saved_queries[] = array(sprintf('%.5f', get_microtime() - $q_start), $sql);

		return $this->result;
	}

	// Fetch assoc
	public function fetch_assoc($query_id = 0)
	{
		return ($query_id) ? @mysql_fetch_assoc($query_id) : false;
	}

	// Fetch row
	public function fetch_row($query_id = 0)
	{
		return ($query_id) ? @mysql_fetch_row($query_id) : false;
	}

	// Nuw rows
	public function num_rows($query_id = 0)
	{
		return ($query_id) ? @mysql_num_rows($query_id) : false;
	}

	// Insert id
	public function insert_id()
	{
		return mysql_insert_id($this->link);
	}

	// Escaping strings
	public function escape($str)
	{
		return is_array($str) ? '' : mysql_real_escape_string($str, $this->link);
	}

	public function error()
	{
		return mysql_error($this->link);
	}

	// Checks if a table exists
	public function table_exists($table)
	{
		if($this->num_rows($this->query('SHOW TABLES FROM '.$this->db_name.' LIKE \''.$this->escape($table).'\'')) > 0)
			return true;
	}

	// Returns all the queries (sql) when debug mode is enabled
	public function saved_queries()
	{
		if (SYSTEM_DEBUG === true)
			return $this->saved_queries;
		else
			return false;
	}
}

?>
