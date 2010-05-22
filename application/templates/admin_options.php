<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Options</h2>

	<p>All options for your website, system modules and libraries.</p>

	<?php if (count($errors) > 0): ?>
	<ul class="form-errors">
		<?php foreach ($errors as $e) echo '<li>'.$e.'</li>'."\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/options') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<p>
			<label<?php if (isset($errors['website_title'])) echo ' class="error-field"' ?>>
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
			<label<?php if (isset($errors['default_usergroup'])) echo ' class="error-field"' ?>>
				<strong>Default usergroup</strong>
				<select name="form[default_usergroup]">
					<option value="<?php echo $values['default_usergroup'] ?>" selected="selected"><?php echo u_htmlencode($usergroups[$values['default_usergroup']]) ?></option>
					<option disabled="disabled">----------</option>
					<?php foreach ($usergroups as $id => $name): ?>
					<option value="<?php echo $id ?>"><?php echo u_htmlencode($name) ?></option>
					<?php endforeach ?>
				</select>
			</label>
		</p>
		<p class="description">This is the default user group, e.g. the group users are placed in when they register. Don't choose a powerful
		                       usergroup (e.g. the administrator group), because that means new users will have the permissions of that
							   usergroup.</p>

		<p>
			<label<?php if (isset($errors['timezone'])) echo ' class="error-field"' ?>>
				<strong>Timezone</strong>
				<input type="text" name="form[timezone]" maxlength="50"<?php echo ' value="'.u_htmlencode($values['timezone']).'"' ?> />
			</label>
		</p>
		<p class="description">Pick a valid timezone from <a href="http://en.wikipedia.org/wiki/List_of_tz_database_time_zones">Wikipedia</a>.</p>

		<p>
			<label<?php if (isset($errors['date_format'])) echo ' class="error-field"' ?>>
				<strong>Date format</strong>
				<input type="text" name="form[date_format]" maxlength="50"<?php echo ' value="'.u_htmlencode($values['date_format']).'"' ?> />
			</label>
		</p>
		<p class="description">Current format: <strong><?php echo $date_format_example ?></strong>. See
		                       <a href="http://php.net/manual/en/function.date.php">PHP manual</a>
		                       for formatting options.</p>

		<p>
			<label<?php if (isset($errors['time_format'])) echo ' class="error-field"' ?>>
				<strong>Time format</strong>
				<input type="text" name="form[time_format]" maxlength="50"<?php echo ' value="'.u_htmlencode($values['time_format']).'"' ?> />
			</label>
		</p>
		<p class="description">Current format: <strong><?php echo $time_format_example ?></strong>. See
		                       <a href="http://php.net/manual/en/function.date.php">PHP manual</a>
		                       for formatting options.</p>

		<p class="buttons"><input type="submit" value="Update" name="form_admin_options" /> or <a href="<?php echo url('admin') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
