<?php

# =============================================================================
# include/classes.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// Note: All methods that are prefixed with an _ are meant for internal use.
// That means they are not used outside the class.

// This class handles the request.  It processes the request string and calls
// the controller and the method that needs to respond to the request.
class Application
{
	public $output = '';
	private $request_methods = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD');


	public function __construct()
	{
		$request = array('path' => false, 'args' => false);
		$request_type = 'GET';
		$controller_path = false;

		// Parse request string
		if (isset($_GET['q'][0]))
		{
			$request_string = str_replace(array('%', '..'), '', trim($_GET['q'], '/ '));

			if (strpos($request_string, ':') !== false)
				list($request['path'], $request['args']) = explode(':', $request_string, 2);
			else
				$request['path'] =& $request_string;
		}

		// Look for the controller
		if (!$request['path'])
			$controller_path = SYS_CONTROL.'/default.php';
		else
		{
			if (file_exists(SYS_CONTROL.'/'.$request['path'].'.php'))
				$controller_path = SYS_CONTROL.'/'.$request['path'].'.php';
			elseif (file_exists(SYS_CONTROL.'/'.$request['path'].'/default.php'))
				$controller_path = SYS_CONTROL.'/'.$request['path'].'/default.php';
		}

		// Send a 404 error when the controller could not be found
		if (!$controller_path)
		{
			global $SYSTEM_DEFAULT_CONTROLLER;

			$controller_instance = new $SYSTEM_DEFAULT_CONTROLLER($request);
			return $controller_instance->send_error(404);
		}

		// Include the controller file
		require $controller_path;

		// Resolve class name and request method
		$class_name = pathinfo($controller_path, PATHINFO_FILENAME).'_controller';

		// Get request method (Default is GET)
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			$request_type = 'AJAX';
		elseif (in_array($_SERVER['REQUEST_METHOD'], $this->request_methods))
			$request_type = $_SERVER['REQUEST_METHOD'];

		// Set controller arguments
		if ($request_type == 'POST')
			$args =& $_POST;
		else
			$args = null;

		// Start the controller
		$controller_instance = new $class_name($request);

		if ($controller_instance->interrupt)
			$this->output = $controller_instance->pre_output;
		else
			$this->output = $controller_instance->$request_type($args);
	}
}

// The template class
class tpl
{
	static private $vars = array();

	// Set a variable
	static public function set($ident, $value)
	{
		self::$vars[$ident] = $value;
	}

	// Get a variable.  Returns false if it doesn't exist.
	static public function get($ident)
	{
		return isset(self::$vars[$ident]) ? self::$vars[$ident] : false;
	}

	// Clear all variables
	static public function clear()
	{
		self::$vars = array();
	}

	// Render the template
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

	// Generate an XSRF token and store it in a cookie, but first check if the
	// cookie already exists or if the token is already generated.  Then return it.
	// See: http://en.wikipedia.org/wiki/Cross-site_request_forgery
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

	// Compare $token with the XSRF token.  Generate an XSRF token if self::$_xsrf_token
	// is false.
	static function check_xsrf_cookie($token)
	{
		if (!self::$_xsrf_token)
			self::xsrf_token();

		return $token == self::$_xsrf_token;
	}

	// Return a hidden form field with the XSRF token.  Generate an XSRF token
	// if self::$_xsrf_token is false.
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

	// Generate and return an url to a controller
	static public function url($relative_path = null)
	{
		return SYSTEM_BASE_URL.'/'.(REWRITE_URL ? '' : '?q=').$relative_path;
	}

	// Append a ?v=<timestamp of last modification> to a static file
	static public function static_url($file_path)
	{
		return SYSTEM_BASE_URL.'/static/'.$file_path.'?v='.filemtime(SYS_STATIC.'/'.$file_path);
	}
}

// A base class for controllers
// This class contains all the supported requests methods
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
	public $interrupt = false, $pre_output = false;

	public function __construct($request)
	{
		$this->request = $request;
		$this->pre_output = $this->prepare();
	}

	/* This is an empty function that's always executed by the constructor of
	   the base controller.  This function can be overwritten to execute  or
	   process certin things before the request method function is executed. */
	protected function prepare()
	{
		return;
	}

	protected function load_module($name, $args = null)
	{
		static $modules = array();

		if (isset($modules[$name]))
			return $modules[$name];

		if (!file_exists(SYS_INCLUDE.'/modules/'.$name.'.php'))
			return false;

		require SYS_INCLUDE.'/modules/'.$name.'.php';

		$modules[$name] = new $name($args);
		return $modules[$name];
	}

	// Send content type header
	protected function set_mimetype($type)
	{
		if (isset($this->_mimetypes[$type]))
			header('Content-type: '.$this->_mimetypes.'; charset=utf-8');
		else
			header('Content-type: text/plain; charset=utf-8');
	}

	// Send an error to the client (e.g. 404, 500)
	public function send_error($status_code)
	{
		if (!isset($this->_status_codes[$status_code]))
			$status_code = 500;

		header('HTTP/1.1 '.$status_code.' '.$this->_status_codes[$status_code]);
		$this->set_mimetype('text');

		return $status_code.': '.$this->_status_codes[$status_code];
	}

	// Redirect a client to an other URL
	protected function redirect($location)
	{
		header('location: '.$location); exit;
	}

	/* Be default all request method function return a 405 (Method Not Allowed)
	   error. Controllers should extend the BaseController and overwrite these
	   function. */
	public function GET($args) { return $this->send_error(405); }
	public function POST($args) { return $this->send_error(405); }
	public function PUT($args) { return $this->send_error(405); }
	public function DELETE($args) { return $this->send_error(405); }
	public function HEAD($args) { return $this->send_error(405); }
	public function AJAX($args) { return $this->send_error(405); }
}

// A controller for web pages with user authentication enabled
abstract class AuthWebController extends BaseController
{
	public function __construct($request)
	{
		$this->request = $request;
		$this->set_mimetype('html');

		// Load modules
		$this->db = $this->load_module('db');

		$this->config = $this->load_module('config', $this->db);
		$this->user = $this->load_module('user', $this->db);
		$this->acl = $this->load_module('acl', $this->db);

		$authenticated = $this->user->authenticated();

		// Set some template variables
		tpl::set('website_title', $this->config->website_title);
		tpl::set('authenticated', $authenticated);

		// Do some extra things for authenticated users
		if ($authenticated)
		{
			$this->acl->set_gid($this->user->data['group_id']);

			tpl::set('username', $this->user->data['username']);
			tpl::set('admin_view', $this->acl->check('administration', ACL_PERM_1));
		}

		// Testing
		/*$this->acl->set('administration', $this->acl->get('administration')
			 | ACL_PERM_3 | ACL_PERM_4 | ACL_PERM_5
			 | ACL_PERM_6 | ACL_PERM_7 | ACL_PERM_8);
		echo '<pre>';
		print_r($this->acl->get('administration'));
		echo '</pre>';
		echo '<pre>';
		print_r($this->acl->check('administration', ACL_PERM_1));
		echo '</pre>';*/

		$this->pre_output = $this->prepare();
	}
}
