<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Groups</h2>

	<p>Usergroups can be used to groups users and give those groups certain permissions to give the members of those groups permission to do
	   certain tasks or give them access to restricted content.</p>

	<p class="note">Note: Groups that contain users can not be deleted.</p>

	<ul class="two-column-list">
	<?php foreach ($usergroups as $index => $group): ?>
		<li class="row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
			<div class="name"><strong><?php echo u_htmlencode($group['name']) ?></strong></div>
			<div class="description"><?php echo u_htmlencode($group['description']) ?></div>
			<div class="actions">
				<a href="<?php echo utils::url('admin/groups/edit/'.$group['id']) ?>">Edit</a> &middot;
				<?php if ($group['user_count'] > 0): ?>
				<span class="disabled-link">Delete</span>
				<?php else: ?>
				<a href="<?php echo utils::url('admin/groups/delete/'.$group['id']), '?', utils::xsrf_token() ?>">Delete</a>
				<?php endif ?>
			</div>
		</li>
	<?php endforeach ?>
	</ul>

	<h3>Add new group</h3>

	<p>Both fields are required. You will be redirected to a page where you can configure the permissions and usertitle for the group
	   after you completed both fields and pressed <em>Add</em>.</p>

	<form  class="form-style-two" method="post" accept-charset="utf-8" action="<?php echo utils::url('admin/groups/add') ?>">
		<div>
			<?php echo utils::xsrf_form_html(), "\n" ?>
		</div>
		<p>
			<label>
				<strong>Name <span>(required)</span></strong>
				<input type="text" name="form[name]" maxlength="20" />
			</label>
			<label>
				<strong>Description <span>(required)</span></strong>
				<input type="text" name="form[description]" maxlength="255" />
			</label>
		</p>
		<p class="buttons">
			<input type="submit" value="Add" name="form_admin_add_group" />
			<input type="reset" value="Reset" />
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
