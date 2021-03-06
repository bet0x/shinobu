<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2><?php echo u_htmlencode($page_title) ?></h2>

	<p>Only the <em>name</em> field is required, but it's recommended that you also provide a user title and a group description.</p>

	<?php if (count($errors) > 0): ?>
	<ul class="form-errors">
		<?php foreach ($errors as $e) echo '<li>'.$e.'</li>'."\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/groups/add') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<p>
			<label<?php if (isset($errors['name'])) echo ' class="error-field"' ?>>
				<strong>Name <span>(required)</span></strong>
				<input type="text" name="form[name]" maxlength="20"<?php echo ' value="'.u_htmlencode($values['name']).'"' ?> />
			</label>
		</p>
		<p class="description">The name of the usergroup. This is viewable by everyone.</p>
		<p>
			<label<?php if (isset($errors['user_title'])) echo ' class="error-field"' ?>>
				<strong>User title</strong>
				<input type="text" name="form[user_title]" maxlength="20"<?php echo ' value="'.u_htmlencode($values['user_title']).'"' ?> />
			</label>
		</p>
		<p class="description">The title that the users gets when he/she/it is a member of this group.</p>
		<p>
			<label<?php if (isset($errors['description'])) echo ' class="error-field"' ?>>
				<strong>Description</strong>
				<input type="text" name="form[description]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['description']).'"' ?> />
			</label>
		</p>
		<p class="description">This is only used for administrative purposes. A short description for each group in the overview
		                       makes it easier to identify a usergroup.</p>

		<?php foreach (_permission_struct::$sets as $set_id => $set): ?>
		<p class="non-text-fields">
			<strong>Permissions: <?php echo $set_id ?></strong>
			<?php foreach($set as $perm_id => $bit): ?>
			<label>
				<input type="checkbox" name="perm[<?php echo $set_id ?>][<?php echo $perm_id ?>]" value="1" />
				<?php echo u_htmlencode($perm_id) ?>
			</label>
			<?php endforeach ?>
		</p>
		<?php endforeach ?>

		<p class="buttons">
			<input type="submit" value="Update" name="form_admin_add_group" /> or
			<a href="<?php echo url('admin/groups') ?>">cancel</a>.
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
