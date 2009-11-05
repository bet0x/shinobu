<?php

// The name of the controller class is the filename and '_controller',
// 'default_controller' in this case.
class default_controller extends BaseController
{
	public function GET($args)
	{
		system::set_mimetype('html');

		tpl::set('page_title', 'Hello!');

		if (user::$logged_in === true)
			tpl::set('test', 'I\'m logged in!');
		else
			tpl::set('test', 'I\'m not logged in.');

		return tpl::render('basic',
			array(
				'page_title' => 'Hello world!'
				)
			);
	}
}
