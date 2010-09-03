<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Groups</h2>

	<p>Usergroups can be used to groups users and give those groups certain permissions to give the members of those groups permission to do
	   certain tasks or give them access to restricted content.</p>

	<p class="note"><strong>Note:</strong> Groups that contain users can not be deleted. All users in those groups should be moved
	                before they can be deleted.</p>

	<?php echo $pagination ?>

	<div class="record-list">
		<ul>
		<?php foreach ($usergroups as $index => $group): ?>
			<li>
				<div class="list-row row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
					&nbsp;<strong><?php echo u_htmlencode($group['name']) ?></strong>
					(<?php echo $group['user_count'] ?>)
					<span class="actions">
						<a class="edit-icon" title="Edit" href="<?php echo url('admin/groups/edit:'.$group['id']) ?>">Edit</a>
						<?php if ($group['user_count'] < 1): ?>
						<a class="delete-icon" title="Delete" href="<?php echo url('admin/groups/delete:'.$group['id']), '&amp;', xsrf::token() ?>">Delete</a>
						<?php endif ?>
					</span>
				</div>
			</li>
		<?php endforeach ?>
		</ul>
	</div>

	<p class="align-right"><a class="inline-button" href="<?php echo url('admin/groups/add') ?>">Add new group</a></p>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
