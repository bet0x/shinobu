<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2><?php echo u_htmlencode($page_title) ?></h2>

	<p>Only the <em>name</em> field is required, but it's recommended that you also provide a user title and a group description.</p>

	<?php if (count($errors) > 0): ?>
	<h4><strong>The following errors need to be corrected before everything will be stored:</strong></h4>

	<ul class="form-errors">
		<?php foreach ($errors as $e) echo '<li>'.$e.'</li>'."\n" ?>
	</ul>
	<?php endif ?>

	<form  class="form-style-one" method="post" accept-charset="utf-8" action="<?php echo utils::url('admin/groups/edit:'.$values['id']) ?>">
		<div>
			<?php echo utils::xsrf_form_html(), "\n" ?>
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

		<p class="non-text-fields">
			<strong>Permissions</strong>
			<?php foreach($permissions as $p): ?>
			<label>
				<input type="checkbox" name="acl[<?php echo $p['acl_id'] ?>][<?php echo $p['name'] ?>]" value="1"<?php echo $p['check'] ? ' checked="checked"' : '' ?>/>
				<?php echo u_htmlencode($p['desc']) ?>
			</label>
			<?php endforeach ?>
		</p>

		<p class="buttons">
			<input type="submit" value="Update" name="form_admin_edit_group" /> or
			<a href="<?php echo utils::url('admin/groups') ?>">cancel</a>.
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
