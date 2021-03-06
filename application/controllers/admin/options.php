<?php

# =============================================================================
# application/controllers/admin/options.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class options_controller extends CmsWebController
{
	private $_pages = array(0 => 'None'), $_usergroups = array();

	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'options'))
			$this->redirect(SYSTEM_BASE_URL);

		// Fetch list of pages
		$result = $this->db->query('SELECT id, title FROM '.DB_PREFIX.'pages WHERE is_published=1 AND is_private=0');

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$this->_pages[$row['id']] = $row['title'];
		}

		// Fetch list of usergroups
		$result = $this->db->query('SELECT id, name FROM '.DB_PREFIX.'usergroups');

		if ($result->num_rows > 0)
		{
			while ($row = $result->fetch_assoc())
				$this->_usergroups[$row['id']] = $row['name'];
		}

		$this->load_timedate();
	}

	public function GET($args)
	{
		return tpl::render('admin_options', array(
			'website_section' => 'Administration',
			'page_title' => 'Options',
			'subsection' => 'options',
			'pages' => $this->_pages,
			'usergroups' => $this->_usergroups,
			'date_format_example' => $this->timedate->date(time()),
			'time_format_example' => $this->timedate->time(time()),
			'values' => array(
				'website_title' => $this->config->website_title,
				'allow_new_registrations' => $this->config->allow_new_registrations,
				'default_homepage' => $this->config->default_homepage,
				'default_usergroup' => $this->config->default_usergroup,
				'timezone' => $this->config->timezone,
				'date_format' => $this->config->date_format,
				'time_format' => $this->config->time_format),
			'errors' => array(),
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_options']) || !isset($args['xsrf_token'])
		    || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/options'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check website title
		if (utf8_strlen($args['form']['website_title']) < 1)
			$errors['website_title'] = 'The website title must at least be 1 character long. Please choose another (longer) title.';
		elseif (utf8_strlen($args['form']['website_title']) > 50)
			$errors['website_title'] = 'The website title must not be more than 50 characters long. Please choose another (shorter) title.';

		// Check default homepage
		if (!isset($this->_pages[intval($args['form']['default_homepage'])]))
		{
			$errors['default_homepage'] = 'The chosen (home)page does not exist.';
			$args['form']['default_homepage'] = 0;
		}

		// Check allow_new_registrations
		$args['form']['allow_new_registrations'] = $args['form']['allow_new_registrations'] == '1' ? 1 : 0;

		// Check default usergroup
		if (!isset($this->_usergroups[intval($args['form']['default_usergroup'])]))
		{
			$errors['default_usergroup'] = 'The chosen usergroup does not exist.';
			$args['form']['default_usergroup'] = 0;
		}

		// Check timezone
		if (!@date_default_timezone_set($args['form']['timezone']))
			$errors['timezone'] = 'The timezone must be valid. Please choose another timezone.';

		// Check date format
		if (strlen($args['form']['date_format']) < 1)
			$errors['date_format'] = 'The date format must at least be 1 character long. Please choose another (longer) format.';
		if (strlen($args['form']['date_format']) > 50)
			$errors['date_format'] = 'The date format must not be more than 50 characters long. Please choose another (shorter) format.';

		// Check time format
		if (strlen($args['form']['time_format']) < 1)
			$errors['time_format'] = 'The time format must at least be 1 character long. Please choose another (longer) format.';
		if (strlen($args['form']['time_format']) > 50)
			$errors['time_format'] = 'The time format must not be more than 50 characters long. Please choose another (shorter) format.';

		if (empty($errors))
		{
			$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'config SET value=? WHERE name=?');

			foreach ($args['form'] as $name => $value)
			{
				$stmt->bind_param('ss', $value, $name);
				$stmt->execute();
			}

			$stmt->close();

			cache::clear('config.json');

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
			'pages' => $this->_pages,
			'usergroups' => $this->_usergroups,
			'date_format_example' => $this->timedate->date(time()),
			'time_format_example' => $this->timedate->time(time()),
			'values' => $args['form'],
			'errors' => $errors,
			));
	}
}
