<?php require 'header.php' ?>
<?php require 'admin_navigation.php' ?>

<div id="admin-content">
	<h2>Information</h2>

	<p>This is the administration panel. From here you can manage the system, pages, menu, users, 'groups and permissions.
	   Below you can see some system statistics and software version information.</p>

	<dl class="infolist">
		<dt>Shinobu</dt>
		<dd><?php echo SHINOBU ?></dd>
	</dl>
	<hr />
	<dl class="infolist">
		<dt>Uptime</dt>
		<dd><?php echo $sys_info['uptime'] ?></dd>

		<dt>Users</dt>
		<dd><?php echo $sys_info['users'] ?></dd>

		<dt>Load averages</dt>
		<dd><?php echo $sys_info['loadavg'] ?></dd>
	</dl>
	<hr />
	<dl class="infolist">
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
