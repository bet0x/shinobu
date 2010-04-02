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
		<ul class="user-list">
		<?php foreach ($users as $index => $user): ?>
			<li class="row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
				<div class="checkbox"><input id ="ch-<?php echo $user['id'] ?>" type="checkbox" name="users[]" value="<?php echo $user['id'] ?>" /></div>
				<div class="name"><label for="ch-<?php echo $user['id'] ?>"><strong><?php echo u_htmlencode($user['username']) ?></strong></label> (<?php echo u_htmlencode($user['user_title']) ?>)</div>
				<div class="actions">
					<a class="tiny-button" href="<?php echo url('admin/users/edit:'.$user['id']) ?>" title="Edit">/</a>
					<a class="tiny-button" href="<?php echo url('admin/users/delete:'.$user['id']), '&amp;', xsrf::token() ?>" title="Delete">X</a>
				</div>
			</li>
		<?php endforeach ?>
		</ul>

		<p class="align-right">
			<input class="inline-button" type="submit" value="Delete" name="form_delete_selected users" /> all selected users or
			<a class="inline-button" href="<?php echo url('admin/users/add') ?>">add a new user</a>.
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
