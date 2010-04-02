<?php require 'header.php' ?>

<h2><?php echo u_htmlencode($page_title) ?></h2>

<?php if ($error): ?>
<p>The username or password you have entered is not correct, please try again.</p>
<?php else: ?>
<p>Fill in both fields and click <em>Log in</em>.</p>
<?php endif ?>

<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('user/login') ?>">
	<div>
		<?php echo xsrf::form_html(), "\n" ?>
	</div>
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

<?php require 'footer.php' ?>
