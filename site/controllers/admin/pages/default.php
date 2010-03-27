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
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		$pages = array();
		$result = $this->db->query('SELECT p.id, p.title, p.is_published, u.username AS author FROM '.DB_PREFIX.'pages AS p
			LEFT JOIN '.DB_PREFIX.'users AS u ON u.id=p.author_id ORDER BY p.id ASC')
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

		return tpl::render('admin_pages', array(
			'website_section' => 'Administration',
			'page_title' => 'Pages',
			'subsection' => 'pages',
			'admin_perms' => $this->acl->get('administration'),
			'pages' => $pages
			));
	}
}