<?php

# =============================================================================
# site/controllers/admin/groups/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;

		$usergroups = array();
		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS g.id, g.name, g.description, COUNT(u.group_id) AS user_count FROM
			'.DB_PREFIX.'usergroups AS g LEFT JOIN '.DB_PREFIX.'users AS u ON u.group_id=g.id GROUP BY g.id LIMIT '.$start_offset.',20')
			or error($this->db->error);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$usergroups[] = $row;
		}
		else
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()') or error($this->db->error);
		list($group_count) = $result->fetch_row();

		$pagination = pagination($current_page, $group_count, url('admin/groups:%d'));

		return tpl::render('admin_groups', array(
			'website_section' => 'Administration',
			'page_title' => 'Groups',
			'subsection' => 'groups',
			'admin_perms' => $this->user->get_acl('administration'),
			'usergroups' => $usergroups,
			'pagination' => $pagination
			));
	}
}
