<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Users</h2>

	<p>New users can be added with the <em>add a new user</em> button. Multiple users can be deleted at the same time by checking the
	   checkboxes and clicking the <em>delete</em> button. You can not delete yourself.</p>

	<?php echo $pagination ?>

	<form accept-charset="utf-8" method="post" action="<?php echo url('admin/users/batch') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<div class="record-list">
			<ul>
			<?php foreach ($users as $index => $user): ?>
				<li>
					<div class="list-row row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
						<input id ="ch-<?php echo $user['id'] ?>" type="checkbox" name="users[]" value="<?php echo $user['id'] ?>" />
						<label for="ch-<?php echo $user['id'] ?>"><strong><?php echo u_htmlencode($user['username']) ?></strong></label> (<?php echo u_htmlencode($user['user_title']) ?>)
						<span class="actions">
							<a class="edit-icon" title="Edit" href="<?php echo url('admin/users/edit:'.$user['id']) ?>">Edit</a>
							<a class="delete-icon" title="Delete" href="<?php echo url('admin/users/delete:'.$user['id']), '&amp;', xsrf::token() ?>">Delete</a>
						</span>
					</div>
				</li>
			<?php endforeach ?>
			</ul>
		</div>

		<p class="align-right">
			<input class="inline-button" type="submit" value="Delete" name="form_delete_selected users" /> all selected users or
			<a class="inline-button" href="<?php echo url('admin/users/add') ?>">add a new user</a>.
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
