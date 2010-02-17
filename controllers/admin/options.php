<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class options_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->module->user->authenticated() || !($this->module->acl->get('administration') & ACL_PERM_5))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_options', array(
			'page_title' => 'Administration',
			'page_body' => '<p>This is the administration panel.</p>',
			'section' => 'options',
			'admin_perms' => $this->module->acl->get('administration')
			));
	}
}
