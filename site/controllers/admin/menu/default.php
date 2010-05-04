<?php

# =============================================================================
# site/controllers/admin/menu/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;

		$m_items = array();
		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS id, name, path FROM '.DB_PREFIX.'menu
			ORDER BY position, name ASC LIMIT '.$start_offset.',20')
			or error($this->db->error);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$m_items[] = $row;
		}
		elseif ($current_page !== 1)
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()') or error($this->db->error);
		list($item_count) = $result->fetch_row();

		$pagination = pagination($item_count, 20, $current_page, url('admin/menu:%d'));

		return tpl::render('admin_menu', array(
			'website_section' => 'Administration',
			'page_title' => 'Menu',
			'subsection' => 'menu',
			'admin_perms' => $this->user->get_acl('administration'),
			'm_items' => $m_items,
			'pagination' => $pagination
			));
	}
}
