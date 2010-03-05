<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_1))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		global $db_name;

		$sys_info = array(
			'webserver' => trim(array_shift(explode(' ', $_SERVER['SERVER_SOFTWARE']))),
			'db' => array(
				'name' => 'MySQLi',
				'version' => $this->db->server_info),
			'os' => 'Not available',
			'uptime' => 'Not available',
			'users' => 'Not available',
			'loadavg' => 'Not available'
			);

		// See if MMCache, PHPA or APC is loaded (From FLuxBB 1.2.*)
		if (function_exists('mmcache'))
			$sys_info['php_accelerator'] = '<a href="http://turck-mmcache.sourceforge.net/">Turck MMCache</a>';
		else if (isset($_PHPA))
			$sys_info['php_accelerator'] = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
		else if (extension_loaded('apc'))
			$sys_info['php_accelerator'] = '<a href="http://php.net/apc">APC</a>';
		else
			$sys_info['php_accelerator'] = 'N/A';

		// Calculate total database size/row count (only MySQLi for now)
		$result = $this->db->query('SHOW TABLE STATUS FROM `'.$db_name.'`')
			or error('Can not get STATUS from MySQLi.', __FILE__, __LINE__);

		$sys_info['db_records'] = $sys_info['db_size'] = 0;
		while ($status = $result->fetch_assoc())
		{
			$sys_info['db_records'] += $status['Rows'];
			$sys_info['db_size'] += $status['Data_length'] + $status['Index_length'];
		}

		// This part doesn't work in Windows
		if (!in_array(PHP_OS, array('WINNT', 'WIN32')))
		{
			// Get uptime, users and load average
			if (!preg_match('#^\d{2}:\d{2}:\d{2} up (.+),  (\d+) users?,  load average: (.+)$#', trim(shell_exec('uptime')), $matches))
				error('Could not get uptime.', __FILE__, __LINE__);

			list (, $sys_info['uptime'], $sys_info['users'], $sys_info['loadavg']) = $matches;

			// Get kernel version and operating system
			$sys_info['os'] = trim(shell_exec('uname -r -o'));
		}

		return tpl::render('admin_info', array(
			'website_section' => 'Administration',
			'page_title' => 'Information',
			'subsection' => 'information',
			'admin_perms' => $this->acl->get('administration'),

			'sys_info' => $sys_info
			));
	}
}
