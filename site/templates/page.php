<?php require 'header.php' ?>

<?php echo $page_data['content'] ?>

<div class="metadata">
	<span>Writen by <?php echo u_htmlencode($page_data['author']) ?> on <?php echo $page_data['pub_date'] ?>.
	<?php echo $page_data['edit_date'] ? 'Last edited on '.$page_data['edit_date'].'' : '' ?></span>
</div>

<?php require 'footer.php' ?>
