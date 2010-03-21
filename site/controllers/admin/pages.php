<?php

# =============================================================================
# site/controllers/admin/pages.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class pages_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);

		return tpl::render('admin_pages', array(
			'website_section' => 'Administration',
			'page_title' => 'Pages',
			'page_body' => '<p>This is the administration panel.</p>',
			'subsection' => 'pages',
			'admin_perms' => $this->acl->get('administration')
			));
	}
}
