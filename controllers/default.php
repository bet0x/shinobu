<?php

// defaultc (default controller) is used, because 'default' is a reserved keyword
class default_controller extends BaseController
{
	public function GET($args)
	{
		system::set_mimetype('html');

		tpl::set('page_title', 'Hello world!');

		if (user::$logged_in === true)
			tpl::set('test', 'I\'m logged in!');
		else
			tpl::set('test', 'I\'m not logged in.');

		return tpl::render('basic');
	}
}
