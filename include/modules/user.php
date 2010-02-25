<?php

# =============================================================================
# include/modules/user.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class user
{
	private $data_fields = array('id', 'username', 'password', 'salt', 'hash', 'email', 'group_id', 'title'),
	        $authenticated = false, $db = null;
	public $data = array();

	public function __construct(db $db = null)
	{
		$this->db = $db;
		$this->authenticate();
	}

	// Check user cookie (only affects the current user/visitor)
	public function authenticate()
	{
		if (($cookie = utils::get_cookie('user')))
		{
			// Get user data
			$result = $this->db->query('SELECT u.id, u.username, u.salt, u.hash, u.email, g.id AS group_id, g.user_title AS title '.
				'FROM '.DB_PREFIX.'users AS u, '.DB_PREFIX.'usergroups AS g '.
				'WHERE u.id='.intval($cookie['id']).' AND g.id=u.group_id LIMIT 1')
				or error('Could not fetch user information. '.$this->db->error() , __FILE__, __LINE__);
			$this->data = $this->db->fetch_assoc($result);

			if ($this->data)
			{
				// Check cookie key
				if ($cookie['key'] == sha1($this->data['salt'].$this->data['hash']))
					$this->authenticated = true;
			}
		}
	}

	// Check if user if user is authenticated/logged in
	public function authenticated()
	{
		return $this->authenticated;
	}

	/* Create a login cookie for the user (only affects the current user/visitor)
	   1 = successful login, 2 = already logged in, 3 = user does not exist,
	   4 = wrong password
	*/
	public function login($username, $password)
	{
		// Check if user is logged in
		if (utils::get_cookie('user') && $this->authenticated)
			return 2;

		// Escape username and password
		$username = trim($this->db->escape($username));
		$password = trim($password);

		// Check if user exists and fetch data
		$result = $this->db->query('SELECT id, password, salt, hash FROM '.DB_PREFIX.'users WHERE username="'.$username.'" LIMIT 1')
			or error('Could not fetch login information.', __FILE__, __LINE__);
		$fetch = $this->db->fetch_row($result);

		if (!$fetch)
			return 3;

		// Check password hashes
		list($user_id, $user_password, $user_salt, $user_hash) = $fetch;

		if ($user_password != generate_hash($password, $user_salt))
			return 4;

		// 1209600: 2 weeks
		utils::set_cookie('user', array('id' => $user_id, 'key' => sha1($user_salt.$user_hash)), time() + 1209600);

		return 1;
	}

	// Let the user cookie expire.  Only affects the current user/visitor.
	public function logout()
	{
		utils::set_cookie('user', null, time()-3600);
	}

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
				"'.$this->db->escape($email).'")') or error('Could not add new user to the database.', __FILE__, __LINE__);

		// Return the ID of the added user
		return $this->db->insert_id();
	}

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
			or error('User data could not be updated: '.$this->db->error(), __FILE__, __LINE__);
	}

	// Remove a user
	public function remove($id)
	{
		// Check if user exists
		$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users WHERE id='.intval($id).' LIMIT 1')
			or error('Could not check user existance.', __FILE__, __LINE__);
		$fetch = $this->db->fetch_row($result);

		if (!$fetch)
			return false;

		// Remove user
		return $this->db->query('DELETE FROM '.DB_PREFIX.'users WHERE id='.intval($id))
			or error('Could not delete user with ID number, '.intval($id).'.', __FILE__, __LINE__);
	}
}
