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

// Construct admin menu
function generate_admin_navigation()
{
	global $sys_user, $sys_plugins, $sys_request;

	$plugin_navigation = NULL;

	// System management navigation
	$main_nav = '<h3 class="menu-title">System</h3>'."\n".'<ul class="menu-items">'."\n";
	$main_nav .= '<li><a'.(empty($sys_request[1]) || $sys_request[1] == 'home' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'home'.URI_SUFFIX.'">Home</a></li>';

	if ($sys_user['gid'] == ADMIN_GID)
		$main_nav .= '<li><a'.(isset($sys_request[1]) && $sys_request[1] == 'options' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'options'.URI_SUFFIX.'">Options</a></li>';

	if ($sys_user['p_manage_nav'] == 1)
		$main_nav .= '<li><a'.(isset($sys_request[1]) && $sys_request[1] == 'navigation' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'navigation'.URI_SUFFIX.'">Navigation</a></li>';

	if ($sys_user['p_manage_users'] == 1)
	{
		$main_nav .= '<li><a'.(isset($sys_request[1]) && $sys_request[1] == 'usergroups' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'">Usergroups</a></li>';
		$main_nav .= '<li><a'.(isset($sys_request[1]) && $sys_request[1] == 'users' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'">Users</a></li>';
	}

	$main_nav .= '</ul>';

	// Page mangement navigation
	$main_nav .= '<h3 class="menu-title">Pages</h3>'."\n".'<ul class="menu-items">'."\n";
	$main_nav .= '<li><a'.(isset($sys_request[2]) && $sys_request[2] == 'add' ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'pages/add'.URI_SUFFIX.'">Add new page</a></li>';
	$main_nav .= '<li><a'.(isset($sys_request[2]) && $sys_request[1] == 'pages' && ($sys_request[2] == 'manage' || $sys_request[2] == 'edit') ? ' class="current"' : NULL).' href="'.ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX.'">Manage pages</a></li>';
	$main_nav .= '</ul>';

	return $main_nav.$plugin_navigation;
}

// Returns all available themes
function get_themes()
{
	$scan = scandir(SYS_THEME_DIR);

	foreach ($scan as $item)
		if (is_dir(SYS_THEME_DIR.$item) && file_exists(SYS_THEME_DIR.$item.'/template.tpl'))
			$styles[] = $item;

	return $styles;
}

// Returns all available themes for the administration panel
function get_admin_themes()
{
	$scan = scandir(APP_ROOT.'admin/themes/');

	foreach ($scan as $item)
		if (is_dir(APP_ROOT.'admin/themes/'.$item) && file_exists(APP_ROOT.'admin/themes/'.$item.'/template.tpl'))
			$themes[] = $item;

	return $themes;
}

// Returns all available markup parsers
function get_markup_parsers()
{
	$scan = glob(SYS_LIBRARY_DIR.'markup_parsers/*.php');

	foreach ($scan as $item)
		$markup_parsers[] = substr(basename($item), 0, -4);

	return $markup_parsers;
}

?>
