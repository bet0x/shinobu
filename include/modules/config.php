<?php

# =============================================================================
# include/modules/config.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/* TODO

   - Determine type of configuration values.
   - Add caching and a cache loader.

   */

class config
{
	private $config = array(), $db = null;

	public function __construct(db $db = null)
	{
		$this->db = $db;

		$result = $this->db->query('SELECT name, value FROM '.DB_PREFIX.'config')
			or error('Unable to fetch configuration data.', __FILE__, __LINE__);

		if ($this->db->num_rows($result) > 0)
		{
			$permissions = array();
			while ($row = $this->db->fetch_assoc($result))
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
	}

	public function __get($name)
	{
		return $this->config[$name];
	}
}
