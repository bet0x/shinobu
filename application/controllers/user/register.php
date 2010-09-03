<?php

# =============================================================================
# application/controllers/user/register.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class register_controller extends CmsWebController
{
	public function prepare()
	{
		if ($this->user->authenticated)
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		if (!$this->config->allow_new_registrations)
			return tpl::render('basic', array(
				'page_title' => 'Register',
				'page_body' => '<p>New registrations are currently disabled.</p>',
				));

		return tpl::render('user_register', array(
			'page_title' => 'Register',
			'errors' => array(),
			'values' => array(
				'username' => '',
				'email' => '')
			));
	}

	public function POST($args)
	{
		if (!$this->config->allow_new_registrations || !isset($args['form_register'])
		    || !isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('user/register'));

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check username length and availability
		if (utf8_strlen($args['form']['username']) < 3)
			$errors['username'] = 'Usernames must be at least 2 characters long. Please choose another (longer) username.';
		elseif (utf8_strlen($args['form']['username']) > 20)
			$errors['username'] = 'Usernames must not be more than 20 characters long. Please choose another (shorter) username.';
		else
		{
			// Check that the username (or a too similar username) is not already registered
			$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'users
				WHERE UPPER(username)=UPPER("'.$this->db->escape($args['form']['username']).'")
				OR UPPER(username)=UPPER("'.$this->db->escape(preg_replace('/[^\w]/', '', $args['form']['username'])).'") LIMIT 1');

			if ($result->num_rows === 1)
				$errors['username'] = 'Someone is already registered with the username '.u_htmlencode($args['form']['username']).'. '.
									  'The username you entered is too similar. The username must differ from that by at least one '.
									  'alphanumerical character (a-z or 0-9). Please choose a different username.';
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
				$this->db->escape($args['form']['email']).'" LIMIT 1');

			if ($result->num_rows === 1)
				$errors['email'] = 'Someone else is already registered with that email address. Please choose another email address.';
		}

		if (empty($errors))
		{
			$this->user->add(
				$args['form']['username'],
				$this->config->default_usergroup,
				$args['form']['password'],
				$args['form']['email']);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>You have been successfully registered. You will be redirected to the login page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('user/login')
				));
		}

		$args['form']['password'] = $args['form']['confirm_password'] = '';

		return tpl::render('user_register', array(
			'page_title' => 'Register',
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
