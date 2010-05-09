<?php require 'header.php' ?>

<h2><?php echo u_htmlencode($page_title) ?></h2>

<?php echo $page_data['content'] ?>

<?php if ($page_data['show_meta'] == '1'): ?>
<div class="metadata">
	<span>Written on <?php echo $page_data['pub_date'] ?>.
	<?php echo $page_data['edit_date'] ? 'Last edited on '.$page_data['edit_date'].'.' : '' ?></span>
	<span><a href="#header">Top</a></span>
</div>
<?php endif ?>

<?php require 'footer.php' ?>
