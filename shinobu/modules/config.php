<?php

# =============================================================================
# shinobu/modules/config.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

# TODO
# - Add caching and a cache loader.

class config
{
	private $config = array(), $db = null;

	public function __construct(db $db = null)
	{
		$this->db = $db;

		if (($this->config = cache::read('config')))
			return;

		$result = $this->db->query('SELECT name, value FROM '.DB_PREFIX.'config')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			$permissions = array();
			while ($row = $result->fetch_assoc())
			{
				// Do some type casting
               	if ($row['value'] === 'true' || $row['value'] === 'false')
                        $this->config[$row['name']] = $row['value'] === 'true' ? true : false;
               	else if (is_numeric($row['value']))
                {
						// http://nl.php.net/manual/en/function.is-float.php#80326
                       	if (is_float($row['value']) || ((float) $row['value'] != round($row['value']) ||
						    strlen($row['value']) != strlen( (int) $row['value'])) && $row['value'] != 0)
                                $this->config[$row['name']] = (float) $row['value'];
                       	else
                               	$this->config[$row['name']] = (int) $row['value'];
                }
               	else
                       	$this->config[$row['name']] = $row['value'];
			}
		}

		cache::write('config', $this->config);
	}

	public function __get($name)
	{
		return $this->config[$name];
	}
}
