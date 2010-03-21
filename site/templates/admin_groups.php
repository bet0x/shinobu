<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Groups</h2>

	<p>Usergroups can be used to groups users and give those groups certain permissions to give the members of those groups permission to do
	   certain tasks or give them access to restricted content.</p>

	<p class="note"><strong>Note:</strong> Groups that contain users can not be deleted. All users in those groups should be moved
	                before they can be deleted.</p>

	<ul class="group-list">
	<?php foreach ($usergroups as $index => $group): ?>
		<li class="row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
			<div class="name"><strong><?php echo u_htmlencode($group['name']) ?></strong></div>
			<div class="description"><?php echo $group['description'] ? u_htmlencode($group['description']) : '&nbsp;' ?></div>
			<div class="actions">
				<a href="<?php echo url('admin/groups/edit:'.$group['id']) ?>">Edit</a> &middot;
				<?php if ($group['user_count'] > 0): ?>
				<span class="disabled-link">Delete</span>
				<?php else: ?>
				<a href="<?php echo url('admin/groups/delete:'.$group['id']), '&amp;', xsrf::token() ?>">Delete</a>
				<?php endif ?>
			</div>
		</li>
	<?php endforeach ?>
	</ul>

	<p class="align-right"><a class="inline-button" href="<?php echo url('admin/groups/add') ?>">Add new group</a></p>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
