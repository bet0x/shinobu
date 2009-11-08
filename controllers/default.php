<?php

// The name of the controller class is the filename and '_controller',
// 'default_controller' in this case.
class default_controller extends BaseController
{
	public function GET($args)
	{
		system::set_mimetype('html');

		if (user::$logged_in === true)
			tpl::set('test', 'I\'m logged in!');
		else
			tpl::set('test', 'I\'m not logged in.');

		// Run all the actions for this hook and pass some arguments
		extensions::run_hook('test_hook', 'arg1', 3, 5.2, true);

		return tpl::render('basic',
			array(
				'page_title' => 'Hello world!'
				)
			);
	}
}
