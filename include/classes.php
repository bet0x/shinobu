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
class request
{
	static public $request = false, $controller_path = false;
	static private $request_methods = array('GET', 'POST', 'PUT', 'DELETE', 'HEAD');

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
				if (file_exists($include_dir.'/'.$include.'.php'))
					return $include_dir.'/'.$include.'.php';
				else if (is_dir($include_dir.'/'.$include))
					$include_dir .= '/'.$include;
				else
					break;

				$has_looped = true;
			}

			if ($has_looped && file_exists($include_dir.'/default.php'))
				return $include_dir.'/default.php';
		}

		return false;
	}

	// Get request type (POST or GET) (defaults to GET)
	static private function _get_request_method()
	{
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			return 'AJAX';
		elseif (in_array($_SERVER['REQUEST_METHOD'], self::$request_methods))
			return $_SERVER['REQUEST_METHOD'];
		else
			return 'GET';
	}

	// Get the contents of $_GET['q'] and filter and split it.
	// If $_GET['q'] is not set false is returned
	static private function _parse_request_string()
	{
		if (!isset($_GET['q'][0]))
			return false;

		$request_path = str_replace('..', '', trim($_GET['q'], '/ '));
		return explode('/', $request_path);
	}

	// Call the controller and echo the output
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

// Modules container (very simple dependency injection)
class ModuleContainer
{
	private $objects = array();

	public function __set($name, $value)
	{
		if (!isset($this->objects[$name]))
		{
			if (!file_exists(SYS_INCLUDE.'/modules/'.$name.'.php'))
				return false;

			require SYS_INCLUDE.'/modules/'.$name.'.php';
		}
		else
			unset($this->objects[$name]);

		$this->objects[$name] = new $name($value);
		return $this->objects[$name];
	}

	public function __get($name)
	{
		if (isset($this->objects[$name]))
			return $this->objects[$name];

		if (!file_exists(SYS_INCLUDE.'/modules/'.$name.'.php'))
			return false;

		require SYS_INCLUDE.'/modules/'.$name.'.php';

		$this->objects[$name] = new $name();
		return $this->objects[$name];
	}
}

$mc = new ModuleContainer();
