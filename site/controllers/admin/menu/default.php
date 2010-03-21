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
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);

		$m_items = array();
		$result = $this->db->query('SELECT id, name, path FROM '.DB_PREFIX.'menu ORDER BY position ASC')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$m_items[] = $row;
		}

		return tpl::render('admin_menu', array(
			'website_section' => 'Administration',
			'page_title' => 'Menu',
			'subsection' => 'menu',
			'admin_perms' => $this->acl->get('administration'),
			'm_items' => $m_items
			));
	}
}
