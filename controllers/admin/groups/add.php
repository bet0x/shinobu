<?php

# =============================================================================
# controllers/admin/groups/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends AuthWebController
{
	private $_group_data = null;

	public function prepare()
	{
		if (!$this->user->authenticated() || !$this->acl->check('administration', ACL_PERM_6))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_add_group']))
			$this->redirect(utils::url('admin/groups'));

		if (!isset($args['xsrf_token']) || !utils::check_xsrf_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check group name
		if (strlen($args['form']['name']) < 1)
			$errors['name'] = 'The name must at least be 1 character long. Please choose another (longer) name.';
		elseif (strlen($args['form']['name']) > 20)
			$errors['name'] = 'The name must not be more than 20 characters long. Please choose another (shorter) name.';

		// Check description
		if (strlen($args['form']['description']) > 255)
			$errors['description'] = 'The description must not be more than 255 characters long. Please choose another (shorter) description.';

		if (count($errors) === 0)
		{
			// Create usergroup
			$this->db->query('INSERT INTO '.DB_PREFIX.'usergroups (name, description) VALUES('.
				'"'.$this->db->escape($args['form']['name']).'", '.
				'"'.$this->db->escape($args['form']['description']).'")')
				or error($this->db->error, __FILE__, __LINE__);
			$group_id = intval($this->db->insert_id);

			// Create ACL groups
			$result = $this->db->query('SELECT id FROM '.DB_PREFIX.'acl')
				or error($this->db->error, __FILE__, __LINE__);

			$stmt = $this->db->prepare('INSERT INTO '.DB_PREFIX.'acl_groups (acl_id, group_id) VALUES(?, ?)')
				or error($this->db->error, __FILE__, __LINE__);

			while ($row = $result->fetch_assoc())
			{
				$stmt->bind_param('si', $row['id'], $group_id);
				$stmt->execute();
			}

			$stmt->close();

			// Redirect
			return tpl::render('redirect', array(
				'redirect_message' => '<p>The new group has been successfully added. You will be redirected to a '.
				                      'page where you can set permissions for this group in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => utils::url('admin/groups/edit:'.$group_id)
				));
		}

		return tpl::render('redirect', array(
			'redirect_message' => '<p><strong>Some errors occured:</strong></p>'.
			                      ('<ul><li>'.implode('</li><li>', $errors).'</li></ul>').
			                      '<p>You will be redirected to the previous page in 10 seconds.</p>',
			'redirect_delay' => 10,
			'destination_url' => utils::url('admin/groups')
			));
	}
}
