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

// Check if user is logged in
if ($sys_user['logged'] === true && !check_token(true))
{
	$sys_tpl->assign('page_title', $sys_lang['e_error'].' - '.$sys_config['website_title']);

	?>

<h2><span><?php echo $sys_lang['e_error']; ?></span></h2>

<p><?php echo $sys_lang['e_already_logged_in']; ?></p>

	<?php
}
else
{
	if (isset($_POST['frm-submit']) && check_token())
	{
		$form = array_map('system_trim', $_POST['form']);
		$errors = login($form['username'], $form['password']);

		if ($errors === false)
		{
			header('location: '.WEBSITE_URL.'?login'); exit;
		}
	}

	if ($sys_user['logged'] === true && check_token(true))
		logout(WEBSITE_URL.'?logout');

	$sys_tpl->assign('page_title', $sys_lang['t_login'].' - '.$sys_config['website_title']);

	if (isset($errors['account']))
		$sys_tpl->add('main_content', '<div class="notice">'.$errors['account'].'</div>');

	?>

<div id="login">
	<h2><span><?php echo $sys_lang['t_login']; ?></span></h2>

	<p><?php echo $sys_lang['d_login']; ?></p>

	<form method="post" accept-charset="utf-8" action="<?php echo WEBSITE_URL.URI_PREFIX.'login'.URI_SUFFIX; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul class="frm-vc">
			<li class="frm-block<?php echo isset($errors['username']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0"><?php echo $sys_lang['g_username']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[username]" id="fld-0" maxlength="20" /></div>
				<?php echo isset($errors['username']) ? '<span class="fld-error-message">'.$errors['username'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block<?php echo isset($errors['password']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-1"><?php echo $sys_lang['g_password']; ?>:</label></div>
				<div class="fld-input"><input class="text" type="password" name="form[password]" id="fld-1" maxlength="50" /></div>
				<?php echo isset($errors['password']) ? '<span class="fld-error-message">'.$errors['password'].'</span>' : NULL; ?>
			</li>

			<li class="frm-block">
				<div class="fld-label">&nbsp;</div>
				<div class="fld-input"><input type="submit" value="<?php echo $sys_lang['b_login']; ?>" name="frm-submit" /></div>
			</li>
		</ul>
	</form>
</div>

	<?php
}
?>
