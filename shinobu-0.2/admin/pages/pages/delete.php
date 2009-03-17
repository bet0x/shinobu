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
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU_ADMIN')) ? exit : NULL;

if ($sys_user['p_manage_pages'] == 0)
{
	// Set page title
	$sys_tpl->assign('page_title', $sys_lang['e_error'].' - '.$sys_config['website_title'].' Admin');

	?>

<h2><span><?php echo $sys_lang['e_error']; ?></span></h2>

<p>You have no permission to access this page</p>

	<?php
}
else
{
	$sys_request[3] = isset($sys_request[3]) ? intval($sys_request[3]) : false;
	$result = $sys_db->query('SELECT p.id FROM '.DB_PREFIX.'content_info AS p WHERE p.id='.$sys_request[3]) or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) && $sys_request[3] !== false && check_token(true))
	{
		$sys_db->query('DELETE FROM '.DB_PREFIX.'content_info WHERE id='.$sys_request[3].' AND type_id=1') or error($sys_db->error(), __FILE__, __LINE__);
		$sys_db->query('DELETE FROM '.DB_PREFIX.'content_data WHERE content_id='.$sys_request[3].' AND type_id=1') or error($sys_db->error(), __FILE__, __LINE__);

		if (file_exists(SYS_CACHE_DIR.'.cache_page_'.$sys_request[3]))
			unlink(SYS_CACHE_DIR.'.cache_page_'.$sys_request[3]);

		header('location: '.ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX.'&deleted'); exit;
	}
	else
	{
		header('location: '.ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX.'&delete_error'); exit;
	}
}

?>
