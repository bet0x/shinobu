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

if ($sys_user['gid'] != ADMIN_GID)
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
	// Update system settings/options
	if (isset($_POST['frm-submit']) && check_token())
	{
		$form = array_map('system_trim', $_POST['form']);
		$errors = false;

		$form['website_title'] = utf8_htmlencode($form['website_title']);
		$form['welcome_message_title'] = utf8_htmlencode($form['welcome_message_title']);

		if (empty($form['website_title']))
			$errors['website_title'] = 'You have to enter a title for your website.';
		else if (utf8_strlen($form['website_title']) > 255)
			$errors['website_title'] = 'The title you entered is too long.';

		if (utf8_strlen($form['website_description']) > 255)
			$errors['website_description'] = 'The description you entered is too long.';

		if (!in_array($form['theme'], get_themes()))
			$form['theme'] = $sys_config['theme'];

		if (!in_array($form['admin_theme'], get_admin_themes()))
			$form['admin_theme'] = $sys_config['admin_theme'];

		$form['welcome_message_display'] = $form['welcome_message_display'] == 1 ? 1: 0;

		if (empty($form['welcome_message_title']))
			$errors['welcome_message_title'] = 'You haven\'t entered a title.';
		else if (utf8_strlen($form['welcome_message_title']) > 255)
			$errors['welcome_message_title'] = 'Your title is too long';

		if (empty($form['welcome_message_body']))
			$errors['welcome_message_body'] = 'You have to enter a welcome message.';
		else if (utf8_strlen($form['welcome_message_body']) > 65535)
			$errors['welcome_message_body'] = 'Your welcome message is too long.';

		if (!is_numeric($form['timezone']))
			$form['timezone'] = 0;

		$form['dst'] = isset($form['dst']) && $form['dst'] == 1 ? 1 : 0;

		if (!in_array($form['language'], get_languages()))
			$form['language'] = $sys_config['language'];

		$form['user_online_stats'] = isset($form['user_online_stats']) && $form['user_online_stats'] == 1 ? 1 : 0;
		$form['show_who_is_online'] = isset($form['show_who_is_online']) && $form['show_who_is_online'] == 1 ? 1 : 0;
		$form['allow_new_registrations'] = isset($form['allow_new_registrations']) && $form['allow_new_registrations'] == 1 ? 1 : 0;
		$form['allow_username_change'] = isset($form['allow_username_change']) && $form['allow_username_change'] == 1 ? 1 : 0;

		// Usergroup check
		$form['default_usergroup'] = intval($form['default_usergroup']);
		if ($form['default_usergroup'] != $sys_config['default_usergroup'] && $form['default_usergroup'] != GUEST_GID)
		{
			$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);

			if ($sys_db->num_rows($result) < 1)
				$form['default_usergroup'] = $sys_config['default_usergroup'];
		}
		else
			$form['default_usergroup'] = $sys_config['default_usergroup'];

		$form['visit_timeout'] = intval($form['visit_timeout']);

		if ($errors === false)
		{
			foreach ($form as $k => $v)
				$sys_db->query('UPDATE '.DB_PREFIX.'config SET value=\''.$sys_db->escape($v).'\' WHERE name=\''.$sys_db->escape($k).'\'') or error($sys_db->error(), __FILE__, __LINE__);

			cache_config();

			header('location: '.ADMIN_URL.URI_PREFIX.'options'.URI_SUFFIX.'&updated'); exit;
		}
	}

	// Set page title
	$sys_tpl->assign('page_title', 'Options - '.$sys_config['website_title'].' Admin');

	$sys_tpl->add('javascript', '', "\n".'<!-- TinyMCE -->
	<script type="text/javascript" src="'.ADMIN_URL.'js/tinymce/tiny_mce.js"></script>
	<script type="text/javascript">
		tinyMCE.init({
			// General options
			mode : "textareas",
			theme : "advanced",
			plugins : "safari,advimage,advlink,inlinepopups,paste,directionality,noneditable,xhtmlxtras",

			// Theme options
			theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,numlist,bullist,|,blockquote,|,link,unlink,anchor,image,|,justifyleft,justifycenter,justifyright,justifyfull",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_statusbar_location : "bottom",
			theme_advanced_resizing : true,

			button_tile_map : true,

			// Drop lists for link/image/media dialogs
			external_link_list_url : "lists/link_list.js",
			external_image_list_url : "lists/image_list.js",
		});
	</script>
	<!-- /TinyMCE -->');

	if (isset($_GET['updated']))
		$sys_tpl->add('main_content', '<div class="success">Options succesfully updated.</div>');

	?>

	<h2>Options</h2>

	<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'options'.URI_SUFFIX; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-vc">
			<li class="frm-title">
				<h3>Website</h3>
			</li>

			<li class="frm-block<?php echo isset($errors['website_title']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0">Title:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[website_title]" id="fld-0" maxlength="20" value="<?php echo $sys_config['website_title']; ?>" /></div>
				<?php echo isset($errors['website_title']) ? '<span class="fld-error-message">'.$errors['website_title'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['website_description']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-1">Description:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[website_description]" id="fld-1" maxlength="100" value="<?php echo utf8_htmlencode($sys_config['website_description']); ?>" /></div>
				<?php echo isset($errors['website_description']) ? '<span class="fld-error-message">'.$errors['website_description'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-2">Theme:</label></div>
				<div class="fld-input">
					<select name="form[theme]" id="fld-2">

					<?php

					$themes = get_themes();
					foreach ($themes as $theme)
						echo '<option value="'.$theme.'" '.($theme == $sys_config['theme'] ? ' selected="selected"' : NULL).'>'.$theme.'</option>'."\n";

					?>

					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-3">Admin theme:</label></div>
				<div class="fld-input">
					<select name="form[admin_theme]" id="fld-3">

					<?php

					$themes = get_admin_themes();
					foreach ($themes as $theme)
						echo '<option value="'.$theme.'" '.($theme == $sys_config['admin_theme'] ? ' selected="selected"' : NULL).'>'.$theme.'</option>'."\n";

					?>

					</select>
				</div>
			</li>

			<li class="frm-title">
				<h3>Welcome message</h3>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>Display message:</label></div>
				<div class="fld-text">
					<div><label for="fld-4"><input type="radio" id="fld-4" name="form[welcome_message_display]" value="1" <?php echo $sys_config['welcome_message_display'] == 1 ? 'checked="checked"' : NULL; ?> /> Yes</label></div>
					<div><label for="fld-5"><input type="radio" id="fld-5" name="form[welcome_message_display]" value="0" <?php echo $sys_config['welcome_message_display'] == 0 ? 'checked="checked"' : NULL; ?> /> No</label></div>
				</div>
			</li>

			<li class="frm-block<?php echo isset($errors['welcome_message_title']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-6">Title:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[welcome_message_title]" id="fld-6" maxlength="100" value="<?php echo $sys_config['welcome_message_title']; ?>" /></div>
				<?php echo isset($errors['welcome_message_title']) ? '<span class="fld-error-message">'.$errors['welcome_message_title'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['welcome_message_body']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-7">Message text:</label></div>
				<div class="fld-input"><textarea name="form[welcome_message_body]" id="fld-7" rows="15" cols="70"><?php echo utf8_htmlencode($sys_config['welcome_message_body']); ?></textarea></div>
				<?php echo isset($errors['welcome_message_body']) ? '<span class="fld-error-message">'.$errors['welcome_message_body'].'</span>' : NULL; ?>
			</li>

			<li class="frm-title">
				<h3>Localisation</h3>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-8">Timezone:</label></div>
				<div class="fld-text">
					<select id="fld-8" name="form[timezone]">
						<option value="-12"<?php if ($sys_config['timezone'] == -12) echo ' selected="selected"' ?>>(UTC-12:00) International Date Line West</option>
						<option value="-11"<?php if ($sys_config['timezone'] == -11) echo ' selected="selected"' ?>>(UTC-11:00) Niue, Samoa</option>
						<option value="-10"<?php if ($sys_config['timezone'] == -10) echo ' selected="selected"' ?>>(UTC-10:00) Hawaii-Aleutian, Cook Island</option>
						<option value="-9.5"<?php if ($sys_config['timezone'] == -9.5) echo ' selected="selected"' ?>>(UTC-09:30) Marquesas Islands</option>
						<option value="-9"<?php if ($sys_config['timezone'] == -9) echo ' selected="selected"' ?>>(UTC-09:00) Alaska, Gambier Island</option>
						<option value="-8"<?php if ($sys_config['timezone'] == -8) echo ' selected="selected"' ?>>(UTC-08:00) Pacific</option>
						<option value="-7"<?php if ($sys_config['timezone'] == -7) echo ' selected="selected"' ?>>(UTC-07:00) Mountain</option>
						<option value="-6"<?php if ($sys_config['timezone'] == -6) echo ' selected="selected"' ?>>(UTC-06:00) Central</option>
						<option value="-5"<?php if ($sys_config['timezone'] == -5) echo ' selected="selected"' ?>>(UTC-05:00) Eastern</option>
						<option value="-4"<?php if ($sys_config['timezone'] == -4) echo ' selected="selected"' ?>>(UTC-04:00) Atlantic</option>
						<option value="-3.5"<?php if ($sys_config['timezone'] == -3.5) echo ' selected="selected"' ?>>(UTC-03:30) Newfoundland</option>
						<option value="-3"<?php if ($sys_config['timezone'] == -3) echo ' selected="selected"' ?>>(UTC-03:00) Amazon, Central Greenland</option>
						<option value="-2"<?php if ($sys_config['timezone'] == -2) echo ' selected="selected"' ?>>(UTC-02:00) Mid-Atlantic</option>
						<option value="-1"<?php if ($sys_config['timezone'] == -1) echo ' selected="selected"' ?>>(UTC-01:00) Azores, Cape Verde, Eastern Greenland</option>
						<option value="0"<?php if ($sys_config['timezone'] == 0) echo ' selected="selected"' ?>>(UTC) Western European, Greenwich</option>
						<option value="1"<?php if ($sys_config['timezone'] == 1) echo ' selected="selected"' ?>>(UTC+01:00) Central European, West African</option>
						<option value="2"<?php if ($sys_config['timezone'] == 2) echo ' selected="selected"' ?>>(UTC+02:00) Eastern European, Central African</option>
						<option value="3"<?php if ($sys_config['timezone'] == 3) echo ' selected="selected"' ?>>(UTC+03:00) Moscow, Eastern African</option>
						<option value="3.5"<?php if ($sys_config['timezone'] == 3.5) echo ' selected="selected"' ?>>(UTC+03:30) Iran</option>
						<option value="4"<?php if ($sys_config['timezone'] == 4) echo ' selected="selected"' ?>>(UTC+04:00) Gulf, Samara</option>
						<option value="4.5"<?php if ($sys_config['timezone'] == 4.5) echo ' selected="selected"' ?>>(UTC+04:30) Afghanistan</option>
						<option value="5"<?php if ($sys_config['timezone'] == 5) echo ' selected="selected"' ?>>(UTC+05:00) Pakistan, Yekaterinburg</option>
						<option value="5.5"<?php if ($sys_config['timezone'] == 5.5) echo ' selected="selected"' ?>>(UTC+05:30) India, Sri Lanka</option>
						<option value="5.75"<?php if ($sys_config['timezone'] == 5.75) echo ' selected="selected"' ?>>(UTC+05:45) Nepal</option>
						<option value="6"<?php if ($sys_config['timezone'] == 6) echo ' selected="selected"' ?>>(UTC+06:00) Bangladesh, Bhutan, Novosibirsk</option>
						<option value="6.5"<?php if ($sys_config['timezone'] == 6.5) echo ' selected="selected"' ?>>(UTC+06:30) Cocos Islands, Myanmar</option>
						<option value="7"<?php if ($sys_config['timezone'] == 7) echo ' selected="selected"' ?>>(UTC+07:00) Indochina, Krasnoyarsk</option>
						<option value="8"<?php if ($sys_config['timezone'] == 8) echo ' selected="selected"' ?>>(UTC+08:00) Great China, Australian Western, Irkutsk</option>
						<option value="8.75"<?php if ($sys_config['timezone'] == 8.75) echo ' selected="selected"' ?>>(UTC+08:45) Southeastern Western Australia</option>
						<option value="9"<?php if ($sys_config['timezone'] == 9) echo ' selected="selected"' ?>>(UTC+09:00) Japan, Korea, Chita</option>
						<option value="9.5"<?php if ($sys_config['timezone'] == 9.5) echo ' selected="selected"' ?>>(UTC+09:30) Australian Central</option>
						<option value="10"<?php if ($sys_config['timezone'] == 10) echo ' selected="selected"' ?>>(UTC+10:00) Australian Eastern, Vladivostok</option>
						<option value="10.5"<?php if ($sys_config['timezone'] == 10.5) echo ' selected="selected"' ?>>(UTC+10:30) Lord Howe</option>
						<option value="11"<?php if ($sys_config['timezone'] == 11) echo ' selected="selected"' ?>>(UTC+11:00) Solomon Island, Magadan</option>
						<option value="11.5"<?php if ($sys_config['timezone'] == 11.5) echo ' selected="selected"' ?>>(UTC+11:30) Norfolk Island</option>
						<option value="12"<?php if ($sys_config['timezone'] == 12) echo ' selected="selected"' ?>>(UTC+12:00) New Zealand, Fiji, Kamchatka</option>
						<option value="12.75"<?php if ($sys_config['timezone'] == 12.75) echo ' selected="selected"' ?>>(UTC+12:45) Chatham Islands</option>
						<option value="13"<?php if ($sys_config['timezone'] == 13) echo ' selected="selected"' ?>>(UTC+13:00) Tonga, Phoenix Islands</option>
						<option value="14"<?php if ($sys_config['timezone'] == 14) echo ' selected="selected"' ?>>(UTC+14:00) Line Islands</option>
					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>Adjust for DST:</label></div>
				<div class="fld-text"><label for="fld-9"><input type="checkbox" id="fld-9" name="form[dst]" value="1" <?php if ($sys_config['dst'] == 1) echo 'checked="checked" ' ?>/> Daylight savings is in effect (advance times by 1 hour).</label></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-10">Language:</label></div>
				<div class="fld-input">
					<select name="form[language]" id="fld-10">

					<?php

					$languages = get_languages();
					foreach ($languages as $language)
						echo '<option value="'.$language.'" '.($language == $sys_config['language'] ? ' selected="selected"' : NULL).'>'.$language.'</option>'."\n";

					?>

					</select>
				</div>
			</li>

			<li class="frm-title">
				<h3>User and usergroup settings</h3>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>User online stats:</label></div>
				<div class="fld-text"><label for="fld-11"><input type="checkbox" id="fld-11" name="form[user_online_stats]" value="1" <?php if ($sys_config['user_online_stats'] == 1) echo 'checked="checked" ' ?>/> Track online users.</label></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>Who's online:</label></div>
				<div class="fld-text"><label for="fld-12"><input type="checkbox" id="fld-12" name="form[show_who_is_online]" value="1" <?php if ($sys_config['show_who_is_online'] == 1) echo 'checked="checked" ' ?>/> Show the "Who's online?" in the sidebar (only works when "Track online users" is enabled).</label></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>Registrations:</label></div>
				<div class="fld-text"><label for="fld-13"><input type="checkbox" id="fld-13" name="form[allow_new_registrations]" value="1" <?php if ($sys_config['allow_new_registrations'] == 1) echo 'checked="checked" ' ?>/> Allow new registrations.</label></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label>Username:</label></div>
				<div class="fld-text"><label for="fld-14"><input type="checkbox" id="fld-14" name="form[allow_username_change]" value="1" <?php if ($sys_config['allow_username_change'] == 1) echo 'checked="checked" ' ?>/> Allow user to change their username.</label></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-15">Default usergroup:</label></div>
				<div class="fld-input">
					<select name="form[default_usergroup]" id="fld-15">

					<?php

					$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);
					while ($row = $sys_db->fetch_assoc($result))
						echo '<option value="'.$row['id'].'" '.($row['id'] == $sys_config['default_usergroup'] ? ' selected="selected"' : NULL).'>'.$row['name'].'</option>'."\n";

					?>

					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-16">Visit timeout:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[visit_timeout]" id="fld-16" maxlength="100" value="<?php echo $sys_config['visit_timeout']; ?>" /></div>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input">
					<input type="submit" value="Submit" name="frm-submit" />
				</div>
			</li>
		</ul>
	</form>

	<?php
}

?>
