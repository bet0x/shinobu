<?php

# =============================================================================
# include/modules/acl.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class acl
{
	private $permisions = array(), $group_id, $db = null;

	public function __construct(db $db = null)
	{
		$this->db = $db;
	}

	// Set a group ID
	public function set_gid($id)
	{
		$this->group_id = intval($id);
	}

	// Get a ACL
	public function get($acl_id)
	{
		if (isset($this->permissions[$this->group_id][$acl_id]))
			return $this->permissions[$this->group_id][$acl_id];

		$result = $this->db->query('SELECT permissions FROM '.DB_PREFIX.'acl_groups WHERE group_id='.$this->group_id.'
			AND acl_id="'.$this->db->escape($acl_id).'" LIMIT 1') or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows === 0)
			return false;

		list($permissions) = $result->fetch_row();
		$this->permissions[$this->group_id][$acl_id] = (int) $permissions;

		return $this->permissions[$this->group_id][$acl_id];
	}

	// Get multiple ACLs
	public function get_multiple()
	{
		$req_perms = '"'.$this->db->escape(implode('", "', func_get_args())).'"';
		$result = $this->db->query('SELECT acl_id, permissions FROM '.DB_PREFIX.'acl_groups WHERE group_id='.$this->group_id.'
			AND acl_id IN ('.$req_perms.')') or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			$permissions = array();
			while ($row = $result->fetch_assoc($stmt))
				$this->permissions[$this->group_id][$row['acl_id']] = $permissions[$this->group_id][$row['acl_id']] = (int) $row['permissions'];
		}
		else
			return false;

		return $permissions;
	}

	// Check a permission
	public function check($acl_id, $bits)
	{
		if (!isset($this->permissions[$this->group_id][$acl_id]))
			$this->get('administration');

		return $this->permissions[$this->group_id][$acl_id] & $bits;
	}
}
