<?php

# =============================================================================
# framework/modules/user.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class user
{
	private $data_fields = array('id', 'username', 'password', 'salt', 'hash', 'email', 'group_id'),
	        $db = null;
	public $data = array(), $authenticated = false, $acl = array();

	public function __construct(db &$db = null)
	{
		$this->db =& $db;
		$this->authenticate();
	}

	// Check user cookie (only affects the current user/visitor)
	public function authenticate()
	{
		if (($cookie = get_cookie('user')))
		{
			// Get user data
			$result = $this->db->query('SELECT u.id, u.username, u.salt, u.hash, u.email, u.group_id, g.user_title AS title '.
				'FROM '.DB_PREFIX.'users AS u, '.DB_PREFIX.'usergroups AS g '.
				'WHERE u.id='.intval($cookie['id']).' AND g.id=u.group_id LIMIT 1')
				or error($this->db->error);
			$this->data = $result->fetch_assoc();

			if ($this->data)
			{
				// Check cookie key
				if ($cookie['key'] == sha1($this->data['salt'].$this->data['hash']))
					$this->authenticated = true;
			}
		}
	}

	// Remove from this class
	/* Create a login cookie for the user (only affects the current user/visitor)
	1 = successful login, 2 = already logged in, 3 = user does not exist,
	4 = wrong password */
	public function login($username, $password)
	{
		// Check if user is logged in
		if (get_cookie('user') && $this->authenticated)
			return 2;

		// Escape username and password
		$username = trim($this->db->escape($username));
		$password = trim($password);

		// Check if user exists and fetch data
		$result = $this->db->query('SELECT id, password, salt, hash FROM '.DB_PREFIX.'users
			WHERE username="'.$username.'" LIMIT 1');
		$fetch = $result->fetch_row();

		if (!$fetch)
			return 3;

		// Check password hashes
		list($user_id, $user_password, $user_salt, $user_hash) = $fetch;

		if ($user_password != generate_hash($password, $user_salt))
			return 4;

		// 1209600: 2 weeks
		set_cookie('user', array(
			'id' => $user_id,
			'key' => sha1($user_salt.$user_hash)), time() + 1209600);

		return 1;
	}

	// Let the user cookie expire.  Only affects the current user/visitor.
	public function logout()
	{
		set_cookie('user', null, time()-3600);
	}

	// Get get a group permission set from the database
	public function _get_permission_set($set_id)
	{
		$result = $this->db->query('SELECT bits FROM '.DB_PREFIX.'permissions WHERE group_id='.$this->data['group_id'].'
			AND set_id="'.$this->db->escape($set_id).'" LIMIT 1');

		if ($result->num_rows === 0)
			return false;

		$permissions = $result->fetch_row();
		$this->permissions[$set_id] = (int) $permissions[0];

		return $this->permissions[$set_id];
	}

	// Check if a user is allowed to do a certain action
	public function is_allowed($set_id, $perm_id)
	{
		if (!isset($this->permissions[$set_id]))
			$this->_get_permission_set($set_id);

		if (!isset(_permission_struct::$sets[$set_id][$perm_id]))
			return false;

		return $this->permissions[$set_id] & _permission_struct::$sets[$set_id][$perm_id];
	}

	// Remove from this class
	// Add new user
	public function add($username, $group_id, $password, $email)
	{
		// Create hashes for the password
		$salt = generate_salt();
		$password = generate_hash($password, $salt);
		$hash = generate_hash(generate_salt(), $salt);

		$this->db->query('
			INSERT INTO '.DB_PREFIX.'users
				(username, group_id, password, salt, hash, email)
			VALUES(
				"'.$this->db->escape($username).'",
				'.intval($group_id).',
				"'.$this->db->escape($password).'",
				"'.$this->db->escape($salt).'",
				"'.$this->db->escape($hash).'",
				"'.$this->db->escape($email).'")');

		// Return the ID of the added user
		return $this->db->insert_id;
	}

	// Remove from this class
	// Update user data
	public function update($id, $new_data = array())
	{
		if (count($new_data) === 0 || count(array_diff(array_keys($new_data), $this->data_fields)) > 0)
			return false;

		// Create hashes when the password is updated
		if (isset($new_data['password']))
		{
			$new_data['salt'] = generate_salt();
			$new_data['password'] = generate_hash($new_data['password'], $new_data['salt']);
			$new_data['hash'] = generate_hash(generate_salt(), $new_data['salt']);
		}

		$data_sql = array();

		foreach ($new_data as $k => $v)
			$data_sql[] = is_int($v) ? $k.'='.intval($v) : $k.'="'.$this->db->escape($v).'"';

		return $this->db->query('UPDATE '.DB_PREFIX.'users SET '.implode(', ', $data_sql).' WHERE id='.intval($id))
			or error($this->db->error);
	}
}
