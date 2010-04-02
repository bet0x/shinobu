<?php

# =============================================================================
# site/controllers/admin/menu/edit.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class edit_controller extends CmsWebController
{
	private $_m_item_data = null;

	public function prepare()
	{
		if (!$this->user->authenticated || !$this->acl->check('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);

		// Get menu item information
		$this->request['args'] = intval($this->request['args']);
		$result = $this->db->query('SELECT m.* FROM '.DB_PREFIX.'menu AS m WHERE id='.$this->request['args'].' LIMIT 1')
			or error($this->db->error, __FILE__, __LINE__);

		$this->_m_item_data = $result->fetch_assoc();
		if (is_null($this->_m_item_data))
			return $this->send_error(404);
	}

	public function GET($args)
	{
		return tpl::render('admin_edit_menu_item', array(
			'website_section' => 'Administration',
			'page_title' => 'Edit item: '.$this->_m_item_data['name'],
			'subsection' => 'menu',
			'admin_perms' => $this->acl->get('administration'),
			'errors' => array(),
			'values' => $this->_m_item_data
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_edit_menu_item']))
			$this->redirect(url('admin/menu'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check name
		if (utf8_strlen($args['form']['name']) < 3)
			$errors['name'] = 'The name must be at least 3 characters long. Please choose another (longer) name.';
		elseif (utf8_strlen($args['form']['name']) > 255)
			$errors['name'] = 'The name must not be more than 255 characters long. Please choose another (shorter) name.';

		// Check path
		if (utf8_strlen($args['form']['path']) < 1)
			$errors['path'] = 'The path must be at least 1 character long. Please choose another (longer) path.';
		elseif (utf8_strlen($args['form']['path']) > 255)
			$errors['path'] = 'The path must not be more than 255 characters long. Please choose another (shorter) path.';

		// Check position
		$args['form']['position'] = intval($args['form']['position']);
		if ($args['form']['position'] < 0)
			$errors['position'] = 'The position must not be lower than 0. Please choose a higher number.';
		elseif ($args['form']['position'] > 255)
			$errors['position'] = 'The position must not be higher than 255. Please choose a lower number.';

		if (empty($errors))
		{
			$this->db->query('UPDATE '.DB_PREFIX.'menu SET
				name="'.$this->db->escape($args['form']['name']).'",
				path="'.$this->db->escape($args['form']['path']).'",
				position="'.$this->db->escape($args['form']['position']).'"
				WHERE id='.$this->request['args'])
				or error($this->db->error, __FILE__, __LINE__);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The menu item has been updated. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/menu')
				));
		}

		return tpl::render('admin_edit_menu_item', array(
			'website_section' => 'Administration',
			'page_title' => 'Edit item: '.$this->_m_item_data['name'],
			'subsection' => 'menu',
			'admin_perms' => $this->acl->get('administration'),
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
