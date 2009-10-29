<?php

# =============================================================================
# include/functions.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

// Disable magic quotes
function disable_magic_quotes()
{
	// Turn off magic_quotes_runtime
	if (get_magic_quotes_runtime())
		set_magic_quotes_runtime(0);

	// Strip slashes from GET/POST/COOKIE (if magic_quotes_gpc is enabled)
	if (get_magic_quotes_gpc())
	{
		function stripslashes_array($array)
		{
			return is_array($array) ? array_map('stripslashes_array', $array) : stripslashes($array);
		}

		$_GET = stripslashes_array($_GET);
		$_POST = stripslashes_array($_POST);
		$_COOKIE = stripslashes_array($_COOKIE);
		$_REQUEST = stripslashes_array($_REQUEST);
	}
}

// Unset any variables instantiated as a result of register_globals being enabled
function unregister_globals()
{
	$register_globals = @ini_get('register_globals');
	if ($register_globals === '' || $register_globals === '0' || strtolower($register_globals) === 'off')
		return;

	// Prevent script.php?GLOBALS[foo]=bar
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
		exit('Wut?!');

	// Variables that shouldn't be unset
	$no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

	// Remove elements in $GLOBALS that are present in any of the superglobals
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

	foreach ($input as $k => $v)
	{
		if (!in_array($k, $no_unset) && isset($GLOBALS[$k]))
		{
			unset($GLOBALS[$k]);
			unset($GLOBALS[$k]); // Double unset to circumvent the zend_hash_del_key_or_index hole in PHP <4.4.3 and <5.1.4
		}
	}
}

// Sends an error message. Used by database and cache functions
function error($messages, $file = false, $line = false)
{
	ob_end_clean();

	// Send (no-cache) headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', false);
	header('Pragma: no-cache'); // For HTTP/1.0 compability
	header('Content-type: text/plain; charset=utf-8');

	if (is_array($messages))
		foreach ($messages as $message)
			echo $message, "\n";
	else
		echo $messages, "\n";

	echo ($file !== false) ? "\n".'File: '.$file : null;
	echo ($file !== false) ? "\n".'Line: '.$line : null;

	exit;
}

// Redirect to specified page
function redirect($location)
{
	header('location: '.$location); exit;
}

// Generates a sha1 hash from a string
function generate_hash($str, $salt=false)
{
	if ($str === false || strlen($salt) === 0)
		$salt = generate_salt();

	return sha1($salt.sha1($str)); // Returns a 40 character long hash
}

// Generates a salt
function generate_salt()
{
	$key = '';

	for ($x = 0; $x < 20; ++$x)
		$key .= chr(mt_rand(33, 126));

	return $key; // Returns a 20 character long salt
}

// Set a cookie
function set_cookie($name, $value, $expire)
{
	global $sys_cookie_name, $sys_cookie_path, $sys_cookie_domain, $sys_cookie_secure;

	header('P3P: CP="CUR ADM"'); // Enable sending of a P3P header

	if (version_compare(PHP_VERSION, '5.2.0', '>='))
		setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path, $sys_cookie_domain, $sys_cookie_secure, true);
	else
		setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path.'; HttpOnly', $sys_cookie_domain, $sys_cookie_secure);
}

// Get a cookie
function get_cookie($name)
{
	global $sys_cookie_name;

	return isset($_COOKIE[$sys_cookie_name.'_'.$name]) ? unserialize($_COOKIE[$sys_cookie_name.'_'.$name]) : false;
}

// Get the IP address from the visitor
function get_remote_address()
{
	return $_SERVER['REMOTE_ADDR'];
}

// Encodes the contents of $str so that they are safe to output on an (X)HTML page
function u_htmlencode($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Convert linebreakes to unix linebreaks
function convert_linebreaks($str)
{
	return str_replace(array("\r\n", "\r"), array("\n"), $str);
}

// Get extension from filename (returns the extension without the dot)
function get_ext($file_name)
{
	return strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
}
