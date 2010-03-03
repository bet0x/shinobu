<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class menu_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_menu', array(
			'website_section' => 'Administration',
			'page_title' => 'Menu',
			'page_body' => '<p>This is the administration panel.</p>',
			'subsection' => 'menu',
			'admin_perms' => $this->acl->get('administration')
			));
	}
}
