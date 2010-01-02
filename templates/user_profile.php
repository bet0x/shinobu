<?php require 'header.php' ?>

<p>On this page you can change or update your password, settings and e-mail address. Under each field there is a text that explains everything.
Please read those texts.</p>

<?php if (count($errors) > 0): ?>
<h4><strong>The following errors need to be corrected before you can update your profile:</strong></h4>

<ul class="form-errors">
	<?php foreach ($errors as $e) echo '<li>'.$e.'</li>'."\n" ?>
</ul>
<?php endif ?>

<form  class="form-style-one" method="post" accept-charset="utf-8" action="<?php utils::url('user') ?>">
	<div>
		<?php echo utils::xsrf_form_html(), "\n" ?>
	</div>
	<p>
		<label>
			<strong>Username</strong>
			<input type="text" disabled="disabled" maxlength="20"<?php echo ' value="'.u_htmlencode($values['username']).'"' ?> />
		</label>
	</p>
	<p class="description">You can not change your username. An administrator or moderator can change this for you when you have a good reason.</p>
	<p class="multiple-fields">
		<label<?php if (isset($errors['password'])) echo ' class="error-field"' ?>>
			<strong>Change password</strong>
			<input type="password" name="form[changed_password]" maxlength="40" />
		</label>
		<label class="confirm-field<?php if (isset($errors['password'])) echo ' error-field' ?>">
			<strong>Confirm password</strong>
			<input type="password" name="form[confirm_changed_password]" maxlength="40" />
		</label>
	</p>
	<p class="description">Passwords can be between 6 and 40 characters long and are case sensitive. It is recommended that you choose a
	                       combination of various characters for your password.</p>
	<p>
		<label<?php if (isset($errors['email'])) echo ' class="error-field"' ?>>
			<strong>E-mail address <span>(required)</span></strong>
			<input type="text" name="form[email]" maxlength="40"<?php echo ' value="'.u_htmlencode($values['email']).'"' ?> />
		</label>
	</p>
	<p class="description">Your e-mail address will not be visible to members or visitors, except administrators and moderators.
	                       Use your active e-mail address, because you will receive an activation mail.</p>
	<p class="buttons"><input type="submit" value="Update" name="form_profile" /> or <a href="<?php echo SYSTEM_BASE_URL ?>">cancel</a>.</p>
</form>

<?php require 'footer.php' ?>
