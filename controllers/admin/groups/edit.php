<?php

# =============================================================================
# controllers/admin/groups/edit.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

# TODO:
# - ACL permissions checklist.
# - List of groupmembers.
# - Show error when group doesn't exist.

class edit_controller extends AuthWebController
{
	private $_group_data = null;

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);

		$result = $this->db->query('SELECT id, name, user_title, description FROM '.DB_PREFIX.'usergroups WHERE id='.
			$this->request['args'].' LIMIT 1') or error($this->db->error(), __FILE__, __LINE__);

		$this->_group_data = $this->db->fetch_assoc($result);
		//if (is_null($this->_group_data))
		//	$this->send_error(404);
	}

	public function GET($args)
	{
		return tpl::render('admin_group_edit', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$this->_group_data['name'],
			'subsection' => 'groups',
			'admin_perms' => $this->acl->get('administration'),
			'values' => $this->_group_data,
			'errors' => array()
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_group_edit']))
			$this->redirect(utils::url('admin/groups'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
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

		if (count($errors) === 0)
		{
			$this->db->query('UPDATE '.DB_PREFIX.'usergroups SET '.
				'name="'.$this->db->escape($args['form']['name']).'", '.
				'user_title="'.$this->db->escape($args['form']['user_title']).'", '.
				'description="'.$this->db->escape($args['form']['description']).'" '.
				'WHERE id='.intval($this->request['args']))
				or error($this->db->error(), __FILE__, __LINE__);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>All the group settings have been successfully updated. You will be redirected to the '.
				                      'previous page in 2 seconds where you can log in.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/groups')
				));
		}

		return tpl::render('admin_group_edit', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$group_data['name'],
			'subsection' => 'groups',
			'admin_perms' => $this->acl->get('administration'),
			'values' => $this->_group_data,
			'errors' => $errors
			));
	}
}
