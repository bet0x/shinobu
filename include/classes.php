<?php

# =============================================================================
# include/classes.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// The system class processes the request and creates a controller object
// and executes the right controller method (GET, POST etc.).
// All variables prefixed with an _ (underscore) are for internal use.
class system
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

	// Find controller and return the path
	static private function _find_controller($request)
	{
		if ($request === false)
		{
			return SYS_CONTROL.'/default.php';
		}
		else
		{
			$include_dir = SYS_CONTROL;
			$has_looped = false; // Necessary to loop at least once not to get
			                     // default controller if there is actually no match.

			foreach ($request as $include)
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
	static private function _parse_request()
	{
		if (!isset($_GET['q']) || empty($_GET['q']))
			return false;

		$request_path = str_replace('..', '', trim($_GET['q'], '/ '));
		return explode('/', $request_path);
	}

	// Set 404 page
	static public function set_fourofour($contents)
	{
		self::$_custom_fourofour = $contents;
	}

	// Send 404 page
	static public function send_fourofour()
	{
		header('HTTP/1.1 404 Not Found', true, 404);

		if (self::$_custom_fourofour === false)
			return 'Didn\'t found the page you requested.';
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

	// Prepare
	static public function prepare()
	{
		self::$request = self::_parse_request();
		self::$controller_path = self::_find_controller(self::$request);
	}

	// Run, Forrest, run!!
	static public function run()
	{
		if (self::$controller_path !== false)
		{
			require self::$controller_path;

			$class_name = pathinfo(self::$controller_path, PATHINFO_FILENAME).'_controller';
			$request_type = self::_get_request_method();

			if ($request_type == 'POST')
				$args =& $_POST;
			else
				$args = null;

			$c_instance = new $class_name(self::$request);
			echo $c_instance->$request_type($args);
		}
		else
			echo self::send_fourofour();
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

// This is a wrapper class for PDO
// It's possible that this wrapper is going to be replaced in the future
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

	/* Log in. Returns 1 on success and the following when an error occurs.
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

		return 1;
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
				'.db::$c->quote($username).',
				'.db::$c->quote($password).',
				'.db::$c->quote($salt).',
				'.db::$c->quote($hash).',
				'.db::$c->quote($email).')') or error('Could not add new user to the database.', __FILE__, __LINE__);

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

// Extension class
class extensions
{
	static private $initialize = false, $hooks = array();
	static public $extensions = array();

	static public function initialize()
	{
		if (self::$initialize === true)
			return false;
		else
			self::$initialize = true;

		$request_path = system::$request === false || empty(system::$request[0]) ? '/' : implode('/', system::$request);

		$result = db::$c->query('
			SELECT f.file, e.dir FROM '.DB_PREFIX.'ext_files AS f, '.DB_PREFIX.'extensions AS e
			WHERE f.extension_id=e.id AND e.enabled=1 AND f.request_path='.db::$c->quote($request_path).'', PDO::FETCH_ASSOC);

		foreach ($result as $row)
		{
			require SYS_EXTENSION.'/'.$row['dir'].'/r_'.$row['file'].'.php';
			self::$extensions[] = $row['dir'];
		}

		//system::$request;
	}

	static public function add_action($hook, $function, $priority = 100, $accepted_args = 1)
	{
		$action_id = md5(implode(func_get_args()));
		self::$hooks[$hook][$priority][$action_id] = array($function, $accepted_args);

		return $action_id;
	}

	// Example: $actions = array(array('hook' => '', 'function' => '', 'priority' => 100, 'accepted_args' => 1));
	static public function add_actions($actions)
	{
		$action_ids = array();

		if (count($actions) > 0)
		{
			foreach ($actions as $k => $action)
			{
				$action_ids[$k] = md5(implode($action));
				self::$hooks[$action['hook']][$action['priority']][$action_ids[$k]] = array($action['function'], $action['accepted_args']);
			}
		}

		return $action_ids;
	}

	static public function remove_action($hook, $priority, $action_id)
	{
		unset(self::$hooks[$hook][$priority][$action_id]);
	}

	static public function run_hook($hook)
	{
		if (!array_key_exists($hook, self::$hooks))
			return;

		krsort(self::$hooks[$hook]);

		if (func_num_args() > 1)
			$args = array_slice(func_get_args(), 1);
		else
			$args = null;

		foreach(self::$hooks[$hook] as $k => $actions)
		{
			// echo '<pre>', $k, ' - ', print_r($actions, true), '</pre><br />'; // For testing

			foreach ($actions as $function)
			{
				call_user_func_array($function[0], array_slice($args, 0, $function[1]));
			}
		}
	}
}

// Cache class
class cache
{
	static public function exists($filename)
	{
		return is_file(SYS_CACHE.'/'.$filename);
	}

	static public function store($filename, $data)
	{
		$handle = fopen(SYS_CACHE.'/'.$filename, 'wb');

		if (!$handle)
			error('Can not open cache file: '.$filename, __FILE__, __LINE__);

		fwrite($handle, $data);
		fclose($handle);

		return true;
	}

	static public function get($filename)
	{
		if (!self::exists($filename))
			error('The file, "'.$filename.'", is not cached.', __FILE__, __LINE__);

		return file_get_contents(SYS_CACHE.'/'.$filename);
	}

	static public function path($filename)
	{
		if (!self::exists($filename))
			error('The file, "'.$filename.'", is not cached.', __FILE__, __LINE__);

		return SYS_CACHE.'/'.$filename;
	}

	static public function clear($pattern = '.*')
	{
		$glob = glob(SYS_CACHE.'/'.$pattern);

		if (!is_array($glob))
			error('The pattern, "'.$pattern.'", is not valid.', __FILE, __LINE__);

		foreach ($glob as $filename)
			unlink($filename);

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

	static public function url($relative_path = null)
	{
		echo SYSTEM_BASE_URL.'/'.(REWRITE_URL === false ? '?q=' : null).$relative_path;
	}

	static public function render($template_name, $local_vars=false, $clear = true)
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

		if ($clear === true)
			self::clear();
	}
}
