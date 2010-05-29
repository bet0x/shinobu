<?php

# =============================================================================
# application/controllers/admin/menu/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends CmsWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_4))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_add_menu_item', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new item',
			'subsection' => 'menu',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => array(),
			'values' => array(
				'name' => '',
				'path' => '',
				'position' => 0)
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_add_menu_item']))
			$this->redirect(url('admin/menu'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();

		// Check name
		if (utf8_strlen($args['form']['name']) < 1)
			$errors['name'] = 'The name must be at least 1 character long. Please choose another (longer) name.';
		elseif (utf8_strlen($args['form']['name']) > 255)
			$errors['name'] = 'The name must not be more than 255 characters long. Please choose another (shorter) name.';

		// Check path
		if (utf8_strlen($args['form']['path']) < 1)
			$errors['path'] = 'The path must be at least 1 character long. Please choose a longer path.';
		elseif (utf8_strlen($args['form']['path']) > 255)
			$errors['path'] = 'The path must not be more than 255 characters long. Please choose a shorter path.';

		// Check position
		$args['form']['position'] = intval($args['form']['position']);
		if ($args['form']['position'] < 0)
			$errors['position'] = 'The position must not be lower than 0. Please choose a higher number.';
		elseif ($args['form']['position'] > 255)
			$errors['position'] = 'The position must not be higher than 255. Please choose a lower number.';

		if (count($errors) === 0)
		{
			$this->db->query('INSERT INTO '.DB_PREFIX.'menu (name, path, position) VALUES(
				"'.$this->db->escape($args['form']['name']).'",
				"'.$this->db->escape($args['form']['path']).'",
				"'.$this->db->escape($args['form']['position']).'")')
				or error($this->db->error);

			cache::clear('main_menu.json');

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The menu item has been successfully added. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/menu')
				));
		}

		return tpl::render('admin_add_menu_item', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new item',
			'subsection' => 'menu',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
