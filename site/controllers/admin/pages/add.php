<?php

# =============================================================================
# site/controllers/admin/pages/add.php
#
# Copyright (c) 2009-2010 Frank Smit
# License: zlib/libpng, see the COPYING file for details
# =============================================================================

class add_controller extends CmsWebController
{
	public function prepare()
	{
		if (!$this->user->authenticated || !$this->user->check_acl('administration', ACL_PERM_2))
			$this->redirect(SYSTEM_BASE_URL);
	}

	public function GET($args)
	{
		return tpl::render('admin_add_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new page',
			'subsection' => 'pages',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => array(),
			'values' => array(
				'title' => '',
				'content' => '',
				'is_published' => 0,
				'is_private' => 0,
				'show_meta' => 0)
			));
	}

	public function POST($args)
	{
		if (!isset($args['form_add_page']))
			$this->redirect(url('admin/pages'));

		if (!isset($args['xsrf_token']) || !xsrf::check_cookie($args['xsrf_token']))
			return $this->send_error(403);

		$args['form'] = array_map('trim', $args['form']);
		$errors = array();
		$now = time();

		// Check title
		if (utf8_strlen($args['form']['title']) < 1)
			$errors['title'] = 'The title must be at least 1 character long. Please choose another (longer) title.';
		elseif (utf8_strlen($args['form']['title']) > 255)
			$errors['title'] = 'The title must not be more than 255 characters long. Please choose another (shorter) title.';

		// Check content
		$args['form']['content'] = convert_linebreaks($args['form']['content']);
		if (utf8_strlen($args['form']['content']) < 1)
			$errors['content'] = 'A page can not be empty. Please provide some content.';
		elseif (utf8_strlen($args['form']['content']) > 65535)
			$errors['content'] = 'The page has too much content. Please remove some content.';

		// Check options
		$args['form']['is_published'] = isset($args['form']['is_published']) ? 1 : 0;
		$args['form']['is_private'] = isset($args['form']['is_private']) ? 1 : 0;
		$args['form']['show_meta'] = isset($args['form']['show_meta']) ? 1 : 0;

		if (empty($errors))
		{
			$this->db->query('INSERT INTO '.DB_PREFIX.'pages (author_id, title, content,
				is_published, is_private, show_meta, pub_date) VALUES(
				'.intval($this->user->data['id']).',
				"'.$this->db->escape($args['form']['title']).'",
				"'.$this->db->escape($args['form']['content']).'",
				'.$args['form']['is_published'].',
				'.$args['form']['is_private'].',
				'.$args['form']['show_meta'].',
				'.$now.')')
				or error($this->db->error);

			return tpl::render('redirect', array(
				'redirect_message' => '<p>The page has been successfully added. You will be redirected to the previous page in 2 seconds.</p>',
				'redirect_delay' => 2,
				'destination_url' => url('admin/pages')
				));
		}

		return tpl::render('admin_add_page', array(
			'website_section' => 'Administration',
			'page_title' => 'Add new page',
			'subsection' => 'pages',
			'admin_perms' => $this->user->get_acl('administration'),
			'errors' => $errors,
			'values' => $args['form']
			));
	}
}
