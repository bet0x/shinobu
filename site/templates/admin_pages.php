<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Pages</h2>

	<?php if (isset($pages[0])): ?>
	<?php echo $pagination ?>

	<form accept-charset="utf-8" method="post" action="<?php echo url('admin/pages/batch') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<div class="nested-list">
			<?php echo $page_list_html ?>
		</div>

		<p class="align-right">
			<input class="inline-button" type="submit" value="Publish" name="form_publish_selected_pages" /> or
			<input class="inline-button" type="submit" value="unpublish" name="form_unpublish_selected_pages" />
			all selected pages or
			<a class="inline-button" href="<?php echo url('admin/pages/add:'.$last_page_right) ?>">add a new page</a>.
		</p>
	</form>
	<?php else: ?>
	<p>There are no pages. <a class="inline-button" href="<?php echo url('admin/pages/add') ?>">Add a new page</a>.</p>
	<?php endif ?>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
