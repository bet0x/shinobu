<?php

# =============================================================================
# include/modules/user.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class user
{
	private $data_fields = array('id', 'username', 'password', 'salt', 'hash', 'email'),
	        $authenticated = false, $data = array();
	private $dbc = false;

	public function __construct()
	{
		$this->dbc = utils::load_module('dbc');
	}

	// Check user cookie (only affects the current user/visitor)
	public function authenticate()
	{
		if (($cookie = utils::get_cookie('user')) !== false)
		{
			// Get user data
			$result = $this->dbc->query('SELECT id, username, salt, hash, email FROM '.DB_PREFIX.'users WHERE id='.intval($cookie['id']).' LIMIT 1')
				or error('Could not fetch user information.', __FILE__, __LINE__);
			$this->data = $result->fetch(PDO::FETCH_ASSOC);

			if ($this->data !== false)
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
		if (utils::get_cookie('user') !== false && $this->authenticated)
			return 2;

		// Escape username and password
		$username = trim($this->dbc->quote($username));
		$password = trim($password);

		// Check if user exists and fetch data
		$result = $this->dbc->query('SELECT id, password, salt, hash FROM '.DB_PREFIX.'users WHERE username='.$username.' LIMIT 1')
			or error('Could not fetch login information.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

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
	public function add($username, $password, $email)
	{
		// Create hashes for the password
		$salt = generate_salt();
		$password = generate_hash($password, $salt);
		$hash = generate_hash(generate_salt(), $salt);

		$this->dbc->exec('
			INSERT INTO '.DB_PREFIX.'users
				(username, password, salt, hash, email)
			VALUES(
				'.$this->dbc->quote($username).',
				'.$this->dbc->quote($password).',
				'.$this->dbc->quote($salt).',
				'.$this->dbc->quote($hash).',
				'.$this->dbc->quote($email).')') or error('Could not add new user to the database.', __FILE__, __LINE__);

		// Return the ID of the added user
		return $this->dbc->lastInsertId();
	}

	/* Returns all the stored user data when no arguments are given.  When an argument (or more)
	   only the selected data will be returned. When the requested data was not stored it will
	   be fetched from the database.
	*/
	public function data()
	{
		if (($func_num_args = func_num_args()) > 0)
		{
			$extra_data = func_get_args();

			// First check if the requested data is already available
			if (count(array_diff($extra_data, array_keys($this->data))) < 1)
			{
				if ($func_num_args === 1)
					return $this->data[$extra_data[0]];
				else
				{
					$selected_data = array();

					foreach ($extra_data as $k)
						$selected_data[$k] = $this->data[$k];

					return $selected_data;
				}
			}

			// Check if fields are valid
			if (count(array_diff($extra_data, $this->data_fields)) > 0)
				return false;

			$extra_data = implode(', ', $extra_data);

			// Fetch user data
			$result = $this->dbc->query('SELECT '.$extra_data.' FROM '.DB_PREFIX.'users WHERE id='.intval($this->data['id']).' LIMIT 1')
				or error('Could not fetch user data.', __FILE__, __LINE__);
			$db_data = $result->fetch(PDO::FETCH_ASSOC);

			// Store the user data
			if ($db_data)
				$this->data = array_merge($this->data, $db_data);

			return $db_data;
		}

		return $this->data;
	}

	// Update user data
	public function update($id, $new_data = array())
	{
		if (count(array_diff(array_keys($new_data), $this->data_fields)) > 0)
			return false;

		$keys = $values = array();

		// Create hashes when the password is updated
		if (isset($new_data['password']))
		{
			$new_data['salt'] = generate_salt();
			$new_data['password'] = generate_hash($new_data['password'], $salt);
			$new_data['hash'] = generate_hash(generate_salt(), $salt);
		}

		// Generate the keys for the query
		foreach($new_data as $k => $v)
		{
			$keys[] = $k.'=:'.$k;
			$values[':'.$k] = $v;
		}
		$values[':user_id'] = $id;

		// Execute query
		$sql = 'UPDATE '.DB_PREFIX.'users SET '.implode(', ', $keys).' WHERE id=:user_id';
		$sth = $this->dbc->prepare($sql);
		$sth->execute($values);
	}

	// Remove a user
	public function remove($id)
	{
		// Check if user exists
		$result = $this->dbc->query('SELECT id FROM '.DB_PREFIX.'users WHERE id='.intval($id).' LIMIT 1')
			or error('Could not check user existance.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

		if (!$fetch)
			return false;

		// Remove user
		$this->dbc->exec('DELETE FROM '.DB_PREFIX.'users WHERE id='.intval($id))
			or error('Could not delete user with ID number, '.intval($id).'.', __FILE__, __LINE__);

		return true;
	}
}
