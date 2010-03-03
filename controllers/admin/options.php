<?php

# =============================================================================
# controllers/admin/default.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class options_controller extends AuthWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->get('administration', ACL_PERM_5))
			$this->redirect(SYSTEM_BASE_URL);
	}

	private function _get_usergroups()
	{
		$usergroups = array();
		$result = $this->db->query('SELECT id, name FROM '.DB_PREFIX.'usergroups')
			or error('Unable to fetch usergroups.', __FILE__, __LINE__);

		if ($this->db->num_rows($result) > 0)
		{
			while ($row = $this->db->fetch_assoc($result))
				$usergroups[$row['id']] = $row['name'];
		}

		return $usergroups;
	}

	public function GET($args)
	{
		return tpl::render('admin_options', array(
			'website_section' => 'Administration',
			'page_title' => 'Options',
			'subsection' => 'options',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $this->_get_usergroups(),
			'values' => array(
				'website_title' => $this->config->website_title,
				'allow_new_registrations' => $this->config->allow_new_registrations,
				'default_usergroup' => $this->config->default_usergroup),
			'errors' => array(),
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_options']))
			$this->redirect(utils::url('admin/options'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$usergroups = $this->_get_usergroups();
		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check website title
		if (strlen($args['form']['website_title']) < 1)
			$errors['website_title'] = 'The website title must at least be 1 characters long. Please choose another (longer) title.';
		elseif (strlen($args['form']['website_title']) > 50)
			$errors['website_title'] = 'The website title must not be more than 50 characters long. Please choose another (shorter) title.';

		// Check `allow_new_registrations`
		$args['form']['allow_new_registrations'] = $args['form']['allow_new_registrations'] == '1' ? 1 : 0;

		// Check default usergroup
		if (!isset($usergroups[intval($args['form']['default_usergroup'])]))
			$errors['default_usergroup'] = 'The chosen usergroup does not exists.';

		if (count($errors) === 0)
		{
			foreach ($args['form'] as $name => $value)
			{
				$this->db->query('UPDATE '.DB_PREFIX.'config SET value="'.$this->db->escape($value).
					'" WHERE name="'.$this->db->escape($name).'"')
					or error('Could not update the configuration.', __FILE__, __LINE__);
			}

			return tpl::render('redirect', array(
				'redirect_message' => '<p>All options have been successfully updated. You will be redirected to the '.
				                      'previous page in 2 seconds where you can log in.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/options')
				));
		}

		return tpl::render('admin_options', array(
			'website_section' => 'Administration',
			'page_title' => 'Options',
			'subsection' => 'options',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $usergroups,
			'values' => array(
				'website_title' => $this->config->website_title,
				'allow_new_registrations' => $this->config->allow_new_registrations,
				'default_usergroup' => $this->config->default_usergroup),
			'errors' => array(),
			));
	}
}
