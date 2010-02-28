<?php

# =============================================================================
# controllers/user/logout.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class logout_controller extends AuthWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated())
			$this->redirect(SYSTEM_BASE_URL);

		$this->request['path'] = explode('/', $this->request['path']);
		if (!isset($this->request['path'][1]) || !isset($_GET[utils::xsrf_token()]))
			return $this->send_error(403);

		$this->user->logout();

		return tpl::render('redirect', array(
			'redirect_message' => '<p>You have been successfully logged out. You will be redirected to the homepage in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => SYSTEM_BASE_URL
			));
	}
}
