<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Pages</h2>

	<?php if (isset($pages[0])): ?>
	<p>Info.</p>

	<?php echo $pagination ?>

	<form accept-charset="utf-8" method="post" action="<?php echo url('admin/pages/batch') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<ul class="user-list">
		<?php foreach ($pages as $index => $page): ?>
			<li class="row-<?php echo $index % 2 ? 'odd' : 'even', $page['is_published'] == '0' ? ' marked-row' : '' ?>">
				<div class="checkbox"><input id ="ch-<?php echo $page['id'] ?>" type="checkbox" name="pages[]" value="<?php echo $page['id'] ?>" /></div>
				<div class="name"><label for="ch-<?php echo $page['id'] ?>"><strong><?php echo u_htmlencode($page['title']) ?></strong></label> (<?php echo u_htmlencode($page['author']) ?>)</div>
				<div class="actions">
					<a class="tiny-button" href="<?php echo url('admin/pages/edit:'.$page['id']) ?>" title="Edit">/</a>
					<a class="tiny-button" href="<?php echo url('admin/pages/delete:'.$page['id']), '&amp;', xsrf::token() ?>" title="Delete">X</a>
				</div>
			</li>
		<?php endforeach ?>
		</ul>

		<p class="align-right">
			<input class="inline-button" type="submit" value="Delete" name="form_delete_selected_pages" />,
			<input class="inline-button" type="submit" value="publish" name="form_publish_selected_pages" /> or
			<input class="inline-button" type="submit" value="unpublish" name="form_unpublish_selected_pages" />
			all selected pages or
			<a class="inline-button" href="<?php echo url('admin/pages/add') ?>">add a new page</a>.
		</p>
	</form>
	<?php else: ?>
	<p>There are no pages. <a class="inline-button" href="<?php echo url('admin/pages/add') ?>">Add a new page</a>.</p>
	<?php endif ?>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
