<?php

# =============================================================================
# site/controllers/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

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
