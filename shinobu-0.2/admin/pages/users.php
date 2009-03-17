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

if ($sys_user['p_manage_users'] == 0)
{
	// Set page title
	$sys_tpl->assign('page_title', $sys_lang['e_error'].' - '.$sys_config['website_title'].' Admin');

	?>

<h2><span><?php echo $sys_lang['e_error']; ?></span></h2>

<p>You have no permission to access this page</p>

	<?php
}

// Edit user
else if (isset($sys_request[2]) && $sys_request[2] == 'edit')
{
	$sys_request[3] = isset($sys_request[3]) && !empty($sys_request[3]) ? intval($sys_request[3]) : 0;
	$result = $sys_db->query('SELECT u.id, u.gid, u.username, u.password, u.salt, u.active, u.real_name, u.description, u.website, u.email, u.msn, u.yahoo, u.show_email, u.language, u.timezone, u.dst, u.register_date, g.name AS usergroup FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id != '.GUEST_UID.' AND u.id='.$sys_request[3].' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) > 0)
	{
		$user = $sys_db->fetch_assoc($result);

		// Update user
		if (isset($_POST['frm-submit']) && check_token())
		{
			$form = array_map('system_trim', $_POST['form']);
			$errors = $password_change = false;

			// Usergroup check
			$form['usergroup'] = intval($form['usergroup']);
			if ($form['usergroup'] != $user['gid'] && $form['usergroup'] != GUEST_GID)
			{
				$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);

				if ($sys_db->num_rows($result) < 1)
					$form['usergroup'] = $user['gid'];
			}
			else
				$form['usergroup'] = $user['gid'];

			$form['user-status'] = $form['user-status'] == 1 ? 1 : 0;

			// Check username
			if ($sys_config['allow_username_change'] == 1 && $form['username'] != $user['username'])
			{
				// Remove double spaces (from FluxBB 1.3)
				$form['username'] = preg_replace('#\s+#s', ' ', system_trim($form['username']));

				// Look in database
				$result = $sys_db->query('SELECT username FROM '.DB_PREFIX.'users WHERE UPPER(username)=UPPER(\''.$sys_db->escape($form['username']).'\') OR UPPER(username)=UPPER(\''.$sys_db->escape(preg_replace('/[^\w]/', '', $form['username'])).'\')') or error($sys_db->error(), __FILE__, __LINE__);

				// Check username
				if (empty($form['username']))
					$errors['username'] = 'You haven\'t entered a username.';
				else if (utf8_strlen($form['username']) <= 2)
					$errors['username'] = 'Your username is too short.';
				else if (utf8_strlen($form['username']) > 20)
					$errors['username'] = 'Your username is too long.';
				else if ($sys_db->num_rows($result))
					$errors['username'] = 'This username is already taken.';
				else
					$form['username'] = $form['username'];
			}
			else
				$form['username'] = $user['username'];

			// Check e-mail
			if (!empty($form['email']))
			{
				if (!check_email($form['email']))
					$errors['email'] = 'You have entered an invalid e-mail address.';
				else if (strlen($form['email']) > 80)
					$errors['email'] = 'The e-mail address you entered is too long.';
			}
			else
				$errors['email'] = 'You haven\'t entered an e-mail address.';

			// Real name
			if (utf8_strlen($form['realname']) > 255)
				$errors['realname'] = 'Your real name is too long.';

			// User description
			$form['description'] = convert_linebreaks($form['description']);
			if (utf8_strlen($form['description']) > 2000)
				$errors['description'] = 'Your description is too long.';

			// Password change
			if (!empty($form['newpw1']) && !empty($form['newpw2']))
			{
				if (utf8_strlen($form['newpw1']) >= 6)
					if (empty($form['newpw1']))
						$errors['newpw1'] = 'You haven\'t entered a new password.';
				else
					$errors['newpw1'] = 'Your new password is too short.';

				if (empty($form['newpw2']))
					$errors['newpw2'] = 'You haven\'t confirmed the new password.';
				else if ($form['newpw2'] !== $form['newpw1'])
					$errors['newpw2'] = 'Confermation password does not match new password.';

				$password_change = true;
			}

			// Check url
			if (!empty($form['website']))
			{
				if (!check_url($form['website']))
					$errors['website'] = 'You entered an invalid website adress.';
				else if (strlen($form['website']) > 100)
					$errors['website'] = 'The website address you entered is too long.';
			}

			// Check MSN
			if (!empty($form['msn']))
			{
				if (!check_email($form['msn']))
					$errors['msn'] = 'You have entered an invalid msn address.';
				else if (strlen($form['msn']) > 100)
					$errors['msn'] = 'The msn address you entered is too long.';
			}

			// Check Yahoo!
			if (!empty($form['yahoo']))
			{
				if (!check_email($form['yahoo']))
					$errors['yahoo'] = 'You have entered an invalid Yahoo! address.';
				else if (strlen($form['yahoo']) > 100)
					$errors['yahoo'] = 'The Yahoo! address you entered is too long.';
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
			$form['dst'] = isset($form['dst']) && $form['dst'] == 1 ? 1 : 0;

			// Process
			if ($errors === false)
			{
				// put stuff in db and redirect
				$sys_db->query('
					UPDATE
						'.DB_PREFIX.'users
					SET
						gid=\''.$form['usergroup'].'\',
						username=\''.$sys_db->escape($form['username']).'\',
						active='.$form['user-status'].',
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

				header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&edited'); exit;
			}
		}

		// Set page title
		$sys_tpl->assign('page_title', 'Edit User - '.$sys_config['website_title'].' Admin');

		?>

<h2>Edit User: <?php echo $user['username']; ?></h2>

<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'users/edit/'.$sys_request[3].URI_SUFFIX; ?>">

	<div>
		<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
	</div>

	<ul class="frm-vc">
		<li class="frm-title">
			<h3>Administration</h3>
		</li>

		<li class="frm-block">Ban, (de)activate or assign an usergroup to the user.</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-0">Usergroup:</label></div>
			<div class="fld-text">
				<select id="fld-0" name="form[usergroup]">

				<?php

				$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);
				while ($row = $sys_db->fetch_assoc($result))
				{
					echo '<option value ="'.$row['id'].'" '.($row['id'] == $user['gid'] ? ' selected="selected"' : NULL).'>'.utf8_htmlencode($row['name']).'</option>'."\n";
				}

				?>

				</select>
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label>User status:</label></div>
			<div class="fld-text">
				<div><label for="fld-1"><input type="radio" id="fld-1" name="form[user-status]" value="0" <?php echo $user['active'] == 0 ? 'checked="checked"' : NULL; ?> /> Deactivated.</label></div>
				<div><label for="fld-2"><input type="radio" id="fld-2" name="form[user-status]" value="1" <?php echo $user['active'] == 1 ? 'checked="checked"' : NULL; ?> /> Activated.</label></div>
			</div>
		</li>

		<li class="frm-title">
			<h3>Account information</h3>
		</li>

		<li class="frm-block">Essential account information.</li>

		<li class="frm-block<?php echo isset($errors['username']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-3">Username:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[username]" id="fld-3" maxlength="20" value="<?php echo isset($form['username']) ? utf8_htmlencode($form['username']) : utf8_htmlencode($user['username']); ?>" /></div>
			<?php echo isset($errors['username']) ? '<span class="fld-error-message">'.$errors['username'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['email']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-4">E-mail:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[email]" id="fld-4" maxlength="100" value="<?php echo $user['email']; ?>" /></div>
			<?php echo isset($errors['email']) ? '<span class="fld-error-message">'.$errors['email'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['realname']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-5">Real name:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[realname]" id="fld-5" maxlength="255" value="<?php echo utf8_htmlencode($user['real_name']); ?>" /></div>
			<?php echo isset($errors['realname']) ? '<span class="fld-error-message">'.$errors['realname'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['description']) ? ' form-error' : NULL; ?>">
			<div class="fld-label">
				<label for="fld-6">Description:</label>
				<div class="fld-desc">Personal information about the user.</div>
			</div>
			<div class="fld-input"><textarea name="form[description]" id="fld-6" rows="10" cols="50"><?php echo utf8_htmlencode($user['description']); ?></textarea></div>
			<?php echo isset($errors['description']) ? '<span class="fld-error-message">'.$errors['description'].'</span>' : NULL; ?>
		</li>

		<li class="frm-title">
			<h3>Change password</h3>
		</li>

		<li class="frm-block">Password must atleast be 6 characters long and it is advised to use a mixture of (capital) characters and numbers.</li>

		<li class="frm-block<?php echo isset($errors['newpw1']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-7">New password:</label></div>
			<div class="fld-input"><input class="text" type="password" name="form[newpw1]" id="fld-7" maxlength="100" /></div>
			<?php echo isset($errors['newpw1']) ? '<span class="fld-error-message">'.$errors['newpw1'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['newpw2']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-8">Confirm:</label></div>
			<div class="fld-input"><input class="text" type="password" name="form[newpw2]" id="fld-8" maxlength="100" /></div>
			<?php echo isset($errors['newpw2']) ? '<span class="fld-error-message">'.$errors['newpw2'].'</span>' : NULL; ?>
		</li>

		<li class="frm-title">
			<h3>Contact information</h3>
		</li>

		<li class="frm-block">This information will be displayed in public.</li>

		<li class="frm-block<?php echo isset($errors['website']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-9">Website:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[website]" id="fld-9" maxlength="100" value="<?php echo $user['website'] ?>" /></div>
			<?php echo isset($errors['website']) ? '<span class="fld-error-message">'.$errors['website'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['msn']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-10">MSN:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[msn]" id="fld-10" maxlength="100" value="<?php echo $user['msn'] ?>" /></div>
			<?php echo isset($errors['msn']) ? '<span class="fld-error-message">'.$errors['msn'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['yahoo']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-11">Yahoo!:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[yahoo]" id="fld-11" maxlength="100" value="<?php echo $user['yahoo'] ?>" /></div>
			<?php echo isset($errors['yahoo']) ? '<span class="fld-error-message">'.$errors['yahoo'].'</span>' : NULL; ?>
		</li>

		<li class="frm-title">
			<h3>Settings</h3>
		</li>

		<li class="frm-block">E-mail, timezone, language and date display settings.</li>

		<li class="frm-block">
			<div class="fld-label"><label>E-mail settings:</label></div>
			<div class="fld-text">
				<div><label for="fld-12"><input type="radio" id="fld-12" name="form[email-setting]" value="0" <?php echo $user['show_email'] == 0 ? 'checked="checked"' : NULL; ?> /> Hide your e-mail address for everyone.</label></div>
				<div><label for="fld-13"><input type="radio" id="fld-13" name="form[email-setting]" value="1" <?php echo $user['show_email'] == 1 ? 'checked="checked"' : NULL; ?> /> Display your e-mail address to other users, but not to guests.</label></div>
				<div><label for="fld-14"><input type="radio" id="fld-14" name="form[email-setting]" value="2" <?php echo $user['show_email'] == 2 ? 'checked="checked"' : NULL; ?> /> Display your e-mail address to everyone.</label></div>
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-15">Language:</label></div>
			<div class="fld-input">
				<select name="form[language]" id="fld-15">

				<?php

				$languages = get_languages();
				foreach ($languages as $language)
					echo '<option value="'.$language.'" '.($language == $user['language'] ? 'selected="selected"' : NULL).'>'.$language.'</option>'."\n";

				?>

				</select>
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label for="fld-16">Timezone:</label></div>
			<div class="fld-text">
				<select id="fld-16" name="form[timezone]">
					<option value="-12"<?php if ($user['timezone'] == -12) echo ' selected="selected"' ?>>(UTC-12:00) International Date Line West</option>
					<option value="-11"<?php if ($user['timezone'] == -11) echo ' selected="selected"' ?>>(UTC-11:00) Niue, Samoa</option>
					<option value="-10"<?php if ($user['timezone'] == -10) echo ' selected="selected"' ?>>(UTC-10:00) Hawaii-Aleutian, Cook Island</option>
					<option value="-9.5"<?php if ($user['timezone'] == -9.5) echo ' selected="selected"' ?>>(UTC-09:30) Marquesas Islands</option>
					<option value="-9"<?php if ($user['timezone'] == -9) echo ' selected="selected"' ?>>(UTC-09:00) Alaska, Gambier Island</option>
					<option value="-8"<?php if ($user['timezone'] == -8) echo ' selected="selected"' ?>>(UTC-08:00) Pacific</option>
					<option value="-7"<?php if ($user['timezone'] == -7) echo ' selected="selected"' ?>>(UTC-07:00) Mountain</option>
					<option value="-6"<?php if ($user['timezone'] == -6) echo ' selected="selected"' ?>>(UTC-06:00) Central</option>
					<option value="-5"<?php if ($user['timezone'] == -5) echo ' selected="selected"' ?>>(UTC-05:00) Eastern</option>
					<option value="-4"<?php if ($user['timezone'] == -4) echo ' selected="selected"' ?>>(UTC-04:00) Atlantic</option>
					<option value="-3.5"<?php if ($user['timezone'] == -3.5) echo ' selected="selected"' ?>>(UTC-03:30) Newfoundland</option>
					<option value="-3"<?php if ($user['timezone'] == -3) echo ' selected="selected"' ?>>(UTC-03:00) Amazon, Central Greenland</option>
					<option value="-2"<?php if ($user['timezone'] == -2) echo ' selected="selected"' ?>>(UTC-02:00) Mid-Atlantic</option>
					<option value="-1"<?php if ($user['timezone'] == -1) echo ' selected="selected"' ?>>(UTC-01:00) Azores, Cape Verde, Eastern Greenland</option>
					<option value="0"<?php if ($user['timezone'] == 0) echo ' selected="selected"' ?>>(UTC) Western European, Greenwich</option>
					<option value="1"<?php if ($user['timezone'] == 1) echo ' selected="selected"' ?>>(UTC+01:00) Central European, West African</option>
					<option value="2"<?php if ($user['timezone'] == 2) echo ' selected="selected"' ?>>(UTC+02:00) Eastern European, Central African</option>
					<option value="3"<?php if ($user['timezone'] == 3) echo ' selected="selected"' ?>>(UTC+03:00) Moscow, Eastern African</option>
					<option value="3.5"<?php if ($user['timezone'] == 3.5) echo ' selected="selected"' ?>>(UTC+03:30) Iran</option>
					<option value="4"<?php if ($user['timezone'] == 4) echo ' selected="selected"' ?>>(UTC+04:00) Gulf, Samara</option>
					<option value="4.5"<?php if ($user['timezone'] == 4.5) echo ' selected="selected"' ?>>(UTC+04:30) Afghanistan</option>
					<option value="5"<?php if ($user['timezone'] == 4) echo ' selected="selected"' ?>>(UTC+05:00) Pakistan, Yekaterinburg</option>
					<option value="5.5"<?php if ($user['timezone'] == 5.5) echo ' selected="selected"' ?>>(UTC+05:30) India, Sri Lanka</option>
					<option value="5.75"<?php if ($user['timezone'] == 5.75) echo ' selected="selected"' ?>>(UTC+05:45) Nepal</option>
					<option value="6"<?php if ($user['timezone'] == 6) echo ' selected="selected"' ?>>(UTC+06:00) Bangladesh, Bhutan, Novosibirsk</option>
					<option value="6.5"<?php if ($user['timezone'] == 6.5) echo ' selected="selected"' ?>>(UTC+06:30) Cocos Islands, Myanmar</option>
					<option value="7"<?php if ($user['timezone'] == 7) echo ' selected="selected"' ?>>(UTC+07:00) Indochina, Krasnoyarsk</option>
					<option value="8"<?php if ($user['timezone'] == 8) echo ' selected="selected"' ?>>(UTC+08:00) Great China, Australian Western, Irkutsk</option>
					<option value="8.75"<?php if ($user['timezone'] == 8.75) echo ' selected="selected"' ?>>(UTC+08:45) Southeastern Western Australia</option>
					<option value="9"<?php if ($user['timezone'] == 9) echo ' selected="selected"' ?>>(UTC+09:00) Japan, Korea, Chita</option>
					<option value="9.5"<?php if ($user['timezone'] == 9.5) echo ' selected="selected"' ?>>(UTC+09:30) Australian Central</option>
					<option value="10"<?php if ($user['timezone'] == 10) echo ' selected="selected"' ?>>(UTC+10:00) Australian Eastern, Vladivostok</option>
					<option value="10.5"<?php if ($user['timezone'] == 10.5) echo ' selected="selected"' ?>>(UTC+10:30) Lord Howe</option>
					<option value="11"<?php if ($user['timezone'] == 11) echo ' selected="selected"' ?>>(UTC+11:00) Solomon Island, Magadan</option>
					<option value="11.5"<?php if ($user['timezone'] == 11.5) echo ' selected="selected"' ?>>(UTC+11:30) Norfolk Island</option>
					<option value="12"<?php if ($user['timezone'] == 12) echo ' selected="selected"' ?>>(UTC+12:00) New Zealand, Fiji, Kamchatka</option>
					<option value="12.75"<?php if ($user['timezone'] == 12.75) echo ' selected="selected"' ?>>(UTC+12:45) Chatham Islands</option>
					<option value="13"<?php if ($user['timezone'] == 13) echo ' selected="selected"' ?>>(UTC+13:00) Tonga, Phoenix Islands</option>
					<option value="14"<?php if ($user['timezone'] == 14) echo ' selected="selected"' ?>>(UTC+14:00) Line Islands</option>
				</select>
			</div>
		</li>

		<li class="frm-block">
			<div class="fld-label"><label>Adjust for DST:</label></div>
			<div class="fld-text"><label for="fld-17"><input type="checkbox" id="fld-17" name="form[dst]" value="1" <?php if ($user['dst'] == 1) echo 'checked="checked" ' ?>/>Daylight savings is in effect (advance times by 1 hour).</label></div>
		</li>

		<li class="frm-block frm-buttons">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-input">
				<input type="submit" value="Edit profile" name="frm-submit" id="frm-submit" />
				<input onclick="window.location='<?php echo ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX; ?>'" type="button" value="Cancel" name="frm-cancel" id="frm-cancel" />
			</div>
		</li>
	</ul>
</form>

		<?php
	}
	else
		send_404($sys_lang['e_error'], '<p>The user does not exist.</p>', false);
}

// Userlist
else
{
	if (isset($_GET['delete']) && check_token(true))
	{
		$id = intval($_GET['delete']);

		if ($id === GUEST_UID)
		{
			header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&guest'); exit;
		}
		else
		{
			$result = $sys_db->query('SELECT u.id FROM '.DB_PREFIX.'users AS u WHERE id='.$id) or error($sys_db->error(), __FILE__, __LINE__);

			if ($sys_db->num_rows($result) > 0)
			{
				$sys_db->query('DELETE FROM '.DB_PREFIX.'users WHERE id='.$id) or error($sys_db->error(), __FILE__, __LINE__);
				header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&deleted'); exit;
			}
			else
			{
				header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&delete_error'); exit;
			}
		}
	}

	if (isset($_GET['edited']))
		$sys_tpl->add('main_content', '<div class="success">User profile has been succesfully changed.</div>');
	else if (isset($_GET['deleted']))
		$sys_tpl->add('main_content', '<div class="success">User succesfully deleted.</div>');
	else if (isset($_GET['delete_error']))
		$sys_tpl->add('main_content', '<div class="warning">The user you tried to delete does not exist.</div>');
	else if (isset($_GET['guest']))
		$sys_tpl->add('main_content', '<div class="notice">You can\'t remove this user.</div>');

	// Set page title
	$sys_tpl->assign('page_title', 'Manage Users - '.$sys_config['website_title'].' Admin');

	?>

<h2>Manage Users</h2>

	<?php

	$user_filter = $user_sql = $group_sql = NULL;
	$group_filter = 0;
	$sortby_filter = 5;
	$sortorder_filter = 1;
	$sorting_sql = ' ORDER BY u.register_date ASC';

	// Reset filter
	if (isset($_POST['frm-reset']))
	{
		header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX); exit;
	}

	// Process filter
	if (isset($_POST['frm-submit']) && check_token())
	{
		$form = array_map('system_trim', $_POST['form']);

		$form['username'] = !empty($form['username']) ? base64_url_encode($form['username']) : 0;
		$form['usergroup'] = intval($form['usergroup']);
		$form['sortby'] = intval($form['sortby']);
		$form['sortorder'] = intval($form['sortorder']);

		header('location: '.ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&filter='.implode('/', $form)); exit;
	}

	// Apply filter
	if (isset($_GET['filter']))
	{
		list($user_filter, $group_filter, $sortby_filter, $sortorder_filter) = explode('/', $_GET['filter']);

		$group_filter = intval($group_filter);
		$sortby_filter = intval($sortby_filter);
		$sortorder_filter = intval($sortorder_filter);

		if (utf8_strlen($user_filter) > 1)
			$user_sql = ' AND u.username=\''.$sys_db->escape(base64_url_decode($user_filter)).'\'';

		if ($group_filter > 0)
			$group_sql = ' AND g.id='.$group_filter;

		if ($sortby_filter == 1)
			$sorting_sql = ' ORDER BY u.username'.($sortorder_filter == 1 ? ' ASC' : ' DESC');
		else if ($sortby_filter == 2)
			$sorting_sql = ' ORDER BY u.real_name'.($sortorder_filter == 1 ? ' ASC' : ' DESC');
		else if ($sortby_filter == 3)
			$sorting_sql = ' ORDER BY g.usertitle'.($sortorder_filter == 1 ? ' ASC' : ' DESC');
		else if ($sortby_filter == 4)
			$sorting_sql = ' ORDER BY g.name'.($sortorder_filter == 1 ? ' ASC' : ' DESC');
		else if ($sortby_filter == 5)
			$sorting_sql = ' ORDER BY u.register_date'.($sortorder_filter == 1 ? ' ASC' : ' DESC');
	}

	?>

<div id="userlist-filter">
	<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul id="userlist-search" class="frm-hc hc-box">
			<li class="frm-block">
				<div class="fld-label"><label for="fld-0">Username:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[username]" id="fld-0" maxlength="20" <?php echo utf8_strlen($user_filter) > 0 ? ' value="'.utf8_htmlencode(base64_url_decode($user_filter)).'"' : NULL; ?>/></div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-1">Usergroup:</label></div>
				<div class="fld-input">
					<select name="form[usergroup]" id="fld-1">
						<option value="0">All usergroups</option>

						<?php

						$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);
						while ($row = $sys_db->fetch_assoc($result))
							echo '<option value="'.$row['id'].'" '.($row['id'] == $group_filter ? ' selected="selected"' : NULL).'>'.utf8_htmlencode($row['name']).'</option>'."\n";

						?>

					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-2">Sort by:</label></div>
				<div class="fld-input">
					<select name="form[sortby]" id="fld-2">
						<option value="1"<?php echo $sortby_filter === 1 ? ' selected="selected"' : NULL; ?>>Username</option>
						<option value="2"<?php echo $sortby_filter === 2 ? ' selected="selected"' : NULL; ?>>Real name</option>
						<option value="3"<?php echo $sortby_filter === 3 ? ' selected="selected"' : NULL; ?>>Title</option>
						<option value="4"<?php echo $sortby_filter === 4 ? ' selected="selected"' : NULL; ?>>Usergroup</option>
						<option value="5"<?php echo $sortby_filter === 5 ? ' selected="selected"' : NULL; ?>>Register date</option>
					</select>
				</div>
			</li>

			<li class="frm-block">
				<div class="fld-label"><label for="fld-3">Sorting order:</label></div>
				<div class="fld-input">
					<select name="form[sortorder]" id="fld-3">
						<option value="1"<?php echo $sortorder_filter === 1 ? ' selected="selected"' : NULL; ?>>Ascending</option>
						<option value="2"<?php echo $sortorder_filter === 2 ? ' selected="selected"' : NULL; ?>>Descending</option>
					</select>
				</div>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-input">
					<input type="submit" value="Search" name="frm-submit" id="frm-submit" />
					<input type="submit" value="Reset" name="frm-reset" id="frm-reset" />
				</div>
			</li>
		</ul>
	</form>
</div>

	<?php

	$sys_request[2] = isset($sys_request[2]) && $sys_request[2] > 0 ? $sys_request[2] : 1;
	$list_start = ($sys_request[2]-1) * 20;
	$list_limit = 20;

	$result = $sys_db->query('SELECT u.id, u.username, u.real_name, u.active, u.register_date, g.name AS usergroup, g.usertitle FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id > 1'.$user_sql.$group_sql.$sorting_sql.' LIMIT '.$list_start.','.$list_limit.'') or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) > 0)
	{
		// Count users
		$user_count = $sys_db->fetch_assoc($sys_db->query('SELECT COUNT(*) as user_count FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id > 2'.$user_sql.$group_sql)) or error($sys_db->error(), __FILE__, __LINE__);
		$user_count = $user_count['user_count'];

		if (!isset($_GET['filter']))
			$userlist_url = ADMIN_URL.URI_PREFIX.'users/%d'.URI_SUFFIX;
		else
			$userlist_url = ADMIN_URL.URI_PREFIX.'users/%d'.URI_SUFFIX.'&amp;filter='.utf8_htmlencode($_GET['filter']);

		$pages = pagination($user_count, 20, $sys_request[2], $userlist_url);

		?>

<div class="info">There is a total of <?php echo $user_count; ?> users. - Users with a red username are inactive users.</div>

<?php echo $pages; ?>

<table id="userlist">
	<thead>

		<tr>
			<th class="td-username" scope="col">Username</th>
			<th class="td-realname" scope="col">Real name</th>
			<th class="td-group" scope="col"><?php echo $sortby_filter === 3 ? 'Title' : 'Usergroup'; ?></th>
			<th class="td-date" scope="col">Register date</th>
			<th class="td-actions" scope="col">Actions</th>
		</tr>
	</thead>

	<tbody>

		<?php

		while ($row = $sys_db->fetch_assoc($result))
		{
			?>

			<tr>
				<td class="td-username<?php echo ($row['active'] == 0 ? ' td-inactive' : NULL); ?>"><a href="<?php echo WEBSITE_URL.URI_PREFIX.'profile/'.$row['id'].URI_SUFFIX; ?>" rel="blank"><?php echo utf8_htmlencode($row['username']); ?></a></td>
				<td class="td-realname"><?php echo $row['real_name']; ?></td>
				<td class="td-group"><?php echo $sortby_filter === 3 ? $row['usertitle'] : utf8_htmlencode($row['usergroup']); ?></td>
				<td class="td-date"><?php echo format_time($row['register_date']); ?></td>
				<td class="td-actions"><a href="<?php echo ADMIN_URL.URI_PREFIX.'users/edit/'.$row['id'].URI_SUFFIX; ?>">Edit</a> - <a class="confirm" href="<?php echo ADMIN_URL.URI_PREFIX.'users'.URI_SUFFIX.'&amp;delete='.$row['id'].'&amp;token='.SYS_TOKEN; ?>">Delete</a></td>
			</tr>

			<?php
		}

		?>

		</tbody>
	</table>

		<?php

		echo $pages;
	}
	else
		echo "\n".'<h3 class="center">No users found.</h3>'."\n";
}
?>
