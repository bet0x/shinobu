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

(!defined('SHINOBU')) ? exit : NULL;

$sys_request[2] = isset($sys_request[2]) && !empty($sys_request[2]) ? intval($sys_request[2]) : 0;
$cache_file_name = '.cache_page_'.$sys_request[2];

$result = $sys_db->query('
	SELECT p.id, p.title, p.create_date, p.edit_date, u.id AS uid, u.username
	FROM '.DB_PREFIX.'content_info AS p
	LEFT JOIN '.DB_PREFIX.'users AS u ON p.author=u.id
	WHERE p.id='.$sys_request[2].' AND p.type_id=1 AND p.status=1 LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

// Check if page exists
if ($sys_db->num_rows($result) > 0)
{
	$page_info = $sys_db->fetch_assoc($result);

	$sys_tpl->assign('page_title', $page_info['title'].' - '.$sys_config['website_title']);
	echo '<div id="page">'."\n".'<h2><span>'.$page_info['title'].'</span></h2>';

	// Fetch page content
	if (file_exists(SYS_CACHE_DIR.$cache_file_name))
		require SYS_CACHE_DIR.$cache_file_name;
	else
	{
		$result = $sys_db->query('SELECT c.parser, c.data FROM '.DB_PREFIX.'content_data AS c WHERE c.content_id='.$page_info['id'].' AND c.type_id=1 LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

		if ($sys_db->num_rows($result) > 0)
		{
			$page_content = $sys_db->fetch_assoc($result);

			if (file_exists(SYS_LIBRARY_DIR.'markup_parsers/'.$page_content['parser'].'.php') && ($page_content['parser'] !== 'xhtml' || $page_content['parser'] !== 'xhtml_tinymce'))
			{
				require SYS_LIBRARY_DIR.'markup_parsers/'.$page_content['parser'].'.php';
				$page_content['data'] = parse($page_content['data']);
			}

			cache_data($cache_file_name, $page_content['data']);
			require SYS_CACHE_DIR.$cache_file_name;
		}
		else
			echo '<p>'.$sys_lang['e_page_no_data_error'].'</p>';
	}

	?>

	<div class="page-toolbox">
		<?php echo sprintf($sys_lang['d_page_created_by'], '<a href="'.WEBSITE_URL.URI_PREFIX.'profile/'.$page_info['uid'].URI_SUFFIX.'">'.utf8_htmlencode($page_info['username']).'</a>', format_time($page_info['create_date']));
		echo $page_info['edit_date'] == 0 ? NULL : sprintf($sys_lang['d_page_edited_on'], format_time($page_info['edit_date']))."\n"; ?>
	</div>
</div>

	<?php
}
else
	send_404();

?>
