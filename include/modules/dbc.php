<?php

# =============================================================================
# include/modules/db.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class dbc extends PDO
{
	public function __construct()
	{
		global $db_type, $db_host, $db_name, $db_user, $db_password;

		$this->connect($db_type, $db_host, $db_name, $db_user, $db_password);
	}

	public function connect($db_type, $db_host, $db_name, $db_user, $db_password)
	{
		try
		{
			switch ($db_type)
			{
				case 'mysql':
					parent::__construct('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_password,
						array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
					break;
				case 'pgsql':
					parent::__construct('pgsql:host='.$db_host.';dbname='.$db_name, $db_user, $db_password);
					break;
				case 'sqlite2':
					parent::__construct('sqlite2:'.$db_name);
					break;
				case 'sqlite':
					parent::__construct('sqlite:'.$db_name);
					break;
				default:
					error('There is no support for the specified database type, "'.$db_type.'".');
			}
		}
		catch (PDOException $e)
		{
			error($e->getMessage(), __FILE__, __LINE__);
		}
	}
}
