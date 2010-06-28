<?php

# =============================================================================
# application/base.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

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

		/* If the timezone isn't stored in a variable first, we'll get a
		   "Indirect modification of overloaded property"  notice. */
		$tmp = $this->config->timezone;

		$this->timedate = $this->load_module('timedate', $tmp);
		$this->timedate->date_format = $this->config->date_format;
		$this->timedate->time_format = $this->config->time_format;
	}
}
