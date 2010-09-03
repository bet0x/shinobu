<?php

# =============================================================================
# application/controllers/admin/groups/edit.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

# TODO:
# - List of groupmembers.
# - Store permissions temporarly on form submit.

class edit_controller extends CmsWebController
{
	private $_group_data;

	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->is_allowed('admin', 'groups'))
			$this->redirect(SYSTEM_BASE_URL);

		// Get usergroup data
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT g.* FROM '.DB_PREFIX.'usergroups AS g WHERE id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error);

		$this->_group_data = $result->fetch_assoc();
		if (is_null($this->_group_data))
			return $this->send_error(404);

		// Get permissions
		$result = $this->db->query('SELECT set_id, bits FROM '.DB_PREFIX.'permissions WHERE group_id='.$this->request['args'])
			or error($this->db->error);

		$this->_group_data['permissions'] = array();
		while ($row = $result->fetch_assoc())
			$this->_group_data['permissions'][$row['set_id']] = $row['bits'];
	}

	public function GET($args)
	{
		return tpl::render('admin_edit_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$this->_group_data['name'],
			'subsection' => 'groups',
			'values' => $this->_group_data,
			'errors' => array(),
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_admin_edit_group']) || !isset($args['xsrf_token'])
		    || !xsrf::check_cookie($args['xsrf_token']))
			$this->redirect(url('admin/groups'));

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

		if (empty($errors))
		{
			$this->db->query('UPDATE '.DB_PREFIX.'usergroups SET
				name="'.$this->db->escape($args['form']['name']).'",
				user_title="'.$this->db->escape($args['form']['user_title']).'",
				description="'.$this->db->escape($args['form']['description']).'"
				WHERE id='.$this->request['args'])
				or error($this->db->error);

			// Store permissions
			if (isset($args['perm']))
			{
				$stmt = $this->db->prepare('UPDATE '.DB_PREFIX.'permissions SET bits=? WHERE set_id=? AND group_id=?')
					or error($this->db->error);

				foreach (_permission_struct::$sets as $set_id => $set)
				{
					$bits = 0;
					foreach ($set as $perm_id => $bit)
					{
						if (isset($args['perm'][$set_id][$perm_id]))
							$bits |= $bit;
					}

					$stmt->bind_param('isi', $bits, $set_id, $this->request['args']);
					$stmt->execute();
				}

				$stmt->close();
			}
			else
				$this->db->query('UPDATE '.DB_PREFIX.'permissions SET bits=0 WHERE group_id='.$this->request['args'])
					or error($this->db->error);

			// Redirect
			return tpl::render('redirect', array(
				'redirect_message' => '<p>All the group settings have been successfully updated. You will be redirected to the '.
				                      'group overview in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/groups')
				));
		}

		return tpl::render('admin_edit_group', array(
			'website_section' => 'Administration',
			'page_title' => 'Group: '.$this->_group_data['name'],
			'subsection' => 'groups',
			'values' => $this->_group_data,
			'errors' => $errors,
			));
	}
}
