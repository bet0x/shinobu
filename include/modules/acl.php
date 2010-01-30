<?php

# =============================================================================
# include/modules/acl.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// NOT DONE YET

class acl
{
	private $permisions = array();

	public function __construct()
	{

	}

	public function __destruct()
	{
		// Write all the changes to the database
	}

	public function __get($name)
	{
		if (!isset($this->permissions[$name]))
			$this->permissions[$name] = 8;

		return $this->permissions[$name];
	}

	public function __set($name, $value)
	{
		$this->permissions[$name] = $value;
	}
}
