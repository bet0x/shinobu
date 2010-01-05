<?php

class default_controller extends AuthWebController
{
	public function GET($args)
	{
		return tpl::render('home', array(
			'page_title' => 'Home',
			'page_body' => '<p>This is the homepage.</p>',
			));
	}
}
