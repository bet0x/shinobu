<?php

# =============================================================================
# site/controllers/admin/pages/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->acl->check('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		$current_page = $this->request['args'] ? intval($this->request['args']) : 1;
		$start_offset = ($current_page-1) * 20;

		$pages = array();
		$result = $this->db->query('SELECT SQL_CALC_FOUND_ROWS p.id, p.title, p.is_published, u.username AS author
			FROM '.DB_PREFIX.'pages AS p LEFT JOIN '.DB_PREFIX.'users AS u ON u.id=p.author_id ORDER BY p.id ASC
			LIMIT '.$start_offset.',20')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
			{
				if (empty($row['author']))
					$row['author'] = 'Unknown';

				$pages[] = $row;
			}
		}
		elseif ($current_page !== 1)
			return $this->send_error(404);

		$result = $this->db->query('SELECT FOUND_ROWS()') or error($this->db->error, __FILE__, __LINE__);
		list($page_count) = $result->fetch_row();

		$pagination = pagination($page_count, 20, $current_page, url('admin/pages:%d'));

		return tpl::render('admin_pages', array(
			'website_section' => 'Administration',
			'page_title' => 'Pages',
			'subsection' => 'pages',
			'admin_perms' => $this->acl->get('administration'),
			'pages' => $pages,
			'pagination' => $pagination
			));
	}
}
