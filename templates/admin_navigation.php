<div id="admin-navigation" class="section-<?php echo $subsection ?>">
	<ul>
		<?php if ($admin_perms & ACL_PERM_1): ?>
		<li><a class="anav-info" href="<?php echo utils::url('admin') ?>">Information</a></li>
		<?php endif ?>
		<?php if ($admin_perms & ACL_PERM_5): ?>
		<li><a class="anav-options" href="<?php echo utils::url('admin/options') ?>">Options</a></li>
		<?php endif ?>
		<?php if ($admin_perms & ACL_PERM_6): ?>
		<li><a class="anav-groups" href="<?php echo utils::url('admin/groups') ?>">Groups</a></li>
		<?php endif ?>
		<?php if ($admin_perms & ACL_PERM_3): ?>
		<li><a class="anav-users" href="<?php echo utils::url('admin/users') ?>">Users</a></li>
		<?php endif ?>
		<?php if ($admin_perms & ACL_PERM_4): ?>
		<li><a class="anav-menu" href="<?php echo utils::url('admin/menu') ?>">Menu</a></li>
		<?php endif ?>
		<?php if ($admin_perms & ACL_PERM_2): ?>
		<li><a class="anav-pages" href="<?php echo utils::url('admin/pages') ?>">Pages</a></li>
		<?php endif ?>
	</ul>
</div>
