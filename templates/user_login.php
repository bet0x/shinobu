<?php require 'header.php' ?>

<?php if ($error): ?>
<p>The username or password you have entered is not correct, please try again. You can
   <a href="<?php tpl::url('user/requestpassword') ?>">request a new password</a> if you forgot your current password.</p>
<?php else: ?>
<p>You can <a href="<?php tpl::url('user/requestpassword') ?>">request a new password</a> if you forgot your current password.</p>
<?php endif ?>

<?php if (!user::$logged_in): ?>
<form  class="form-style-one" method="post" accept-charset="utf-8" action="<?php tpl::url('user/login') ?>">
	<p>
		<label<?php if ($error) echo ' class="error-field"' ?>>
			<strong>Username <span>(required)</span></strong>
			<input type="text" name="form[username]" maxlength="20" />
		</label>
	</p>
	<p>
		<label<?php if ($error) echo ' class="error-field"' ?>>
			<strong>Password <span>(required)</span></strong>
			<input type="password" name="form[password]" maxlength="40" />
		</label>
	</p>
	<p><input type="submit" value="Log in" name="form_login" /></p>
</form>
<?php endif ?>

<?php require 'footer.php' ?>
