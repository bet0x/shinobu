<?php require 'header.php' ?>

<h2><?php echo u_htmlencode($page_title) ?></h2>

<p>By registering yourself on this website you will gain access to member-only features on this website. Fill in all the required fields and
click <em>register</em>.</p>

<?php if (count($errors) > 0): ?>
<h4><strong>The following errors need to be corrected before you can register:</strong></h4>

<ul class="form-errors">
<?php foreach ($errors as $e) echo "\t", '<li>', $e, '</li>', "\n" ?>
</ul>
<?php endif ?>

<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('user/register') ?>">
	<div>
		<?php echo xsrf::form_html(), "\n" ?>
	</div>
	<p>
		<label<?php if (isset($errors['username'])) echo ' class="error-field"' ?>>
			<strong>Username <span>(required)</span></strong>
			<input type="text" name="form[username]" maxlength="20"<?php echo ' value="'.u_htmlencode($values['username']).'"' ?> />
		</label>
	</p>
	<p class="description">A unique (nick)name, which is used to identify you. A username can be between 2 and 20 characters long.
	                       After the registration the username can not be changed. Only if you have a really good reason.</p>
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
	<p class="description">Passwords can be between 6 and 40 characters long and are case sensitive. It is recommended that you choose a
	                       combination of various characters for your password.</p>
	<p class="multiple-fields">
		<label<?php if (isset($errors['email'])) echo ' class="error-field"' ?>>
			<strong>E-mail address <span>(required)</span></strong>
			<input type="text" name="form[email]" maxlength="40"<?php echo ' value="'.u_htmlencode($values['email']).'"' ?> />
		</label>
		<label class="confirm-field<?php if (isset($errors['email'])) echo ' error-field' ?>">
			<strong>Confirm e-mail address <span>(required)</span></strong>
			<input type="text" name="form[confirm_email]" maxlength="255" />
		</label>
	</p>
	<p class="description">Your e-mail address will not be visible to members or visitors, except administrators and moderators.
	                       Use your active e-mail address, because you will receive an activation mail.</p>
	<p class="buttons"><input type="submit" value="Register" name="form_register" /> or <a href="<?php echo SYSTEM_BASE_URL ?>">cancel</a>.</p>
</form>

<?php require 'footer.php' ?>
