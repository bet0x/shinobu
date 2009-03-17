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

$user_filter = $user_sql = $group_sql = NULL;
$group_filter = 0;
$sortby_filter = 5;
$sortorder_filter = 1;
$sorting_sql = ' ORDER BY u.register_date ASC';

// Reset filter
if (isset($_POST['frm-reset']))
{
	header('location: '.WEBSITE_URL.URI_PREFIX.'userlist'.URI_SUFFIX); exit;
}

// Process filter
if (isset($_POST['frm-submit']) && check_token())
{
	$form = array_map('system_trim', $_POST['form']);

	$form['username'] = !empty($form['username']) ? base64_url_encode($form['username']) : 0;
	$form['usergroup'] = intval($form['usergroup']);
	$form['sortby'] = intval($form['sortby']);
	$form['sortorder'] = intval($form['sortorder']);

	header('location: '.WEBSITE_URL.URI_PREFIX.'userlist'.URI_SUFFIX.'&filter='.implode('/', $form)); exit;
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

$sys_tpl->assign('page_title', $sys_lang['t_userlist'].' - '.$sys_config['website_title']);

?>

<div id="userlist">
	<h2><span><?php echo $sys_lang['t_userlist']; ?></span></h2>

	<div id="userlist-filter">
		<form method="post" accept-charset="utf-8" action="<?php echo WEBSITE_URL.URI_PREFIX.'userlist'.URI_SUFFIX; ?>">

			<div>
				<input type="hidden" name="token" value="<?php echo SYS_TOKEN ?>" />
			</div>

			<ul id="userlist-search" class="frm-hc">
				<li class="frm-block">
					<div class="fld-label"><label for="fld-0"><?php echo $sys_lang['g_username']; ?>:</label></div>
					<div class="fld-input"><input class="text" type="text" name="form[username]" id="fld-0" maxlength="20" <?php echo utf8_strlen($user_filter) > 0 ? ' value="'.utf8_htmlencode(base64_url_decode($user_filter)).'"' : NULL; ?>/></div>
				</li>

				<li class="frm-block">
					<div class="fld-label"><label for="fld-1"><?php echo $sys_lang['g_usergroup']; ?>:</label></div>
					<div class="fld-input">
						<select name="form[usergroup]" id="fld-1">
							<option value="0"><?php echo $sys_lang['f_all_usergroups']; ?></option>

							<?php

							$result = $sys_db->query('SELECT g.id, g.name FROM '.DB_PREFIX.'usergroups AS g WHERE id != '.GUEST_GID) or error($sys_db->error(), __FILE__, __LINE__);
							while ($row = $sys_db->fetch_assoc($result))
								echo '<option value="'.$row['id'].'" '.($row['id'] == $group_filter ? ' selected="selected"' : NULL).'>'.utf8_htmlencode($row['name']).'</option>'."\n";

							?>

						</select>
					</div>
				</li>

				<li class="frm-block">
					<div class="fld-label"><label for="fld-2"><?php echo $sys_lang['f_sort_by']; ?>:</label></div>
					<div class="fld-input">
						<select name="form[sortby]" id="fld-2">
							<option value ="1"<?php echo $sortby_filter === 1 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_username']; ?></option>
							<option value ="2"<?php echo $sortby_filter === 2 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_realname']; ?></option>
							<option value ="3"<?php echo $sortby_filter === 3 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_title']; ?></option>
							<option value ="4"<?php echo $sortby_filter === 4 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_usergroup']; ?></option>
							<option value ="5"<?php echo $sortby_filter === 5 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_register_date']; ?></option>
						</select>
					</div>
				</li>

				<li class="frm-block">
					<div class="fld-label"><label for="fld-3"><?php echo $sys_lang['f_sorting_order']; ?>:</label></div>
					<div class="fld-input">
						<select name="form[sortorder]" id="fld-3">
							<option value ="1"<?php echo $sortorder_filter === 1 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_ascending']; ?></option>
							<option value ="2"<?php echo $sortorder_filter === 2 ? ' selected="selected"' : NULL; ?>><?php echo $sys_lang['g_descending']; ?></option>
						</select>
					</div>
				</li>

				<li class="frm-block frm-buttons">
					<div class="fld-input">
						<input type="submit" value="<?php echo $sys_lang['b_search']; ?>" name="frm-submit" />
						<input type="submit" value="<?php echo $sys_lang['b_reset']; ?>" name="frm-reset" />
					</div>
				</li>
			</ul>
		</form>
	</div>

	<?php

	$sys_request[2] = isset($sys_request[2]) && $sys_request[2] > 0 ? intval($sys_request[2]) : 1;
	$list_start = ($sys_request[2]-1) * 20;
	$list_limit = 20;

	$result = $sys_db->query('SELECT u.id, u.username, u.real_name, u.register_date, g.name AS usergroup, g.usertitle FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id!='.GUEST_UID.' AND u.active=1'.$user_sql.$group_sql.$sorting_sql.' LIMIT '.$list_start.','.$list_limit) or error($sys_db->error(), __FILE__, __LINE__);

	if ($sys_db->num_rows($result) > 0)
	{
		// Count users
		$user_count = $sys_db->fetch_assoc($sys_db->query('SELECT COUNT(*) FROM '.DB_PREFIX.'users AS u INNER JOIN '.DB_PREFIX.'usergroups AS g ON u.gid=g.id WHERE u.id > 1 AND u.active=1'.$user_sql.$group_sql)) or error($sys_db->error(), __FILE__, __LINE__);
		$user_count = $user_count['COUNT(*)'];

		if (!isset($_GET['filter']))
			$userlist_url = WEBSITE_URL.URI_PREFIX.'userlist/%d'.URI_SUFFIX;
		else
			$userlist_url = WEBSITE_URL.URI_PREFIX.'userlist/%d'.URI_SUFFIX.'&filter='.utf8_htmlencode($_GET['filter']);

		$pages = pagination($user_count, 20, $sys_request[2], $userlist_url);

		?>

	<div class="info"><?php echo sprintf($sys_lang['d_userlist'], $user_count); ?></div>

	<?php echo $pages; ?>

	<table>
		<thead>

			<tr>
				<th class="ul-username" scope="col"><?php echo $sys_lang['g_username']; ?></th>
				<th class="ul-realname" scope="col"><?php echo $sys_lang['g_realname']; ?></th>
				<th class="ul-usergroup" scope="col"><?php echo $sys_lang['g_title']; ?>/<?php echo $sys_lang['g_usergroup']; ?></th>
				<th class="ul-regdate" scope="col"><?php echo $sys_lang['g_register_date']; ?></th>
			</tr>
		</thead>

		<tbody>

		<?php

		while ($row = $sys_db->fetch_assoc($result))
		{
			?>

			<tr>
				<td class="ul-username"><a href="<?php echo WEBSITE_URL.URI_PREFIX.'profile/'.$row['id'].URI_SUFFIX; ?>"><?php echo utf8_htmlencode($row['username']); ?></a></td>
				<td class="ul-realname"><?php echo $row['real_name']; ?></td>
				<td class="ul-usergroup"><?php echo $sortby_filter === 3 ? $row['usertitle'] : utf8_htmlencode($row['usergroup']); ?></td>
				<td class="ul-regdate"><?php echo format_time( $row['register_date']); ?></td>
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
		echo "\n".'<h3 class="no-users-found">'.$sys_lang['e_no_users_found'].'</h3>'."\n";

	?>
</div>
