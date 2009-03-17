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

$start_timer = microtime();

error_reporting(E_ALL);

// Define system vars and include important files
define('SHINOBU', 1);
require './system/core/init.php';

if (MOD_REWRITE === true)
{
	$protocol = (!isset($_SERVER['HTTPS']) || strtolower($_SERVER['HTTPS']) == 'off') ? 'http://' : 'https://';
	$port = (isset($_SERVER['SERVER_PORT']) && (($_SERVER['SERVER_PORT'] != '80' && $protocol == 'http://') || ($_SERVER['SERVER_PORT'] != '443' && $protocol == 'https://')) && strpos($_SERVER['HTTP_HOST'], ':') === false) ? ':'.$_SERVER['SERVER_PORT'] : '';
	$current_url = urldecode($protocol.$_SERVER['HTTP_HOST'].$port.$_SERVER['REQUEST_URI']);

	$prefix_pos = strpos($current_url, $url_query) === false ? false : true;

	if ($prefix_pos === true)
	{
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.str_replace(array('index.php'.$url_query, $url_query), '', $current_url)); exit;
	}
}

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-type: text/html; charset='.$sys_lang['s_encoding']);

// Get uri
$sys_request = array();
$sys_request[0] = isset($_GET['q']) ? trim($_GET['q'], '/') : false;

if ($sys_request[0] !== false)
{
	// Remove uri suffix and /'s at the beginning and end of the uri
	if (URI_SUFFIX != '')
	{
		$suffix_pos = strpos($sys_request[0], URI_SUFFIX);
		$sys_tmp = explode('/', is_bool($suffix_pos) && !$suffix_pos ? NULL : substr($sys_request[0], 0, $suffix_pos));
	}
	else
		$sys_tmp = explode('/', $sys_request[0]);
}

// Filter uri parts
if (isset($sys_tmp) && count($sys_tmp) > 0)
	foreach ($sys_tmp as $v)
		$sys_request[] = preg_replace('/[^A-Za-z0-9_\-()]+/', '', $v);

// Load template (and parser) and assign content
require SYS_LIBRARY_DIR.'template_parser.php';
$sys_tpl = new template_parser(SYS_THEME_DIR.$sys_config['theme'].'/');

// Assign tags
$sys_tpl->assign(
	array(
		'content_direction',
		'content_encoding',
		'content_type',
		'theme_path',
		'website_title',
		'website_description',
		'main_navigation',
		'user_block',
		'software_version'),
	array(
		$sys_lang['s_direction'],
		$sys_lang['s_encoding'],
		'text/html',
		SYS_THEME_PATH.$sys_config['theme'],
		$sys_config['website_title'],
		$sys_config['website_description'],
		generate_navigation(),
		generate_user_block(),
		'<a href="http://code.google.com/p/shinobu/">Shinobu</a> '.SHINOBU_VERSION)
	);

if ($sys_config['user_online_stats'] === 1)
	$sys_tpl->assign('whoisonline_block', generate_whoisonline_block());

// Global system messages
if (isset($_GET['login']))
	$sys_tpl->add('main_content', '<div class="success">'.$sys_lang['m_login_succes'].'</div>');
else if (isset($_GET['logout']))
	$sys_tpl->add('main_content', '<div class="success">'.$sys_lang['m_logout_succes'].'</div>');

// Start capturing stuff for the main content
ob_start();

// Load page
if (isset($sys_request[1]) && is_dir(APP_ROOT.'pages/'.$sys_request[1]))
{
	if (isset($sys_request[2]) && is_file(APP_ROOT.'pages/'.$sys_request[1].'/'.$sys_request[2].'.php'))
		require APP_ROOT.'pages/'.$sys_request[1].'/'.$sys_request[2].'.php';
	else
		send_404();
}
else if (isset($sys_request[1]) && is_file(APP_ROOT.'pages/'.$sys_request[1].'.php'))
	require APP_ROOT.'pages/'.$sys_request[1].'.php';
else if (!isset($sys_request[1]))
	require APP_ROOT.'pages/home.php';
else
	send_404();

// Stop capturing stuff for the main content
$sys_tpl_content = trim(ob_get_contents());
ob_end_clean();
$sys_tpl->assign('main_content', $sys_tpl_content);

// Replace all tags in the template
$sys_tpl->process();

// Close database connection
$sys_db->close();

// Show debug information is debug mode is enabled
if (SYSTEM_DEBUG === true)
	$sys_tpl->template = str_replace('<!-- DEBUG -->', output_debug_info($start_timer, microtime()), $sys_tpl->template);

// DISPLAY :O
exit($sys_tpl->template);

?>
