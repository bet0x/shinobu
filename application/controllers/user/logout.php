<?php

# =============================================================================
# application/controllers/user/logout.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class logout_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !isset($_GET[xsrf::token()]))
			$this->redirect(SYSTEM_BASE_URL);

		$this->user->logout();

		return tpl::render('redirect', array(
			'redirect_message' => '<p>You have been successfully logged out. You will be redirected to the homepage in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => SYSTEM_BASE_URL
			));
	}
}
