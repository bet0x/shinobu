<?php

class default_controller extends BaseWebController
{
	public function GET($args)
	{
		tpl::set('page_title', 'Home');
		tpl::set('page_body', '<p>This is the homepage.</p>');

		return tpl::render('home');
	}
}
