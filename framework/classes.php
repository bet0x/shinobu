<?php

# =============================================================================
# framework/classes.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/**
 * The Application class processes the request and calls the controller.
 */
class Application
{
	public $output;

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
			$controller_instance = new conf::$default_controller($request);
			return $controller_instance->send_error(404);
		}

		// Include the controller file
		require $controller_path;

		// Resolve class name and request method
		$class_name = pathinfo($controller_path, PATHINFO_FILENAME).'_controller';

		// Get request method (Default is GET)
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
			$request_type = 'AJAX';
		else
			$request_type = $_SERVER['REQUEST_METHOD'];

		// Set controller arguments
		if ($request_type == 'POST')
			$args =& $_POST;
		else
			$args = null;

		// Start the controller
		try
		{
			$controller_instance = new $class_name($request);

			if (!$controller_instance->interrupt)
				$this->output = $controller_instance->$request_type($args);
			else
				$this->output = $controller_instance->pre_out;
		}
		catch (Exception $e)
		{
			// Send (no-cache) headers
			header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
			header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
			header('Cache-Control: post-check=0, pre-check=0', false);
			header('Pragma: no-cache'); // For HTTP/1.0 compability
			header('Content-type: text/plain; charset=utf-8');

			// Print exception info
			echo $e->getMessage(), "\n\n",
			     '#- ', $e->getFile(), '(', $e->getLine(), ')', "\n",
			     $e->getTraceAsString();

			// Stop execution
			exit;
		}
	}
}

/**
 * A simple template class.
 */
class tpl
{
	static private $vars = array();

	/**
	 * Set a template variable.
	 *
	 * @param string $ident
	 * @param mixed $value
	 */
	static public function set($ident, $value)
	{
		self::$vars[$ident] = $value;
	}

	/**
	 * Get a template variable.
	 *
	 * @param string $ident
	 * @return mixed
	 */
	static public function get($ident)
	{
		return self::$vars[$ident];
	}

	/**
	 * Clear all template variables.
	 */
	static public function clear()
	{
		self::$vars = array();
	}

	/**
	 * Render a template.
	 *
	 * @param string $template_name
	 * @param array $local_vars
	 * @param boolean $clear
	 * @return string
	 */
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
			error('Template could not be found!');

		if ($clear)
			self::clear();
	}
}

/**
 * A simple cache class.
 */
class cache
{
	/**
	 * Write an array to the cache.
	 *
	 * @param string $name
	 * @param array $array
	 * @return mixed Returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	static public function awrite($name, $array)
	{
        return file_put_contents(SYS_CACHE.'/'.$name.'.php', '<?php'."\n\n".'return '.var_export($array, true).';');
	}

	/**
	 * Read an array from the cache.
	 *
	 * @param string $name
	 * @return array Returns the read data or FALSE on failure.
	 */
	static public function aread($name)
	{
		return file_exists(SYS_CACHE.'/'.$name.'.php') ? require SYS_CACHE.'/'.$name.'.php' : false;
	}

	/**
	 * Read a Json file from the cache.
	 *
	 * @param string $name
	 * @param boolean $assoc
	 * @return mixed
	 */
	static public function read($name, $assoc = true)
	{
		return json_decode(@file_get_contents(SYS_CACHE.'/'.$name.'.json'), $assoc);
	}

	/**
	 * Write a Json file to the cache.
	 *
	 * @param string $name
	 * @param mixed $data
	 * @return mixed Returns the number of bytes that were written to the file, or FALSE on failure.
	 */
	static public function write($name, $data)
	{
		return file_put_contents(SYS_CACHE.'/'.$name.'.json', json_encode($data));
	}

	/**
	 * Check file existance in the cache
	 *
	 * @param string $filename
	 * @return boolean
	 */
	static public function exists($filename)
	{
		return file_exists(SYS_CACHE.'/'.$filename);
	}

	/**
	 * Clear specified file(s) or clear the whole cache.
	 *
	 * @param string
	 */
	static public function clear()
	{
		$files = func_get_args();

		if (!isset($files[0]))
			$files = scandir(SYS_CACHE);

		foreach ($files as $file)
			if ($file != '.' && $file != '..')
				@unlink(SYS_CACHE.'/'.$file);
	}
}

