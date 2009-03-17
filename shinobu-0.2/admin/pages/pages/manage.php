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
	if (isset($_GET['added']))
		$sys_tpl->add('main_content', '<div class="success">Page succesfully added.</div>');
	if (isset($_GET['edited']))
		$sys_tpl->add('main_content', '<div class="success">Page succesfully edited.</div>');
	else if (isset($_GET['deleted']))
		$sys_tpl->add('main_content', '<div class="success">Page succesfully deleted.</div>');
	else if (isset($_GET['delete_error']))
		$sys_tpl->add('main_content', '<div class="warning">The page you tried to delete does not exist.</div>');

	// Set page title
	$sys_tpl->assign('page_title', 'Manage Pages - '.$sys_config['website_title'].' Admin');

	?>

	<h2>Manage pages</h2>

	<p>When a page title is red it means that the page is a <strong>draft</strong> and won't be accessible on the website. When a visitor tries to access the page he will get a 404 (page not found) message.</p>

	<table id="pagelist">
		<thead>
			<tr>
				<th class="td-title">Title</th>
				<th class="td-author">Author</th>
				<th class="td-creation">Creation</th>
				<th class="td-last-edit">Last edit</th>
				<th class="td-actions">Action</th>
			</tr>
		</thead>
		<tbody>

			<?php

			$result = $sys_db->query('SELECT p.*, u.username, u.id AS uid FROM '.DB_PREFIX.'content_info AS p LEFT JOIN '.DB_PREFIX.'users AS u ON p.author=u.id ORDER BY p.create_date ASC') or error($sys_db->error(), __FILE__, __LINE__);

			if ($sys_db->num_rows($result) > 0)
			{
				while ($row = $sys_db->fetch_assoc($result))
				{
					?>

			<tr>
				<td class="td-title<?php echo $row['status'] == 0 ? ' td-draft' : NULL; ?>"><?php echo $row['title']; ?></td>
				<td class="td-author"><a href="<?php echo WEBSITE_URL.URI_PREFIX.'profile/'.$row['uid'].URI_SUFFIX; ?>"><?php echo utf8_htmlencode($row['username']); ?></a></td>
				<td class="td-creation"><?php echo format_time($row['create_date']); ?></td>
				<td class="td-last-edit"><?php echo $row['edit_date'] > 0 ? format_time($row['edit_date']) : '-'; ?></td>
				<td class="td-actions"><a href="<?php echo ADMIN_URL.URI_PREFIX.'pages/edit/'.$row['id'].URI_SUFFIX; ?>">Edit</a> - <a class="confirm" href="<?php echo ADMIN_URL.URI_PREFIX.'pages/delete/'.$row['id'].URI_SUFFIX; ?>&amp;token=<?php echo SYS_TOKEN; ?>">Delete</a></td>
			</tr>

					<?php
				}
			}
			else
			{
				?>

			<tr>
				<td colspan="5">There are no pages.</td>
			</tr>

				<?php
			}

		?>

		</tbody>
	</table>

	<?php
}

?>
