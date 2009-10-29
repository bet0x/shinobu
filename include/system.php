<?php

# =============================================================================
# include/system.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class system
{
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
			return SYS_CONTROL.'/defaultc.php';
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

			if ($has_looped && is_file($include_dir.'/defaultc.php'))
				return $include_dir.'/defaultc.php';
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
		if (isset($_GET['q']) && !empty($_GET['q']))
		{
			$request_string = str_replace('..', '', trim($_GET['q'], '/ '));
			$request = explode('/', $request_string);
		}
		else
			$request = false;

		return $request;
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
	static public function set_type($type='html')
	{
		if (in_array($type, self::$mimetypes))
			header('Content-type: '.self::$mimetypes[$type].'; charset=utf-8');
		else
			header('Content-type: text/html; charset=utf-8');
	}

	// Run, Forrest, run!!
	static public function run()
	{
		$request = self::_parse_request();
		$controller_path = self::_find_controller($request);

		if ($controller_path !== false)
		{
			require $controller_path;

			$class_name = pathinfo($controller_path, PATHINFO_FILENAME);
			$request_type = self::_get_request_method();
			$args = false;

			if ($request_type == 'POST')
				$args = $_POST;

			$c_instance = new $class_name($request);
			echo $c_instance->$request_type($args);
		}
		else
			echo self::send_fourofour();
	}
}

// Abstract class for controllers
abstract class controller
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
