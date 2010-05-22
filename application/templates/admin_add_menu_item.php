<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Add new item</h2>

	<p>If the path starts with <strong>http/https/ftp/irc</strong> or with a
	   <strong>slash</strong> (/) it is an external path, otherwise it will be transformed intro something like
	   <strong>http://example.com/?q=user</strong> (an internal path).</p>

	<?php if (count($errors) > 0): ?>
	<ul class="form-errors">
	<?php foreach ($errors as $e) echo "\t", '<li>', $e, '</li>', "\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/menu/add') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<p>
			<label<?php if (isset($errors['name'])) echo ' class="error-field"' ?>>
				<strong>Name <span>(required)</span></strong>
				<input type="text" name="form[name]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['name']).'"' ?> />
			</label>
		</p>
		<p class="description">A name must not be shorther than 3 characters or longer than 255 characters.</p>

		<p>
			<label<?php if (isset($errors['path'])) echo ' class="error-field"' ?>>
				<strong>Path <span>(required)</span></strong>
				<input type="text" name="form[path]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['path']).'"' ?> />
			</label>
		</p>
		<p class="description">This can be anything as long as it represents a path (e.g. an URL). A path must not be shorther
		                       than 1 character or longer than 255 characters.</p>

		<p>
			<label<?php if (isset($errors['position'])) echo ' class="error-field"' ?>>
				<strong>Position</strong>
				<input type="text" name="form[position]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['position']).'"' ?> />
			</label>
		</p>
		<p class="description">The position must be a numeric value, not lower than 0 and not higher than 255.</p>

		<p class="buttons"><input type="submit" value="Add" name="form_add_menu_item" /> or <a href="<?php echo url('admin/menu') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
