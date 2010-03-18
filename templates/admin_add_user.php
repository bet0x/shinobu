<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Add new user</h2>

	<p>All fields are required. The added user will not be notified after he/she has been added.</p>

	<?php if (count($errors) > 0): ?>
	<h4><strong>The following errors need to be corrected before you can register:</strong></h4>

	<ul class="form-errors">
	<?php foreach ($errors as $e) echo "\t", '<li>', $e, '</li>', "\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/users/add') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<p>
			<label<?php if (isset($errors['username'])) echo ' class="error-field"' ?>>
				<strong>Username <span>(required)</span></strong>
				<input type="text" name="form[username]" maxlength="20"<?php echo ' value="'.u_htmlencode($values['username']).'"' ?> />
			</label>
		</p>
		<p class="description">A username must be unique and must be shorther than 3 characters or longer than 40 characters.</p>
		<p>
			<label>
				<strong>Usergroup <span>(required)</span></strong>
				<select name="form[group_id]">
					<option value="<?php echo $values['group_id'] ?>" selected="selected"><?php echo u_htmlencode($usergroups[$values['group_id']]) ?></option>
					<option disabled="disabled">----------</option>
					<?php foreach ($usergroups as $id => $name): ?>
					<option value="<?php echo $id ?>"><?php echo u_htmlencode($name) ?></option>
					<?php endforeach ?>
				</select>
			</label>
		</p>
		<p class="description">The user's usergroup.</p>
		<p class="multiple-fields">
			<label<?php if (isset($errors['password'])) echo ' class="error-field"' ?>>
				<strong>Password <span>(required)</span></strong>
				<input type="password" name="form[password]" maxlength="40" />
			</label>
			<label class="confirm-field<?php if (isset($errors['password'])) echo ' error-field' ?>">
				<strong>Confirm password <span>(required)</span></strong>
				<input type="password" name="form[confirm_password]" maxlength="40" />
			</label>
		</p>
		<p class="description">Passwords can be between 6 and 40 characters long and are case sensitive. It is recommended that a
							   combination of various characters is chosen for the password.</p>
		<p class="multiple-fields">
			<label<?php if (isset($errors['email'])) echo ' class="error-field"' ?>>
				<strong>E-mail address <span>(required)</span></strong>
				<input type="text" name="form[email]" maxlength="40"<?php echo ' value="'.u_htmlencode($values['email']).'"' ?> />
			</label>
			<label class="confirm-field<?php if (isset($errors['email'])) echo ' error-field' ?>">
				<strong>Confirm e-mail address <span>(required)</span></strong>
				<input type="text" name="form[confirm_email]" maxlength="40" />
			</label>
		</p>
		<p class="description">The e-mail address will not be visible to members or visitors, except administrators and moderators.
							   Use an active e-mail address, because activation e-mails will be send to the user.</p>
		<p class="buttons"><input type="submit" value="Add" name="form_add_user" /> or <a href="<?php echo url('admin/users') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
