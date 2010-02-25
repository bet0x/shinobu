<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class users_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->get('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_users', array(
			'website_section' => 'Administration',
			'page_title' => 'Users',
			'page_body' => '<p>This is the administration panel.</p>',
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration')
			));
	}
}
