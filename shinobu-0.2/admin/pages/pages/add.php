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
	if ((isset($_POST['frm-submit']) || isset($_POST['frm-submit-add-link'])) && check_token())
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

			$sys_db->query('INSERT INTO '.DB_PREFIX.'content_info (type_id, title, author, create_date, status) VALUES(1, \''.$sys_db->escape($form['title']).'\', '.intval($sys_user['id']).', '.$now.', '.$form['status'].')') or error($sys_db->error(), __FILE__, __LINE__);
			$page_id = intval($sys_db->insert_id());
			$sys_db->query('INSERT INTO '.DB_PREFIX.'content_data (content_id, type_id, parser, data) VALUES('.$page_id.', 1, \''.$sys_db->escape($form['markup_parser']).'\', \''.$sys_db->escape($form['content']).'\')') or error($sys_db->error(), __FILE__, __LINE__);

			// Add a link to the page to the main navigation
			if ($sys_user['p_manage_nav'] == 1 && isset($_POST['frm-submit-add-link']))
			{
				if (utf8_strlen($form['title']) > 50)
					$form['title'] = utf8_substr($form['title'], 0, 50);

				$sys_db->query('INSERT INTO '.DB_PREFIX.'navigation (name, url) VALUES(\''.$sys_db->escape($form['title']).'\', \''.$sys_db->escape('p/'.$page_id.URI_SUFFIX).'\')') or error($sys_db->error(), __FILE__, __LINE__);
				generate_navigation(true);
			}

			header('location: '.ADMIN_URL.URI_PREFIX.'pages/manage'.URI_SUFFIX.'&added'); exit;
		}
	}

	// Set page title
	$sys_tpl->assign('page_title', 'Add New Page - '.$sys_config['website_title'].' Admin');

	$sys_tpl->add('javascript', '', "\n".'<!-- TinyMCE -->
	<script type="text/javascript" src="'.ADMIN_URL.'js/tinymce/tiny_mce.js"></script>
	<script type="text/javascript">
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

	?>

	<h2>Add New Page</h2>

	<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'pages/add'.URI_SUFFIX; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-hc">
			<li class="frm-block<?php echo isset($errors['title']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0">Title:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[title]" id="fld-0" maxlength="255" /></div>
				<?php echo isset($errors['title']) ? '<span class="fld-error-message">'.$errors['title'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-1">Status:</label></div>
				<div class="fld-input">
					<select name="form[status]" id="fld-1">
						<option value="0" selected="selected">Draft</option>
						<option value="1">Published</option>
					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-2">Markup parser:</label></div>
				<div class="fld-input">
					<select name="form[markup_parser]" id="fld-2" onchange="toggleEditor('fld-3', this)">
						<option value="xhtml" selected="selected">xHTML/PHP</option>
						<option value="xhtml_tinymce">xHTML (TinyMCE)</option>

						<?php

						$markup_parsers = get_markup_parsers();
						foreach ($markup_parsers as $markup_parser)
							echo '<option value="'.$markup_parser.'">'.$markup_parser.'</option>'."\n";

						?>

					</select>
				</div>
			</li>
		</ul>

		<ul class="frm-avc">
			<li class="frm-block<?php echo isset($errors['content']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-3">Content:</label></div>
				<div class="fld-input"><textarea name="form[content]" id="fld-3" class="big pre" rows="10" cols="50"></textarea></div>
				<?php echo isset($errors['content']) ? '<span class="fld-error-message">'.$errors['content'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input">
					<input type="submit" value="Add Page" name="frm-submit" />
					<?php if ($sys_user['p_manage_nav'] == 1): ?>
					<input type="submit" value="Add Page &amp; Add to navigation" name="frm-submit-add-link" title="Add a link to the navigation and add the page to the database" />
					<?php endif; ?>
				</div>
			</li>
		</ul>
	</form>

	<?php
}

?>
