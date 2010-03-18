<?php

# =============================================================================
# controllers/admin/groups/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends AuthWebController
{
	private $_group_data = null, $_group_permissions = array(), $acl_ids = array();

	// Permission id => permission byte
	private $permission_list = array(
		'permission_01' => ACL_PERM_1,
		'permission_02' => ACL_PERM_2,
		'permission_03' => ACL_PERM_3,
		'permission_04' => ACL_PERM_4,
		'permission_05' => ACL_PERM_5,
		'permission_06' => ACL_PERM_6,
		'permission_07' => ACL_PERM_7,
		'permission_08' => ACL_PERM_8);

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);

		// Get permissions
		$result = $this->db->query('SELECT a.* FROM '.DB_PREFIX.'acl AS a')
			or error($this->db->error, __FILE__, __LINE__);

		while ($row = $result->fetch_assoc())
		{
			$this->acl_ids[] = $row['id'];

			foreach ($this->permission_list as $p => $b)
			{
				if ($row[$p])
				{
					$this->_group_permissions[] = array(
						'acl_id' => $row['id'],
						'name' => $p,
						'desc' => $row[$p]);
				}
			}
		}
	}

	public function GET($args)
	{
		return tpl::render('admin_add_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new group',
			'subsection' => 'groups',
			'admin_perms' => $this->acl->get('administration'),
			'values' => array(
				'name' => '',
				'user_title' => '',
				'description' => ''),
			'errors' => array(),
			'permissions' => $this->_group_permissions
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_add_group']))
			$this->redirect(url('admin/groups'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check group name
		if (strlen($args['form']['name']) < 1)
			$errors['name'] = 'The name must at least be 1 characters long. Please choose another (longer) name.';
		elseif (strlen($args['form']['name']) > 20)
			$errors['name'] = 'The name must not be more than 20 characters long. Please choose another (shorter) name.';

		// Check user title
		if (strlen($args['form']['user_title']) > 20)
			$errors['user_title'] = 'The user title must not be more than 20 characters long. Please choose another (shorter) title.';

		// Check description
		if (strlen($args['form']['description']) > 255)
			$errors['description'] = 'The description must not be more than 255 characters long. Please choose another (shorter) description.';

		// If no checkbox is checked, ACL won't be set
		if (!isset($args['acl']))
			$args['acl'] = array();

		if (count($errors) === 0)
		{
			// Create usergroup
			$this->db->query('INSERT INTO '.DB_PREFIX.'usergroups (name, description) VALUES('.
				'"'.$this->db->escape($args['form']['name']).'", '.
				'"'.$this->db->escape($args['form']['description']).'")')
				or error($this->db->error, __FILE__, __LINE__);
			$group_id = intval($this->db->insert_id);

			// Create and store permissions
			$stmt = $this->db->prepare('INSERT INTO '.DB_PREFIX.'acl_groups (acl_id, group_id, permissions) VALUES(?, ?, ?)')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($this->acl_ids as $acl_id)
			{
				$permissions = 0;

				foreach ($this->permission_list as $permission => $byte)
					if (isset($args['acl'][$acl_id][$permission]))
						$permissions |= $byte;

				$stmt->bind_param('ssi', $acl_id, $group_id, $permissions);
				$stmt->execute();
			}

			$stmt->close();

			// Redirect
			return tpl::render('redirect', array(
				'redirect_message' => '<p>The new group have been successfully added. You will be redirected to the '.
				                      'group overview in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/groups')
				));
		}

		return tpl::render('admin_add_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new group',
			'subsection' => 'groups',
			'admin_perms' => $this->acl->get('administration'),
			'values' => array(
				'name' => $args['form']['name'],
				'user_title' => $args['form']['user_title'],
				'description' => $args['form']['description']),
			'errors' => $errors,
			'permissions' => $this->_group_permissions
			));
	}
}
