<?php

/* ---

	Copyright (C) 2008 Frank Smit
	http://code.google.com/p/shinobu/

	This file is part of Shinobu.

	Shinobu is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	Shinobu is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with Shinobu. If not, see <http://www.gnu.org/licenses/>.

--- */

(!defined('SHINOBU_ADMIN')) ? exit : NULL;

// Get the server load averages (if possible) (From FLuxBB 1.2.*)
if (@file_exists('/proc/loadavg') && is_readable('/proc/loadavg'))
{
	// We use @ just in case
	$fh = @fopen('/proc/loadavg', 'r');
	$load_averages = @fread($fh, 64);
	@fclose($fh);

	$load_averages = @explode(' ', $load_averages);
	$server_load = isset($load_averages[2]) ? $load_averages[0].' '.$load_averages[1].' '.$load_averages[2] : 'Not available';
}
else if (!in_array(PHP_OS, array('WINNT', 'WIN32')) && preg_match('/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/i', @exec('uptime'), $load_averages))
	$server_load = $load_averages[1].' '.$load_averages[2].' '.$load_averages[3];
else
	$server_load = 'Not available';

// Get number of online users
if ($sys_config['user_online_stats'] === 1)
{
	$guests = $users = 0;
	$online_users = NULL;

	$result = $sys_db->query('SELECT u.id FROM '.DB_PREFIX.'online AS u') or error($sys_db->error(), __FILE__, __LINE__);
	while($row = $sys_db->fetch_assoc($result))
	{
		if ($row['id'] == GUEST_UID)
			$guests++;
		else
			$users++;
	}

	$online_users = ($sys_config['user_online_stats'] == 1) ? ' ('.$users.' users and '.$guests.' guests online)' : NULL;
}

$result = $sys_db->query('SELECT VERSION()') or error($sys_db->error(), __FILE__, __LINE__);
$db_version = $sys_db->fetch_row($result);
$db_version = $db_version[0];

// Collect some additional info about MySQL (From FLuxBB 1.2.*)
if ($db_type == 'mysql' || $db_type == 'mysqli')
{
	$db_version = 'MySQL '.$db_version;

	// Calculate total db size/row count
	$result = $sys_db->query('SHOW TABLE STATUS FROM `'.$db_name.'`') or error($sys_db->error(), __FILE__, __LINE__);

	$total_records = $total_size = 0;
	while ($status = $sys_db->fetch_assoc($result))
	{
		$total_records += $status['Rows'];
		$total_size += $status['Data_length'] + $status['Index_length'];
	}

	$total_size = file_size($total_size);
}

// See if MMCache or PHPA is loaded (From FLuxBB 1.2.*)
if (function_exists('mmcache'))
	$php_accelerator = '<a href="http://turck-mmcache.sourceforge.net/">Turck MMCache</a>';
else if (isset($_PHPA))
	$php_accelerator = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
else if (extension_loaded('apc'))
	$php_accelerator = '<a href="http://nl2.php.net/apc">APC</a>';
else
	$php_accelerator = 'N/A';

// Set page title
$sys_tpl->assign('page_title', 'Home - '.$sys_config['website_title'].' Admin');

?>

<h2>Administration Panel</h2>

<p>Admin start page.</p>

<h3>Statistics</h3>

<dl class="col-2">
	<dt>Version</dt>
	<dd><?php echo SHINOBU_VERSION; ?> - <a href="http://code.google.com/p/shinobu/" rel="external">Check for newer versions</a></dd>

	<dt>Server load</dt>
	<dd><?php echo $server_load.$online_users; ?></dd>

	<dt>Environment</dt>
	<dd>
		Operating system: <?php echo PHP_OS; ?><br />
		Web server: <?php echo utf8_htmlencode($_SERVER['SERVER_SOFTWARE']); ?><br />
		PHP: <?php echo phpversion(); ?><br />
		Accelerator/Cache: <?php echo $php_accelerator."\n"; ?>
	</dd>

	<dt>Database</dt>
	<dd>
		<?php echo $db_version."\n"; ?>
		<?php if (isset($total_records) && isset($total_size)): ?>
			<br />Rows: <?php echo $total_records."\n"; ?>
			<br />Size: <?php echo $total_size."\n"; ?>
		<?php endif; ?>
	</dd>
</dl>
