<?php

# =============================================================================
# include/controllers.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// A base class for controllers
// This class contains all the supported requests methods
class BaseController
{
	protected $request = false, $module = null;
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

	public function __construct($request, ModuleContainer $module = null)
	{
		$this->request = $request;
		$this->module = $module;
		$this->prepare();
	}

	/* This is an empty function that's always executed by the constructor of
	   the base controller.  This function can be overwritten to execute  or
	   process certin things before the request method function is executed. */
	protected function prepare()
	{

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

// A controller for web pages
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

// A controller for web pages with user authentication enabled
abstract class AuthWebController extends BaseController
{
	public function __construct($request, ModuleContainer $module = null)
	{
		$this->request = $request;
		$this->module = $module;

		$this->set_mimetype('html');

		// Load user and ACL module
		$this->module->user = $this->module->acl = $this->module->db;
		$authenticated = $this->module->user->authenticated();

		// Set some template variables
		tpl::set('website_title', 'Shinobu');
		tpl::set('authenticated', $authenticated);

		if ($authenticated)
		{
			#$this->module->acl = $this->module->db;
			$data = $this->module->user->data();
			$this->module->acl->set_gid($data['group_id']);

			tpl::set('username', $data['username']);
			tpl::set('admin_view', $this->module->acl->get('admin_read') & ACL_READ);
		}

		$this->prepare();
	}
}
