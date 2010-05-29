<?php

# =============================================================================
# framework/functions.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/**
 * Unset any variables instantiated as a result of register_globals being enabled.
 */
function unregister_globals()
{
	$register_globals = ini_get('register_globals');
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

/**
 * A wrapper for throwing a new exception.
 *
 * @param string $messages
 */
function error($message)
{
	throw new Exception($message);
}

/**
 * Set a cookie.
 *
 * @global string $sys_cookie_name
 * @global string $sys_cookie_path
 * @global string $sys_cookie_domain
 * @global int $sys_cookie_secure
 * @param string $name
 * @param mixed $value
 * @param int $expire
 */
function set_cookie($name, $value, $expire = 0)
{
	global $sys_cookie_name, $sys_cookie_path, $sys_cookie_domain, $sys_cookie_secure;

	header('P3P: CP="CUR ADM"'); // Enable sending of a P3P header

	if (version_compare(PHP_VERSION, '5.2.0', '>='))
		setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path,
			$sys_cookie_domain, $sys_cookie_secure, true);
	else
		setcookie($sys_cookie_name.'_'.$name, serialize($value), $expire, $sys_cookie_path.'; HttpOnly',
			$sys_cookie_domain, $sys_cookie_secure);
}

/**
 * Get a cookie.
 *
 * @global string $sys_cookie_name
 * @param string $name
 * @return mixed
 */
function get_cookie($name)
{
	global $sys_cookie_name;

	return isset($_COOKIE[$sys_cookie_name.'_'.$name]) ? unserialize($_COOKIE[$sys_cookie_name.'_'.$name]) : false;
}

/**
 * Generate and return an url to a controller.
 *
 * @param string $relative_path
 * @return string
 */
function url($relative_path = null)
{
	return SYSTEM_BASE_URL.'/'.(REWRITE_URL ? '' : '?q=').$relative_path;
}

/**
 * Append a ?v=<timestamp of last modification> to a static file
 *
 * @param string $file_path
 * @return string
 */
function static_url($file_path)
{
	return SYSTEM_BASE_URL.'/static/'.$file_path.'?v='.filemtime(SYS_STATIC.'/'.$file_path);
}

/**
 * Generate a SHA1 hash from a string.
 *
 * @param string $str
 * @param string $salt
 * @return string
 */
function generate_hash($str, $salt = '')
{
	if (!$str || !isset($salt[0]))
		$salt = generate_salt();

	return sha1($salt.sha1($str)); // Returns a 40 character long hash
}

/**
 * Generate a salt.
 *
 * @return string
 */
function generate_salt()
{
	$key = '';

	for ($x = 0; $x < 20; ++$x)
		$key .= chr(mt_rand(33, 126));

	return $key; // Returns a 20 character long salt
}

/**
 * Get the IP address from the visitor.
 *
 * @return string
 */
function get_remote_address()
{
	return $_SERVER['REMOTE_ADDR'];
}

/**
 * Encode HTML characters in a string.
 *
 * @param string $str
 * @return string
 */
function u_htmlencode($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Convert all linebreakes (Windows, Mac) to Unix linebreaks.
 *
 * @param string $str
 * @return string
 */
function convert_linebreaks($str)
{
	return str_replace(array("\r\n", "\r"), array("\n"), $str);
}

/**
 * Get extension from filename.
 *
 * @param string $filename
 * @return string The file extension without the dot.
 */
function get_ext($filename)
{
	return strtolower(substr($filename, strrpos($filename, '.') + 1));
}

/**
 * Convert the file size in bytes to a human readable file size.
 *
 * @staticvar array $units
 * @param int $size
 * @param boolean $base10
 * @return float
 */
function file_size($size, $base10 = false)
{
	static $units;

    if (!isset($units))
        $units = array(1000 => array('kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'),
                       1024 => array('KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'));

    $base = $base10 ? 1000 : 1024;

	for ($i = 0; $size > $base; $i++)
    {
		$size /= $base;

        if ($size < $base)
            return round($size, 2).' '.$units[$base][$i];
    }

    return;
}

/**
 * Get microtime
 *
 * @param string $microtime
 * @return float
 */
function get_microtime($microtime)
{
	list($usec, $sec) = explode(' ', $microtime);
	return ((float)$usec + (float)$sec);
}
