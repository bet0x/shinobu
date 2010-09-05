<?php

# =============================================================================
# application/controllers/admin/menu/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'menu'))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;

		$m_items = array();
		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS id, name, path FROM '.DB_PREFIX.'menu
			ORDER BY position, name ASC LIMIT '.$start_offset.',20');

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$m_items[] = $row;
		}
		elseif ($current_page !== 1)
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()');
		$item_count = $result->fetch_offset();

		$pagination = pagination($current_page, $item_count, url('admin/menu:%d'));

		return tpl::render('admin_menu', array(
			'website_section' => 'Administration',
			'page_title' => 'Menu',
			'subsection' => 'menu',
			'm_items' => $m_items,
			'pagination' => $pagination
			));
	}
}
