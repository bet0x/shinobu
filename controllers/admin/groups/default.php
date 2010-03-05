<?php

# =============================================================================
# controllers/admin/groups/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		// Get usergroups
		$usergroups = array();
		$result = $this->db->query('SELECT g.id, g.name, g.description, COUNT(u.group_id) AS user_count FROM '.DB_PREFIX.'usergroups AS g '.
		                           'LEFT JOIN '.DB_PREFIX.'users AS u ON u.group_id=g.id GROUP BY g.id')
			or error('Unable to fetch usergroups.', __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$usergroups[] = $row;
		}

		return tpl::render('admin_groups', array(
			'website_section' => 'Administration',
			'page_title' => 'Groups',
			'subsection' => 'groups',
			'admin_perms' => $this->acl->get('administration'),

			'usergroups' => $usergroups
			));
	}
}
