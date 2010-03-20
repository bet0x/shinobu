<?php

# =============================================================================
# site/controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		// Get users
		$users = array();
		$result = $this->db->query('SELECT u.id, u.username, g.user_title FROM '.DB_PREFIX.'users AS u, '.DB_PREFIX.'usergroups AS g '.
		                           'WHERE g.id=u.group_id')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$users[] = $row;
		}

		return tpl::render('admin_users', array(
			'website_section' => 'Administration',
			'page_title' => 'Users',
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration'),
			'users' => $users
			));
	}
}
