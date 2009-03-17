<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU')) ? exit : NULL;

/* --- User (related) functions
----------------------------------------------------------- */

// Check if user is logged in
function check_user_cookie()
{
	global $sys_config, $sys_db, $sys_lang, $cookie_seed;

	$sys_user = NULL;

	// Get cookie and check if user is logged in
	$cookie = array('key' => '', 'id' => GUEST_UID);
	if (($tmp_cookie = get_cookie('user')) === false)
		$tmp_cookie = array('key' => '', 'id' => GUEST_UID);

	if ($tmp_cookie['id'] !== GUEST_UID)
	{
		$result = $sys_db->query('
			SELECT
				u.id, u.gid, u.username, u.salt, u.active, u.hash, u.real_name, u.website, u.email,
				u.msn, u.yahoo, u.show_email, u.language, u.timezone, u.dst, u.last_login, u.register_date,
				u.registration_ip, g.name AS usergroup,
				g.p_manage_nav,
				g.p_manage_users,
				g.p_manage_pages
			FROM '.DB_PREFIX.'users AS u
			INNER JOIN '.DB_PREFIX.'usergroups AS g
			ON u.gid=g.id
			WHERE u.id='.intval($tmp_cookie['id']).'
			LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

		if ($sys_db->num_rows($result) === 1)
		{
			$row = $sys_db->fetch_assoc($result);

			if ($tmp_cookie['key'] == md5($cookie_seed.$row['salt']))
			{
				foreach ($row as $k => $v)
					$sys_user[$k] = $v;

				if ($sys_user['p_manage_nav'] == 1 || $sys_user['p_manage_users'] == 1 || $sys_user['p_manage_pages'] == 1)
					$sys_user['p_access_admin'] = 1;
				else
					$sys_user['p_access_admin'] = 0;

				$sys_user['logged'] = true;
				$cookie = $tmp_cookie;
			}
		}
	}

	if ($cookie['id'] === GUEST_UID)
	{
		$sys_user = array(
			'id' => GUEST_UID,
			'gid' => GUEST_GID,
			'username' => $sys_lang['g_guest'],
			'name' => $sys_lang['g_guest'],
			'timezone' => $sys_config['timezone'],
			'dst' => $sys_config['dst'],
			'logged' => false
			);
	}

	// The anti-CSRF token is saved in the 'online' table.
	// If the 'Track online users' feature is disabled the token will be saved in a session.
	if ($sys_config['user_online_stats'] === 1)
	{
		$now = time();
		$sys_user['id'] = intval($sys_user['id']);
		$username = $sys_user['id'] == GUEST_UID ? $sys_db->escape(get_remote_address()) : $sys_db->escape($sys_user['username']);

		// Update user in the online table
		$result = $sys_db->query('SELECT o.token FROM '.DB_PREFIX.'online AS o WHERE o.username=\''.$username.'\' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

		if ($sys_db->num_rows($result) > 0)
		{
			$row = $sys_db->fetch_row($result);
			define('SYS_TOKEN', $row[0]);

			$sys_db->query('UPDATE '.DB_PREFIX.'online SET id='.$sys_user['id'].', username=\''.$username.'\', last_visit='.$now.' WHERE username=\''.$username.'\'') or error($sys_db->error(), __FILE__, __LINE__);
		}
		else
		{
			define('SYS_TOKEN', sha1(md5(uniqid(mt_rand(),true).$_SERVER['HTTP_USER_AGENT'])));
			$sys_db->query('INSERT INTO '.DB_PREFIX.'online (id, username, token, last_visit) VALUES('.$sys_user['id'].', \''.$username.'\', \''.SYS_TOKEN.'\', '.$now.')') or error($sys_db->error(), __FILE__, __LINE__);
		}

		// Delete inactive users
		$sys_db->query('DELETE FROM '.DB_PREFIX.'online WHERE last_visit < '.($now - $sys_config['visit_timeout'])) or error($sys_db->error(), __FILE__, __LINE__);
	}
	else
	{
		session_start();

		// Regenerate session id if $_SESSION['initiated'] isn't set
		if (!isset($_SESSION['initiated']) || $_SESSION['initiated'] !== 1)
		{
			session_regenerate_id();
			$_SESSION['initiated'] = 1;
		}

		// Generate a token if there isn't one
		if (!isset($_SESSION['token']))
			$_SESSION['token'] = sha1(md5(uniqid(mt_rand(), true).$_SERVER['HTTP_USER_AGENT']));

		define('SYS_TOKEN', $_SESSION['token']);
	}

	return $sys_user;
}

// Processes the login submission
function login($username, $password)
{
	global $sys_db, $sys_lang, $sys_user, $cookie_seed;

	$now = time();
	$errors = false;

	// Login check
	if (get_cookie('user') !== false && $sys_user['logged'] === true)
		return array('account' => $sys_lang['e_already_logged_in']);

	$username = system_trim($sys_db->escape($username));
	$password = system_trim($sys_db->escape($password));

	if (empty($username))
		$errors['username'] = $sys_lang['e_username_error_1'];

	if (empty($password))
		$errors['password'] = $sys_lang['e_password_error_2'];

	if ($errors === false)
	{
		$result = $sys_db->query('SELECT u.id, u.password, u.salt, u.active FROM '.DB_PREFIX.'users as u WHERE u.username=\''.$username.'\' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

		if ($sys_db->num_rows($result) === 1)
		{
			$row = $sys_db->fetch_assoc($result);

			if ($row['active'] == 0)
				$errors['account'] = $sys_lang['e_inactive_account'];
			else if ($row['password'] == generate_password($password, $row['salt']))
			{
				set_cookie('user', array('id' => $row['id'], 'key' => md5($cookie_seed.$row['salt'])), time() + 1209600); // 1209600: 2 weeks - 43200: Logs user in for 12 hours
				$sys_db->query('UPDATE '.DB_PREFIX.'users SET last_login='.$now.' WHERE id='.intval($row['id'])) or error($sys_db->error(), __FILE__, __LINE__);
			}
			else
				$errors['password'] = $sys_lang['e_password_error_1'];
		}
		else
			$errors['username'] = $sys_lang['e_user_does_not_exist'];
	}

	return is_array($errors) ? $errors : false;
}

// Log out (let cookie expire)
function logout($redirect=false)
{
	global $sys_db, $sys_user;

	set_cookie('user', NULL, time() - 3600);
	$sys_db->query('DELETE FROM '.DB_PREFIX.'online WHERE id='.intval($sys_user['id'])) or error($sys_db->error(), __FILE__, __LINE__);

	// Redirect
	if ($redirect !== false)
	{
		header('location: '.$redirect); exit;
	}
}

// Generates a hash
function generate_password($str, $salt)
{
	if (strlen($str) === 0 || strlen($salt) === 0)
		return false;

	return md5($salt).sha1($salt.$str); // Returns a 72 character long hash
}

// Generates a salt
function generate_salt()
{
	$key = NULL;

	for ($x = 0; $x < SALT_LENGTH; ++$x)
		$key .= chr(mt_rand(33, 126));

	return $key;
}

/* --- System funtions
----------------------------------------------------------- */

// Set a cookie
function set_cookie($name, $value, $expire)
{
	global $cookie_name, $cookie_path, $cookie_domain, $cookie_secure;

	@header('P3P: CP="CUR ADM"'); // Enable sending of a P3P header

	if (version_compare(PHP_VERSION, '5.2.0', '>='))
		setcookie($cookie_name.'['.$name.']', base64_encode(serialize($value)), $expire, $cookie_path, $cookie_domain, $cookie_secure, true);
	else
		setcookie($cookie_name.'['.$name.']', base64_encode(serialize($value)), $expire, $cookie_path.'; HttpOnly', $cookie_domain, $cookie_secure);
}

// Get a cookie
function get_cookie($name)
{
	global $cookie_name;

	return isset($_COOKIE[$cookie_name][$name]) ? unserialize(base64_decode($_COOKIE[$cookie_name][$name])) : false;
}

// Unset any variables instantiated as a result of register_globals being enabled (From FluxBB)
function unregister_globals()
{
	$register_globals = @ini_get('register_globals');
	if ($register_globals === '' || $register_globals === '0' || strtolower($register_globals) === 'off')
		return;

	// Prevent script.php?GLOBALS[foo]=bar
	if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS']))
		error('I\'ll have a steak sandwich and... a steak sandwich.', __FILE__, __LINE__);

	// Variables that shouldn't be unset
	$no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

	// Remove elements in $GLOBALS that are present in any of the superglobals
	$input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());

	foreach ($input as $k => $v)
		if (!in_array($k, $no_unset) && isset($GLOBALS[$k]))
		{
			unset($GLOBALS[$k]);
			unset($GLOBALS[$k]); // Double unset to circumvent the zend_hash_del_key_or_index hole in PHP <4.4.3 and <5.1.4
		}
}

// Check token
function check_token($get=false)
{
	if (isset($_POST['token']) && $get === false)
		$token = $_POST['token'];
	else if (isset($_GET['token']) && $get === true)
		$token = $_GET['token'];
	else
		$token = false;

	// Check token
	return $token === SYS_TOKEN ? true : false;
}

function get_remote_address()
{
	return $_SERVER['REMOTE_ADDR'];
}

function pagination($item_count, $limit, $cur_page, $link)
{
	global $sys_lang;

	$page_count = ceil($item_count/$limit);
	$current_range = array(($cur_page-2 < 1 ? 1 : $cur_page-2), ($cur_page+2 > $page_count ? $page_count : $cur_page+2));

	// First and Last pages
	$first_page = $cur_page > 3 ? '<a href="'.sprintf($link, '1').'">1</a>'.($cur_page < 5 ? ', ' : ' ... ') : NULL;
	$last_page = $cur_page < $page_count-2 ? ($cur_page > $page_count-4 ? ', ' : ' ... ').'<a href="'.sprintf($link, $page_count).'">'.$page_count.'</a>' : NULL;

	// Previous and next page
	$previous_page = $cur_page > 1 ? '<a href="'.sprintf($link, ($cur_page-1)).'">'.$sys_lang['g_previous'].'</a> | ' : NULL;
	$next_page = $cur_page < $page_count ? ' | <a href="'.sprintf($link, ($cur_page+1)).'">'.$sys_lang['g_next'].'</a>' : NULL;

	// Display pages that are in range
	for ($x=$current_range[0];$x <= $current_range[1];$x++)
		$pages[] = '<a href="'.sprintf($link, $x).'">'.($x == $cur_page ? '<strong>'.$x.'</strong>' : $x).'</a>';

	if ($page_count > 1)
		return '<p class="pagination"><strong>'.$sys_lang['g_pages'].':</strong> '.$previous_page.$first_page.implode(', ', $pages).$last_page.$next_page.'</p>';
}

// Outputs debug information
function output_debug_info($start_timer, $end_timer)
{
	global $sys_db;

	$query_html = NULL;
	$total_query_time = 0;
	$total_script_time = round(get_microtime($end_timer) - get_microtime($start_timer), 5);
	$saved_queries = $sys_db->saved_queries();

	for ($x=0; $x < $sys_db->query_count; $x++)
	{
		$total_query_time += $saved_queries[$x][0];
		$query_html .= '<div class="query-sql row"><div class="query-time"><em>'.$saved_queries[$x][0].'</em></div>'.utf8_htmlencode($saved_queries[$x][1]).'</div>'."\n";
	}

	if ($sys_db->query_count === 1)
		$total_query_time = $saved_queries[0][0];

	return '<div id="system-debug">
	<h2>Executed queries ('.$sys_db->query_count.'):</h2>
	'.$query_html.'
	<h2>System Statistics</h2>
	<div class="row"><strong>Total query time:</strong> <em>'.$total_query_time.'</em> seconds</div>
	<div class="row"><strong>Script processing time:</strong> <em>'.($total_script_time - $total_query_time).'</em> seconds</div>
	<div class="row"><strong>Script + query time:</strong> <em>'.$total_script_time.'</em> seconds</div>
	<div class="row"><strong>Memory usage:</strong> <em>'.file_size(memory_get_usage()).'</em></div>
</div>';
}

// Sends a 404 page
function send_404($title=false, $content=false, $set_title=true)
{
	global $sys_config, $sys_tpl, $sys_lang;

	header('HTTP/1.1 404 NOT FOUND', true, 404);
	header('Status: 404 NOT FOUND', true, 404);

	if ($title === false)
		$title = $sys_lang['e_page_not_found'];

	if ($content === false)
		$content = '<p>'.$sys_lang['e_page_not_found_info'].'</p>';

	if ($set_title === true)
		$sys_tpl->assign('page_title', '404 - '.$sys_config['website_title']);

	echo '<h2><span>'.$title.'</span></h2>'."\n".$content;
}

// Sends an error message. Used by database and cache functions
function error($message, $file, $line)
{
	global $sys_db;

	@ob_end_clean();

	// Should we use gzip output compression?
	if (extension_loaded('zlib') && (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false || strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'deflate') !== false))
		ob_start('ob_gzhandler');
	else
		ob_start();

	echo '<?xml version="1.0" encoding="utf-8"?>';

	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" xml:lang="en" lang="en" />
	<meta http-equiv="content-language" content="en"/>
	<title>Home - Shinobu</title>

	<style type="text/css">
		html, body	{ padding: 1em 2em;margin:0 }
		body		{ background-color: #fff;font-size: 83%;line-height: 1.4em;color: #222;
					  font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Verdana, Arial, sans-serif }

		code { font-family: Consolas, 'andale mono', 'monotype.com', 'lucida console', monospace }
	</style>
</head>
<body>

<div id="error-message">
	<p><?php echo utf8_htmlencode($message); ?></p>
	<p><strong>File:</strong> <code><?php echo $file; ?></code><br /><strong>Line:</strong> <code><?php echo $line; ?></code></p>
</div>

</body>
</html>

	<?php

	if (isset($sys_db))
		$sys_db->close();

	exit;
}

// Returns all available languages
function get_languages()
{
	$scan = glob(SYS_LANG_DIR.'*', GLOB_ONLYDIR);

	foreach ($scan as $item)
		$languages[] = basename($item);

	return $languages;
}

/* --- Variable check and manipulation functions
----------------------------------------------------------- */

// Input a UNIX timestamp and converts it to GMT time and then converts it to
function format_time($timestamp, $showtime=false)
{
	global $sys_config, $sys_user, $sys_lang;

	if ($sys_user['logged'] === false)
		$timestamp += ($sys_config['timezone'] + $sys_config['dst']) * 3600;
	else
		$timestamp += ($sys_user['timezone'] + $sys_user['dst']) * 3600;

	if ($showtime === true)
		$date = gmdate($sys_lang['s_date_format'].' '.$sys_lang['s_time_format'], $timestamp);
	else
		$date = gmdate($sys_lang['s_date_format'], $timestamp);

	if ($sys_user['logged'] === false)
		$date .= ' UTC '.($sys_config['timezone'] > 0 ? '+' : NULL).$sys_config['timezone'];

	return $sys_config['language'] == 'English' ? $date : strtr($date, $sys_lang['strtr']);
}

// Encodes the contents of $str so that they are safe to output on an (X)HTML page
function utf8_htmlencode($str)
{
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Convert linebreakes to unix linebreaks
function convert_linebreaks($str)
{
	return str_replace(array("\r\n", "\r"), array("\n"), $str);
}

// Trim whitespace including non-breaking space (FluxBB 1.3)
function system_trim($str, $charlist = " \t\n\r\x0b\xc2\xa0")
{
	return utf8_trim($str, $charlist);
}

// Check email (FluxBB 1.3)
function check_email($email)
{
	if (strlen($email) > 80)
		return false;

	return preg_match('/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|("[^"]+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/', $email);
}

// Check url
function check_url($url)
{
	return preg_match('/^(http|ftp|https|irc)\:\/\/*/i', $url);
}

function base64_url_encode($input)
{
	return strtr(base64_encode($input), '+/=', '-_,');
}

function base64_url_decode($input)
{
	return base64_decode(strtr($input, '-_,', '+/='));
}

// Convert a filesize given in bytes to a more readable something
function file_size($size)
{
	$units = array('b', 'Kb', 'Mb', 'Gb', 'Tb', 'Pb', 'Eb');

	for ($i = 0; $size > 1024; $i++)
		$size /= 1024;

	return round($size, 2).' '.$units[$i];
}

function get_microtime($microtime=false)
{
	if ($microtime === false)
		$microtime = microtime();

	list($usec, $sec) = explode(' ', $microtime);
	return ((float)$usec + (float)$sec);
}

/* --- Cache functions and content block functions
----------------------------------------------------------- */

// Get menu from database, cache it, get content from cache file and return it
function generate_navigation($refresh_cache=false)
{
	global $sys_db, $sys_lang;

	// Get menu items from the database and cache them
	if ($refresh_cache === true || !file_exists(SYS_CACHE_DIR.'.cache_navigation'))
	{
		$menu_links = NULL;

		$result = $sys_db->query('SELECT n.id, n.name, n.url, n.position FROM '.DB_PREFIX.'navigation AS n ORDER BY n.position ASC') or error($sys_db->error(), __FILE__, __LINE__);

		while($row = $sys_db->fetch_assoc($result))
		{
			if (check_url($row['url']))
				$menu_links .= "\t".'<li id="nav'.$row['id'].'"><a href="'.$row['url'].'">'.$row['name'].'</a></li>'."\n";
			else
				$menu_links .= "\t".'<li id="nav'.$row['id'].'"><a href="'.WEBSITE_URL.URI_PREFIX.$row['url'].'">'.$row['name'].'</a></li>'."\n";
		}

		cache_data('.cache_navigation', $menu_links);
	}

	return '<ul>'."\n"."\t".'<li id="nav-home"><a href="'.WEBSITE_URL.'">'.$sys_lang['g_home'].'</a></li>'."\n".file_get_contents(SYS_CACHE_DIR.'.cache_navigation').'</ul>';
}

// Get user menu
function generate_user_block()
{
	global $sys_config, $sys_user, $sys_lang;

	$menu_items[] = '<li><a href="'.WEBSITE_URL.URI_PREFIX.'userlist'.URI_SUFFIX.'">'.$sys_lang['g_userlist'].'</a></li>';

	if ($sys_user['logged'] === true)
	{
		$menu_items[] = '<li><a href="'.WEBSITE_URL.URI_PREFIX.'profile'.URI_SUFFIX.'">'.$sys_lang['g_profile'].'</a></li>';

		if ($sys_user['gid'] == ADMIN_GID || $sys_user['p_access_admin'] === 1)
			$menu_items[] = '<li><a href="'.WEBSITE_URL.'admin/">'.$sys_lang['g_admin'].'</a></li>';

		$menu_items[] = '<li><a href="'.WEBSITE_URL.URI_PREFIX.'login'.URI_SUFFIX.'&amp;token='.SYS_TOKEN.'">'.$sys_lang['g_logout'].'</a></li>';
	}
	else
	{
		if ($sys_config['allow_new_registrations'] === 1)
			$menu_items[] = '<li><a href="'.WEBSITE_URL.URI_PREFIX.'register'.URI_SUFFIX.'">'.$sys_lang['g_register'].'</a></li>';

		$menu_items[] = '<li><a href="'.WEBSITE_URL.URI_PREFIX.'login'.URI_SUFFIX.'">'.$sys_lang['g_login'].'</a></li>';
	}

	return '<div id="user-block" class="sideblock">
		<h2><span>'.sprintf($sys_lang['t_welcome_user'], utf8_htmlencode($sys_user['username'])).'</span></h2>

		<ul>
			'.implode("\n", $menu_items).'
		</ul>
	</div>';
}

// Get list of online users
function generate_whoisonline_block()
{
	global $sys_config, $sys_db, $sys_lang;

	if ($sys_config['show_who_is_online'] === 0)
		return;

	if (file_exists(SYS_CACHE_DIR.'.cache_whos_online') && filemtime(SYS_CACHE_DIR.'.cache_whos_online') > (time() - $sys_config['visit_timeout']))
		return file_get_contents(SYS_CACHE_DIR.'.cache_whos_online');

	$users = false;
	$guests = 0;

	$result = $sys_db->query('SELECT o.id, o.username FROM '.DB_PREFIX.'online AS o') or error($sys_db->error(), __FILE__, __LINE__);
	while($row = $sys_db->fetch_assoc($result))
		if ($row['id'] == GUEST_UID)
			$guests++;
		else
			$users[] = '<a href="'.WEBSITE_URL.URI_PREFIX.'profile/'.$row['id'].URI_SUFFIX.'">'.utf8_htmlencode($row['username']).'</a>';

	$user_count = $users === false ? 0 : count($users);

	if ($user_count > 20)
	{
		$users = array_slice($users , 0 , 19);
		$users[] = ' <span title="There are more users online!">&hellip;</span>';
	}

	if ($user_count !== 1)
		$online_users = sprintf($sys_lang['d_whos_online_3'], $user_count);
	else
		$online_users = $sys_lang['d_whos_online_1'];

	if ($guests !== 1)
		$online_users .= sprintf($sys_lang['d_whos_online_4'], $guests);
	else
		$online_users .= $sys_lang['d_whos_online_2'];

	$output = '<div id="whoisonline-block" class="sideblock">
		<h2><span>'.$sys_lang['t_whos_online'].'</span></h2>
		'.(is_array($users) ? '<p>'.implode(', ', $users).'</p>' : NULL).'
		<p>'.$online_users.'</p>
	</div>';

	cache_data('.cache_whos_online', $output);

	return $output;
}

function cache_data($file_name, $data)
{
	$file = @fopen(SYS_CACHE_DIR.$file_name, 'wb');

	if (!$file)
		error('Cannot write "'.$file_name.'" to cache.', __FILE__, __LINE__);

	fwrite($file, $data);
	fclose($file);
}

function cache_config()
{
	global $sys_db;

	// Fetch config
	$result = $sys_db->query('SELECT c.name, c.value FROM '.DB_PREFIX.'config AS c') or error($sys_db->error(), __FILE__, __LINE__);

	while($row = $sys_db->fetch_assoc($result))
	{
		if ($row['value'] === 'true' || $row['value'] === 'false')
			$sys_config[$row['name']] = $row['value'] === 'true' ? true : false;
		else if (is_numeric($row['value']))
		{
			if (is_float($row['value']) || ((float) $row['value'] != round($row['value']) || strlen($row['value']) != strlen( (int) $row['value'])) && $row['value'] != 0) // http://nl.php.net/manual/en/function.is-float.php#80326
				$sys_config[$row['name']] = (float) $row['value'];
			else
				$sys_config[$row['name']] = (int) $row['value'];
		}
		else
			$sys_config[$row['name']] = (string) $row['value'];
	}

	cache_data('.cache_config', '<?php'."\n\n".'$sys_config = '.var_export($sys_config, true).';'."\n\n".'?>');
}



?>
