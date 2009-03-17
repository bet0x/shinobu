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

if ($sys_user['logged'] === true || $sys_config['allow_new_registrations'] === 0)
{
	$sys_tpl->assign('page_title', $sys_lang['e_error'].' - '.$sys_config['website_title']);

	?>

<h2><span><?php echo $sys_lang['e_error']; ?></span></h2>

<p><?php echo $sys_user['logged'] === false && $sys_config['allow_new_registrations'] === 0 ? $sys_lang['e_no_new_registrations'] : $sys_lang['e_already_logged_in']; ?></p>

	<?php
}
else
{
	// Initialise anti-spam captcha
	require(SYS_LIBRARY_DIR.'captcha.class.php');
	$captcha = new captcha;

	if (isset($_POST['frm-submit']) && check_token() && $sys_user['logged'] === false)
	{
		$form = array_map('system_trim', $_POST['form']);
		$errors = false;

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

		// Check passwords
		if (empty($form['password1']))
			$errors['password1'] = $sys_lang['e_password_error_6'];
		else if (utf8_strlen($form['password1']) < 6)
			$errors['password1'] = $sys_lang['e_password_error_7'];

		if (empty($form['password2']))
			$errors['password2'] = $sys_lang['e_password_error_8'];
		else if ($form['password2'] != $form['password1'])
			$errors['password2'] = $sys_lang['e_password_error_9'];

		// Check anti-spam/bot question
		if (empty($form['antibot']))
			$errors['antibot'] = $sys_lang['e_anti_bot_awnser'];
		else if ($captcha->check_answer($form['antibot']) === false)
			$errors['antibot'] = $sys_lang['e_anti_bot_wrong_awnser'];

		// Sent mail(?) and redirect
		if ($errors === false)
		{
			$now = time();
			$salt = generate_salt();
			$password = generate_password($form['password1'], $salt);
			$hash = sha1(generate_salt());

			// Put user en database
			$sys_db->query('
				INSERT INTO '.DB_PREFIX.'users
				(gid, username, password, salt, active, hash, email,language ,timezone, dst, register_date, registration_ip)
				VALUES(
					'.$sys_db->escape($sys_config['default_usergroup']).',
					\''.$sys_db->escape($form['username']).'\',
					\''.$sys_db->escape($password).'\',
					\''.$sys_db->escape($salt).'\',
					1,
					\''.$sys_db->escape($hash).'\',
					\''.$sys_db->escape($form['email']).'\',
					\''.$sys_db->escape($sys_config['language']).'\',
					\''.$sys_db->escape($sys_config['timezone']).'\',
					'.$sys_db->escape($sys_config['dst']).',
					'.$now.',
					\''.$sys_db->escape(get_remote_address()).'\')
				') or error($sys_db->error(), __FILE__, __LINE__);

			header('location: '.WEBSITE_URL.URI_PREFIX.'register'.URI_SUFFIX.'&registered'); exit;
		}
	}

	if (isset($_GET['registered']))
		$sys_tpl->add('main_content', '<div class="success">'.$sys_lang['m_register_succes'].'</div>');

	$sys_tpl->assign('page_title', $sys_lang['t_register'].' - '.$sys_config['website_title']);

	?>

<div id="register">
	<h2><span><?php echo $sys_lang['t_register']; ?></span></h2>

	<p><?php echo $sys_lang['d_register']; ?></p>

	<form method="post" accept-charset="utf-8" action="<?php echo WEBSITE_URL.URI_PREFIX.'register'.URI_SUFFIX; ?>">
		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-vc">
			<li class="frm-block<?php echo isset($errors['username']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0"><?php echo $sys_lang['g_username']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[username]" id="fld-0" maxlength="20" value="" /></div>
				<?php echo isset($errors['username']) ? '<span class="fld-error-message">'.$errors['username'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['email']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-1"><?php echo $sys_lang['g_email']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[email]" id="fld-1" maxlength="100" value="" /></div>
				<?php echo isset($errors['email']) ? '<span class="fld-error-message">'.$errors['email'].'</span>' : NULL; ?>
			</li>

			<li class="frm-hr">&nbsp;</li>

			<li class="frm-block<?php echo isset($errors['password1']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-2"><?php echo $sys_lang['g_password']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[password1]" id="fld-2" maxlength="20" value="" /></div>
				<?php echo isset($errors['password1']) ? '<span class="fld-error-message">'.$errors['password1'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['password2']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-3"><?php echo $sys_lang['g_confirm']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[password2]" id="fld-3" maxlength="100" value="" /></div>
				<?php echo isset($errors['password2']) ? '<span class="fld-error-message">'.$errors['password2'].'</span>' : NULL; ?>
			</li>

			<li class="frm-hr">&nbsp;</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-4"><?php echo $sys_lang['g_antibot']; ?>:</label></div>
				<div class="fld-text"><?php echo $captcha->get_question(); ?></div>
			</li>

			<li class="frm-block<?php echo isset($errors['antibot']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label>&nbsp;</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[antibot]" id="fld-4" maxlength="100" value="" /></div>
				<?php echo isset($errors['antibot']) ? '<span class="fld-error-message">'.$errors['antibot'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input">
					<input type="submit" value="<?php echo $sys_lang['b_register']; ?>" name="frm-submit" />
					<input onclick="window.location='<?php echo WEBSITE_URL; ?>'" type="button" value="<?php echo $sys_lang['b_cancel']; ?>" name="frm-cancel" />
				</div>
			</li>
		</ul>
	</form>
</div>

	<?php
}

?>
