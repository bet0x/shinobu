<?php

# =============================================================================
# include/classes.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class request
{
	static public $request = false, $controller_path = false;
	static private $_custom_fourofour = false;
	static private $mimetypes = array(
		'text'  => 'text/plain',
		'html'  => 'text/html',
		'xml'   => 'text/xml',
		'xhtml' => 'application/xhtml+xml',
		'atom'  => 'application/atom+xml',
		'rss'   => 'application/rss+xml',
		'json'  => 'application/json',
		'svg'   => 'image/svg+xml',
		'gif'   => 'image/gif',
		'png'   => 'image/png',
		'jpg'   => 'image/jpeg'
		);
	static private $status_codes = array(
		303 => 'HTTP/1.1 See Other',
		400 => 'HTTP/1.1 Bad Request',
		401 => 'HTTP/1.1 Unauthorized',
		402 => 'HTTP/1.1 Payment Required',
		403 => 'HTTP/1.1 Forbidden',
		404 => 'HTTP/1.1 Not Found',
		405 => 'HTTP/1.1 Method Not Allowed',
		406 => 'HTTP/1.1 Not Acceptable',
		408 => 'HTTP/1.1 Request Timeout',
		409 => 'HTTP/1.1 Conflict',
		410 => 'HTTP/1.1 Gone'
		);

	// Find controller and return the path
	static private function _find_controller($request_path)
	{
		if ($request_path === false)
		{
			return SYS_CONTROL.'/default.php';
		}
		else
		{
			$include_dir = SYS_CONTROL;
			$has_looped = false;

			foreach ($request_path as $include)
			{
				if (is_file($include_dir.'/'.$include.'.php'))
					return $include_dir.'/'.$include.'.php';
				else if (is_dir($include_dir.'/'.$include))
					$include_dir .= '/'.$include;
				else
					break;

				$has_looped = true;
			}

			if ($has_looped && is_file($include_dir.'/default.php'))
				return $include_dir.'/default.php';
		}

		return false;
	}

	// Get request type (POST or GET) (defaults to GET)
	static private function _get_request_method()
	{
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return 'AJAX';

		switch ($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':
				$request_method = 'GET';
				break;
			case 'POST':
				$request_method = 'POST';
				break;
			case 'PUT':
				$request_method = 'PUT';
				break;
			case 'DELETE':
				$request_method = 'DELETE';
				break;
			case 'HEAD':
				$request_method = 'HEAD';
				break;
			default:
				$request_method = 'GET';
				break;
		}

		return $request_method;
	}

	// Get the contents of $_GET['q'] and filter and split it.
	// If $_GET['q'] is not set false is returned
	static private function _parse_request()
	{
		if (!isset($_GET['q']) || empty($_GET['q']))
			return false;

		$request_path = str_replace('..', '', trim($_GET['q'], '/ '));
		return explode('/', $request_path);
	}

	// Send status header
	static public function set_status($status_code)
	{
		if (array_key_exists($status_code, self::$status_codes))
		{
			header(self::$status_codes[$status_code]);
			return true;
		}
		else
			return false;
	}

	// Set 404 page
	static public function set_fourofour($contents)
	{
		self::$_custom_fourofour = $contents;
	}

	// Set 404 page
	static public function send_fourofour()
	{
		self::set_status(404);

		if (self::$_custom_fourofour === false)
			return 'The page you requested could not be found.';
		else
			return self::$_custom_fourofour;
	}

	// Send content type header
	static public function set_mimetype($type='html')
	{
		if (in_array($type, self::$mimetypes))
			header('Content-type: '.self::$mimetypes[$type].'; charset=utf-8');
		else
			header('Content-type: text/html; charset=utf-8');
	}

	static public function answer()
	{
		$request = self::_parse_request();
		$controller_path = self::_find_controller($request);

		if (!$controller_path)
			return self::send_fourofour();

		require $controller_path;

		$class_name = pathinfo($controller_path, PATHINFO_FILENAME).'_controller';
		$request_type = self::_get_request_method();

		if ($request_type == 'POST')
			$args =& $_POST;
		else
			$args = null;

		// Start de controller
		if (!class_exists($class_name))
			return self::send_fourofour();

		$controller_instance = new $class_name($request);
		return $controller_instance->$request_type($args);
	}
}

// Abstract class for controllers
// This class contains all the supported requests methods
// Sublassing is also possible
abstract class BaseController
{
	protected $request = false;

	public function __construct($request)
	{
		$this->request = $request;
	}

	public function GET($args) {}
	public function POST($args) {}
	public function PUT($args) {}
	public function DELETE($args) {}
	public function HEAD($args) {}
	public function AJAX($args) {}
}

abstract class BaseWebController extends BaseController
{
	protected $request = false;

	public function __construct($request)
	{
		parent::__construct($request);
		tpl::set('website_title', 'Shinobu');

		request::set_mimetype('html');
	}
}

// This is a wrapper class for PDO
// It's possible that this wrapper is going to be replaced in the future
class db
{
	static public $connected = false, $c = false;

	static public function connect($db_type, $db_host, $db_name, $db_user, $db_password)
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
	static private $user_fields = array('id', 'username', 'password', 'salt', 'hash', 'email');

	// Check user cookie
	// Only affects the current user/visitor
	static public function initialize()
	{
		if (($cookie = get_cookie('user')) !== false)
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
		if (get_cookie('user') !== false && self::$logged_in)
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
		set_cookie('user', array('id' => $user_id, 'key' => sha1($user_salt.$user_hash)), time() + 1209600);

		return 1;
	}

	// Let the user cookie expire
	// Only affects the current user/visitor
	static public function logout()
	{
		set_cookie('user', null, time()-3600);
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

	static public function url($relative_path = null, $return = false)
	{
		if ($return)
			return SYSTEM_BASE_URL.'/?q='.$relative_path;

		echo SYSTEM_BASE_URL.'/?q='.$relative_path;
	}

	static public function render($template_name, $local_vars = false, $clear = true)
	{
		if (file_exists(SYS_TEMPLATE.'/'.$template_name.'.php'))
		{
			if (is_array($local_vars))
				self::$vars = array_merge(self::$vars, $local_vars);

			extract(self::$vars);
			ob_start();

			require SYS_TEMPLATE.'/'.$template_name.'.php';

			return ob_get_clean();
		}
		else
			error('Template could not be found!', __FILE__, __LINE__);

		if ($clear)
			self::clear();
	}
}
