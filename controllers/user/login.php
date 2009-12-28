<?php

class login_controller extends BaseWebController
{
	public function GET($args)
	{
		return tpl::render('user_login', array(
			'page_title' => 'Log in',
			'error' => false
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_login']))
			redirect(tpl::url('user/login', true));

		if (user::login($args['form']['username'], $args['form']['password']) === 1)
		{
			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have been successfully logged in. You will be redirected to the homepage in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => SYSTEM_BASE_URL
				));
		}
		else
			tpl::set('error', true);

		return tpl::render('user_login', array(
			'page_title' => 'Log in'
			));
	}
}
