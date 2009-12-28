<?php

class logout_controller extends BaseWebController
{
	public function GET($args)
	{
		if (!user::$logged_in)
			redirect(SYSTEM_BASE_URL);

		user::logout();

		return tpl::render('redirect', array(
			'redirect_message' => '<p>You have been successfully logged out. You will be redirected to the homepage in 2 seconds.</p>',
			'redirect_delay' => 2,
			'destination_url' => SYSTEM_BASE_URL
			));
	}
}
