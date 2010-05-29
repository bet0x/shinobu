<?php

# =============================================================================
# application/controllers/admin/users/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;

		$users = array();
		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS u.id, u.username, g.user_title FROM '.DB_PREFIX.'users AS u, '.
			DB_PREFIX.'usergroups AS g WHERE g.id=u.group_id ORDER BY u.username LIMIT '.$start_offset.',20')
			or error($this->db->error);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$users[] = $row;
		}
		else
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()') or error($this->db->error);
		list($user_count) = $result->fetch_row();

		$pagination = pagination($current_page, $user_count, url('admin/users:%d'));

		return tpl::render('admin_users', array(
			'website_section' => 'Administration',
			'page_title' => 'Users',
			'subsection' => 'users',
			'admin_perms' => $this->user->get_acl('administration'),
			'users' => $users,
			'pagination' => $pagination
			));
	}
}
