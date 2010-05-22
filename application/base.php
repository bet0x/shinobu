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

		// Set some template variables
		tpl::set('website_title', $this->config->website_title);
		tpl::set('authenticated', $this->user->authenticated);

		// Do some extra things for authenticated users
		if ($this->user->authenticated)
		{
			tpl::set('username', $this->user->data['username']);
			tpl::set('admin_view', $this->user->check_acl('administration', ACL_PERM_1));
		}

		// Load main menu
		if (!($main_menu = cache::read('main_menu')))
		{
			$main_menu = array();
			$result = $this->db->query('SELECT name, path FROM '.DB_PREFIX.'menu ORDER BY position, name ASC')
				or error($this->db->error);

			if ($result->num_rows > 0)
			{
				while ($row = $result->fetch_assoc())
				{
					if ($row['path'][0] != '/' && !preg_match('/^(https?|ftp|irc)/i', $row['path']))
						$row['path'] = url($row['path']);

					$main_menu[] = $row;
				}
			}

			cache::write('main_menu', $main_menu);
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

// Generate HTML pagination links
function pagination($cur_page_nr, $items, $link, $limit = 20)
{
	$page_count = ceil($items / $limit);

	if ($page_count == 1 || $cur_page_nr > $page_count)
		return;

	// Define some important variables
	$prev_nr = 0;
	$range = 3;
	$padding = array(0 => 3, 1 => 2, 2 => 1, 3 => 0);
	$html = array();

	// Calculate start- and endpoint
	$start = $cur_page_nr-$range < 1 ? 1 : $cur_page_nr-$range;
	$end = $cur_page_nr+$range > $page_count ? $page_count : $cur_page_nr+$range;

	// Calculate left and right padding
	$left_padding = $padding[$end - $cur_page_nr];
	$right_padding = $padding[$cur_page_nr - $start];

	// Add padding to the start- and endpoint
	$start = $start - $left_padding < 1 ? 1 : $start - $left_padding;
	$end = $right_padding + $end > $page_count ? $page_count : $right_padding + $end;

	$page_nrs = range($start, $end);

	// Add a first and last page if necessary
	if ($start != 1)
		array_unshift($page_nrs, 1);
	if ($end < $page_count)
		$page_nrs[] = $page_count;

	// Previous page link
	$html[] = $cur_page_nr > 1 ? '<a href="'.sprintf($link, ($cur_page_nr-1)).'">Previous</a>' : '<span>Previous</span>';

	// Shoop da loop
	foreach ($page_nrs as $i => $nr)
	{
		if ($prev_nr+1 < $nr)
			$html[] = '&hellip;';

		$html[] = '<a href="'.sprintf($link, $nr).'">'.($nr == $cur_page_nr ? '<strong>['.$nr.']</strong>' : $nr).'</a>';
		$prev_nr = $nr;
	}

	// Next page link
	$html[] = $cur_page_nr < $page_count ? '<a href="'.sprintf($link, $cur_page_nr+1).'">Next</a>' : '<span>Next</span>';

	return '<p class="pagination">'.implode('&nbsp;&nbsp;', $html).'</p>';
}
