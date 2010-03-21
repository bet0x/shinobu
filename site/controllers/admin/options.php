<?php

# =============================================================================
# site/controllers/admin/options.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class options_controller extends CmsWebController
{
	private $_usergroups = array();

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_5))
			$this->redirect(SYSTEM_BASE_URL);

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
		return tpl::render('admin_options', array(
			'website_section' => 'Administration',
			'page_title' => 'Options',
			'subsection' => 'options',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $this->_usergroups,
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
			$this->redirect(url('admin/options'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

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
		if (!isset($this->_usergroups[intval($args['form']['default_usergroup'])]))
		{
			$errors['default_usergroup'] = 'The chosen usergroup does not exist.';
			$args['form']['default_usergroup'] = 0;
		}

		if (count($errors) === 0)
		{
			$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'config SET value=? WHERE name=?')
				or error($this->db->error, __FILE__, __LINE__);

			foreach ($args['form'] as $name => $value)
			{
				$stmt->bind_param('ss', $value, $name);
				$stmt->execute();
			}

			$stmt->close();

			return tpl::render('redirect', array(
				'redirect_message' => '<p>All options have been successfully updated. You will be redirected to the '.
				                      'previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/options')
				));
		}

		return tpl::render('admin_options', array(
			'website_section' => 'Administration',
			'page_title' => 'Options',
			'subsection' => 'options',
			'admin_perms' => $this->acl->get('administration'),
			'usergroups' => $this->_usergroups,
			'values' => array(
				'website_title' => $this->config->website_title,
				'allow_new_registrations' => $this->config->allow_new_registrations,
				'default_usergroup' => $this->config->default_usergroup),
			'errors' => array(),
			));
	}
}
