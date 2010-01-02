<?php

# =============================================================================
# include/classes.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// Note: All methods that are prefixed with an _ are meant for internal use.
// That means they are not used outside the class.

// This class handles the request.  It calls the controller and the
// method/function that fits the request method.
class request
{
	static public $request = false, $controller_path = false;

	// Find controller and return the path
	static private function _find_controller($request_path)
	{
		if (!$request_path)
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
	static private function _parse_request_string()
	{
		if (!isset($_GET['q']) || empty($_GET['q']))
			return false;

		$request_path = str_replace('..', '', trim($_GET['q'], '/ '));
		return explode('/', $request_path);
	}

	static public function answer()
	{
		$request = self::_parse_request_string();
		$controller_path = self::_find_controller($request);

		if (!$controller_path)
		{
			global $SYSTEM_DEFAULT_CONTROLLER;

			$controller_instance = new $SYSTEM_DEFAULT_CONTROLLER($request);
			return $controller_instance->send_error(404);
		}

		require $controller_path;

		$class_name = pathinfo($controller_path, PATHINFO_FILENAME).'_controller';
		$request_type = self::_get_request_method();

		if ($request_type == 'POST')
			$args =& $_POST;
		else
			$args = null;

		$controller_instance = new $class_name($request);
		return $controller_instance->$request_type($args);
	}
}

// A base class for controllers
// This class contains all the supported requests methods
// Sublassing is also possible
class BaseController
{
	protected $request = false;
	protected $_mimetypes = array(
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
		'jpg'   => 'image/jpeg');
	protected $_status_codes = array(
		100 => 'Continue',
		101 => 'Switching Protocols',
		200 => 'OK',
		201 => 'Created',
		202 => 'Accepted',
		203 => 'Non-Authoritative Information',
		204 => 'No Content',
		205 => 'Reset Content',
		206 => 'Partial Content',
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		306 => '(Unused)',
		307 => 'Temporary Redirect',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Timeout',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Long',
		415 => 'Unsupported Media Type',
		416 => 'Requested Range Not Satisfiable',
		417 => 'Expectation Failed',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported');

	public function __construct($request)
	{
		$this->request = $request;
		$this->prepare();
	}

	protected function prepare()
	{
		// This is an empty function that's always executed by the constructor
	}

	// Send content type header
	protected function set_mimetype($type)
	{
		if (array_key_exists($type, $this->_mimetypes))
			header('Content-type: '.$this->_mimetypes.'; charset=utf-8');
		else
			header('Content-type: text/plain; charset=utf-8');
	}

	public function send_error($status_code)
	{
		if (!array_key_exists($status_code, $this->_status_codes))
			$status_code = 500;

		header('HTTP/1.1 '.$status_code.' '.$this->_status_codes[$status_code]);
		$this->set_mimetype('text');

		return $status_code.': '.$this->_status_codes[$status_code];
	}

	protected function redirect($location)
	{
		header('location: '.$location); exit;
	}

	public function GET($args) { return $this->send_error(405); }
	public function POST($args) { return $this->send_error(405); }
	public function PUT($args) { return $this->send_error(405); }
	public function DELETE($args) { return $this->send_error(405); }
	public function HEAD($args) { return $this->send_error(405); }
	public function AJAX($args) { return $this->send_error(405); }
}

abstract class BaseWebController extends BaseController
{
	protected $request = false;

	public function __construct($request)
	{
		parent::__construct($request);
		$this->set_mimetype('html');

		tpl::set('website_title', 'Shinobu');
	}
}

// This is a wrapper class for PDO
// It's possible that this wrapper is going to be replaced in the future
class db
{
	static public $connected = false, $c = false;

	static public function connect($db_type, $db_host, $db_name, $db_user, $db_password)
	{
		if (self::$connected)
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

// A class with web related functions
class utils
{
	static private $_xsrf_token = false;

	static function xsrf_token()
	{
		if (($token = utils::get_cookie('xsrf')) !== false)
			self::$_xsrf_token =& $token;
		elseif (!self::$_xsrf_token)
		{
			self::$_xsrf_token = generate_hash(generate_salt());
			utils::set_cookie('xsrf', self::$_xsrf_token);
		}

		return self::$_xsrf_token;
	}

	static function check_xsrf_cookie($token)
	{
		if (!self::$_xsrf_token)
			self::xsrf_token();

		return $token == self::$_xsrf_token;
	}

	static function xsrf_form_html()
	{
		if (!self::$_xsrf_token)
			self::xsrf_token();

		return '<input type="hidden" name="xsrf_token" value="'.self::$_xsrf_token.'" />';
	}

	// Set a cookie
	static public function set_cookie($name, $value, $expire = 0)
	{
		global $sys_cookie_name, $sys_cookie_path, $sys_cookie_domain, $sys_cookie_secure;

		header('P3P: CP="CUR ADM"'); // Enable sending of a P3P header

		if (version_compare(PHP_VERSION, '5.2.0', '>='))
			setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path, $sys_cookie_domain, $sys_cookie_secure, true);
		else
			setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path.'; HttpOnly', $sys_cookie_domain, $sys_cookie_secure);
	}

	// Get a cookie
	static public function get_cookie($name)
	{
		global $sys_cookie_name;

		return isset($_COOKIE[$sys_cookie_name.'_'.$name]) ? unserialize($_COOKIE[$sys_cookie_name.'_'.$name]) : false;
	}

	static public function url($relative_path = null)
	{
		return SYSTEM_BASE_URL.'/'.(REWRITE_URL ? '' : '?q=').$relative_path;
	}

	static public function static_url($file_path)
	{
		return SYSTEM_BASE_URL.'/static/'.$file_path.'?v='.filemtime(SYS_STATIC.'/'.$file_path);
	}
}
