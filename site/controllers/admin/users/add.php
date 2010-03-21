<?php

# =============================================================================
# site/controllers/admin/users/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends CmsWebController
{
	private $_usergroups = array();

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);

		// Get a list of groups
		$result = $this->db->query('SELECT id, name FROM '.DB_PREFIX.'usergroups')
			or error($this->db->error, __FILE__, __LINE__);

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$this->_usergroups[$row['id']] = $row['name'];
		}
	}

	public function GET($args)
	{
		return tpl::render('admin_add_user', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new user',
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $this->_usergroups,
			'errors' => array(),
			'values' => array(
				'username' => '',
				'group_id' => $this->config->default_usergroup,
				'email' => '')
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_add_user']))
			$this->redirect(url('admin/users'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check username length and availability
		if (strlen($args['form']['username']) < 3)
			$errors['username'] = 'Usernames must be at least 3 characters long. Please choose another (longer) username.';
		elseif (strlen($args['form']['username']) > 20)
			$errors['username'] = 'Usernames must not be more than 20 characters long. Please choose another (shorter) username.';
		else
		{
			// Check that the username (or a too similar username) is not already registered
			$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users
				WHERE UPPER(username)=UPPER("'.$this->db->escape($args['form']['username']).'")
				OR UPPER(username)=UPPER("'.$this->db->escape(preg_replace('/[^\w]/', '', $args['form']['username'])).'") LIMIT 1')
				or error($this->db->error, __FILE__, __LINE__);

			if ($result->num_rows === 1)
				$errors['username'] = 'Someone is already registered with the username '.u_htmlencode($args['form']['username']).'. '.
									  'The username you entered is too similar. The username must differ from that by at least one '.
									  'alphanumerical character (a-z or 0-9). Please choose a different username.';
		}

		// Check default usergroup
		$args['form']['group_id'] = intval($args['form']['group_id']);
		if (!isset($this->_usergroups[$args['form']['group_id']]))
		{
			$errors['group_id'] = 'The chosen usergroup does not exist.';
			$args['form']['group_id'] = 0;
		}

		// Check password
		if (strlen($args['form']['password']) < 6)
			$errors['password'] = 'Passwords must be at least 6 characters long. Please choose another (longer) password.';
		elseif (strlen($args['form']['password']) > 40)
			$errors['password'] = 'Passwords must not be more than 40 characters long. Please choose another (shorter) password.';
		elseif ($args['form']['password'] != $args['form']['confirm_password'])
			$errors['password'] = 'Passwords do not match.';

		// E-mail address
		if (!filter_var($args['form']['email'], FILTER_VALIDATE_EMAIL))
			$errors['email'] = 'You have entered an invalid e-mail address.';
		elseif ($args['form']['email'] != $args['form']['confirm_email'])
			$errors['email'] = 'E-mail addresses do not match.';
		else
		{
			$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users WHERE email="'.
				$this->db->escape($args['form']['email']).'" LIMIT 1')
				or error($this->db->error, __FILE__, __LINE__);

			if ($result->num_rows === 1)
				$errors['email'] = 'Someone else is already registered with that email address. Please choose another email address.';
		}

		if (count($errors) === 0)
		{
			$this->user->add(
				$args['form']['username'],
				$args['form']['group_id'],
				$args['form']['password'],
				$args['form']['email']);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The user has been successfully added. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/users')
				));
		}

		return tpl::render('admin_add_user', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new user',
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $this->_usergroups,
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