/**
 * A simple XSRF class.
 */
class xsrf
{
	static private $_token = false;

	/**
	 * Generate an XSRF token.
	 *
	 * Generate an XSRF token and store it in a cookie, but first check if the
	 * cookie already exists or if the token is already generated.  Then return it.
	 * See: http://en.wikipedia.org/wiki/Cross-site_request_forgery
	 *
	 * @return string
	 */
	static function token()
	{
		if (($token = get_cookie('xsrf')) !== false)
			self::$_token =& $token;
		elseif (!self::$_token)
		{
			self::$_token = generate_hash(generate_salt());
			set_cookie('xsrf', self::$_token);
		}

		return self::$_token;
	}

	/**
	 * Compare XSRF token with $token.
	 *
	 * @param string $token
	 * @return boolean
	 */
	static function check_cookie($token)
	{
		if (!self::$_token)
			self::token();

		return $token == self::$_token;
	}

	/**
	 * Return a hidden form field with the XSRF token.
	 *
	 * @return string
	 */
	static function form_html()
	{
		if (!self::$_token)
			self::token();

		return '<input type="hidden" name="xsrf_token" value="'.self::$_token.'" />';
	}
}

/**
 * The BaseController class.
 *
 * BaseController contains all the functions and variables for a controller.
 * This class (or children of this class) is called by the Applications class
 * after a request has been processed.
 */
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
	private $_mime_is_set = false;

	public function __construct($request)
	{
		$this->request = $request;
		$this->pre_output = $this->prepare();

		if ($this->pre_output)
			$this->interrupt = true;
	}

	/**
	 * An empty function.
	 *
	 * This is an empty function that's always executed by the constructor of
	 * the base controller.  This function can be overwritten to execute  or
	 * process certain things before the request method function is executed.
	 *
	 * @access protected
	 * @return null
	 */
	protected function prepare()
	{
		return;
	}

	/**
	 * Load a module.
	 *
	 * @access protected
	 * @staticvar array $modules
	 * @param string $name
	 * @param mixed $args
	 * @param string $suffix
	 * @return object
	 */
	protected function &load_module($name, &$args = null, $suffix = '')
	{
		static $modules = array();

		if (isset($modules[$name.$suffix]))
			return $modules[$name.$suffix];

		if (!isset($modules[$name]))
			require SYS_INCLUDE.'/modules/'.$name.'.php';

		$modules[$name.$suffix] = new $name($args);
		return $modules[$name.$suffix];
	}

	/**
	 * Send content type header.
	 *
	 * @access protected
	 * @param string $type
	 */
	protected function set_mimetype($type)
	{
		if ($this->_mime_is_set)
			return;

		if (isset($this->_mimetypes[$type]))
			header('Content-type: '.$this->_mimetypes[$type].'; charset=utf-8');
		else
			header('Content-type: text/plain; charset=utf-8');

		$this->_mime_is_set = true;
	}

	/**
	 * Send an error to the client (e.g. 404, 500).
	 *
	 * @access public
	 * @param int $status_code
	 * @return string
	 */
	public function send_error($status_code)
	{
		if (!isset($this->_status_codes[$status_code]))
			$status_code = 500;

		header('HTTP/1.1 '.$status_code.' '.$this->_status_codes[$status_code]);
		$this->set_mimetype('text');

		return $status_code.': '.$this->_status_codes[$status_code];
	}

	/**
	 * Redirect a client to an other URL.
	 *
	 * @access protected
	 * @param string $location
	 */
	protected function redirect($location)
	{
		header('location: '.$location); exit;
	}

	/* By default all request method function return a 405 (Method Not Allowed)
	error. Controllers should extend the BaseController and overwrite these
	function. */
	public function GET($args) { return $this->send_error(405); }
	public function POST($args) { return $this->send_error(405); }
	public function PUT($args) { return $this->send_error(405); }
	public function DELETE($args) { return $this->send_error(405); }
	public function HEAD($args) { return $this->send_error(405); }
	public function AJAX($args) { return $this->send_error(405); }
}
