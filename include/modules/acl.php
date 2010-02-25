<?php

# =============================================================================
# include/modules/acl.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class acl
{
	private $permisions = array(), $new_perms = array(), $group_id, $db = null;

	public function __construct(db $db = null)
	{
		$this->db = $db;
	}

	// Set a group ID
	public function set_gid($id)
	{
		$this->group_id = intval($id);
	}

	// Write all the changes to the database
	public function __destruct()
	{
		// Loop through groups
		foreach ($this->new_perms as $gid => $acl)
		{
			// Loop through ACLs
			foreach ($acl as $acl_id => $permission)
			{
				$this->db->query('UPDATE '.DB_PREFIX.'acl_groups SET permissions='.intval($permission).' WHERE group_id='.intval($gid).'
					AND acl_id="'.$this->db->escape($acl_id).'"')
					or error('User data could not be updated: '.$this->db->error(), __FILE__, __LINE__);
			}
		}
	}

	// Get a ACL
	public function get($acl_id)
	{
		if (isset($this->permissions[$this->group_id][$acl_id]))
			return $this->permissions[$this->group_id][$acl_id];

		$result = $this->db->query('SELECT permissions FROM '.DB_PREFIX.'acl_groups WHERE group_id='.$this->group_id.'
			AND acl_id="'.$this->db->escape($acl_id).'" LIMIT 1') or error('Unable to fetch ACL permission.', __FILE__, __LINE__);

		if ($this->db->num_rows($result) === 1)
			$this->permissions[$this->group_id][$acl_id] = $permissions = (int) $this->db->result($result);
		else
			return false;

		return $permissions;
	}

	// Get multiple ACLs
	public function get_multiple()
	{
		$req_perms = '"'.$this->db->escape(implode('", "', func_get_args())).'"';
		$result = $this->db->query('SELECT acl_id, permissions FROM '.DB_PREFIX.'acl_groups WHERE group_id='.$this->group_id.'
			AND acl_id IN ('.$req_perms.')') or error('Unable to fetch permissions.', __FILE__, __LINE__);

		if ($this->db->num_rows($result) > 0)
		{
			$permissions = array();
			while ($row = $this->db->fetch_assoc($result))
				$this->permissions[$this->group_id][$row['acl_id']] = $permissions[$this->group_id][$row['acl_id']] = (int) $row['permissions'];
		}
		else
			return false;

		return $permissions;
	}

	// Update a ACL
	public function set($acl_id, $permissions)
	{
		$this->permissions[$this->group_id][$acl_id] = $permissions;
		$this->new_perms[$this->group_id][$acl_id] =& $this->permissions[$this->group_id][$acl_id];
	}

	// Check a permission
	public function check($acl_id, $bits)
	{
		if (!isset($this->permissions[$this->group_id][$acl_id]))
			$this->get('administration');

		return $this->permissions[$this->group_id][$acl_id] & $bits;
	}
}
