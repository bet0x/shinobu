<?php

# =============================================================================
# application/controllers/admin/groups/edit.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

# TODO:
# - List of groupmembers.
# - Store permissions temporarly on form submit.

class edit_controller extends CmsWebController
{
	private $_group_data = null, $_group_permissions = array();

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
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);

		// Get usergroup data
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT g.*, p.permissions FROM '.DB_PREFIX.'usergroups AS g, '.
			DB_PREFIX.'acl_groups AS p WHERE id='.$this->request['args'].' AND p.group_id=g.id LIMIT 1')
			or error($this->db->error);

		$this->_group_data = $result->fetch_assoc();
		if (is_null($this->_group_data))
			return $this->send_error(404);

		// Get permissions
		$result = $this->db->query('SELECT a.* FROM '.DB_PREFIX.'acl AS a')
			or error($this->db->error);

		while ($row = $result->fetch_assoc())
		{
			foreach ($this->permission_list as $p => $b)
			{
				if ($row[$p])
					$this->_group_permissions[] = array(
						'acl_id' =>$row['id'],
						'name' => $p,
						'check' => $this->_group_data['permissions'] & $b,
						'desc' => $row[$p]);
			}
		}
	}

	public function GET($args)
	{
		return tpl::render('admin_edit_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$this->_group_data['name'],
			'subsection' => 'groups',
			'admin_perms' => $this->user->get_acl('administration'),
			'values' => $this->_group_data,
			'errors' => array(),
			'permissions' => $this->_group_permissions
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_edit_group']))
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

		if (empty($errors))
		{
			$this->db->query('UPDATE '.DB_PREFIX.'usergroups SET
				name="'.$this->db->escape($args['form']['name']).'",
				user_title="'.$this->db->escape($args['form']['user_title']).'",
				description="'.$this->db->escape($args['form']['description']).'"
				WHERE id='.$this->request['args'])
				or error($this->db->error);

			// Store permissions
			if (isset($args['acl']))
			{
				$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'acl_groups SET permissions=? WHERE acl_id=? AND group_id=?')
					or error($this->db->error);

				foreach ($args['acl'] as $acl_id => $acl)
				{
					$tmp = 0;

					foreach ($this->permission_list as $p => $b)
						if (isset($args['acl'][$acl_id][$p]))
							$tmp |= $b;

					$stmt->bind_param('isi', $tmp, $acl_id, $this->request['args']);
					$stmt->execute();
				}

				$stmt->close();
			}
			else
				$this->db->query('UPDATE '.DB_PREFIX.'acl_groups SET permissions=0 WHERE group_id='.$this->request['args'])
					or error($this->db->error);

			// Redirect
			return tpl::render('redirect', array(
				'redirect_message' => '<p>All the group settings have been successfully updated. You will be redirected to the '.
				                      'group overview in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/groups')
				));
		}

		return tpl::render('admin_edit_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$this->_group_data['name'],
			'subsection' => 'groups',
			'admin_perms' => $this->user->get_acl('administration'),
			'values' => $this->_group_data,
			'errors' => $errors,
			'permissions' => $this->_group_permissions
			));
	}
}
