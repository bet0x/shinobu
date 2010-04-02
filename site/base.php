<?php

# =============================================================================
# site/base.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

/* In the base.php file of a site or application, all the basecontrollers and
common function (the ones that are used the most) are defined in this file */

/* This is the main controller for the CMS. All the needed modules, the menu and
user system are loaded and configured here. */
abstract class CmsWebController extends BaseController
{
	protected $timedate = null;

	public function __construct($request)
	{
		$this->request = $request;
		$this->set_mimetype('html');

		// Load modules
		$this->db = $this->load_module('db');
		$this->config = $this->load_module('config', $this->db);
		$this->user = $this->load_module('user', $this->db);
		$this->acl = $this->load_module('acl', $this->db);

		// Set some template variables
		tpl::set('website_title', $this->config->website_title);
		tpl::set('authenticated', $this->user->authenticated);

		// Do some extra things for authenticated users
		if ($this->user->authenticated)
		{
			$this->acl->set_gid($this->user->data['group_id']);

			tpl::set('username', $this->user->data['username']);
			tpl::set('admin_view', $this->acl->check('administration', ACL_PERM_1));
		}

		// Load main menu
		$main_menu = array();
		$result = $this->db->query('SELECT name, path FROM '.DB_PREFIX.'menu ORDER BY position, name ASC')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
			while ($row = $result->fetch_assoc())
			{
				if ($row['path'][0] != '/' && !preg_match('/^(https?|ftp|irc)/i', $row['path']))
					$row['path'] = url($row['path']);

				$main_menu[] = $row;
			}

		tpl::set('main_menu', $main_menu);

		// Testing
		/*$this->acl->set('administration', $this->acl->get('administration')
			 | ACL_PERM_3 | ACL_PERM_4 | ACL_PERM_5
			 | ACL_PERM_6 | ACL_PERM_7 | ACL_PERM_8);
		echo '<pre>';
		print_r($this->acl->get('administration'));
		echo '</pre>';
		echo '<pre>';
		print_r($this->acl->check('administration', ACL_PERM_1));
		echo '</pre>';*/

		$this->pre_output = $this->prepare();

		if (!is_null($this->pre_output))
			$this->interrupt = true;
	}

	/* The timedate module is wrapped in a function unlike the other modules,
	because there are some more things that need to be configured in the timedate
	module */
	protected function load_timedate()
	{
		if ($this->timedate)
			return;

		$this->timedate = $this->load_module('timedate', $this->config->timezone);
		$this->timedate->date_format = $this->config->date_format;
		$this->timedate->time_format = $this->config->time_format;
	}
}

// Generates HTML pagination links
function pagination($item_count, $limit, $cur_page, $link)
{
	$page_count = ceil($item_count / $limit);

	if ($page_count <= 1)
		return;

	$pages = $page_range = array();

	// Calculate range
	$lowest = $cur_page - 3 < 2 ? 2 : $cur_page - 3;
	$highest = $cur_page + 3 > $page_count-1 ? $page_count-1 : $cur_page + 3;

	if ($page_count > 7)
	{
		$range_padding = array(-1 => 4, 0 => 3, 1 => 2, 2 => 1, 3 => 0);
		$page_range = range(
			$lowest - $range_padding[$highest - $cur_page],
			$highest + $range_padding[$cur_page - $lowest]);
	}
	elseif ($page_count > 2)
		$page_range = range($lowest, $highest);

	// Previous and first page links
	$pages[] = $cur_page > 1 ? '<a href="'.sprintf($link, ($cur_page-1)).'">Previous</a>' : '<span>Previous</span>';
	$pages[] = '<a href="'.sprintf($link, 1).'">'.($cur_page == 1 ? '<strong>1</strong>' : '1').'</a>';

	if ($cur_page > 5) $pages[] = '&hellip;';

	// Pages that are in the range
	foreach ($page_range as $nr)
		$pages[] = '<a href="'.sprintf($link, $nr).'">'.($cur_page == $nr ? '<strong>'.$nr.'</strong>' : $nr).'</a>';

	if ($cur_page < $page_count-4) $pages[] = '&hellip;';

	// Last and previous page links
	$pages[] = '<a href="'.sprintf($link, $page_count).'">'.($cur_page == $page_count ?
		'<strong>'.$page_count.'</strong>' : $page_count).'</a>';
	$pages[] = $cur_page < $page_count ? '<a href="'.sprintf($link, $cur_page+1).'">Next</a>' : '<span>Next</span>';

	return '<p class="pagination">'.implode('&nbsp;&nbsp;', $pages).'</p>';
}
