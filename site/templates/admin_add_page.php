<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Add new page</h2>

	<p>Info</p>

	<?php if (count($errors) > 0): ?>
	<h4><strong>The following errors need to be corrected before a new page can be added:</strong></h4>

	<ul class="form-errors">
	<?php foreach ($errors as $e) echo "\t", '<li>', $e, '</li>', "\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/pages/add') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<p>
			<label<?php if (isset($errors['title'])) echo ' class="error-field"' ?>>
				<strong>Title <span>(required)</span></strong>
				<input type="text" name="form[title]" maxlength="255"<?php echo ' value="'.u_htmlencode($values['title']).'"' ?> />
			</label>
		</p>
		<p class="description">The page title</p>

		<p>
			<label<?php if (isset($errors['content'])) echo ' class="error-field"' ?>>
				<strong>Content <span>(required)</span></strong>
				<textarea name="form[content]" rows="15" cols="40"><?php echo u_htmlencode($values['content']) ?></textarea>
			</label>
		</p>
		<p class="description"><a href="http://daringfireball.net/projects/markdown/syntax">Markdown</a> can be used to format the content of the
		                       page and also <a href="http://michelf.com/projects/php-markdown/extra/">Markdown Extra</a>. HTML is also allowed.</p>

		<p class="non-text-fields">
			<strong>Options</strong>
			<label><input type="checkbox" name="form[is_published]" value="1"<?php echo $values['is_published'] == 1 ? ' checked="checked"' : '' ?> />
			       Publish page, so it's visible to users.</label>
			<label><input type="checkbox" name="form[is_private]" value="1"<?php echo $values['is_private'] == 1 ? ' checked="checked"' : '' ?> />
			       Only grant access to this page for registered users.</label>
			<label><input type="checkbox" name="form[show_meta]" value="1"<?php echo $values['show_meta'] == 1 ? ' checked="checked"' : '' ?> />
			       Show information about the author and publication/edit date.</label>
		</p>

		<p class="buttons"><input type="submit" value="Add" name="form_add_page" /> or <a href="<?php echo url('admin/pages') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
