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

// Edit usergroup
else if (isset($sys_request[2]) && $sys_request[2] == 'edit')
{
	$sys_request[3] = isset($sys_request[3]) && !empty($sys_request[3]) ? intval($sys_request[3]) : 0;
	$result = $sys_db->query('SELECT g.* FROM '.DB_PREFIX.'usergroups AS g WHERE g.id='.$sys_request[3].' LIMIT 1') or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) > 0)
	{
		$usergroup = $sys_db->fetch_assoc($result);

		// Process
		if (isset($_POST['frm-submit']) && check_token())
		{
			$form = array_map('system_trim', $_POST['form']);
			$permissions_sql = NULL;
			$errors = false;

			$form['usertitle'] = utf8_htmlencode($form['usertitle']);
			$form['description'] = utf8_htmlencode($form['description']);

			// Query usergroup table
			$result = $sys_db->query('SELECT name FROM '.DB_PREFIX.'usergroups WHERE UPPER(name)=UPPER(\''.$sys_db->escape($form['groupname']).'\') OR UPPER(name)=UPPER(\''.$sys_db->escape(preg_replace('/[^\w]/', '', $form['groupname'])).'\')') or error($sys_db->error(), __FILE__, __LINE__);

			// Check usergroup name
			if (empty($form['groupname']))
				$errors['groupname'] = 'You must enter a name.';
			else if (utf8_strlen($form['groupname']) > 50)
				$errors['groupname'] = 'Usergroup name is too long.';
			else if ($sys_db->num_rows($result) && strtoupper($form['groupname']) != strtoupper($usergroup['name']))
				$errors['groupname'] = 'The name you entered is already taken.';

			// Check usertitle
			if (empty($form['usertitle']))
				$errors['usertitle'] = 'You must enter a title.';
			else if (utf8_strlen($form['usertitle']) > 50)
				$errors['usertitle'] = 'Usertille is too long.';

			// Check description
			if (utf8_strlen($form['description']) > 2000)
				$errors['description'] = 'The description you entered is too long.';

			// Check and process all permissions
			if ($usergroup['id'] != GUEST_GID && $usergroup['id'] != ADMIN_GID)
			{
				$permissions = array('p_manage_nav', 'p_manage_users', 'p_manage_pages');
				$form_p = array_map('system_trim', $_POST['form_p']);

				foreach ($permissions as $permission)
				{
					if (array_key_exists($permission, $form_p))
						$permissions_sql .= $permission.'=1, ';
					else
						$permissions_sql .= $permission.'=0, ';
				}
			}

			if ($errors === false)
			{
				$sys_db->query('
					UPDATE
						'.DB_PREFIX.'usergroups
					SET
						name=\''.$sys_db->escape($form['groupname']).'\',
						usertitle=\''.$sys_db->escape($form['usertitle']).'\',
						'.$permissions_sql.'
						description=\''.$sys_db->escape($form['description']).'\'
					WHERE
						id='.$sys_request[3]) or error($sys_db->error(), __FILE__, __LINE__);

				header('location: '.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&edited'); exit;
			}
		}

		if (isset($_GET['added']))
			$sys_tpl->add('main_content', '<div class="success">The usergroup has been added.</div>');

		// Set page title
		$sys_tpl->assign('page_title', 'Edit Usergroup - '.$sys_config['website_title'].' Admin');

		?>

<h2>Edit Usergroup: <?php echo $usergroup['name']; ?></h2>

<p>Edit usergroup.</p>

<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'usergroups/edit/'.$sys_request[3].URI_SUFFIX; ?>">

	<div>
		<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
	</div>

	<ul class="frm-vc">
		<li class="frm-title">
			<h3>Details</h3>
		</li>

		<li class="frm-block<?php echo isset($errors['groupname']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-0">Usergroup name:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[groupname]" id="fld-0" maxlength="20" value="<?php echo $usergroup['name']; ?>" /></div>
			<?php echo isset($errors['groupname']) ? '<span class="fld-error-message">'.$errors['groupname'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['usertitle']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-1">User title:</label></div>
			<div class="fld-input"><input class="text" type="text" name="form[usertitle]" id="fld-1" maxlength="100" value="<?php echo $usergroup['usertitle']; ?>" /></div>
			<?php echo isset($errors['usertitle']) ? '<span class="fld-error-message">'.$errors['usertitle'].'</span>' : NULL; ?>
		</li>

		<li class="frm-block<?php echo isset($errors['description']) ? ' form-error' : NULL; ?>">
			<div class="fld-label"><label for="fld-2">Description:</label></div>
			<div class="fld-input"><textarea name="form[description]" id="fld-2" rows="10" cols="50"><?php echo $usergroup['description'] ?></textarea></div>
			<?php echo isset($errors['description']) ? '<span class="fld-error-message">'.$errors['description'].'</span>' : NULL; ?>
		</li>

		<?php if ($usergroup['id'] != GUEST_GID && $usergroup['id'] != ADMIN_GID): ?>

		<li class="frm-title">
			<h3>Permissions</h3>
		</li>

		<li class="frm-block">If one of the following permissions is enabled all users in this usergroup are able to access the administration panel.</li>

		<li class="frm-block">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-text"><label for="fld-3"><input type="checkbox" id="fld-3" name="form_p[p_manage_nav]" value="1" <?php if ($usergroup['p_manage_nav'] == 1) echo 'checked="checked" ' ?>/> Add, edit or remove items from the navigation.</label></div>
		</li>

		<li class="frm-block">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-text"><label for="fld-4"><input type="checkbox" id="fld-4" name="form_p[p_manage_users]" value="1" <?php if ($usergroup['p_manage_users'] == 1) echo 'checked="checked" ' ?>/> Add, edit or remove users and usergroups.</label></div>
		</li>

		<li class="frm-block">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-text"><label for="fld-5"><input type="checkbox" id="fld-5" name="form_p[p_manage_pages]" value="1" <?php if ($usergroup['p_manage_pages'] == 1) echo 'checked="checked" ' ?>/> Add, edit or remove pages.</label></div>
		</li>

		<?php endif; ?>

		<li class="frm-block frm-buttons">
			<div class="fld-label">&nbsp;</div>
			<div class="fld-input">
				<input type="submit" value="Update Usergroup" name="frm-submit" id="frm-submit" />
				<input type="button" onclick="window.location='<?php echo ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX; ?>'" value="Cancel" name="frm-cancel" />
			</div>
		</li>
	</ul>
</form>

		<?php
	}
	else
		send_404($sys_lang['e_error'], '<p>Usergroup does not exist.</p>');
}

// View usergroups (overview)
else
{
	// Delete usergroup
	if (isset($_GET['delete']) && check_token(true))
	{
		$group_id = intval($_GET['delete']);

		if ($group_id === GUEST_GID || $group_id === ADMIN_GID)
		{
			header('location: '.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&admin_guest'); exit;
		}
		else
		{
			$result_exist = $sys_db->query('SELECT g.id FROM '.DB_PREFIX.'usergroups AS g WHERE g.id='.$group_id) or error($sys_db->error(), __FILE__, __LINE__);
			$result_members = $sys_db->query('SELECT u.gid FROM '.DB_PREFIX.'users AS u WHERE u.gid='.$group_id) or error($sys_db->error(), __FILE__, __LINE__);

			if ($sys_db->num_rows($result_exist) === 0)
			{
				header('location: '.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&delete_error'); exit;
			}
			else if ($sys_db->num_rows($result_members) > 0)
			{
				header('location: '.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&not_empty'); exit;
			}
			else
			{
				$sys_db->query('DELETE FROM '.DB_PREFIX.'usergroups WHERE id='.intval($_GET['delete'])) or error($sys_db->error(), __FILE__, __LINE__);
				header('location: '.ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&deleted'); exit;
			}
		}
	}

	// Add usergroup
	if (isset($_POST['frm-submit']) && isset($_GET['add']) && check_token())
	{
		// Set vars
		$form = array_map('system_trim', $_POST['form']);
		$errors = false;

		// Query usergroup table
		$result = $sys_db->query('SELECT name FROM '.DB_PREFIX.'usergroups WHERE UPPER(name)=UPPER(\''.$sys_db->escape($form['groupname']).'\') OR UPPER(name)=UPPER(\''.$sys_db->escape(preg_replace('/[^\w]/', '', $form['groupname'])).'\')') or error($sys_db->error(), __FILE__, __LINE__);

		// Check usergroup name
		if (empty($form['groupname']))
			$errors['groupname'] = 'You must enter a name.';
		else if (utf8_strlen($form['groupname']) > 50)
			$errors['groupname'] = 'Usergroup name is too long.';
		else if ($sys_db->num_rows($result))
			$errors['groupname'] = 'The name you entered is already taken.';

		// Check usertitle
		if (empty($form['usertitle']))
			$errors['usertitle'] = 'You must enter a title.';
		else if (utf8_strlen($form['usertitle']) > 50)
			$errors['usertitle'] = 'Usertitle is too long.';

		if ($errors === false)
		{
			$sys_db->query('INSERT INTO '.DB_PREFIX.'usergroups (name, usertitle) VALUES(\''.$sys_db->escape($form['groupname']).'\', \''.$sys_db->escape($form['usertitle']).'\')') or error($sys_db->error(), __FILE__, __LINE__);
			header('location: '.ADMIN_URL.URI_PREFIX.'usergroups/edit/'.$sys_db->insert_id().URI_SUFFIX.'&added'); exit;
		}
	}

	if (isset($_GET['edited']))
		$sys_tpl->add('main_content', '<div class="success">Usergroup succesfully edited.</div>');
	else if (isset($_GET['admin_guest']))
		$sys_tpl->add('main_content', '<div class="warning">The <em>Guests</em> and <em>Administrators</em> groups cannot be deleted.</div>');
	else if (isset($_GET['delete_error']))
		$sys_tpl->add('main_content', '<div class="warning">The usergroup you tried to delete does not exist.</div>');
	else if (isset($_GET['not_empty']))
		$sys_tpl->add('main_content', '<div class="warning">Usergroup cannot be deleted. There are still users in this group.</div>');
	else if (isset($_GET['deleted']))
		$sys_tpl->add('main_content', '<div class="success">Usergroup succesfully deleted.</div>');

	// Set page title
	$sys_tpl->assign('page_title', 'Manage Usergroups - '.$sys_config['website_title'].' Admin');

	?>

	<h2>Manage Usergroups</h2>

	<p>Edit or delete usergroups.</p>

	<table id="grouplist">
		<thead>
			<tr>
				<th class="td-groupname">Name</th>
				<th class="td-usertitle">User title</th>
				<th class="td-actions">Actions</th>
			</tr>
		</thead>
		<tbody>
		<?php

		$result = $sys_db->query('SELECT g.* FROM '.DB_PREFIX.'usergroups AS g ORDER BY g.id ASC') or error($sys_db->error(), __FILE__, __LINE__);

		while ($row = $sys_db->fetch_assoc($result))
		{
			?>
			<tr>
				<td class="td-groupname"><?php echo $row['name']; ?></td>
				<td class="td-usertitle"><?php echo $row['usertitle']; ?></td>
				<td class="td-actions"><a href="<?php echo ADMIN_URL.URI_PREFIX.'usergroups/edit/'.$row['id'].URI_SUFFIX; ?>">Edit</a> - <a class="confirm" href="<?php echo ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&amp;delete='.$row['id'].'&amp;token='.SYS_TOKEN; ?>">Delete</a></td>
			</tr>
			<?php
		}

		?>
		</tbody>
	</table>

	<h2>Add new usergroup</h2>

	<p>The usergroup name and usertitle can not be longer than 50 characters. Once you clicked <strong>Add Group</strong> the user group is added to the database and you will be redirected to a page where you can configurate the usergroup to your needs.</p>

	<?php

	if (isset($errors) && count($errors) > 0)
	{
		echo '<ul>';
		foreach ($errors as $error)
			echo '<li>'.$error.'</li>';
		echo '</ul>';
	}

	?>

	<form method="post" accept-charset="utf-8" action="<?php echo ADMIN_URL.URI_PREFIX.'usergroups'.URI_SUFFIX.'&amp;add'; ?>">

		<div>
			<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
		</div>

		<ul id="add-usergroup" class="frm-hc hc-box hc-inline-box">
			<li class="frm-block<?php echo isset($errors['groupname']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-0">Name:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[groupname]" id="fld-0" maxlength="50" /></div>
			</li>

			<li class="frm-block<?php echo isset($errors['usertitle']) ? ' form-error' : NULL; ?>">
				<div class="fld-label"><label for="fld-1">Usertitle:</label></div>
				<div class="fld-input"><input class="text" type="text" name="form[usertitle]" id="fld-1" maxlength="50" /></div>
			</li>

			<li class="frm-block frm-buttons">
				<div class="fld-input">
					<input type="submit" value="Add Group" name="frm-submit" />
				</div>
			</li>
		</ul>
	</form>

<?php } ?>
