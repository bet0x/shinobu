<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU')) ? exit : NULL;

// Set page title
$sys_tpl->assign('page_title', 'Home - '.$sys_config['website_title']);

?>

<div id="home">

<?php

if ($sys_config['welcome_message_display'] === 1)
	echo '<h2><span>'.$sys_config['welcome_message_title'].'</span></h2>'."\n".$sys_config['welcome_message_body'];

?>

</div>