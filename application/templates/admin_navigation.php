<div id="admin-navigation" class="section-<?php echo $subsection ?>">
	<ul>
		<?php if ($user->is_allowed('admin', 'info')): ?>
		<li><a class="anav-info" href="<?php echo url('admin') ?>">Information</a></li>
		<?php endif ?>
		<?php if ($user->is_allowed('admin', 'options')): ?>
		<li><a class="anav-options" href="<?php echo url('admin/options') ?>">Options</a></li>
		<?php endif ?>
		<?php if ($user->is_allowed('admin', 'groups')): ?>
		<li><a class="anav-groups" href="<?php echo url('admin/groups') ?>">Groups</a></li>
		<?php endif ?>
		<?php if ($user->is_allowed('admin', 'users')): ?>
		<li><a class="anav-users" href="<?php echo url('admin/users') ?>">Users</a></li>
		<?php endif ?>
		<?php if ($user->is_allowed('admin', 'menu')): ?>
		<li><a class="anav-menu" href="<?php echo url('admin/menu') ?>">Menu</a></li>
		<?php endif ?>
		<?php if ($user->is_allowed('admin', 'pages')): ?>
		<li><a class="anav-pages" href="<?php echo url('admin/pages') ?>">Pages</a></li>
		<?php endif ?>
	</ul>
</div>
