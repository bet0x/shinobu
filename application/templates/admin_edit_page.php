<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2><?php echo u_htmlencode($page_title) ?></h2>

	<p>Written on <?php echo $values['pub_date'] ?>.
	<?php echo $values['edit_date'] ? 'Last edited on '.$values['edit_date'].'' : '' ?>.</p>

	<?php if (count($errors) > 0): ?>
	<ul class="form-errors">
	<?php foreach ($errors as $e) echo "\t", '<li>', $e, '</li>', "\n" ?>
	</ul>
	<?php endif ?>

	<form accept-charset="utf-8" class="form-style-one" method="post" action="<?php echo url('admin/pages/edit:'.$values['id']) ?>">
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
				<textarea name="form[content]" rows="20" cols="40"><?php echo u_htmlencode($values['content']) ?></textarea>
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
			<label><input type="checkbox" name="form[show_toc]" value="1"<?php echo $values['show_toc'] == 1 ? ' checked="checked"' : '' ?> />
			       Show a table of contents at the top of the page.</label>
			<label><input type="checkbox" name="form[show_meta]" value="1"<?php echo $values['show_meta'] == 1 ? ' checked="checked"' : '' ?> />
			       Show information about the author and publication/edit date.</label>
		</p>

		<p class="buttons"><input type="submit" value="Update" name="form_edit_page" /> or <a href="<?php echo url('admin/pages') ?>">cancel</a>.</p>
	</form>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
