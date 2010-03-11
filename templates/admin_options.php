<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Options</h2>

	<p>All options for your website, system modules and libraries.</p>

	<?php if (count($errors) > 0): ?>
	<h4><strong>The following errors need to be corrected before all options will be stored:</strong></h4>

	<ul class="form-errors">
		<?php foreach ($errors as $e) echo '<li>'.$e.'</li>'."\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" accept-charset="utf-8" action="<?php echo utils::url('admin/options') ?>">
		<div>
			<?php echo utils::xsrf_form_html(), "\n" ?>
		</div>
		<p>
			<label>
				<strong>Website title</strong>
				<input type="text" name="form[website_title]" maxlength="50"<?php echo ' value="'.u_htmlencode($values['website_title']).'"' ?> />
			</label>
		</p>
		<p class="description">The title of the website. This field may not contain HTML.</p>

		<p class="non-text-fields">
			<strong>Allow new user registrations?</strong>
			<label><input type="radio" name="form[allow_new_registrations]" value="1"<?php echo $values['allow_new_registrations'] === 1 ? ' checked="checked"' : '' ?>/> Yes</label>
			<label><input type="radio" name="form[allow_new_registrations]" value="0"<?php echo $values['allow_new_registrations'] === 0 ? ' checked="checked"' : '' ?>/> No</label>
		</p>

		<p>
			<label>
				<strong>Default usergroup</strong>
				<select name="form[default_usergroup]">
					<option value="<?php echo $values['default_usergroup'] ?>"><?php echo u_htmlencode($usergroups[$values['default_usergroup']]) ?></option>
					<option value="0">----------</option>
					<?php foreach ($usergroups as $id => $name): ?>
					<option value="<?php echo $id ?>"><?php echo u_htmlencode($name) ?></option>
					<?php endforeach ?>
				</select>
			</label>
		</p>
		<p class="description">This is the default user group, e.g. the group users are placed in when they register. Don't choose a powerful
		                       usergroup (e.g. the administrator group), because that means new users will have the permissions of that
							   usergroup.</p>

		<p class="buttons"><input type="submit" value="Update" name="form_admin_options" /> or <a href="<?php echo utils::url('admin') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
