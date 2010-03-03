<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2><?php echo u_htmlencode($page_title) ?></h2>

	<p>Info here</p>

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
		<p class="description">Info here</p>
		<p>
			<label<?php if (isset($errors['user_title'])) echo ' class="error-field"' ?>>
				<strong>User title</strong>
				<input type="text" name="form[user_title]" maxlength="20"<?php echo ' value="'.u_htmlencode($values['user_title']).'"' ?> />
			</label>
		</p>
		<p class="description">Info here</p>
		<p>
			<label<?php if (isset($errors['description'])) echo ' class="error-field"' ?>>
				<strong>Description</strong>
				<input type="text" name="form[description]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['description']).'"' ?> />
			</label>
		</p>
		<p class="description">Info here</p>
		<p class="buttons">
			<input type="submit" value="Update" name="form_admin_group_edit" /> or
			<a href="<?php echo utils::url('admin/groups') ?>">cancel</a>.
		</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
