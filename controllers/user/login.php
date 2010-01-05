<?php

class login_controller extends AuthWebController
{
	protected function prepare()
	{
		if ($this->user->authenticated())
			$this->redirect(SYSTEM_BASE_URL);
	}

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
			$this->redirect(utils::url('user/login'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		if ($this->user->login($args['form']['username'], $args['form']['password']) === 1)
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
