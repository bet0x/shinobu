<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Menu</h2>

	<?php if (isset($m_items[0])): ?>
	<p>New menu items can be added with the <em>add a new item</em> button. Multiple itemsx can be deleted at the same time by checking the
	   checkboxes and clicking the <em>delete</em> button, if there are any items.</p>

	<p>The URLs or paths, which are between the parentheses, that start with <strong>http/https/ftp/irc</strong> or with a
	   <strong>slash</strong> (/) are external paths anything else will be transformed intro something like
	   <strong>http://example.com/?q=user</strong> (an internal path).</p>

	<?php echo $pagination ?>

	<form accept-charset="utf-8" method="post" action="<?php echo url('admin/menu/batch') ?>">
		<div>
			<?php echo xsrf::form_html(), "\n" ?>
		</div>
		<div class="record-list">
			<ul>
			<?php foreach ($m_items as $index => $item): ?>
				<li>
					<div class="list-row row-<?php echo $index % 2 ? 'odd' : 'even' ?>">
						<input id ="ch-<?php echo $item['id'] ?>" type="checkbox" name="m_items[]" value="<?php echo $item['id'] ?>" />
						<label for="ch-<?php echo $item['id'] ?>"><strong><?php echo u_htmlencode($item['name']) ?></strong></label> (<?php echo u_htmlencode($item['path']) ?>)
						<span class="actions">
							<a class="edit-icon" href="<?php echo url('admin/menu/edit:'.$item['id']) ?>">Edit</a>
							<a class="delete-icon" href="<?php echo url('admin/menu/delete:'.$item['id']), '&amp;', xsrf::token() ?>">Delete</a>
						</span>
					</div>
				</li>
			<?php endforeach ?>
			</ul>
		</div>

		<p class="align-right">
			<input class="inline-button" type="submit" value="Delete" name="form_delete_selected_m_items" /> all selected items or
			<a class="inline-button" href="<?php echo url('admin/menu/add') ?>">add a new item</a>.
		</p>
	</form>
	<?php else: ?>
	<p>The menu currently contains no items. <a class="inline-button" href="<?php echo url('admin/menu/add') ?>">Add a new item</a>.</p>
	<?php endif ?>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
