<?php

# =============================================================================
# site/basecontrollers.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

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

		$authenticated = $this->user->authenticated();

		// Set some template variables
		tpl::set('website_title', $this->config->website_title);
		tpl::set('authenticated', $authenticated);

		// Do some extra things for authenticated users
		if ($authenticated)
		{
			$this->acl->set_gid($this->user->data['group_id']);

			tpl::set('username', $this->user->data['username']);
			tpl::set('admin_view', $this->acl->check('administration', ACL_PERM_1));
		}

		// Load menu
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

	protected function load_timedate()
	{
		if ($this->timedate)
			return;

		$this->timedate = $this->load_module('timedate', $this->config->timezone);
		$this->timedate->date_format = $this->config->date_format;
		$this->timedate->time_format = $this->config->time_format;
	}
}
