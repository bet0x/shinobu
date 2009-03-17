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
	$has_nav_link = false;
	$sys_request[3] = isset($sys_request[3]) && !empty($sys_request[3]) ? intval($sys_request[3]) : 0;
	$result = $sys_db->query('SELECT i.title, i.create_date, i.edit_date, d.parser, d.data, i.status FROM '.DB_PREFIX.'content_info AS i INNER JOIN '.DB_PREFIX.'content_data AS d ON i.id=d.content_id WHERE i.id='.$sys_request[3].' AND i.type_id=1 LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

	// Check if this page has a link in the main navigation
	$result_link = $sys_db->query('SELECT n.* FROM '.DB_PREFIX.'navigation AS n WHERE n.url=\'p/'.$sys_request[3].URI_SUFFIX.'\'') or error($sys_db->error(), __FILE__, __LINE__);
	if ($sys_db->num_rows($result_link) > 0)
		$has_nav_link = true;

	if ($sys_db->num_rows($result) > 0)
	{
		$page = $sys_db->fetch_assoc($result);

		if ((isset($_POST['frm-submit']) || isset($_POST['frm-submit-link'])) && check_token())
		{
			$form = array_map('system_trim', $_POST['form']);
			$errors = false;

			$form['title'] = utf8_htmlencode($form['title']);
			$form['content'] = convert_linebreaks($form['content']);

			if (empty($form['title']))
				$errors['title'] = 'No title entered.';
			else if ($form['title'] > 255)
				$errors['title'] = 'The title is too long.';

			$form['status'] = $form['status'] == 1 ? 1 : 0;

			if (empty($form['content']))
				$errors['content'] = 'No content entered.';
			else if (utf8_strlen($form['content']) > 65535)
				$errors['content'] = 'The content is too long.';

			if ($errors === false)
			{
				$now = time();

				$sys_db->query('UPDATE '.DB_PREFIX.'content_info SET title=\''.$sys_db->escape($form['title']).'\', edit_date='.$now.', status='.$form['status'].' WHERE id='.$sys_request[3]) or error($sys_db->error(), __FILE__, __LINE__);
				$sys_db->query('UPDATE '.DB_PREFIX.'content_data SET parser=\''.$sys_db->escape($form['markup_parser']).'\', data=\''.$sys_db->escape($form['content']).'\' WHERE content_id='.$sys_request[3]) or error($sys_db->error(), __FILE__, __LINE__);

				if (file_exists(SYS_CACHE_DIR.'.cache_page_'.$sys_request[3]))
					unlink(SYS_CACHE_DIR.'.cache_page_'.$sys_request[3]);

				// Add, update or remove a link to the page to the main navigation
				if ($sys_user['p_manage_nav'] == 1)
				{
					if (isset($_POST['frm-submit-link']))
					{
						if ($has_nav_link === false)
						{
							if (utf8_strlen($form['title']) > 50)
								$form['title'] = utf8_substr($form['title'], 0, 50);

							$sys_db->query('INSERT INTO '.DB_PREFIX.'navigation (name, url) VALUES(\''.$sys_db->escape($form['title']).'\', \'p/'.$sys_request[3].URI_SUFFIX.'\')') or error($sys_db->error(), __FILE__, __LINE__);
							generate_navigation(true);
						}
						else if ($has_nav_link === true)
						{
							$sys_db->query('DELETE FROM '.DB_PREFIX.'navigation WHERE url=\'p/'.$sys_request[3].URI_SUFFIX.'\'') or error($sys_db->error(), __FILE__, __LINE__);
							generate_navigation(true);
						}

						header('location: '.ADMIN_URL.URI_PREFIX.'pages/edit/'.$sys_request[3].URI_SUFFIX.($has_nav_link === false ? '&add_navigation' : '&del_navigation')); exit;
					}
					else if ($has_nav_link === true)
					{
						if (utf8_strlen($form['title']) > 50)
							$form['title'] = utf8_substr($form['title'], 0, 50);

						$sys_db->query('UPDATE '.DB_PREFIX.'navigation SET name=\''.$sys_db->escape($form['title']).'\' WHERE url=\'p/'.$sys_request[3].URI_SUFFIX.'\'') or error($sys_db->error(), __FILE__, __LINE__);
						generate_navigation(true);
					}
				}

				header('location: '.ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX.'&edited'); exit;
			}
		}

		// Set page title
		$sys_tpl->assign('page_title', 'Edit Page - '.$sys_config['website_title'].' Admin');

		$sys_tpl->add('javascript', '', "\n".'<!-- TinyMCE -->
	<script type="text/javascript" src="'.ADMIN_URL.'js/tinymce/tiny_mce.js"></script>
	<script type="text/javascript">
		'.('xhtml_tinymce' == $page['parser'] ? 'start_tinymce();'."\n\t".'var start_tinymce = true;' : NULL).'

		function start_tinymce()
		{
			tinyMCE.init({
				// General options
				mode : "textareas",
				theme : "advanced",
				plugins : "safari,pagebreak,style,table,advimage,advlink,inlinepopups,insertdatetime,media,searchreplace,print,paste,directionality,fullscreen,visualchars,xhtmlxtras",

				// Theme options
				theme_advanced_buttons1 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,visualchars,visualaid,|,ltr,rtl,|,undo,redo,|,del,ins,attribs,|,pagebreak,|,removeformat,cleanup,code,|,print,fullscreen,help",
				theme_advanced_buttons2 : "formatselect,fontselect,fontsizeselect,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,numlist,bullist,outdent,indent,|,forecolor,backcolor,|,cite,abbr,acronym",
				theme_advanced_buttons3 : "tablecontrols,|,hr,|,sub,sup,|,charmap,styleprops,media,|,link,unlink,anchor,image,|,insertdate,inserttime,|,blockquote",
				theme_advanced_toolbar_location : "top",
				theme_advanced_toolbar_align : "left",
				theme_advanced_statusbar_location : "bottom",
				theme_advanced_resizing : true,

				button_tile_map : true,

				// Drop lists for link/image/media dialogs
				external_link_list_url : "lists/link_list.js",
				external_image_list_url : "lists/image_list.js",
				media_external_list_url : "lists/media_list.js",
			});

			var start_tinymce = true;
		}

		function toggleEditor(textarea_id, select_id)
		{
			if (select_id.value == \'xhtml_tinymce\')
			{
				if (start_tinymce == true)
					tinyMCE.execCommand(\'mceAddControl\', false, textarea_id);
				else
					start_tinymce();
			}
			else
			{
				tinyMCE.execCommand(\'mceRemoveControl\', false, textarea_id);
			}
		}
	</script>
	<!-- /TinyMCE -->');

		if (isset($_GET['add_navigation']))
			$sys_tpl->add('main_content', '<div class="success">Link succesfully added.</div>');
		else if (isset($_GET['del_navigation']))
			$sys_tpl->add('main_content', '<div class="success">Page succesfully removed.</div>');

		?>


	<h2>Edit Page: <?php echo $page['title']; ?></h2>

	<p>Created on <strong><?php echo format_time($page['create_date'], true); ?></strong>.<?php echo $page['edit_date'] != 0 ? ' Last edited on <strong>'.format_time($page['edit_date'], true).'</strong>.' : NULL; ?></p>

	<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'pages/edit/'.$sys_request[3].URI_SUFFIX; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-hc">
			<li class="frm-block<?php echo isset($errors['title']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0">Title:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[title]" id="fld-0" maxlength="255" value="<?php echo $page['title']; ?>" /></div>
				<?php echo isset($errors['title']) ? '<span class="fld-error-message">'.$errors['title'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-1">Status:</label></div>
				<div class="fld-input">
					<select name="form[status]" id="fld-1">
						<option value="0"<?php echo $page['status'] == 1 ? ' selected="selected"' : NULL; ?>>Draft</option>
						<option value="1"<?php echo $page['status'] == 1 ? ' selected="selected"' : NULL; ?>>Published</option>
					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-2">Markup parser:</label></div>
				<div class="fld-input">
					<select name="form[markup_parser]" id="fld-2" onchange="toggleEditor('fld-3', this)">
						<option value="xhtml"<?php echo 'xhtml' == $page['parser'] ? ' selected="selected"' : NULL; ?>>xHTML/PHP</option>
						<option value="xhtml_tinymce"<?php echo 'xhtml_tinymce' == $page['parser'] ? ' selected="selected"' : NULL; ?>>xHTML (TinyMCE)</option>

						<?php

						$markup_parsers = get_markup_parsers();
						foreach ($markup_parsers as $markup_parser)
							echo '<option value="'.$markup_parser.'"'.($markup_parser == $page['parser'] ? ' selected="selected"' : NULL).'>'.$markup_parser.'</option>'."\n";

						?>

					</select>
				</div>
			</li>
		</ul>

		<ul class="frm-avc">
			<li class="frm-block<?php echo isset($errors['content']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-3">Content:</label></div>
				<div class="fld-input"><textarea name="form[content]" id="fld-3" class="big pre" rows="10" cols="50"><?php echo utf8_htmlencode($page['data']); ?></textarea></div>
				<?php echo isset($errors['content']) ? '<span class="fld-error-message">'.$errors['content'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input">
					<input type="submit" value="Save page" name="frm-submit" />
					<?php if ($sys_user['p_manage_nav'] == 1): if ($has_nav_link === false): ?>
					<input type="submit" value="Add to navigation" name="frm-submit-link" title="Add a link to the navigation and continue editing" />
					<?php else: ?>
					<input type="submit" value="Remove from navigation" name="frm-submit-link" title="Remove the link from the navigation and continue editing" />
					<?php endif; endif; ?>
					&nbsp;&nbsp;<input type="button" onclick="window.location='<?php echo ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX; ?>'" value="Cancel" name="frm-cancel" />
				</div>
			</li>
		</ul>
	</form>

		<?php
	}
}

?>
