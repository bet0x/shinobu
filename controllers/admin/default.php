<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends AuthWebController
{
	public function prepare()
	{
		global $mc;

		if (!$this->user->authenticated() || !($mc->acl->get('admin_read') & ACL_READ))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('basic', array(
			'page_title' => 'Administration',
			'page_body' => '<p>This is the administration panel.</p>',
			));
	}
}
