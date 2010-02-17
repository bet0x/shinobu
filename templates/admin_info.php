<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Information</h2>

	<?php echo $page_body ?>

	<dl class="info-list">
		<dt>Shinobu</dt>
		<dd><?php echo SHINOBU ?></dd>
	</dl>
	<hr />
	<dl class="info-list">
		<dt>Uptime</dt>
		<dd><?php echo $sys_info['uptime'] ?></dd>

		<dt>Users</dt>
		<dd><?php echo $sys_info['users'] ?></dd>

		<dt>Load averages</dt>
		<dd><?php echo $sys_info['loadavg'] ?></dd>
	</dl>
	<hr />
	<dl class="info-list">
		<dt>Operating System</dt>
		<dd><?php echo $sys_info['os'] ?></dd>

		<dt>Webserver</dt>
		<dd><?php echo u_htmlencode($sys_info['webserver']) ?></dd>

		<dt>PHP</dt>
		<dd><?php echo PHP_VERSION ?></dd>
		<dd>Accelerator/Cache: <?php echo $sys_info['php_accelerator'] ?></dd>

		<dt>Database</dt>
		<dd>Type: <?php echo $sys_info['db']['name'] ?></dd>
		<dd>Version: <?php echo $sys_info['db']['version'] ?></dd>
		<dd>Rows: <?php echo $sys_info['db_records'] ?></dd>
		<dd>Size: <?php echo file_size($sys_info['db_size']) ?></dd>
	</dl>
</div>

<div class="clear">&nbsp;</div>

<?php require 'footer.php' ?>
