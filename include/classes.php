<?php

# =============================================================================
# include/classes.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// This is a wrapper class for PDO
class db
{
	static public $connected = false, $c = false;

	static public function initialize($db_type, $db_host, $db_name, $db_user, $db_password)
	{
		if (self::$connected === true)
			return false;

		try
		{
			switch ($db_type)
			{
				case 'mysql':
					self::$c = new PDO('mysql:host='.$db_host.';dbname='.$db_name, $db_user, $db_password,
						array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
					break;
				case 'pgsql':
					self::$c = new PDO('pgsql:host='.$db_host.';dbname='.$db_name, $db_user, $db_password);
					break;
				case 'sqlite2':
					self::$c = new PDO('sqlite2:'.$db_name);
					break;
				case 'sqlite':
					self::$c = new PDO('sqlite:'.$db_name);
					break;
				default:
					error('There is no support for the specified database type, "'.$db_type.'".');
			}
		}
		catch (PDOException $e)
		{
			error($e->getMessage(), __FILE__, __LINE__);
		}

		self::$connected = true;
	}

	static public function close()
	{
		self::$c = null;
	}
}

// The user class
class user
{
	static public $logged_in = false, $data = false;

	// Check user cookie
	static public function initialize()
	{
		if (($cookie = get_cookie('user')) !== false)
		{
			$result = db::$c->query('SELECT id, group_id, username, salt, hash FROM '.DB_PREFIX.'users WHERE id='.intval($cookie['id']).' LIMIT 1')
				or error('Could not fetch user information.', __FILE__, __LINE__);
			self::$data = $result->fetch(PDO::FETCH_ASSOC);

			if (self::$data !== false)
			{
				if ($cookie['key'] == sha1(self::$data['salt'].self::$data['hash']))
					self::$logged_in = true;
			}
		}
	}

	/* Log in. Returns true on success and int when an error occurs.
	   2 = already logged in, 3 = username or password not provided,
	   4 = wrong password, 5 = wrong username. */
	static public function login($username, $password)
	{
		if (get_cookie('user') !== false && self::$logged_in === true)
			return 2;

		if (empty($username) || empty($password))
			return 3;

		$username = trim(db::$c->quote($username));
		$password = trim($password);

		$result = db::$c->query('SELECT id, password, salt, hash FROM '.DB_PREFIX.'users WHERE username='.$username.' LIMIT 1')
			or error('Could not fetch login information.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

		if ($fetch !== false)
		{
			list($user_id, $user_password, $user_salt, $user_hash) = $fetch;

			if ($user_password == generate_hash($password, $user_salt))
			{
				// 1209600: 2 weeks - 43200: 12 hours
				set_cookie('user', array('id' => $user_id, 'key' => sha1($user_salt.$user_hash)), time() + 1209600);
			}
			else
				return 4;
		}
		else
			return 5;

		return true;
	}

	// Log the user out by letting the cookie expire
	static public function logout()
	{
		set_cookie('user', null, time()-3600);
	}

	// Add a new user to the database
	// TODO: Test this function
	public static function add($group_id, $username, $password, $email)
	{
		$salt = generate_salt();
		$password = generate_hash($password, $salt);
		$hash = generate_hash($username, $salt);

		db::$c->exec('
			INSERT INTO '.DB_PREFIX.'users
				(group_id, username, password, salt, hash, email)
			VALUES(
				'.intval($group_id).',
				\''.sys_db::escape($username).'\',
				\''.sys_db::escape($password).'\',
				\''.sys_db::escape($salt).'\',
				\''.sys_db::escape($hash).'\',
				\''.sys_db::escape($email).'\')') or error('Could not add new user to the database.', __FILE__, __LINE__);

		return db::$c->lastInsertId();
	}

	/* Removes the user with the given ID number.
	   If the action fails, false is returned
	   True is returned upon success. */
	// TODO: Test this function
	static public function remove($id)
	{
		$result = $sys_db->query('SELECT id FROM '.DB_PREFIX.'users WHERE id='.intval($id).' LIMIT 1')
			or error('Could not check user existance.', __FILE__, __LINE__);
		$fetch = $result->fetch(PDO::FETCH_NUM);

		if ($fetch === false)
			return false;

		db::$c->exec('DELETE FROM '.DB_PREFIX.'users WHERE id='.intval($id))
			or error('Could not delete user with ID number, '.intval($id).'.', __FILE__, __LINE__);

		return true;
	}
}

// The template class
class tpl
{
	static private $vars = array();

	static public function set($ident, $value)
	{
		self::$vars[$ident] = $value;
	}

	static public function get($ident)
	{
		return isset(self::$vars[$ident]) ? self::$vars[$ident] : false;
	}

	static public function clear()
	{
		self::$vars = array();
	}

	static public function url($relative_path='')
	{
		echo SYSTEM_BASE_URL.'/'.(REWRITE_URL === false ? '?q=' : null).$relative_path;
	}

	static public function render($template_name, $clear = true)
	{
		if (file_exists(SYS_TEMPLATE.'/'.$template_name.'.php'))
		{
			extract(self::$vars);
			ob_start();

			require SYS_TEMPLATE.'/'.$template_name.'.php';

			return ob_get_clean();
		}
		else
			error('Template could not be found!', __FILE__, __LINE__);

		if ($clear === true)
			self::clear();
	}
}
