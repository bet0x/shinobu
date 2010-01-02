<?php

class logout_controller extends BaseWebController
{
	public function GET($args)
	{
		if (!user::$logged_in)
			$this->redirect(SYSTEM_BASE_URL);

		if (!isset($this->request[2]) || !utils::check_xsrf_cookie($this->request[2]))
			return $this->send_error(403);

		user::logout();

		return tpl::render('redirect', array(
			'redirect_message' => '<p>You have been successfully logged out. You will be redirected to the homepage in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => SYSTEM_BASE_URL
			));
	}
}
