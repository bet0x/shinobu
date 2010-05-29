<?php

# =============================================================================
# application/controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends CmsWebController
{
	public function GET($args)
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_1))
			$this->redirect(SYSTEM_BASE_URL);

		global $db_name;

		$software_versions = array(
			'Shinobu' => SHINOBU,
			'PHP-UTF8' => trim(file_get_contents(UTF8.'/VERSION')),
			'Markdown' => trim(file_get_contents(SYS_LIB.'/markdown/VERSION'))
			);

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

		// Check for the existence of various PHP opcode caches/optimizers
		if (function_exists('mmcache'))
			$sys_info['php_accelerator'] = '<a href="http://turck-mmcache.sourceforge.net">Turk MMCache</a>';
		else if (isset($_PHPA))
			$sys_info['php_accelerator'] = '<a href="http://www.php-accelerator.co.uk/">ionCube PHP Accelerator</a>';
		else if (ini_get('apc.enabled'))
			$sys_info['php_accelerator'] ='<a href="http://www.php.net/apc/">Alternative PHP Cache (APC)</a>';
		else if (ini_get('zend_optimizer.optimization_level'))
			$sys_info['php_accelerator'] = '<a href="http://www.zend.com/products/guard/zend-optimizer">Zend Optimizer</a>';
		else if (ini_get('eaccelerator.enable'))
			$sys_info['php_accelerator'] = '<a href="http://www.eaccelerator.net/">eAccelerator</a>';
		else if (ini_get('xcache.cacher'))
			$sys_info['php_accelerator'] = '<a href="http://xcache.lighttpd.net/">XCache</a>';
		else
			$sys_info['php_accelerator'] = 'N/A';

		// Calculate total database size/row count (only MySQLi for now)
		$result = $this->db->query('SHOW TABLE STATUS FROM `'.$db_name.'`')
			or error($this->db->error);

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
			if (!preg_match('%^\d{2}:\d{2}:\d{2} up (.+),  (\d+) users?,  load average: (.+)$%', trim(shell_exec('uptime')), $matches))
				error('Could not get uptime.');

			list (, $sys_info['uptime'], $sys_info['users'], ) = $matches;

			// Get kernel version and operating system
			$sys_info['os'] = trim(shell_exec('uname -r -o'));

			// Get loadavg
			$sys_info['loadavg'] = implode(' ', sys_getloadavg());
		}

		return tpl::render('admin_info', array(
			'website_section' => 'Administration',
			'page_title' => 'Information',
			'subsection' => 'information',
			'admin_perms' => $this->user->get_acl('administration'),
			'software_versions' => $software_versions,
			'sys_info' => $sys_info
			));
	}
}
