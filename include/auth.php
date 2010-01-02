<?php

# =============================================================================
# include/auth.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// The user class
class user
{
	static public $logged_in = false, $data = false;
	static private $user_fields = array('id', 'username', 'password', 'salt', 'hash', 'email');

	// Check user cookie
	// Only affects the current user/visitor
	static public function authenticate()
	{
		if (($cookie = utils::get_cookie('user')) !== false)
		{
			// Get user data
			$result = db::$c->query('SELECT id, username, salt, hash, email FROM '.DB_PREFIX.'users WHERE id='.intval($cookie['id']).' LIMIT 1')
				or error('Could not fetch user information.', __FILE__, __LINE__);
			self::$data = $result->fetch(PDO::FETCH_ASSOC);

			if (self::$data !== false)
			{
				// Check cookie key
				if ($cookie['key'] == sha1(self::$data['salt'].self::$data['hash']))
					self::$logged_in = true;
			}
		}
	}

	// Get more data of the user
	static public function get_info($id, $fields = array(), $store = false)
	{
		if (count(array_diff($fields, self::$user_fields)) > 0)
			return false;

		$fields = implode(', ', $fields);

		// Fetch user data
		$result = db::$c->query('SELECT '.$fields.' FROM '.DB_PREFIX.'users WHERE id='.intval($id).' LIMIT 1')
			or error('Could not fetch user data.', __FILE__, __LINE__);

		return $result->fetch(PDO::FETCH_ASSOC);
	}

	/* Create a login cookie for the user
	   Only affects the current user/visitor
	   1 = successful login, 2 = already logged in,
	   3 = user does not exist, 4 = wrong password */
	static public function login($username, $password)
	{
		if (utils::get_cookie('user') !== false && self::$logged_in)
			return 2;

		// Escape username and password
		$username = trim(db::$c->quote($username));
		$password = trim($password);

		// Fetch user data
		$result = db::$c->query('SELECT id, password, salt, hash FROM '.DB_PREFIX.'users WHERE username='.$username.' LIMIT 1')
			or error('Could not fetch login information.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

		if (!$fetch)
			return 3;

		// Check password hashes
		list($user_id, $user_password, $user_salt, $user_hash) = $fetch;

		if ($user_password != generate_hash($password, $user_salt))
			return 4;

		// 1209600: 2 weeks - 43200: 12 hours
		utils::set_cookie('user', array('id' => $user_id, 'key' => sha1($user_salt.$user_hash)), time() + 1209600);

		return 1;
	}

	// Let the user cookie expire
	// Only affects the current user/visitor
	static public function logout()
	{
		utils::set_cookie('user', null, time()-3600);
	}

	// Add new user
	public static function add($username, $password, $email)
	{
		// Create hashes for the password
		$salt = generate_salt();
		$password = generate_hash($password, $salt);
		$hash = generate_hash($username, $salt);

		db::$c->exec('
			INSERT INTO '.DB_PREFIX.'users
				(username, password, salt, hash, email)
			VALUES(
				'.db::$c->quote($username).',
				'.db::$c->quote($password).',
				'.db::$c->quote($salt).',
				'.db::$c->quote($hash).',
				'.db::$c->quote($email).')') or error('Could not add new user to the database.', __FILE__, __LINE__);

		// Return the ID of the added user
		return db::$c->lastInsertId();
	}

	// Update user data
	// Warning: `$keys` is not escaped
	public static function update($id, $data = array())
	{
		$keys = $values = array();

		// Create hashes when the password is updated
		if (isset($data['password']))
		{
			$data['salt'] = generate_salt();
			$data['password'] = generate_hash($password, $salt);
			$data['hash'] = generate_hash($username, $salt);
		}

		// Generate the keys for the query
		foreach($data as $k => $v)
		{
			$keys[] = $k.'=:'.$k;
			$values[':'.$k] = $v;
		}
		$values[':user_id'] = $id;

		// Execute query
		$sql = 'UPDATE '.DB_PREFIX.'users SET '.implode(', ', $keys).' WHERE id=:user_id';
		$sth = db::$c->prepare($sql);
		$sth->execute($values);
	}

	// Remove a user
	static public function remove($id)
	{
		$result = db::$c->query('SELECT id FROM '.DB_PREFIX.'users WHERE id='.intval($id).' LIMIT 1')
			or error('Could not check user existance.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

		if ($fetch === false)
			return false;

		db::$c->exec('DELETE FROM '.DB_PREFIX.'users WHERE id='.intval($id))
			or error('Could not delete user with ID number, '.intval($id).'.', __FILE__, __LINE__);

		return true;
	}
}
