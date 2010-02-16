<?php

# =============================================================================
# controllers/user/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class default_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->module->user->authenticated())
			$this->redirect(utils::url('user/login'));
	}

	public function GET($args)
	{
		return tpl::render('user_profile', array(
			'page_title' => 'Profile',
			'errors' => array(),
			'values' => $this->module->user->data('username', 'email')
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_profile']))
			$this->redirect(utils::url('user/register'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = $values = array();
		$new_password = $new_email = false;

		// Check password
		if (!empty($args['form']['changed_password']) && !empty($args['form']['confirm_changed_password']))
		{
			if (strlen($args['form']['changed_password']) < 6)
				$errors['password'] = 'Passwords must be at least 6 characters long. Please choose another (longer) password.';
			elseif (strlen($args['form']['changed_password']) > 40)
				$errors['password'] = 'Usernames must not be more than 40 characters long. Please choose another (shorter) password.';
			elseif ($args['form']['changed_password'] != $args['form']['confirm_changed_password'])
				$errors['password'] = 'Passwords do not match.';
			else
			{
				$cur_auth = $this->module->user->data('password', 'salt');

				if ($cur_auth['password'] == generate_hash($args['form']['changed_password'], $cur_auth['salt']))
					$errors['password'] = 'The given password is the same as the old password.';
			}

			if (!isset($errors['password']))
				$new_password = true;
		}

		// Check e-mail address
		if ($args['form']['email'] != $this->module->user->data('email'))
		{
			if (!filter_var($args['form']['email'], FILTER_VALIDATE_EMAIL))
				$errors['email'] = 'You have entered an invalid e-mail address.';
			else
			{
				$result = $this->module->db->query('SELECT id FROM '.DB_PREFIX.'users WHERE email="'.$this->module->db->escape($args['form']['email']).'" LIMIT 1')
					or error('Unable to fetch user info', __FILE__, __LINE__);

				if ($this->module->db->num_rows($result) === 1)
					$errors['email'] = 'Someone else is already registered with that email address. Please choose another email address.';
			}

			if (!isset($errors['email']))
				$new_email = true;
		}

		if (count($errors) === 0)
		{

			if ($new_password)
			{
				$args['form']['password'] = $args['form']['changed_password'];
				unset($args['form']['changed_password'], $args['form']['confirm_changed_password']);
			}
			else
				unset($args['form']['changed_password'], $args['form']['confirm_changed_password']);

			if (!$new_email)
				unset($args['form']['email']);

			$this->module->user->update($this->module->user->data('id'), $args['form']);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>Your profile has been updated.'.
				                      ' You will be redirected to the homepage in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('user', true)
				));
		}

		return tpl::render('user_profile', array(
			'page_title' => 'Profile',
			'errors' => $errors,
			'values' => $this->module->user->data('username', 'email')
			));
	}
}
