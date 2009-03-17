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
define('SHINOBU_ADMIN', 1);
require '../system/core/init.php';

// Check if user is admin
if ($sys_user['gid'] == GUEST_GID)
{
	header('location: '.WEBSITE_URL.URI_PREFIX.'login'.URI_SUFFIX); exit;
}
else if ($sys_user['p_access_admin'] === 0)
{
	header('location: '.WEBSITE_URL); exit;
}

// Define vars
define('ADMIN_URL', WEBSITE_URL.'admin/');

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache'); // For HTTP/1.0 compability
header('Content-type: text/html; charset='.$sys_lang['s_encoding']);

// Load admin functions
require APP_ROOT.'admin/include/functions.php';

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

// Check uri
if (!isset($uri[0]) && empty($uri[0]))
	$uri[0] = false;

// Load template (and parser) and assign content
require(SYS_LIBRARY_DIR.'template_parser.php');
$sys_tpl = new template_parser(APP_ROOT.'admin/themes/'.$sys_config['admin_theme'].'/');

// Assign tags
$sys_tpl->assign(
	array(
		'content_direction',
		'content_encoding',
		'content_type',
		'theme_path',
		'javascript',
		'admin_panel_title',
		'top_links',
		'admin_navigation',
		'software_version'),
	array(
		$sys_lang['s_direction'],
		$sys_lang['s_encoding'],
		'text/html',
		ADMIN_URL.'/themes/'.$sys_config['admin_theme'],
		'<script type="text/javascript" src="'.ADMIN_URL.'js/mootools.js"></script>'."\n\t".'<script type="text/javascript" src="'.ADMIN_URL.'js/init.js"></script>',
		$sys_config['website_title'].' Admin',
		'You are logged in as <a href="'.WEBSITE_URL.URI_PREFIX.'profile/'.$sys_user['id'].URI_SUFFIX.'" rel="external"><em>'.$sys_user['username'].'</em></a> |
		<a href="'.WEBSITE_URL.URI_PREFIX.'login'.URI_SUFFIX.'&amp;token='.SYS_TOKEN.'">Logout</a> |
		<a href="'.WEBSITE_URL.'" rel="external">Go to website</a>',
		generate_admin_navigation(),
		'<a href="http://code.google.com/p/shinobu/" rel="blank">Shinobu</a> '.SHINOBU_VERSION)
	);

// Start capturing stuff for the main content
ob_start();

// Load page
if (isset($sys_request[1]) && is_dir(APP_ROOT.'admin/pages/'.$sys_request[1]))
{
	if (isset($sys_request[2]) && is_file(APP_ROOT.'admin/pages/'.$sys_request[1].'/'.$sys_request[2].'.php'))
		require APP_ROOT.'admin/pages/'.$sys_request[1].'/'.$sys_request[2].'.php';
	else
		send_404(false, false, false);
}
else if (isset($sys_request[1]) && is_file(APP_ROOT.'admin/pages/'.$sys_request[1].'.php'))
	require APP_ROOT.'admin/pages/'.$sys_request[1].'.php';
else if (!isset($sys_request[1]))
	require APP_ROOT.'admin/pages/home.php';
else
	send_404(false, false, false);

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
