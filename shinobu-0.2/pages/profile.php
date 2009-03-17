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

$sys_request[2] = isset($sys_request[2]) ? intval($sys_request[2]) : 0;
$user_id = $sys_request[2] === 0 ? $sys_user['id'] : intval($sys_request[2]);
$result = $sys_db->query('SELECT u.id, u.username, u.password, u.salt, u.real_name, u.description, u.website, u.email, u.msn, u.yahoo, u.show_email, u.language, u.timezone, u.dst, u.register_date, g.name AS usergroup FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id!='.GUEST_UID.' AND u.id='.$user_id.' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

if ($sys_db->num_rows($result) > 0)
{
	$user = $sys_db->fetch_assoc($result);

	if (isset($sys_request[2]) && !empty($sys_request[2]))
		$profile_url = WEBSITE_URL.URI_PREFIX.'profile/'.$sys_request[2].URI_SUFFIX;
	else
		$profile_url = WEBSITE_URL.URI_PREFIX.'profile'.URI_SUFFIX;

	// Process
	if (isset($_POST['frm-submit']) && check_token() && $sys_user['id'] == $user['id'])
	{
		// Set vars
		$form = array_map('system_trim', $_POST['form']);
		$errors = false;
		$password_change = false;

		$form['description'] = convert_linebreaks($form['description']);

		// Check username
		if ($sys_config['allow_username_change'] === 1 && $form['username'] != $user['username'])
		{
			// Remove double spaces (from FluxBB 1.3)
			$form['username'] = preg_replace('#\s+#s', ' ', system_trim($form['username']));

			// Look in database
			$result = $sys_db->query('SELECT username FROM '.DB_PREFIX.'users WHERE UPPER(username)=UPPER(\''.$sys_db->escape($form['username']).'\') OR UPPER(username)=UPPER(\''.$sys_db->escape(preg_replace('/[^\w]/', '', $form['username'])).'\')') or error($sys_db->error(), __FILE__, __LINE__);

			// Check username
			if (empty($form['username']))
				$errors['username'] = $sys_lang['e_username_error_1'];
			else if (utf8_strlen($form['username']) <= 2)
				$errors['username'] = $sys_lang['e_username_error_2'];
			else if (utf8_strlen($form['username']) > 20)
				$errors['username'] = $sys_lang['e_username_error_3'];
			else if ($sys_db->num_rows($result))
				$errors['username'] = $sys_lang['e_username_error_4'];
		}
		else
			$form['username'] = $user['username'];

		// Check e-mail
		if (!empty($form['email']))
		{
			if (!check_email($form['email']))
				$errors['email'] = $sys_lang['e_email_error_1'];
			else if (strlen($form['email']) > 80)
				$errors['email'] = $sys_lang['e_email_error_2'];
		}
		else
			$errors['email'] = $sys_lang['e_email_error_3'];

		// Real name
		if (utf8_strlen($form['realname']) > 255)
			$errors['realname'] = $sys_lang['e_realname_error_1'];

		// User description
		$form['description'] = convert_linebreaks($form['description']);
		if (utf8_strlen($form['description']) > 2000)
			$errors['description'] = $sys_lang['e_desc_error_1'];

		// Password change
		if (!empty($form['oldpw']) && !empty($form['newpw1']))
		{
			$form['oldpw'] = generate_password($form['oldpw'], $user['salt']);
			if ($form['oldpw'] !== $user['password'])
				$errors['oldpw'] = $sys_lang['e_password_error_1'];

			if (utf8_strlen($form['newpw1']) < 6)
				$errors['newpw1'] = $sys_lang['e_password_error_7'];

			if (empty($form['newpw2']))
				$errors['newpw2'] = $sys_lang['e_password_error_4'];
			else if ($form['newpw2'] !== $form['newpw1'])
				$errors['newpw2'] = $sys_lang['e_password_error_5'];

			$password_change = true;
		}

		// Check url
		if (!empty($form['website']))
			if (!check_url($form['website']))
				$errors['website'] = $sys_lang['e_website_error_1'];
			else if (strlen($form['website']) > 100)
				$errors['website'] = $sys_lang['e_website_error_2'];

		// Check MSN
		if (!empty($form['msn']))
		{
			if (!check_email($form['msn']))
				$errors['msn'] = $sys_lang['e_msn_error_1'];
			else if (strlen($form['msn']) > 100)
				$errors['msn'] = $sys_lang['e_msn_error_2'];
		}

		// Check Yahoo!
		if (!empty($form['yahoo']))
		{
			if (!check_email($form['yahoo']))
				$errors['yahoo'] = $sys_lang['e_yahoo_error_1'];
			else if (strlen($form['yahoo']) > 100)
				$errors['yahoo'] = $sys_lang['e_yahoo_error_2'];
		}

		// Email settings
		$form['email-setting'] = intval($form['email-setting']);

		// Language
		if (!in_array($form['language'], get_languages()))
			$form['language'] = $sys_config['language'];

		// Time zone
		if (is_float($form['timezone']))
			$form['timezone'] = 0;

		// DST
		$form['dst'] = isset($form['dst']) && $form['dst'] == 1 ? 1: 0;

		if ($errors === false)
		{
			$sys_db->query('
				UPDATE
					'.DB_PREFIX.'users
				SET
					username=\''.$sys_db->escape($form['username']).'\',
					email=\''.$sys_db->escape($form['email']).'\',
					real_name=\''.$sys_db->escape($form['realname']).'\',
					description=\''.$sys_db->escape($form['description']).'\',
					'.($password_change === false ? NULL : 'password=\''.$sys_db->escape(generate_password($form['newpw1'], $user['salt'])).'\', ').'
					website=\''.$sys_db->escape($form['website']).'\',
					msn=\''.$sys_db->escape($form['msn']).'\',
					yahoo=\''.$sys_db->escape($form['yahoo']).'\',
					show_email='.$form['email-setting'].',
					language=\''.$sys_db->escape($form['language']).'\',
					timezone='.$sys_db->escape($form['timezone']).',
					dst='.$form['dst'].'
				WHERE
					id='.intval($user['id'])) or error($sys_db->error(), __FILE__, __LINE__);

			header('location: '.$profile_url.'&edited'); exit;
		}
	}

	if (isset($_GET['edited']))
		$sys_tpl->add('main_content', '<div class="success">'.$sys_lang['m_profile_update_succes'].'</div>');

	?>

<div id="user-profile">

		<?php

		if ($sys_user['id'] == $user['id'])
		{
			$sys_tpl->assign('page_title', sprintf($sys_lang['t_profile'], $user['username']).' - '.$sys_config['website_title']);

			?>

	<h2><span><?php echo sprintf($sys_lang['t_profile'], $user['username']); ?></span></h2>

	<p><?php echo $sys_lang['d_profile']; ?></p>

	<form method="post" accept-charset="utf-8" action="<?php echo $profile_url; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-vc">
			<li class="frm-title">
				<h3><?php echo $sys_lang['t_account_info']; ?></h3>
			</li>

			<li class="frm-block"><?php echo $sys_lang['d_account_info']; ?></li>

			<li class="frm-block<?php echo isset($errors['username']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0"><?php echo $sys_lang['g_username']; ?>:</label></div>
				<div class="fld-input"><input class="text<?php echo $sys_config['allow_username_change'] === 0 ? ' disabled" disabled="disabled" ' : '"'; ?> type="text" name="form[username]" id="fld-0" maxlength="20" value="<?php echo isset($form['username']) ? utf8_htmlencode($form['username']) : utf8_htmlencode($user['username']) ?>" /></div>
				<?php echo isset($errors['username']) ? '<span class="fld-error-message">'.$errors['username'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['email']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-1"><?php echo $sys_lang['g_email']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[email]" id="fld-1" maxlength="100" value="<?php echo $user['email']; ?>" /></div>
				<?php echo isset($errors['email']) ? '<span class="fld-error-message">'.$errors['email'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['realname']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-2"><?php echo $sys_lang['g_realname']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[realname]" id="fld-2" maxlength="255" value="<?php echo utf8_htmlencode($user['real_name']); ?>" /></div>
				<?php echo isset($errors['realname']) ? '<span class="fld-error-message">'.$errors['realname'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['description']) ? ' form-error' : NULL; ?>">
				<div class="fld-label">
					<label for="fld-3"><?php echo $sys_lang['g_description']; ?>:</label>
					<div class="fld-desc"><?php echo $sys_lang['f_description_info']; ?></div>
				</div>
				<div class="fld-input"><textarea name="form[description]" id="fld-3" rows="15" cols="50"><?php echo utf8_htmlencode($user['description']); ?></textarea></div>
				<?php echo isset($errors['description']) ? '<span class="fld-error-message">'.$errors['description'].'</span>' : NULL; ?>
			</li>

			<li class="frm-title">
				<h3><?php echo $sys_lang['t_change_password']; ?></h3>
			</li>

			<li class="frm-block"><?php echo $sys_lang['d_change_password']; ?></li>

			<li class="frm-block<?php echo isset($errors['oldpw']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-4"><?php echo $sys_lang['g_old_password']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[oldpw]" id="fld-4" maxlength="100" /></div>
				<?php echo isset($errors['oldpw']) ? '<span class="fld-error-message">'.$errors['oldpw'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['newpw1']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-5"><?php echo $sys_lang['g_new_password']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[newpw1]" id="fld-5" maxlength="100" /></div>
				<?php echo isset($errors['newpw1']) ? '<span class="fld-error-message">'.$errors['newpw1'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['newpw2']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-6"><?php echo $sys_lang['g_confirm']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[newpw2]" id="fld-6" maxlength="100" /></div>
				<?php echo isset($errors['newpw2']) ? '<span class="fld-error-message">'.$errors['newpw2'].'</span>' : NULL; ?>
			</li>

			<li class="frm-title">
				<h3><?php echo $sys_lang['t_contact_info']; ?></h3>
			</li>

			<li class="frm-block"><?php echo $sys_lang['d_contact_info']; ?></li>

			<li class="frm-block<?php echo isset($errors['website']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-7"><?php echo $sys_lang['g_website']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[website]" id="fld-7" maxlength="100" value="<?php echo $user['website']; ?>" /></div>
				<?php echo isset($errors['website']) ? '<span class="fld-error-message">'.$errors['website'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['msn']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-8"><?php echo $sys_lang['g_msn']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[msn]" id="fld-8" maxlength="100" value="<?php echo $user['msn']; ?>" /></div>
				<?php echo isset($errors['msn']) ? '<span class="fld-error-message">'.$errors['msn'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['yahoo']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-9"><?php echo $sys_lang['g_yahoo']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[yahoo]" id="fld-9" maxlength="100" value="<?php echo $user['yahoo']; ?>" /></div>
				<?php echo isset($errors['yahoo']) ? '<span class="fld-error-message">'.$errors['yahoo'].'</span>' : NULL; ?>
			</li>

			<li class="frm-title">
				<h3><?php echo $sys_lang['t_settings']; ?></h3>
			</li>

			<li class="frm-block"><?php echo $sys_lang['d_settings']; ?></li>

			<li class="frm-block">
				<div class="fld-label"><label><?php echo $sys_lang['f_email_settings']; ?>:</label></div>
				<div class="fld-text">
					<div><label for="fld-10"><input type="radio" id="fld-10" name="form[email-setting]" value="0" <?php echo $user['show_email'] == 0 ? 'checked="checked"' : NULL; ?> /> <?php echo $sys_lang['f_email_settings_choice_1']; ?></label></div>
					<div><label for="fld-11"><input type="radio" id="fld-11" name="form[email-setting]" value="1" <?php echo $user['show_email'] == 1 ? 'checked="checked"' : NULL; ?> /> <?php echo $sys_lang['f_email_settings_choice_2']; ?></label></div>
					<div><label for="fld-12"><input type="radio" id="fld-12" name="form[email-setting]" value="2" <?php echo $user['show_email'] == 2 ? 'checked="checked"' : NULL; ?> /> <?php echo $sys_lang['f_email_settings_choice_3']; ?></label></div>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-13"><?php echo $sys_lang['f_language']; ?>:</label></div>
				<div class="fld-input">
					<select name="form[language]" id="fld-13">

					<?php

					$languages = get_languages();
					foreach ($languages as $language)
						echo '<option value="'.$language.'" '.($language == $user['language'] ? 'selected="selected"' : NULL).'>'.$language.'</option>'."\n";

					?>

					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-14"><?php echo $sys_lang['g_timezone']; ?>:</label></div>
				<div class="fld-text">
					<select id="fld-14" name="form[timezone]">
						<option value="-12"<?php if ($user['timezone'] == -12) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-12']; ?></option>
						<option value="-11"<?php if ($user['timezone'] == -11) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-11']; ?></option>
						<option value="-10"<?php if ($user['timezone'] == -10) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-10']; ?></option>
						<option value="-9.5"<?php if ($user['timezone'] == -9.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-9.5']; ?></option>
						<option value="-9"<?php if ($user['timezone'] == -9) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-9']; ?></option>
						<option value="-8"<?php if ($user['timezone'] == -8) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-8']; ?></option>
						<option value="-7"<?php if ($user['timezone'] == -7) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-7']; ?></option>
						<option value="-6"<?php if ($user['timezone'] == -6) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-6']; ?></option>
						<option value="-5"<?php if ($user['timezone'] == -5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-5']; ?></option>
						<option value="-4"<?php if ($user['timezone'] == -4) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-4']; ?></option>
						<option value="-3.5"<?php if ($user['timezone'] == -3.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-3.5']; ?></option>
						<option value="-3"<?php if ($user['timezone'] == -3) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-3']; ?></option>
						<option value="-2"<?php if ($user['timezone'] == -2) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-2']; ?></option>
						<option value="-1"<?php if ($user['timezone'] == -1) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['-1']; ?></option>
						<option value="0"<?php if ($user['timezone'] == 0) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['0']; ?></option>
						<option value="1"<?php if ($user['timezone'] == 1) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['1']; ?></option>
						<option value="2"<?php if ($user['timezone'] == 2) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['2']; ?></option>
						<option value="3"<?php if ($user['timezone'] == 3) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['3']; ?></option>
						<option value="3.5"<?php if ($user['timezone'] == 3.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['3.5']; ?></option>
						<option value="4"<?php if ($user['timezone'] == 4) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['4']; ?></option>
						<option value="4.5"<?php if ($user['timezone'] == 4.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['4.5']; ?></option>
						<option value="5"<?php if ($user['timezone'] == 5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['5']; ?></option>
						<option value="5.5"<?php if ($user['timezone'] == 5.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['5.5']; ?></option>
						<option value="5.75"<?php if ($user['timezone'] == 5.75) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['5.75']; ?></option>
						<option value="6"<?php if ($user['timezone'] == 6) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['6']; ?></option>
						<option value="6.5"<?php if ($user['timezone'] == 6.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['6.5']; ?></option>
						<option value="7"<?php if ($user['timezone'] == 7) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['7']; ?></option>
						<option value="8"<?php if ($user['timezone'] == 8) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['8']; ?></option>
						<option value="8.75"<?php if ($user['timezone'] == 8.75) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['8.75']; ?></option>
						<option value="9"<?php if ($user['timezone'] == 9) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['9']; ?></option>
						<option value="9.5"<?php if ($user['timezone'] == 9.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['9.5']; ?></option>
						<option value="10"<?php if ($user['timezone'] == 10) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['10']; ?></option>
						<option value="10.5"<?php if ($user['timezone'] == 10.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['10.5']; ?></option>
						<option value="11"<?php if ($user['timezone'] == 11) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['11']; ?></option>
						<option value="11.5"<?php if ($user['timezone'] == 11.5) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['11.5']; ?></option>
						<option value="12"<?php if ($user['timezone'] == 12) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['12']; ?></option>
						<option value="12.75"<?php if ($user['timezone'] == 12.75) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['12.75']; ?></option>
						<option value="13"<?php if ($user['timezone'] == 13) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['13']; ?></option>
						<option value="14"<?php if ($user['timezone'] == 14) echo ' selected="selected"' ?>><?php echo $sys_lang['timezones']['14']; ?></option>
					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label><?php echo $sys_lang['f_adjust_for_dst']; ?>:</label></div>
				<div class="fld-text"><label for="fld-15"><input type="checkbox" id="fld-15" name="form[dst]" value="1" <?php if ($user['dst'] == 1) echo 'checked="checked" ' ?>/> <?php echo $sys_lang['f_dst_question']; ?></label></div>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input">
					<input type="submit" value="<?php echo $sys_lang['b_update']; ?>" name="frm-submit" />
					<input onclick="window.location='<?php echo WEBSITE_URL; ?>'" type="button" value="<?php echo $sys_lang['b_cancel']; ?>" name="frm-cancel" />
				</div>
			</li>
		</ul>
	</form>

			<?php
		}
		else
		{
			$sys_tpl->assign('page_title', sprintf($sys_lang['t_profile'], $user['username']).' - '.$sys_config['website_title']);

			?>

	<h2><span><?php echo sprintf($sys_lang['t_profile'], $user['username']); ?></span></h2>

	<?php

	if (!empty($user['usergroup']))
	{
		require SYS_LIBRARY_DIR.'markup_parsers/SimpleText.php';
		$parser = new SimpleText;

		echo $parser->parse($user['description'], true);
	}

	?>

	<h3><?php echo $sys_lang['t_information']; ?></h3>

	<dl class="col-2">
		<dt><?php echo $sys_lang['g_username']; ?>:</dt>
		<dd><?php echo utf8_htmlencode($user['username']); ?></dd>

		<dt><?php echo $sys_lang['g_usergroup']; ?>:</dt>
		<dd><?php echo utf8_htmlencode($user['usergroup']); ?></dd>

		<dt><?php echo $sys_lang['g_realname']; ?>:</dt>
		<dd><?php echo !empty($user['real_name']) ? utf8_htmlencode($user['real_name']) : '&nbsp;'; ?></dd>

		<dt><?php echo $sys_lang['g_registered_on']; ?>:</dt>
		<dd><?php echo format_time($user['register_date'], true); ?></dd>
	</dl>

	<h3><?php echo $sys_lang['t_contact']; ?></h3>

	<dl class="col-2">
		<?php if (!empty($user['website'])) : ?>
		<dt><?php echo $sys_lang['g_website']; ?>:</dt>
		<dd><?php echo '<a href="'.$user['website'].'">'.$user['website'].'</a>'; ?></dd>
		<?php endif; ?>

		<dt><?php echo $sys_lang['g_email']; ?>:</dt>
		<?php if (($user['show_email'] == 1 && $sys_user['id'] != GUEST_GID) || ($user['show_email'] == 2)) : ?>
		<dd><?php echo $user['email']; ?></dd>
		<?php else : ?>
		<dd><?php echo $sys_lang['g_private']; ?></dd>
		<?php endif; ?>

		<?php if (!empty($user['msn'])) : ?>
		<dt><?php echo $sys_lang['g_msn']; ?>:</dt>
		<dd><?php echo $user['msn']; ?></dd>
		<?php endif; ?>

		<?php if (!empty($user['yahoo'])) : ?>
		<dt><?php echo $sys_lang['g_yahoo']; ?>:</dt>
		<dd><?php echo $user['yahoo']; ?></dd>
		<?php endif; ?>
	</dl>

			<?php
		}
		?>

</div>

	<?php
}
else
	send_404($sys_lang['e_profile_not_found'], '<p>'.$sys_lang['e_profile_not_found_info'].'</p>');
