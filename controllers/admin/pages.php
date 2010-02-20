<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class pages_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->module->user->authenticated() || !($this->module->acl->get('administration') & ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_pages', array(
			'website_section' => 'Administration',
			'page_title' => 'Pages',
			'page_body' => '<p>This is the administration panel.</p>',
			'subsection' => 'pages',
			'admin_perms' => $this->module->acl->get('administration')
			));
	}
}
