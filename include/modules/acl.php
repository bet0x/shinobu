<?php

# =============================================================================
# include/modules/acl.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// NOT DONE YET

class acl
{
	private $permisions = array(), $new_perms = array(), $group_id;

	public function set_gid($id)
	{
		$this->group_id = intval($id);
	}

	public function __destruct()
	{
		// Write all the changes to the database
	}

	public function get($perm_id)
	{
		if (isset($this->permissions[$this->group_id][$perm_id]))
			return $this->permissions[$this->group_id][$perm_id];

		global $mc;

		$result = $mc->db->query('SELECT perm_id, bits FROM '.DB_PREFIX.'group_perms WHERE group_id='.$this->group_id.'
			AND perm_id="'.$mc->db->escape($perm_id).'"') or error('Unable to fetch permission.', __FILE__, __LINE__);

		if ($mc->db->num_rows($result) === 1)
			$this->permissions[$this->group_id][$perm_id] = $bits = (int) $mc->db->result($result, 0, 1);
		else
			return false;

		return $bits;
	}

	public function get_multiple()
	{
		global $mc;

		$req_perms = '"'.$mc->db->escape(implode('", "', func_get_args())).'"';
		$result = $mc->db->query('SELECT perm_id, bits FROM '.DB_PREFIX.'group_perms WHERE group_id='.$this->group_id.'
			AND perm_id IN ('.$req_perms.')') or error('Unable to fetch permissions: '.$mc->db->error(), __FILE__, __LINE__);

		if ($mc->db->num_rows($result) > 0)
		{
			$permissions = array();
			while ($row = $mc->db->fetch_assoc($result))
				$this->permissions[$this->group_id][$row['perm_id']] = $permissions[$this->group_id][$row['perm_id']] = (int) $row['bits'];
		}
		else
			return false;

		return $permissions;
	}

	public function set($name, $value)
	{
		$this->permissions[$this->group_id][$name] = $value;
		$this->new_perms[$this->group_id][$name] =& $this->permissions[$this->group_id][$name];
	}
}
