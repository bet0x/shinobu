<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class edit_controller extends AuthWebController
{
	private $_user_data = null, $_usergroups = array();

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_3))
			$this->redirect(SYSTEM_BASE_URL);

		// Get user information
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT u.* FROM '.DB_PREFIX.'users AS u WHERE id='.$this->request['args'])
			or error($this->db->error, __FILE__, __LINE__);

		$this->_user_data = $result->fetch_assoc();
		if (is_null($this->_user_data))
			return $this->send_error(404);

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
		return tpl::render('admin_edit_user', array(
			'website_section' => 'Administration',
			'page_title' => 'User: '.$this->_user_data['username'],
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration'),
			'errors' => array(),
			'values' => $this->_user_data,
			'usergroups' => $this->_usergroups
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_edit_user']))
			$this->redirect(utils::url('admin/users'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();
		$new_password = $new_email = false;

		// Check username length and availability
		if ($args['form']['username'] != $this->_user_data['username'])
		{
			if (strlen($args['form']['username']) < 3)
				$errors['username'] = 'Usernames must be at least 2 characters long. Please choose another (longer) username.';
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
		}

		// Check default usergroup
		if (!isset($this->_usergroups[intval($args['form']['group_id'])]))
		{
			$errors['group_id'] = 'The chosen usergroup does not exist.';
			$args['form']['group_id'] = 0;
		}

		// Check password
		if (!empty($args['form']['changed_password']) && !empty($args['form']['confirm_changed_password']))
		{
			if (strlen($args['form']['changed_password']) < 6)
				$errors['password'] = 'Passwords must be at least 6 characters long. Please choose another (longer) password.';
			elseif (strlen($args['form']['changed_password']) > 40)
				$errors['password'] = 'Passwords can not be more than 40 characters long. Please choose another (shorter) password.';
			elseif ($args['form']['changed_password'] != $args['form']['confirm_changed_password'])
				$errors['password'] = 'Passwords do not match.';
			else
			{
				if ($this->_user_data == generate_hash($args['form']['changed_password'], $this->_user_data['salt']))
					$errors['password'] = 'The given password is the same as the old password.';
			}

			if (!isset($errors['password']))
				$new_password = true;
		}

		// Check e-mail address
		if ($args['form']['email'] != $this->_user_data['email'])
		{
			if (!filter_var($args['form']['email'], FILTER_VALIDATE_EMAIL))
				$errors['email'] = 'You have entered an invalid e-mail address.';
			else
			{
				$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users WHERE email="'.
					$this->db->escape($args['form']['email']).'" LIMIT 1')
					or error($this->db->error, __FILE__, __LINE__);

				if ($result->num_rows === 1)
					$errors['email'] = 'Someone else is already registered with that email address. Please choose another email address.';
			}

			if (!isset($errors['email']))
				$new_email = true;
		}

		if (count($errors) === 0)
		{

			if ($new_password)
				$args['form']['password'] = $args['form']['changed_password'];

			unset($args['form']['changed_password'], $args['form']['confirm_changed_password']);

			if (!$new_email)
				unset($args['form']['email']);

			$this->user->update($this->_user_data['id'], $args['form']);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The user has been updated.'.
				                      ' You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/users')
				));
		}

		return tpl::render('admin_edit_user', array(
			'website_section' => 'Administration',
			'page_title' => 'User: '.$this->_user_data['username'],
			'subsection' => 'users',
			'admin_perms' => $this->acl->get('administration'),
			'errors' => $errors,
			'values' => $this->_user_data,
			'usergroups' => $this->_usergroups
			));
	}
}
