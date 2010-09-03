<?php

# =============================================================================
# application/controllers/admin/groups/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends CmsWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'groups'))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_add_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new group',
			'subsection' => 'groups',
			'values' => array(
				'name' => '',
				'user_title' => '',
				'description' => ''),
			'errors' => array()
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_add_group']) || !isset($args['xsrf_token'])
		    || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/groups'));

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check group name
		if (utf8_strlen($args['form']['name']) < 1)
			$errors['name'] = 'The name must at least be 1 characters long. Please choose another (longer) name.';
		elseif (utf8_strlen($args['form']['name']) > 20)
			$errors['name'] = 'The name must not be more than 20 characters long. Please choose another (shorter) name.';

		// Check user title
		if (utf8_strlen($args['form']['user_title']) > 20)
			$errors['user_title'] = 'The user title must not be more than 20 characters long. Please choose another (shorter) title.';

		// Check description
		if (utf8_strlen($args['form']['description']) > 255)
			$errors['description'] = 'The description must not be more than 255 characters long. Please choose another (shorter) description.';

		if (empty($errors))
		{
			// Create usergroup
			$this->db->query('INSERT INTO '.DB_PREFIX.'usergroups (name, description) VALUES('.
				'"'.$this->db->escape($args['form']['name']).'", '.
				'"'.$this->db->escape($args['form']['description']).'")');
			$group_id = intval($this->db->insert_id);

			// Create and store permissions
			$stmt = $this->db->prepare('INSERT INTO '.DB_PREFIX.'permissions (set_id, group_id, bits) VALUES(?, ?, ?)');

			foreach (_permission_struct::$sets as $set_id => $set)
			{
				$bits = 0;
				foreach ($set as $perm_id => $bit)
				{
					if (isset($args['perm'][$set_id][$perm_id]))
						$bits |= $bit;
				}

				$stmt->bind_param('sii', $set_id, $group_id, $bits);
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
			'values' => array(
				'name' => $args['form']['name'],
				'user_title' => $args['form']['user_title'],
				'description' => $args['form']['description']),
			'errors' => $errors
			));
	}
}
